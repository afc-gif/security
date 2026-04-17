<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobItemAttempt;
use App\Models\JobRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JobItemController extends Controller
{
    public function show(JobRequestItem $jobItem)
    {
        $jobItem->markOverdueIfPast();
        $jobItem->refresh();

        $jobItem->load([
            'jobRequest.client',
            'serviceCategory',
            'claimer',
            'attempts' => fn ($query) => $query->with('user')->latest('id'),
        ]);

        return view('admin.job-items.show', compact('jobItem'));
    }

    public function review(Request $request, JobRequestItem $jobItem)
    {
        $validated = $request->validate(
            [
                'action' => ['required', Rule::in(['approve', 'return', 'reject'])],
                'admin_note' => [
                    Rule::requiredIf(fn () => in_array($request->input('action'), ['return', 'reject'], true)),
                    'nullable',
                    'string',
                ],
            ],
            [
                'admin_note.required' => 'Please add an admin note for returned or rejected jobs.',
            ]
        );

        $action = $validated['action'];
        $adminNote = trim((string) ($validated['admin_note'] ?? ''));

        if (in_array($action, ['return', 'reject'], true) && $adminNote === '') {
            return back()
                ->withErrors(['admin_note' => 'Please add an admin note for returned or rejected jobs.'])
                ->withInput();
        }

        DB::transaction(function () use ($jobItem, $action, $adminNote) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->where('status', JobRequestItem::STATUS_SUBMITTED)
                ->lockForUpdate()
                ->first();

            if (!$lockedItem) {
                abort(409, 'This job item is not awaiting review.');
            }

            $latestAttempt = JobItemAttempt::query()
                ->where('job_request_item_id', $lockedItem->id)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$latestAttempt || $latestAttempt->status !== JobItemAttempt::STATUS_SUBMITTED) {
                abort(409, 'No submitted attempt is available for review.');
            }

            match ($action) {
                'approve' => $this->approve($lockedItem, $latestAttempt, $adminNote),
                'return' => $this->returnForFix($lockedItem, $latestAttempt, $adminNote),
                'reject' => $this->rejectAndReopen($lockedItem, $latestAttempt, $adminNote),
            };
        });

        return redirect()
            ->route('admin.job-items.show', $jobItem)
            ->with('success', match ($action) {
                'approve' => 'Job approved.',
                'return' => 'Job returned for correction.',
                'reject' => 'Job rejected and reopened.',
            });
    }

    public function reopen(Request $request, JobRequestItem $jobItem)
    {
        $validated = $request->validate([
            'admin_note' => 'nullable|string',
        ]);

        DB::transaction(function () use ($jobItem, $request, $validated) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->whereIn('status', [
                    JobRequestItem::STATUS_OVERDUE,
                    JobRequestItem::STATUS_CLOSED,
                    JobRequestItem::STATUS_REJECTED,
                ])
                ->lockForUpdate()
                ->first();

            if (!$lockedItem) {
                abort(409, 'This job item cannot be reopened in its current state.');
            }

            $lockedItem->update([
                'status' => JobRequestItem::STATUS_REOPENED,
                'claimed_by' => null,
                'claimed_at' => null,
                'submitted_at' => null,
                'reopened_at' => now(),
            ]);

            $adminNote = trim((string) ($validated['admin_note'] ?? ''));

            if ($adminNote !== '') {
                JobItemAttempt::create([
                    'job_request_item_id' => $lockedItem->id,
                    'user_id' => $request->user()->id,
                    'status' => JobRequestItem::STATUS_REOPENED,
                    'notes' => "Reopened by admin: {$adminNote}",
                ]);
            }
        });

        return redirect()
            ->route('admin.job-items.show', $jobItem)
            ->with('success', 'Job reopened successfully.');
    }

    private function approve(JobRequestItem $jobItem, JobItemAttempt $attempt, string $adminNote): void
    {
        $jobItem->update([
            'status' => JobRequestItem::STATUS_APPROVED,
        ]);

        $attempt->update([
            'status' => JobItemAttempt::STATUS_APPROVED,
            'notes' => $this->withAdminNote($attempt->notes, $adminNote),
        ]);
    }

    private function returnForFix(JobRequestItem $jobItem, JobItemAttempt $attempt, string $adminNote): void
    {
        $jobItem->update([
            'status' => JobRequestItem::STATUS_RETURNED,
            'submitted_at' => null,
        ]);

        $attempt->update([
            'status' => JobItemAttempt::STATUS_RETURNED,
            'notes' => $this->withAdminNote($attempt->notes, $adminNote),
        ]);
    }

    private function rejectAndReopen(JobRequestItem $jobItem, JobItemAttempt $attempt, string $adminNote): void
    {
        $jobItem->update([
            'status' => JobRequestItem::STATUS_REOPENED,
            'claimed_by' => null,
            'claimed_at' => null,
            'submitted_at' => null,
            'reopened_at' => now(),
        ]);

        $attempt->update([
            'status' => JobItemAttempt::STATUS_REJECTED,
            'notes' => $this->withAdminNote($attempt->notes, $adminNote),
        ]);
    }

    private function withAdminNote(?string $notes, string $adminNote): ?string
    {
        if ($adminNote === '') {
            return $notes;
        }

        $existingNotes = trim((string) $notes);

        if ($existingNotes === '') {
            return "Admin note: {$adminNote}";
        }

        return "{$existingNotes}\n\nAdmin note: {$adminNote}";
    }
}
