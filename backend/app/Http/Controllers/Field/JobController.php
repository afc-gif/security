<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\JobItemAttempt;
use App\Models\JobRequestItem;
use App\Services\CloudinaryImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class JobController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $availableJobs = JobRequestItem::query()
            ->available()
            ->with(['jobRequest.client', 'serviceCategory'])
            ->whereNotExists(function ($query) use ($userId) {
                $query->selectRaw('1')
                    ->from('job_item_attempts')
                    ->whereColumn('job_item_attempts.job_request_item_id', 'job_request_items.id')
                    ->where('job_item_attempts.user_id', $userId)
                    ->where('job_item_attempts.status', JobItemAttempt::STATUS_REJECTED);
            })
            ->latest('id')
            ->paginate(10, ['*'], 'available_page');

        $myJobs = JobRequestItem::query()
            ->with([
                'jobRequest.client',
                'serviceCategory',
                'attempts' => fn ($query) => $query->where('user_id', $userId)->latest('id'),
            ])
            ->where(function ($query) use ($userId) {
                $query->where(function ($claimedQuery) use ($userId) {
                    $claimedQuery->where('claimed_by', $userId)
                        ->whereIn('status', [
                            JobRequestItem::STATUS_CLAIMED,
                            JobRequestItem::STATUS_SUBMITTED,
                            JobRequestItem::STATUS_PENDING_ADMIN_REVIEW,
                            JobRequestItem::STATUS_RETURNED,
                            JobRequestItem::STATUS_APPROVED,
                            JobRequestItem::STATUS_OVERDUE,
                        ]);
                })->orWhereHas('attempts', function ($attemptQuery) use ($userId) {
                    $attemptQuery->where('user_id', $userId)
                        ->where('status', JobItemAttempt::STATUS_REJECTED);
                });
            })
            ->latest('claimed_at')
            ->latest('id')
            ->paginate(10, ['*'], 'my_page');

        return view('field.jobs.index', compact('availableJobs', 'myJobs'));
    }

    public function claim(Request $request, JobRequestItem $jobItem)
    {
        DB::transaction(function () use ($jobItem, $request) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->whereIn('status', [JobRequestItem::STATUS_OPEN, JobRequestItem::STATUS_REOPENED])
                ->where(function ($query) {
                    $query->whereNull('due_date')
                        ->orWhere('due_date', '>=', now());
                })
                ->lockForUpdate()
                ->first();

            if (!$lockedItem || $lockedItem->claimed_by !== null) {
                abort(409, 'This job has already been claimed or is no longer available.');
            }

            $lockedItem->update([
                'claimed_by' => $request->user()->id,
                'claimed_at' => now(),
                'status' => JobRequestItem::STATUS_CLAIMED,
            ]);
        });

        return redirect()
            ->route('field.jobs.index')
            ->with('success', 'Job claimed successfully.');
    }

    public function show(JobRequestItem $jobItem)
    {
        $this->authorizeClaimedJob($jobItem);

        $jobItem->load([
                'jobRequest.client',
                'serviceCategory',
                'attempts' => fn ($query) => $query
                    ->with(['requirements', 'media'])
                    ->where('user_id', auth()->id())
                    ->latest('id'),
        ]);

        $jobItem->markOverdueIfPast();
        $jobItem->refresh()->load([
            'jobRequest.client',
            'serviceCategory',
            'attempts' => fn ($query) => $query
                ->with(['requirements', 'media'])
                ->where('user_id', auth()->id())
                ->latest('id'),
        ]);

        return view('field.jobs.show', compact('jobItem'));
    }

    public function submit(Request $request, JobRequestItem $jobItem, CloudinaryImageService $cloudinary)
    {
        $validated = $request->validate([
            'notes' => 'required|string|min:5',
            'requirements' => 'required|array|min:1',
            'requirements.*.type' => 'required|in:material,task',
            'requirements.*.name' => 'required|string|max:255',
            'requirements.*.quantity' => 'nullable|string|max:100',
            'requirements.*.notes' => 'nullable|string',
            'media' => 'nullable|array',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $requirements = collect($validated['requirements'])
            ->map(fn ($requirement) => [
                'type' => $requirement['type'],
                'name' => trim($requirement['name']),
                'quantity' => isset($requirement['quantity']) && trim((string) $requirement['quantity']) !== ''
                    ? trim((string) $requirement['quantity'])
                    : null,
                'notes' => isset($requirement['notes']) && trim((string) $requirement['notes']) !== ''
                    ? trim((string) $requirement['notes'])
                    : null,
            ])
            ->values();

        $uploads = [];
        try {
            foreach ($request->file('media', []) as $file) {
                $uploads[] = [
                    'file' => $file,
                    'upload' => $cloudinary->uploadMedia($file, 'jobs/' . $jobItem->id . '/inspection'),
                ];
            }
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['media' => 'Photo upload failed. Please confirm Cloudinary is configured and try again.'])
                ->withInput();
        }

        DB::transaction(function () use ($jobItem, $request, $validated, $requirements, $uploads) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->where('claimed_by', $request->user()->id)
                ->whereIn('status', [JobRequestItem::STATUS_CLAIMED, JobRequestItem::STATUS_RETURNED])
                ->lockForUpdate()
                ->first();

            if (!$lockedItem) {
                abort(409, 'This job cannot be submitted in its current state.');
            }

            if ($lockedItem->isOverdue()) {
                $lockedItem->markOverdueIfPast();

                return;
            }

            $lockedItem->update([
                'status' => JobRequestItem::STATUS_SUBMITTED,
                'submitted_at' => now(),
            ]);

            $attempt = JobItemAttempt::create([
                'job_request_item_id' => $lockedItem->id,
                'user_id' => $request->user()->id,
                'status' => JobItemAttempt::STATUS_SUBMITTED,
                'notes' => $validated['notes'],
            ]);

            foreach ($requirements as $index => $requirement) {
                $attempt->requirements()->create([
                    'type' => $requirement['type'],
                    'name' => $requirement['name'],
                    'quantity' => $requirement['quantity'],
                    'notes' => $requirement['notes'],
                    'sort_order' => $index,
                ]);
            }

            foreach ($uploads as $stored) {
                $file = $stored['file'];
                $upload = $stored['upload'];

                $attempt->media()->create([
                    'uploaded_by' => $request->user()->id,
                    'file_path' => $upload['url'],
                    'cloudinary_public_id' => $upload['public_id'],
                    'cloudinary_resource_type' => $upload['resource_type'] ?? null,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        });

        $jobItem->refresh();

        if ($jobItem->status === JobRequestItem::STATUS_OVERDUE) {
            return redirect()
                ->route('field.jobs.show', $jobItem)
                ->withErrors(['deadline' => 'Submission deadline exceeded. Contact admin.']);
        }

        return redirect()
            ->route('field.jobs.show', $jobItem)
            ->with('success', 'Job submitted successfully. Awaiting review.');
    }

    private function authorizeClaimedJob(JobRequestItem $jobItem): void
    {
        if ((int) $jobItem->claimed_by !== (int) auth()->id()) {
            $hasRejectedAttempt = $jobItem->attempts()
                ->where('user_id', auth()->id())
                ->where('status', JobItemAttempt::STATUS_REJECTED)
                ->exists();

            if (!$hasRejectedAttempt) {
                abort(403, 'Unauthorized job access');
            }
        }
    }
}
