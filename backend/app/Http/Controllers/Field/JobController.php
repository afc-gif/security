<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\JobChecklistItem;
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
        $jobItem->ensureChecklistFromCategory();

        $jobItem->load([
                'jobRequest.client',
                'serviceCategory',
                'checklistItems',
                'attempts' => fn ($query) => $query
                    ->with(['requirements', 'media'])
                    ->where('user_id', auth()->id())
                    ->latest('id'),
        ]);

        $jobItem->markOverdueIfPast();
        $jobItem->refresh()->load([
            'jobRequest.client',
            'serviceCategory',
            'checklistItems',
            'attempts' => fn ($query) => $query
                ->with(['requirements', 'media'])
                ->where('user_id', auth()->id())
                ->latest('id'),
        ]);

        return view('field.jobs.show', compact('jobItem'));
    }

    public function submit(Request $request, JobRequestItem $jobItem, CloudinaryImageService $cloudinary)
    {
        $jobItem->ensureChecklistFromCategory();

        $validated = $request->validate([
            'notes' => 'required|string|min:5',
            'checklist' => 'nullable|array',
            'checklist.*.status' => 'nullable|in:pending,done,not_applicable',
            'checklist.*.response' => 'nullable',
            'checklist.*.notes' => 'nullable|string',
            'checklist.*.photos' => 'nullable|array',
            'checklist.*.photos.*' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'custom_checklist' => 'nullable|array',
            'custom_checklist.*.title' => 'nullable|string|max:255',
            'custom_checklist.*.status' => 'nullable|in:pending,done,not_applicable',
            'custom_checklist.*.notes' => 'nullable|string',
            'requirements' => 'nullable|array',
            'requirements.*.type' => 'required|in:material,task',
            'requirements.*.name' => 'nullable|string|max:255',
            'requirements.*.quantity' => 'nullable|string|max:100',
            'requirements.*.notes' => 'nullable|string',
            'media' => 'nullable|array',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $requirements = collect($validated['requirements'] ?? [])
            ->filter(fn ($requirement) => trim((string) ($requirement['name'] ?? '')) !== '')
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

        $customChecklistItems = collect($validated['custom_checklist'] ?? [])
            ->filter(fn ($item) => trim((string) ($item['title'] ?? '')) !== '')
            ->map(fn ($item) => [
                'title' => trim((string) $item['title']),
                'status' => $item['status'] ?? JobChecklistItem::STATUS_PENDING,
                'notes' => isset($item['notes']) && trim((string) $item['notes']) !== ''
                    ? trim((string) $item['notes'])
                    : null,
            ])
            ->values();

        $photoChecklistItems = $jobItem->checklistItems()
            ->where('input_type', 'photo')
            ->get(['id', 'title', 'is_required']);

        foreach ($photoChecklistItems as $photoChecklistItem) {
            $checklistInput = $validated['checklist'][$photoChecklistItem->id] ?? [];
            $status = $checklistInput['status'] ?? JobChecklistItem::STATUS_PENDING;
            $files = $this->normalizeUploadedFiles(data_get($request->file('checklist', []), "{$photoChecklistItem->id}.photos", []));

            if ($photoChecklistItem->is_required && $status !== JobChecklistItem::STATUS_NOT_APPLICABLE && count($files) === 0) {
                return back()
                    ->withErrors(["checklist.{$photoChecklistItem->id}.photos" => "Please upload at least one photo for {$photoChecklistItem->title}."])
                    ->withInput();
            }
        }

        $uploads = [];
        $checklistUploads = [];
        try {
            foreach ($request->file('media', []) as $file) {
                $uploads[] = [
                    'file' => $file,
                    'upload' => $cloudinary->uploadMedia($file, 'jobs/' . $jobItem->id . '/inspection'),
                ];
            }

            foreach ($photoChecklistItems as $photoChecklistItem) {
                $files = $this->normalizeUploadedFiles(data_get($request->file('checklist', []), "{$photoChecklistItem->id}.photos", []));

                foreach ($files as $file) {
                    $checklistUploads[$photoChecklistItem->id][] = [
                        'file' => $file,
                        'upload' => $cloudinary->uploadMedia($file, 'jobs/' . $jobItem->id . '/checklist/' . $photoChecklistItem->id),
                    ];
                }
            }
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['media' => 'Photo upload failed. Please confirm Cloudinary is configured and try again.'])
                ->withInput();
        }

        DB::transaction(function () use ($jobItem, $request, $validated, $requirements, $customChecklistItems, $uploads, $checklistUploads) {
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

            $lockedItem->ensureChecklistFromCategory();

            foreach (($validated['checklist'] ?? []) as $checklistItemId => $checklistInput) {
                $status = $checklistInput['status'] ?? null;
                $notes = isset($checklistInput['notes']) && trim((string) $checklistInput['notes']) !== ''
                    ? trim((string) $checklistInput['notes'])
                    : null;
                $response = $checklistInput['response'] ?? null;

                if (in_array($status, [null, '', JobChecklistItem::STATUS_PENDING], true) && $this->checklistInputHasResponse($checklistInput)) {
                    $status = JobChecklistItem::STATUS_DONE;
                }

                if (is_array($response)) {
                    $response = collect($response)
                        ->filter(fn ($value) => trim((string) $value) !== '')
                        ->implode(', ');
                }

                JobChecklistItem::query()
                    ->where('id', $checklistItemId)
                    ->where('job_request_item_id', $lockedItem->id)
                    ->update([
                        'status' => $status,
                        'response' => trim((string) $response) !== '' ? trim((string) $response) : null,
                        'notes' => $notes,
                        'completed_by' => $status === JobChecklistItem::STATUS_DONE ? $request->user()->id : null,
                        'completed_at' => $status === JobChecklistItem::STATUS_DONE ? now() : null,
                    ]);
            }

            $nextSortOrder = (int) $lockedItem->checklistItems()->max('sort_order') + 1;

            foreach ($customChecklistItems as $index => $item) {
                $lockedItem->checklistItems()->create([
                    'added_by' => $request->user()->id,
                    'title' => $item['title'],
                    'status' => $item['status'],
                    'notes' => $item['notes'],
                    'is_required' => false,
                    'is_custom' => true,
                    'sort_order' => $nextSortOrder + $index,
                    'completed_by' => $item['status'] === JobChecklistItem::STATUS_DONE ? $request->user()->id : null,
                    'completed_at' => $item['status'] === JobChecklistItem::STATUS_DONE ? now() : null,
                ]);
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

            foreach ($checklistUploads as $checklistItemId => $storedFiles) {
                foreach ($storedFiles as $stored) {
                    $file = $stored['file'];
                    $upload = $stored['upload'];

                    $attempt->media()->create([
                        'job_checklist_item_id' => $checklistItemId,
                        'uploaded_by' => $request->user()->id,
                        'file_path' => $upload['url'],
                        'cloudinary_public_id' => $upload['public_id'],
                        'cloudinary_resource_type' => $upload['resource_type'] ?? null,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
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

    private function checklistInputHasResponse(array $checklistInput): bool
    {
        $response = data_get($checklistInput, 'response');
        $notes = data_get($checklistInput, 'notes');
        $photos = data_get($checklistInput, 'photos');

        if (is_array($response)) {
            return collect($response)->contains(fn ($value) => trim((string) $value) !== '');
        }

        if (is_string($response) && trim($response) !== '') {
            return true;
        }

        if (is_string($notes) && trim($notes) !== '') {
            return true;
        }

        if ($photos instanceof \Illuminate\Http\UploadedFile) {
            return true;
        }

        if (is_array($photos)) {
            return collect($photos)->contains(fn ($file) => $file instanceof \Illuminate\Http\UploadedFile);
        }

        return false;
    }

    private function normalizeUploadedFiles(mixed $files): array
    {
        if ($files instanceof \Illuminate\Http\UploadedFile) {
            return [$files];
        }

        if (!is_array($files)) {
            return [];
        }

        return collect($files)
            ->filter(fn ($file) => $file instanceof \Illuminate\Http\UploadedFile)
            ->values()
            ->all();
    }
}
