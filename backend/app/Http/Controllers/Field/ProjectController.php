<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\CloudinaryImageService;
use Illuminate\Http\Request;
use RuntimeException;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('client')
            ->where('assigned_field_staff_id', auth()->id())
            ->latest('deadline')
            ->latest('id')
            ->paginate(15);

        return view('field.projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        $this->authorizeAssignedProject($project);

        $project->load([
            'client',
            'inspection',
            'updates' => fn ($query) => $query->with(['user', 'reviewedBy', 'media.uploader'])->latest('work_date')->latest('id'),
        ]);

        return view('field.projects.show', compact('project'));
    }

    public function submitUpdate(Request $request, Project $project, CloudinaryImageService $cloudinary)
    {
        $this->authorizeAssignedProject($project);

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

        $projectUpdate = $project->updates()->create([
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
            $project->media()->create([
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

        if ($hasProgress) {
            $progress = (int) $validated['progress_percentage'];
            $project->update([
                'progress_percentage' => $progress,
                'status' => $this->statusForProgress($project->status, $progress),
            ]);
        }

        return redirect()
            ->route('field.projects.show', $project)
            ->with('success', 'Project update submitted successfully.');
    }

    private function authorizeAssignedProject(Project $project): void
    {
        if ((int) $project->assigned_field_staff_id !== (int) auth()->id()) {
            abort(403, 'Unauthorized project access');
        }
    }

    private function statusForProgress(string $currentStatus, int $progress): string
    {
        if ($progress === 100) {
            return 'completed';
        }

        if ($progress > 0) {
            return 'ongoing';
        }

        return $currentStatus === 'completed' ? 'not_started' : $currentStatus;
    }
}
