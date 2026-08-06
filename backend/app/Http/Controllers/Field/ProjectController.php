<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Services\CloudinaryImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['client', 'activeEditor'])
            ->latest('deadline')
            ->latest('id')
            ->paginate(15);

        return view('field.projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        $project->load([
            'client',
            'inspection',
            'activeEditor',
            'requirements.completedBy',
            'updates' => fn ($query) => $query->with(['user', 'reviewedBy', 'media.uploader'])->latest('work_date')->latest('id'),
        ]);

        return view('field.projects.show', compact('project'));
    }

    public function startUpdate(Project $project)
    {
        DB::transaction(function () use ($project) {
            $lockedProject = Project::query()
                ->with('activeEditor')
                ->where('id', $project->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($lockedProject->status, ['completed', 'ready_for_review'], true)) {
                throw ValidationException::withMessages([
                    'project' => $lockedProject->status === 'completed'
                        ? 'Project completed.'
                        : 'Project is awaiting admin review.',
                ]);
            }

            if ($lockedProject->isBeingEdited() && (int) $lockedProject->active_editor_id !== (int) auth()->id()) {
                $editorName = $lockedProject->activeEditor?->name ?? 'another field staff member';

                throw ValidationException::withMessages([
                    'project' => "This project is currently being updated by {$editorName}.",
                ]);
            }

            $lockedProject->forceFill([
                'active_editor_id' => auth()->id(),
                'editing_started_at' => now(),
            ])->save();
        });

        return redirect()
            ->route('field.projects.show', $project)
            ->with('success', 'Project update session started.');
    }

    public function submitUpdate(Request $request, Project $project, CloudinaryImageService $cloudinary)
    {
        if (in_array($project->status, ['completed', 'ready_for_review'], true)) {
            return back()
                ->withErrors(['project' => $project->status === 'completed'
                    ? 'Project completed.'
                    : 'Project is awaiting admin review.'])
                ->withInput();
        }

        $validated = $request->validate([
            'summary' => 'nullable|string|max:255',
            'work_done' => 'nullable|string',
            'materials_used' => 'nullable|string',
            'issues_encountered' => 'nullable|string',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'next_step' => 'nullable|string',
            'work_date' => 'nullable|date',
            'media' => 'nullable|array',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ((int) $project->active_editor_id !== (int) $request->user()->id) {
            return back()
                ->withErrors(['project' => 'Click Continue Project before submitting an update.'])
                ->withInput();
        }

        $hasTextInput = collect([
            $validated['summary'] ?? null,
            $validated['work_done'] ?? null,
            $validated['materials_used'] ?? null,
            $validated['issues_encountered'] ?? null,
            $validated['next_step'] ?? null,
        ])->contains(fn ($value) => trim((string) $value) !== '');

        $hasProgress = array_key_exists('progress_percentage', $validated) && $validated['progress_percentage'] !== null;

        if (!$hasTextInput && !$hasProgress && !$request->hasFile('media')) {
            return back()
                ->withErrors(['update' => 'Add update text, progress, or upload at least one file before submitting.'])
                ->withInput();
        }

        $submittedProgress = $hasProgress ? (int) $validated['progress_percentage'] : null;

        if ($hasProgress && $submittedProgress < (int) ($project->progress_percentage ?? 0)) {
            return back()
                ->withErrors(['progress_percentage' => 'Project progress cannot move backwards.'])
                ->withInput();
        }

        $uploads = [];
        try {
            foreach ($request->file('media', []) as $file) {
                $uploads[] = [
                    'file' => $file,
                    'upload' => $cloudinary->uploadMedia($file, 'projects/' . $project->project_code . '/updates'),
                ];
            }
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['media' => 'Media upload failed. Please confirm Cloudinary is configured and try again.'])
                ->withInput();
        }

        DB::transaction(function () use ($project, $request, $validated, $uploads, $hasProgress, $submittedProgress) {
            $lockedProject = Project::query()
                ->where('id', $project->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($lockedProject->status, ['completed', 'ready_for_review'], true)) {
                throw ValidationException::withMessages([
                    'project' => $lockedProject->status === 'completed'
                        ? 'Project completed.'
                        : 'Project is awaiting admin review.',
                ]);
            }

            if (!$lockedProject->canBeUpdatedBy($request->user())) {
                throw ValidationException::withMessages([
                    'project' => 'Only the current active updater can submit this project update.',
                ]);
            }

            if ($hasProgress && $submittedProgress < (int) ($lockedProject->progress_percentage ?? 0)) {
                throw ValidationException::withMessages([
                    'progress_percentage' => 'Project progress cannot move backwards.',
                ]);
            }

            $projectUpdate = $lockedProject->updates()->create([
                'user_id' => $request->user()->id,
                'summary' => $validated['summary'] ?? null,
                'work_done' => $validated['work_done'] ?? null,
                'materials_used' => $validated['materials_used'] ?? null,
                'issues_encountered' => $validated['issues_encountered'] ?? null,
                'progress_percentage' => $validated['progress_percentage'] ?? null,
                'next_step' => $validated['next_step'] ?? null,
                'work_date' => $validated['work_date'] ?? null,
                'review_status' => 'pending_review',
            ]);

            foreach ($uploads as $stored) {
                $file = $stored['file'];
                $upload = $stored['upload'];
                $lockedProject->media()->create([
                    'project_update_id' => $projectUpdate->id,
                    'uploaded_by' => $request->user()->id,
                    'file_path' => $upload['url'],
                    'cloudinary_public_id' => $upload['public_id'],
                    'cloudinary_resource_type' => $upload['resource_type'] ?? null,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }

            $projectPayload = [
                'active_editor_id' => null,
                'editing_started_at' => null,
            ];

            if ($hasProgress) {
                $projectPayload['progress_percentage'] = $submittedProgress;
                $projectPayload['status'] = $this->statusForProgress($submittedProgress);
            }

            $lockedProject->update($projectPayload);
        });

        return redirect()
            ->route('field.projects.show', $project)
            ->with('success', 'Project update submitted successfully.');
    }

    public function releaseUpdate(Project $project)
    {
        DB::transaction(function () use ($project) {
            $lockedProject = Project::query()
                ->where('id', $project->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$lockedProject->canBeUpdatedBy(auth()->user())) {
                throw ValidationException::withMessages([
                    'project' => 'Only the active updater can release this project.',
                ]);
            }

            $lockedProject->update([
                'active_editor_id' => null,
                'editing_started_at' => null,
            ]);
        });

        return redirect()
            ->route('field.projects.show', $project)
            ->with('success', 'Project update session released.');
    }

    public function updateRequirement(Request $request, Project $project, ProjectRequirement $requirement)
    {
        if ((int) $requirement->project_id !== (int) $project->id) {
            abort(404);
        }

        if (in_array($project->status, ['completed', 'ready_for_review'], true)) {
            return back()->withErrors(['project' => $project->status === 'completed'
                ? 'Project completed.'
                : 'Project is awaiting admin review.']);
        }

        $validated = $request->validate([
            'is_done' => 'nullable|boolean',
        ]);

        $isDone = (bool) ($validated['is_done'] ?? false);

        $requirement->update([
            'is_done' => $isDone,
            'completed_by' => $isDone ? $request->user()->id : null,
            'completed_at' => $isDone ? now() : null,
        ]);

        $total = $project->requirements()->count();
        if ($total > 0) {
            $done = $project->requirements()->where('is_done', true)->count();
            $progress = (int) round(($done / $total) * 100);

            $project->update([
                'progress_percentage' => $progress,
                'status' => $this->statusForProgress($progress),
            ]);
        }

        return redirect()
            ->route('field.projects.show', $project)
            ->with('success', 'Checklist updated.');
    }

    private function statusForProgress(int $progress): string
    {
        if ($progress === 100) {
            return 'ready_for_review';
        }

        if ($progress > 0) {
            return 'ongoing';
        }

        return 'not_started';
    }
}
