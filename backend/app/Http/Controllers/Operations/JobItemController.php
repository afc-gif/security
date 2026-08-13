<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\FinancialExpense;
use App\Models\FinancialMaterialCost;
use App\Models\JobItemAttempt;
use App\Models\JobRequestItem;
use App\Models\Project;
use App\Models\ProjectPayment;
use App\Models\User;
use App\Services\TransportFareNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
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
            'project',
            'checklistItems.addedBy',
            'checklistItems.completedBy',
            'attempts' => fn ($query) => $query
                ->with(['user', 'requirements', 'media.uploader'])
                ->latest('created_at')
                ->latest('id'),
        ]);

        $latestAttempt = $jobItem->attempts->first();

        return view('admin.job-items.show', compact('jobItem', 'latestAttempt'));
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
                'requirements' => 'nullable|array',
                'requirements.*.type' => 'nullable|in:material,task',
                'requirements.*.include' => 'nullable',
                'requirements.*.name' => 'nullable|string|max:255',
                'requirements.*.quantity' => 'nullable|string|max:100',
                'requirements.*.notes' => 'nullable|string',
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

        $rawRequirements = $validated['requirements'] ?? $request->input('requirements', []);
        $requirements = $action === 'approve'
            ? $this->normalizedRequirements(is_array($rawRequirements) ? $rawRequirements : [])
            : collect();

        if ($action === 'approve' && $requirements->isEmpty()) {
            return back()
                ->withErrors(['requirements' => 'Please keep at least one approved requirement before approving this job.'])
                ->withInput();
        }

        $errorMessage = DB::transaction(function () use ($jobItem, $action, $adminNote, $requirements) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedItem || !in_array($lockedItem->status, [
                JobRequestItem::STATUS_PENDING_ADMIN_REVIEW,
                JobRequestItem::STATUS_SUBMITTED,
            ], true)) {
                return 'This job item is not awaiting review.';
            }

            $latestAttempt = JobItemAttempt::query()
                ->where('job_request_item_id', $lockedItem->id)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$latestAttempt || !in_array($latestAttempt->status, [JobItemAttempt::STATUS_COORDINATOR_APPROVED, JobItemAttempt::STATUS_SUBMITTED], true)) {
                return 'No submitted or coordinator-approved attempt is available for review.';
            }

            match ($action) {
                'approve' => $this->approve($lockedItem, $latestAttempt, $adminNote, $requirements),
                'return' => $this->returnForFix($lockedItem, $latestAttempt, $adminNote),
                'reject' => $this->rejectAndReopen($lockedItem, $latestAttempt, $adminNote),
            };

            return null;
        });

        if ($errorMessage) {
            return back()->withErrors(['review' => $errorMessage])->withInput();
        }

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
            'due_date' => 'nullable|date',
        ]);

        $errorMessage = DB::transaction(function () use ($jobItem, $request, $validated) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedItem || (!$lockedItem->isOverdue() && !in_array($lockedItem->status, [
                JobRequestItem::STATUS_OVERDUE,
                JobRequestItem::STATUS_CLOSED,
                JobRequestItem::STATUS_REJECTED,
                JobRequestItem::STATUS_OPEN,
                JobRequestItem::STATUS_CLAIMED,
                JobRequestItem::STATUS_RETURNED,
            ], true))) {
                return 'This job item cannot be reopened in its current state.';
            }

            $updatePayload = [
                'status' => JobRequestItem::STATUS_REOPENED,
                'claimed_by' => null,
                'claimed_at' => null,
                'submitted_at' => null,
                'reopened_at' => now(),
            ];

            if (isset($validated['due_date'])) {
                $updatePayload['due_date'] = $validated['due_date'];
            } elseif ($lockedItem->due_date && now()->greaterThan($lockedItem->due_date)) {
                $updatePayload['due_date'] = null;
            }

            $lockedItem->update($updatePayload);

            $adminNote = trim((string) ($validated['admin_note'] ?? ''));
            $userId = $request->user()?->id;

            if ($userId) {
                JobItemAttempt::create([
                    'job_request_item_id' => $lockedItem->id,
                    'user_id' => $userId,
                    'status' => JobItemAttempt::STATUS_RETURNED,
                    'notes' => $adminNote !== '' ? "Reopened by admin: {$adminNote}" : 'Reopened by admin',
                ]);
            }

            return null;
        });

        if ($errorMessage) {
            return back()->withErrors(['reopen' => $errorMessage])->withInput();
        }

        return redirect()
            ->route('admin.job-items.show', $jobItem)
            ->with('success', 'Job reopened successfully.');
    }

    public function assign(Request $request, JobRequestItem $jobItem, TransportFareNotificationService $fareNotifications)
    {
        /** @var User|null $authUser */
        $authUser = auth()->user();

        if (!$authUser || (!$authUser->isSuperAdmin() && !$authUser->isCoordinator() && !$authUser->isAdmin())) {
            abort(403, 'Unauthorized to assign jobs.');
        }

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
                ->lockForUpdate()
                ->firstOrFail();

            $lockedItem->update([
                'claimed_by' => $validated['assigned_to'],
                'claimed_at' => now(),
                'status' => JobRequestItem::STATUS_CLAIMED,
            ]);

            return $lockedItem->fresh(['jobRequest.client', 'serviceCategory']);
        });

        $assignedStaff = User::findOrFail($validated['assigned_to']);
        $whatsappUrl = $fareNotifications->notifyAssignedJob($assignedJob, $assignedStaff);

        Log::info('Job assigned to field staff', [
            'assigned_by' => $authUser->id,
            'job_item_id' => $jobItem->id,
            'assigned_to' => $assignedStaff->id,
        ]);

        return back()
            ->with('success', 'Job assigned successfully.')
            ->with('whatsapp_url', $whatsappUrl);
    }

    public function convertToProject(JobRequestItem $jobItem)
    {
        /** @var User|null $authUser */
        $authUser = auth()->user();

        $jobItem->load(['jobRequest.client', 'serviceCategory', 'project']);

        if ($jobItem->project) {
            return redirect()
                ->route('admin.projects.show', $jobItem->project)
                ->with('success', 'This category item has already been converted to a project.');
        }

        // Non-approved jobs can ONLY be directly converted by Super Admin
        if ($jobItem->status !== JobRequestItem::STATUS_APPROVED) {
            if ($authUser?->isSuperAdmin()) {
                // Super Admin direct conversion bypass allowed
            } elseif ($authUser && in_array($authUser->role, ['field_staff', 'field_coordinator', 'pos', 'finance'], true)) {
                abort(403, 'Unauthorized to convert jobs.');
            } else {
                return back()->withErrors(['conversion' => 'Only approved category items can be converted to a project.']);
            }
        }

        $project = DB::transaction(function () use ($jobItem, $authUser) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedItem->status !== JobRequestItem::STATUS_APPROVED) {
                if ($authUser?->isSuperAdmin()) {
                    // Super Admin direct conversion bypass allowed
                } elseif ($authUser && in_array($authUser->role, ['field_staff', 'field_coordinator', 'pos', 'finance'], true)) {
                    abort(403, 'Unauthorized to convert jobs.');
                } else {
                    return back()->withErrors(['conversion' => 'Only approved category items can be converted to a project.']);
                }
            }

            if ($lockedItem->project) {
                return $lockedItem->project;
            }

            $lockedItem->load([
                'jobRequest.client',
                'serviceCategory',
                'project',
                'attempts' => fn ($query) => $query
                    ->where('status', JobItemAttempt::STATUS_APPROVED)
                    ->with('requirements')
                    ->latest('id'),
            ]);

            $projectPayload = [
                'project_code' => $this->generateProjectCode(),
                'job_request_item_id' => $lockedItem->id,
                'client_id' => $lockedItem->jobRequest->client_id,
                'title' => $this->buildProjectTitle($lockedItem),
                'description' => $this->buildProjectDescription($lockedItem),
                'status' => 'not_started',
                'priority' => $lockedItem->priority ?? 'medium',
                'deadline' => $lockedItem->due_date?->toDateString(),
                'created_by' => $authUser?->id,
            ];

            if ($lockedItem->claimed_by && Schema::hasColumn('projects', 'assigned_field_staff_id')) {
                $projectPayload['assigned_field_staff_id'] = $lockedItem->claimed_by;
            }

            $project = Project::create($projectPayload);
            $this->attachJobItemFinanceToProject($lockedItem, $project, (int) $authUser?->id);

            $approvedAttempt = $lockedItem->attempts->first();
            foreach (($approvedAttempt?->requirements ?? collect()) as $requirement) {
                $project->requirements()->create([
                    'type' => $requirement->type,
                    'name' => $requirement->name,
                    'quantity' => $requirement->quantity,
                    'notes' => $requirement->notes,
                    'sort_order' => $requirement->sort_order,
                ]);
            }

            Log::info('Job converted to project', [
                'converted_by' => $authUser?->id,
                'is_super_admin' => $authUser?->isSuperAdmin(),
                'job_request_item_id' => $lockedItem->id,
                'project_id' => $project->id,
                'original_job_status' => $lockedItem->status,
            ]);

            return $project;
        });

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Category item converted to project successfully.');
    }

    private function attachJobItemFinanceToProject(JobRequestItem $jobItem, Project $project, int $userId): void
    {
        FinancialExpense::query()
            ->where('job_request_item_id', $jobItem->id)
            ->whereNull('project_id')
            ->update([
                'project_id' => $project->id,
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);

        FinancialMaterialCost::query()
            ->where('job_request_item_id', $jobItem->id)
            ->whereNull('project_id')
            ->update([
                'project_id' => $project->id,
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);

        ProjectPayment::query()
            ->where(function ($query) use ($jobItem) {
                $query->where('job_request_item_id', $jobItem->id)
                    ->orWhere('job_request_id', $jobItem->job_request_id);
            })
            ->whereNull('project_id')
            ->update([
                'project_id' => $project->id,
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);
    }

    private function approve(JobRequestItem $jobItem, JobItemAttempt $attempt, string $adminNote, array|Collection $requirements): void
    {
        $jobItem->update([
            'status' => JobRequestItem::STATUS_APPROVED,
        ]);

        $attempt->requirements()->delete();
        foreach ($requirements as $index => $requirement) {
            $attempt->requirements()->create([
                'type' => $requirement['type'] ?? 'material',
                'name' => $requirement['name'] ?? '',
                'quantity' => $requirement['quantity'] ?? null,
                'notes' => $requirement['notes'] ?? null,
                'sort_order' => $index,
            ]);
        }

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

    private function normalizedRequirements(array $requirements)
    {
        return collect($requirements)
            ->map(fn ($requirement) => [
                'include' => (bool) ($requirement['include'] ?? false),
                'type' => $requirement['type'] ?? 'material',
                'name' => trim((string) ($requirement['name'] ?? '')),
                'quantity' => isset($requirement['quantity']) && trim((string) $requirement['quantity']) !== ''
                    ? trim((string) $requirement['quantity'])
                    : null,
                'notes' => isset($requirement['notes']) && trim((string) $requirement['notes']) !== ''
                    ? trim((string) $requirement['notes'])
                    : null,
            ])
            ->filter(fn ($requirement) => $requirement['include'] && $requirement['name'] !== '')
            ->values();
    }

    private function generateProjectCode(): string
    {
        do {
            $code = 'PROJ-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4));
        } while (Project::where('project_code', $code)->exists());

        return $code;
    }

    private function buildProjectTitle(JobRequestItem $jobItem): string
    {
        $category = $jobItem->serviceCategory?->name ?? $jobItem->title ?? 'Service';
        $client = $jobItem->jobRequest?->client?->client_name;

        return Str::limit(trim($category . ($client ? " - {$client}" : '')), 255, '');
    }

    private function buildProjectDescription(JobRequestItem $jobItem): ?string
    {
        $parts = [];

        if ($jobItem->jobRequest?->title) {
            $parts[] = "Job Request: {$jobItem->jobRequest->title}";
        }

        if ($jobItem->jobRequest?->description) {
            $parts[] = "Request Description:\n{$jobItem->jobRequest->description}";
        }

        if ($jobItem->description) {
            $parts[] = "Category Item Description:\n{$jobItem->description}";
        }

        return $parts ? implode("\n\n", $parts) : null;
    }
}
