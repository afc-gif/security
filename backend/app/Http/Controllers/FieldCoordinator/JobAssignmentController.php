<?php

namespace App\Http\Controllers\FieldCoordinator;

use App\Http\Controllers\Controller;
use App\Models\JobChecklistItem;
use App\Models\JobItemAttempt;
use App\Models\JobRequestItem;
use App\Models\User;
use App\Services\TransportFareNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JobAssignmentController extends Controller
{
    public function index()
    {
        $pendingJobs = JobRequestItem::query()
            ->with(['jobRequest.client', 'serviceCategory', 'creator', 'checklistItems'])
            ->where('status', JobRequestItem::STATUS_PENDING_ASSIGNMENT)
            ->latest('id')
            ->paginate(15);

        $fieldStaff = User::query()
            ->where('status', 'approved')
            ->whereIn('role', ['field_staff', 'field_coordinator'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        $submittedJobs = JobRequestItem::query()
            ->with([
                'jobRequest.client',
                'serviceCategory',
                'checklistItems.addedBy',
                'checklistItems.completedBy',
                'checklistItems.media',
                'claimer',
                'attempts' => fn ($query) => $query
                    ->with(['user', 'requirements', 'media.uploader'])
                    ->latest('id'),
            ])
            ->where('status', JobRequestItem::STATUS_SUBMITTED)
            ->latest('submitted_at')
            ->latest('id')
            ->paginate(15, ['*'], 'review_page');

        return view('coordinator.jobs.index', compact('pendingJobs', 'fieldStaff', 'submittedJobs'));
    }

    public function assign(Request $request, JobRequestItem $jobItem, TransportFareNotificationService $fareNotifications)
    {
        $validated = $request->validate([
            'assigned_to' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('status', 'approved')
                    ->whereIn('role', ['field_staff', 'field_coordinator'])),
            ],
        ]);

        $assignedJob = DB::transaction(function () use ($jobItem, $validated) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->where('status', JobRequestItem::STATUS_PENDING_ASSIGNMENT)
                ->whereNull('claimed_by')
                ->lockForUpdate()
                ->first();

            if (!$lockedItem) {
                abort(409, 'This job is no longer pending assignment.');
            }

            $lockedItem->update([
                'claimed_by' => $validated['assigned_to'],
                'claimed_at' => now(),
                'status' => JobRequestItem::STATUS_CLAIMED,
            ]);

            return $lockedItem->fresh(['jobRequest.client', 'serviceCategory']);
        });

        $assignedStaff = User::findOrFail($validated['assigned_to']);
        $whatsappUrl = $fareNotifications->notifyAssignedJob($assignedJob, $assignedStaff);

        return redirect()
            ->route('coordinator.jobs.index')
            ->with('success', 'Job assigned successfully.')
            ->with('whatsapp_url', $whatsappUrl);
    }

    public function addChecklistItem(Request $request, JobRequestItem $jobItem)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($jobItem, $request, $validated) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->whereIn('status', [
                    JobRequestItem::STATUS_PENDING_ASSIGNMENT,
                    JobRequestItem::STATUS_CLAIMED,
                    JobRequestItem::STATUS_RETURNED,
                ])
                ->lockForUpdate()
                ->first();

            if (!$lockedItem) {
                abort(409, 'Checklist cannot be changed for this job status.');
            }

            $lockedItem->ensureChecklistFromCategory();

            $lockedItem->checklistItems()->create([
                'added_by' => $request->user()->id,
                'title' => trim($validated['title']),
                'description' => isset($validated['description']) && trim((string) $validated['description']) !== ''
                    ? trim((string) $validated['description'])
                    : null,
                'status' => 'pending',
                'is_required' => false,
                'is_custom' => true,
                'sort_order' => ((int) $lockedItem->checklistItems()->max('sort_order')) + 1,
            ]);
        });

        return redirect()
            ->route('coordinator.jobs.index')
            ->with('success', 'Checklist item added.');
    }

    public function destroyChecklistItem(JobRequestItem $jobItem, JobChecklistItem $checklistItem)
    {
        abort_unless((int) $checklistItem->job_request_item_id === (int) $jobItem->id, 404);

        if (!in_array($jobItem->status, [
            JobRequestItem::STATUS_PENDING_ASSIGNMENT,
            JobRequestItem::STATUS_CLAIMED,
            JobRequestItem::STATUS_RETURNED,
        ], true)) {
            abort(409, 'Checklist cannot be changed for this job status.');
        }

        $checklistItem->delete();

        return redirect()
            ->route('coordinator.jobs.index')
            ->with('success', 'Checklist item removed.');
    }

    public function claim(JobRequestItem $jobItem, TransportFareNotificationService $fareNotifications)
    {
        $assignedJob = DB::transaction(function () use ($jobItem) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->where('status', JobRequestItem::STATUS_PENDING_ASSIGNMENT)
                ->whereNull('claimed_by')
                ->lockForUpdate()
                ->first();

            if (!$lockedItem) {
                abort(409, 'This job is no longer pending assignment.');
            }

            $lockedItem->update([
                'claimed_by' => auth()->id(),
                'claimed_at' => now(),
                'status' => JobRequestItem::STATUS_CLAIMED,
            ]);

            return $lockedItem->fresh(['jobRequest.client', 'serviceCategory']);
        });

        $whatsappUrl = $fareNotifications->notifyAssignedJob($assignedJob, auth()->user());

        return redirect()
            ->route('coordinator.jobs.index')
            ->with('success', 'Job assigned to you.')
            ->with('whatsapp_url', $whatsappUrl);
    }

    public function release(JobRequestItem $jobItem)
    {
        DB::transaction(function () use ($jobItem) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->where('status', JobRequestItem::STATUS_PENDING_ASSIGNMENT)
                ->whereNull('claimed_by')
                ->lockForUpdate()
                ->first();

            if (!$lockedItem) {
                abort(409, 'This job is no longer pending assignment.');
            }

            $lockedItem->update([
                'status' => JobRequestItem::STATUS_OPEN,
            ]);
        });

        return redirect()
            ->route('coordinator.jobs.index')
            ->with('success', 'Job released for field staff claim.');
    }

    public function review(Request $request, JobRequestItem $jobItem)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['approve', 'return'])],
            'coordinator_note' => [
                Rule::requiredIf(fn () => $request->input('action') === 'return'),
                'nullable',
                'string',
            ],
        ], [
            'coordinator_note.required' => 'Please add a note when returning a report for correction.',
        ]);

        $action = $validated['action'];
        $coordinatorNote = trim((string) ($validated['coordinator_note'] ?? ''));

        if ($action === 'return' && $coordinatorNote === '') {
            return back()
                ->withErrors(['coordinator_note' => 'Please add a note when returning a report for correction.'])
                ->withInput();
        }

        DB::transaction(function () use ($jobItem, $action, $coordinatorNote) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->where('status', JobRequestItem::STATUS_SUBMITTED)
                ->lockForUpdate()
                ->first();

            if (!$lockedItem) {
                abort(409, 'This job report is no longer waiting for coordinator review.');
            }

            $latestAttempt = JobItemAttempt::query()
                ->where('job_request_item_id', $lockedItem->id)
                ->where('status', JobItemAttempt::STATUS_SUBMITTED)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$latestAttempt) {
                abort(409, 'No submitted report is available for review.');
            }

            if ($action === 'approve') {
                $lockedItem->update([
                    'status' => JobRequestItem::STATUS_PENDING_ADMIN_REVIEW,
                ]);

                $latestAttempt->update([
                    'status' => JobItemAttempt::STATUS_COORDINATOR_APPROVED,
                    'notes' => $this->withCoordinatorNote($latestAttempt->notes, $coordinatorNote),
                ]);

                return;
            }

            $lockedItem->update([
                'status' => JobRequestItem::STATUS_RETURNED,
                'submitted_at' => null,
            ]);

            $latestAttempt->update([
                'status' => JobItemAttempt::STATUS_RETURNED,
                'notes' => $this->withCoordinatorNote($latestAttempt->notes, $coordinatorNote),
            ]);
        });

        return redirect()
            ->route('coordinator.jobs.index')
            ->with('success', $action === 'approve'
                ? 'Report approved and sent to admin.'
                : 'Report returned to field staff for correction.');
    }

    private function withCoordinatorNote(?string $notes, string $coordinatorNote): ?string
    {
        if ($coordinatorNote === '') {
            return $notes;
        }

        $existingNotes = trim((string) $notes);

        if ($existingNotes === '') {
            return "Coordinator note: {$coordinatorNote}";
        }

        return "{$existingNotes}\n\nCoordinator note: {$coordinatorNote}";
    }
}
