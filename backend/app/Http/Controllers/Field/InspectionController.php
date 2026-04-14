<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
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

        $inspection->load(['client', 'media.uploader']);

        return view('field.inspections.show', compact('inspection'));
    }

    public function submitReport(Request $request, Inspection $inspection, CloudinaryImageService $cloudinary)
    {
        $this->authorizeAssignedInspection($inspection);
        abort_if((int) $inspection->assigned_to !== (int) auth()->id(), 403);

        if ($inspection->status === 'completed') {
            return back()->withErrors([
                'inspection' => 'This inspection has already been submitted.',
            ]);
        }

        $validated = $request->validate([
            'findings' => 'nullable|string',
            'risks_identified' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'media' => 'nullable|array',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $hasReportText = collect([
            $validated['findings'] ?? null,
            $validated['risks_identified'] ?? null,
            $validated['recommendations'] ?? null,
        ])->contains(fn ($value) => trim((string) $value) !== '');

        if (!$hasReportText && !$request->hasFile('media')) {
            return back()
                ->withErrors(['report' => 'Add report text or upload at least one evidence file before submitting.'])
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

        $inspection->update([
            'findings' => $validated['findings'] ?? null,
            'risks_identified' => $validated['risks_identified'] ?? null,
            'recommendations' => $validated['recommendations'] ?? null,
            'submitted_at' => now(),
            'status' => 'completed',
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

        return redirect()
            ->route('field.inspections.show', $inspection)
            ->with('success', 'Inspection report submitted successfully.');
    }

    private function authorizeAssignedInspection(Inspection $inspection): void
    {
        if ((int) $inspection->assigned_to !== (int) auth()->id()) {
            abort(403, 'Unauthorized inspection access');
        }
    }
}
