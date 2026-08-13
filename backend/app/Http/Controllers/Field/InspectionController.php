<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\InspectionRevision;
use App\Models\JobChecklistItem;
use App\Services\CloudinaryImageService;
use Illuminate\Http\Request;
use RuntimeException;

class InspectionController extends Controller
{
    public function index()
    {
        $inspections = Inspection::with('client')
            ->where('assigned_to', auth()->id())
            ->latest('scheduled_date')
            ->latest('id')
            ->paginate(15);

        return view('field.inspections.index', compact('inspections'));
    }

    public function show(Inspection $inspection)
    {
        $this->authorizeAssignedInspection($inspection);

        $inspection->load([
            'client',
            'reviewedBy',
            'returnedBy',
            'media.uploader',
            'checklistItems.addedBy',
            'checklistItems.completedBy',
            'checklistItems.media',
            'revisions.user',
        ]);

        $inspection->ensureChecklistFromCategory();

        return view('field.inspections.show', compact('inspection'));
    }

    public function submitReport(Request $request, Inspection $inspection, CloudinaryImageService $cloudinary)
    {
        $this->authorizeAssignedInspection($inspection);
        abort_if((int) $inspection->assigned_to !== (int) auth()->id(), 403);

        if ($inspection->status === Inspection::STATUS_COMPLETED && $inspection->review_status === Inspection::REVIEW_STATUS_APPROVED) {
            return back()->withErrors([
                'inspection' => 'This inspection has already been approved.',
            ]);
        }

        $validated = $request->validate([
            'findings' => 'nullable|string',
            'risks_identified' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'media' => 'nullable|array',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'checklist' => 'nullable|array',
            'checklist.*.status' => 'nullable|in:pending,done,not_applicable',
            'checklist.*.response' => 'nullable',
            'checklist.*.notes' => 'nullable|string',
        ]);

        $hasReportText = collect([
            $validated['findings'] ?? null,
            $validated['risks_identified'] ?? null,
            $validated['recommendations'] ?? null,
        ])->contains(fn ($value) => trim((string) $value) !== '');

        $hasChecklistResponses = collect($validated['checklist'] ?? [])
            ->contains(fn ($input) => !empty($input['response']) || !empty($input['notes']) || ($input['status'] ?? '') === 'done');

        if (!$hasReportText && !$hasChecklistResponses && !$request->hasFile('media')) {
            return back()
                ->withErrors(['report' => 'Add report text, complete checklist items, or upload evidence files before submitting.'])
                ->withInput();
        }

        $uploads = [];
        try {
            foreach ($request->file('media', []) as $file) {
                $uploads[] = [
                    'file' => $file,
                    'upload' => $cloudinary->uploadMedia($file, 'inspections/' . $inspection->inspection_code),
                ];
            }
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['media' => 'Media upload failed. Please confirm Cloudinary is configured and try again.'])
                ->withInput();
        }

        $inspection->ensureChecklistFromCategory();

        // Process checklist items
        foreach (($validated['checklist'] ?? []) as $checklistItemId => $checklistInput) {
            $status = $checklistInput['status'] ?? null;
            $notes = isset($checklistInput['notes']) && trim((string) $checklistInput['notes']) !== ''
                ? trim((string) $checklistInput['notes'])
                : null;
            $response = $checklistInput['response'] ?? null;

            if (in_array($status, [null, '', JobChecklistItem::STATUS_PENDING], true) && (!empty($response) || !empty($notes))) {
                $status = JobChecklistItem::STATUS_DONE;
            }

            if (is_array($response)) {
                $response = collect($response)
                    ->filter(fn ($val) => trim((string) $val) !== '')
                    ->implode(', ');
            }

            JobChecklistItem::query()
                ->where('id', $checklistItemId)
                ->where('inspection_id', $inspection->id)
                ->update([
                    'status' => $status ?: JobChecklistItem::STATUS_PENDING,
                    'response' => trim((string) $response) !== '' ? trim((string) $response) : null,
                    'notes' => $notes,
                    'completed_by' => $status === JobChecklistItem::STATUS_DONE ? $request->user()->id : null,
                    'completed_at' => $status === JobChecklistItem::STATUS_DONE ? now() : null,
                ]);
        }

        $inspection->update([
            'findings' => $validated['findings'] ?? $inspection->findings,
            'risks_identified' => $validated['risks_identified'] ?? $inspection->risks_identified,
            'recommendations' => $validated['recommendations'] ?? $inspection->recommendations,
            'submitted_at' => now(),
            'status' => Inspection::STATUS_COMPLETED,
            'review_status' => Inspection::REVIEW_STATUS_PENDING,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_notes' => null,
        ]);

        foreach ($uploads as $stored) {
            $file = $stored['file'];
            $upload = $stored['upload'];
            $inspection->media()->create([
                'uploaded_by' => $request->user()->id,
                'file_path' => $upload['url'],
                'cloudinary_public_id' => $upload['public_id'],
                'cloudinary_resource_type' => $upload['resource_type'] ?? null,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        InspectionRevision::create([
            'inspection_id' => $inspection->id,
            'user_id' => $request->user()->id,
            'action' => InspectionRevision::ACTION_SUBMITTED,
            'findings' => $inspection->findings,
            'risks_identified' => $inspection->risks_identified,
            'recommendations' => $inspection->recommendations,
            'snapshot_data' => [
                'checklist_responses' => $inspection->effective_checklist_items->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'status' => $item->status,
                    'response' => $item->response,
                    'notes' => $item->notes,
                ])->all(),
            ],
        ]);

        return redirect()
            ->route('field.inspections.show', $inspection)
            ->with('success', 'Inspection report resubmitted successfully for Admin review.');
    }

    private function authorizeAssignedInspection(Inspection $inspection): void
    {
        if ((int) $inspection->assigned_to !== (int) auth()->id()) {
            abort(403, 'Unauthorized inspection access');
        }
    }
}
