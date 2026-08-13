<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Inspection;
use App\Models\InspectionRevision;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InspectionController extends Controller
{
    public function index()
    {
        $inspections = Inspection::with(['client', 'assignedUser'])
            ->latest()
            ->paginate(20);

        return view('admin.inspections.index', compact('inspections'));
    }

    public function create()
    {
        $clients = Client::query()
            ->orderBy('client_name')
            ->get();

        $fieldStaff = User::query()
            ->where('role', 'field_staff')
            ->orderBy('name')
            ->get();

        return view('admin.inspections.create', compact('clients', 'fieldStaff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'inspection_type' => 'nullable|string|max:255',
            'scheduled_date' => 'nullable|date',
            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')->where('role', 'field_staff'),
            ],
            'priority' => ['nullable', 'string', Rule::in(['low', 'medium', 'high'])],
        ]);

        $validated['inspection_code'] = $this->generateInspectionCode();
        $validated['status'] = empty($validated['assigned_to']) ? Inspection::STATUS_PENDING : Inspection::STATUS_ASSIGNED;
        $validated['created_by'] = $request->user()->id;

        $inspection = Inspection::create($validated);
        $inspection->ensureChecklistFromCategory();

        return redirect()
            ->route('admin.inspections.show', $inspection)
            ->with('success', 'Inspection created successfully.');
    }

    public function show(Inspection $inspection)
    {
        $inspection->load([
            'client',
            'assignedUser',
            'creator',
            'reviewedBy',
            'returnedBy',
            'media.uploader',
            'project',
            'checklistItems.addedBy',
            'checklistItems.completedBy',
            'checklistItems.media',
            'jobRequestItem.checklistItems',
            'revisions.user',
        ]);

        $inspection->ensureChecklistFromCategory();

        return view('admin.inspections.show', compact('inspection'));
    }

    public function review(Request $request, Inspection $inspection)
    {
        $validated = $request->validate([
            'review_status' => ['required', Rule::in(['approved', 'returned', 'rejected'])],
            'review_notes' => ['nullable', 'string', 'max:5000'],
            'return_reason' => [
                Rule::requiredIf(fn () => $request->input('review_status') === 'returned'),
                'nullable',
                'string',
                'max:5000',
            ],
        ], [
            'return_reason.required' => 'Please provide a reason for returning the inspection to Field Staff.',
        ]);

        $action = $validated['review_status'];
        $notes = trim((string) ($validated['review_notes'] ?? ''));
        $returnReason = trim((string) ($validated['return_reason'] ?? $notes));

        if ($action === 'returned' && $returnReason === '') {
            return back()
                ->withErrors(['return_reason' => 'Please provide a reason for returning the inspection to Field Staff.'])
                ->withInput();
        }

        if ($action === 'approved') {
            $inspection->update([
                'status' => Inspection::STATUS_COMPLETED,
                'review_status' => Inspection::REVIEW_STATUS_APPROVED,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_notes' => $notes ?: null,
            ]);

            InspectionRevision::create([
                'inspection_id' => $inspection->id,
                'user_id' => $request->user()->id,
                'action' => InspectionRevision::ACTION_APPROVED,
                'findings' => $inspection->findings,
                'risks_identified' => $inspection->risks_identified,
                'recommendations' => $inspection->recommendations,
                'admin_notes' => $notes,
            ]);

            $message = 'Inspection approved successfully.';
        } elseif ($action === 'returned') {
            $inspection->update([
                'status' => Inspection::STATUS_RETURNED,
                'review_status' => Inspection::REVIEW_STATUS_RETURNED,
                'returned_by' => $request->user()->id,
                'returned_at' => now(),
                'return_reason' => $returnReason,
                'review_notes' => $notes ?: $returnReason,
            ]);

            InspectionRevision::create([
                'inspection_id' => $inspection->id,
                'user_id' => $request->user()->id,
                'action' => InspectionRevision::ACTION_RETURNED,
                'findings' => $inspection->findings,
                'risks_identified' => $inspection->risks_identified,
                'recommendations' => $inspection->recommendations,
                'return_reason' => $returnReason,
                'admin_notes' => $notes,
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

            $message = 'Inspection returned to Field Staff for additional information.';
        } else {
            $inspection->update([
                'review_status' => Inspection::REVIEW_STATUS_REJECTED,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_notes' => $notes ?: null,
            ]);

            InspectionRevision::create([
                'inspection_id' => $inspection->id,
                'user_id' => $request->user()->id,
                'action' => InspectionRevision::ACTION_REJECTED,
                'findings' => $inspection->findings,
                'risks_identified' => $inspection->risks_identified,
                'recommendations' => $inspection->recommendations,
                'admin_notes' => $notes,
            ]);

            $message = 'Inspection rejected.';
        }

        return redirect()
            ->route('admin.inspections.show', $inspection)
            ->with('success', $message);
    }

    private function generateInspectionCode(): string
    {
        do {
            $code = 'INSP-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4));
        } while (Inspection::where('inspection_code', $code)->exists());

        return $code;
    }
}
