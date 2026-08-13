<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Inspection;
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
        $validated['status'] = empty($validated['assigned_to']) ? 'pending' : 'assigned';
        $validated['created_by'] = $request->user()->id;

        $inspection = Inspection::create($validated);

        return redirect()
            ->route('admin.inspections.show', $inspection)
            ->with('success', 'Inspection created successfully.');
    }

    public function show(Inspection $inspection)
    {
        $inspection->load(['client', 'assignedUser', 'creator', 'reviewedBy', 'media.uploader', 'project']);

        return view('admin.inspections.show', compact('inspection'));
    }

    public function review(Request $request, Inspection $inspection)
    {
        if (($inspection->review_status ?? 'pending_review') !== 'pending_review') {
            return redirect()
                ->route('admin.inspections.show', $inspection)
                ->with('success', 'This inspection has already been reviewed.');
        }

        $validated = $request->validate([
            'review_status' => ['required', Rule::in(['approved', 'rejected'])],
            'review_notes' => 'nullable|string',
        ]);

        $inspection->update([
            'review_status' => $validated['review_status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.inspections.show', $inspection)
            ->with('success', 'Inspection review saved successfully.');
    }

    private function generateInspectionCode(): string
    {
        do {
            $code = 'INSP-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4));
        } while (Inspection::where('inspection_code', $code)->exists());

        return $code;
    }
}
