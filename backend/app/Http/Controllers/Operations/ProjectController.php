<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FinancialExpense;
use App\Models\FinancialMaterialCost;
use App\Models\Inspection;
use App\Models\Project;
use App\Models\ProjectPayment;
use App\Models\ProjectUpdate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['client', 'inspection'])
            ->latest()
            ->paginate(20);

        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        $clients = Client::query()
            ->orderBy('client_name')
            ->get();

        $managers = User::query()
            ->where('role', 'manager')
            ->orderBy('name')
            ->get();

        $fieldStaff = User::query()
            ->where('role', 'field_staff')
            ->orderBy('name')
            ->get();

        return view('admin.projects.create', compact('clients', 'managers', 'fieldStaff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'status' => ['nullable', 'string', Rule::in(['not_started', 'ongoing', 'on_hold', 'ready_for_review', 'completed'])],
            'priority' => ['nullable', 'string', Rule::in(['low', 'medium', 'high'])],
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'assigned_manager_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('role', 'manager'),
            ],
            'assigned_field_staff_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('role', 'field_staff'),
            ],
        ]);

        $validated['project_code'] = $this->generateProjectCode();
        $validated['status'] = $validated['status'] ?? 'not_started';
        $validated['created_by'] = $request->user()->id;

        $project = Project::create($validated);

        if ($project->assigned_field_staff_id) {
            try {
                $project->load('fieldStaff');
                if ($project->fieldStaff) {
                    $project->fieldStaff->notify(new \App\Notifications\GenericWebPush(
                        'New Project Assigned',
                        "You have been assigned to the new project: {$project->title}.",
                        route('field.projects.show', $project)
                    ));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Push notification failed: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $project->load([
            'client',
            'inspection',
            'jobRequestItem.jobRequest.client',
            'jobRequestItem.serviceCategory',
            'manager',
            'fieldStaff',
            'activeEditor',
            'creator',
            'requirements.completedBy',
            'updates' => fn ($query) => $query->with(['user', 'reviewedBy', 'media.uploader'])->latest('work_date')->latest('id'),
        ]);

        return view('admin.projects.show', compact('project'));
    }

    public function reviewUpdate(Request $request, ProjectUpdate $update)
    {
        if (($update->review_status ?? 'pending_review') !== 'pending_review') {
            return redirect()
                ->route('admin.projects.show', $update->project_id)
                ->with('success', 'This project update has already been reviewed.');
        }

        $validated = $request->validate([
            'review_status' => ['required', Rule::in(['reviewed', 'needs_correction'])],
            'review_notes' => 'nullable|string',
        ]);

        $update->update([
            'review_status' => $validated['review_status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.projects.show', $update->project_id)
            ->with('success', 'Project update review saved successfully.');
    }

    public function complete(Project $project)
    {
        if ($project->status === 'completed') {
            return redirect()
                ->route('admin.projects.show', $project)
                ->with('success', 'Project is already completed.');
        }

        $project->update([
            'status' => 'completed',
            'progress_percentage' => 100,
            'active_editor_id' => null,
            'editing_started_at' => null,
        ]);

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Project marked completed. Field updates are now closed.');
    }

    public function reopenWork(Project $project)
    {
        if (!in_array($project->status, ['ready_for_review', 'completed'], true)) {
            return redirect()
                ->route('admin.projects.show', $project)
                ->withErrors(['project' => 'Only completed or review-ready projects can be reopened for field work.']);
        }

        $project->update([
            'status'             => 'ongoing',
            'active_editor_id'   => null,
            'editing_started_at' => null,
        ]);

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Project reopened for field work.');
    }

    public function destroy(Request $request, Project $project)
    {
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can delete projects.');
        }

        DB::transaction(function () use ($project) {
            // Delete child financial records first
            FinancialExpense::where('project_id', $project->id)->delete();
            FinancialMaterialCost::where('project_id', $project->id)->delete();
            ProjectPayment::where('project_id', $project->id)->delete();

            // Delete project updates
            ProjectUpdate::where('project_id', $project->id)->delete();

            // Remove the project
            $project->delete();
        });

        return redirect()
            ->route('admin.projects.index')
            ->with('success', "Project \"{$project->project_code}\" has been permanently deleted.");
    }

    public function convertFromInspection(Request $request, Inspection $inspection)
    {
        $inspection->load(['project', 'client']);

        if ($inspection->project) {
            return redirect()
                ->route('admin.projects.show', $inspection->project)
                ->with('success', 'This inspection is already linked to a project.');
        }

        $project = DB::transaction(function () use ($request, $inspection) {
            $project = Project::create([
                'project_code' => $this->generateProjectCode(),
                'inspection_id' => $inspection->id,
                'client_id' => $inspection->client_id,
                'title' => $inspection->title,
                'location' => $inspection->location,
                'description' => $this->buildDescriptionFromInspection($inspection),
                'status' => 'not_started',
                'priority' => $inspection->priority,
                'assigned_field_staff_id' => $inspection->assigned_to,
                'created_by' => $request->user()->id,
            ]);

            $this->attachInspectionFinanceToProject($inspection, $project, $request->user()->id);

            return $project;
        });

        if ($project->assigned_field_staff_id) {
            try {
                $project->load('fieldStaff');
                if ($project->fieldStaff) {
                    $project->fieldStaff->notify(new \App\Notifications\GenericWebPush(
                        'New Project Assigned',
                        "You have been assigned to the new project: {$project->title}.",
                        route('field.projects.show', $project)
                    ));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Push notification failed: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Inspection converted to project successfully.');
    }

    private function generateProjectCode(): string
    {
        do {
            $code = 'PROJ-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4));
        } while (Project::where('project_code', $code)->exists());

        return $code;
    }

    private function attachInspectionFinanceToProject(Inspection $inspection, Project $project, int $userId): void
    {
        FinancialExpense::query()
            ->where('inspection_id', $inspection->id)
            ->whereNull('project_id')
            ->update([
                'project_id' => $project->id,
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);

        FinancialMaterialCost::query()
            ->where('inspection_id', $inspection->id)
            ->whereNull('project_id')
            ->update([
                'project_id' => $project->id,
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);

        ProjectPayment::query()
            ->where('inspection_id', $inspection->id)
            ->whereNull('project_id')
            ->update([
                'project_id' => $project->id,
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);
    }

    private function buildDescriptionFromInspection(Inspection $inspection): ?string
    {
        $parts = [];

        if ($inspection->findings) {
            $parts[] = "Findings:\n" . $inspection->findings;
        }

        if ($inspection->recommendations) {
            $parts[] = "Recommendations:\n" . $inspection->recommendations;
        }

        if ($inspection->risks_identified) {
            $parts[] = "Risks Identified:\n" . $inspection->risks_identified;
        }

        return $parts ? implode("\n\n", $parts) : null;
    }
}
