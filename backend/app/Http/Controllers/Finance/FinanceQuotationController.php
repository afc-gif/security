<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FinancePermission;
use App\Models\Inspection;
use App\Models\JobRequestItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class FinanceQuotationController extends Controller
{
    private function authorizeFinance(string $permission): void
    {
        $user = auth()->user();

        if (!$user instanceof User || !$user->hasFinancePermission($permission)) {
            abort(403, 'Unauthorized finance action.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeFinance(FinancePermission::VIEW);

        $query = Quotation::query()
            ->with(['client', 'creator'])
            ->orderByDesc('id');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($cq) => $cq->where('company_name', 'like', "%{$search}%")
                        ->orWhere('client_name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($clientId = $request->input('client_id')) {
            $query->where('client_id', $clientId);
        }

        $quotations = $query->paginate(15)->withQueryString();

        $summary = [
            'total_count' => Quotation::count(),
            'draft_count' => Quotation::where('status', Quotation::STATUS_DRAFT)->count(),
            'sent_count' => Quotation::where('status', Quotation::STATUS_SENT)->count(),
            'accepted_count' => Quotation::where('status', Quotation::STATUS_ACCEPTED)->count(),
            'accepted_value' => (float) Quotation::where('status', Quotation::STATUS_ACCEPTED)->sum('grand_total'),
        ];

        $clients = Client::orderBy('company_name')->orderBy('client_name')->get();
        $financeMoney = fn ($amount) => '₦' . number_format((float) ($amount ?? 0), 2);

        return view('finance.quotations.index', compact('quotations', 'summary', 'clients', 'financeMoney'));
    }

    public function create(Request $request)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        $clients = Client::orderBy('company_name')->orderBy('client_name')->get();
        $jobItems = JobRequestItem::with(['jobRequest.client'])->orderByDesc('id')->get();
        $inspections = Inspection::with('client')->orderByDesc('id')->get();

        $selectedClientId = $request->input('client_id');
        $selectedJobItemId = $request->input('job_request_item_id');
        $selectedInspectionId = $request->input('inspection_id');

        return view('finance.quotations.create', compact(
            'clients',
            'jobItems',
            'inspections',
            'selectedClientId',
            'selectedJobItemId',
            'selectedInspectionId'
        ));
    }

    public function store(Request $request)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'title' => ['required', 'string', 'max:255'],
            'quotation_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quotation_date'],
            'job_request_item_id' => ['nullable', 'exists:job_request_items,id'],
            'inspection_id' => ['nullable', 'exists:inspections,id'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $jobItem = !empty($validated['job_request_item_id'])
            ? JobRequestItem::find($validated['job_request_item_id'])
            : null;

        $quotation = DB::transaction(function () use ($request, $validated, $jobItem) {
            $subtotal = 0.00;
            $processedItems = [];

            foreach ($validated['items'] as $index => $itemData) {
                $qty = (float) $itemData['quantity'];
                $unitPrice = (float) $itemData['unit_price'];
                $lineTotal = round($qty * $unitPrice, 2);
                $subtotal += $lineTotal;

                $processedItems[] = [
                    'description' => $itemData['description'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                    'notes' => $itemData['notes'] ?? null,
                    'sort_order' => $index + 1,
                ];
            }

            $discount = (float) ($validated['discount_amount'] ?? 0.00);
            $tax = (float) ($validated['tax_amount'] ?? 0.00);

            $discount = min($subtotal, max(0.00, $discount));
            $grandTotal = max(0.00, round($subtotal - $discount + $tax, 2));

            $quotation = Quotation::create([
                'quotation_number' => Quotation::generateQuotationNumber(),
                'client_id' => $validated['client_id'],
                'job_request_id' => $jobItem?->job_request_id,
                'job_request_item_id' => $jobItem?->id,
                'inspection_id' => $validated['inspection_id'] ?? null,
                'project_id' => $jobItem?->project?->id,
                'title' => $validated['title'],
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'] ?? null,
                'status' => Quotation::STATUS_DRAFT,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'grand_total' => $grandTotal,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            foreach ($processedItems as $pItem) {
                $quotation->items()->create($pItem);
            }

            return $quotation;
        });

        return redirect()
            ->route('finance.quotations.show', $quotation)
            ->with('success', "Quotation {$quotation->quotation_number} created successfully.");
    }

    public function show(Quotation $quotation)
    {
        $this->authorizeFinance(FinancePermission::VIEW);

        $quotation->load([
            'client',
            'jobRequest',
            'jobRequestItem',
            'inspection',
            'project',
            'items',
            'payments.recorder',
            'creator',
            'updater',
        ]);

        $financeMoney = fn ($amount) => '₦' . number_format((float) ($amount ?? 0), 2);

        return view('finance.quotations.show', compact('quotation', 'financeMoney'));
    }

    public function download(Quotation $quotation)
    {
        $this->authorizeFinance(FinancePermission::VIEW);

        $quotation->load([
            'client',
            'jobRequest',
            'jobRequestItem',
            'inspection',
            'project',
            'items',
            'creator',
        ]);

        $financeMoney = fn ($amount) => '₦' . number_format((float) ($amount ?? 0), 2);

        $logoBase64 = null;
        $logoPath = public_path('head.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('logo.png');
        }
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $mimeType = $ext === 'png' ? 'image/png' : ($ext === 'webp' ? 'image/webp' : 'image/jpeg');
            $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoData);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('finance.quotations.pdf', compact('quotation', 'financeMoney', 'logoBase64'))
            ->setPaper('a4', 'portrait');

        $filename = 'Quotation_' . $quotation->quotation_number . '.pdf';

        return $pdf->download($filename);
    }

    public function edit(Quotation $quotation)
    {
        $this->authorizeFinance(FinancePermission::EDIT);

        if ($quotation->status === Quotation::STATUS_ACCEPTED && $quotation->payments()->exists()) {
            return redirect()
                ->route('finance.quotations.show', $quotation)
                ->withErrors(['quotation' => 'This quotation has been accepted and has associated payments. Direct editing is locked to protect financial payment integrity.']);
        }

        $quotation->load('items');
        $clients = Client::orderBy('company_name')->orderBy('client_name')->get();
        $jobItems = JobRequestItem::with(['jobRequest.client'])->orderByDesc('id')->get();
        $inspections = Inspection::with('client')->orderByDesc('id')->get();

        return view('finance.quotations.edit', compact('quotation', 'clients', 'jobItems', 'inspections'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $this->authorizeFinance(FinancePermission::EDIT);

        if ($quotation->status === Quotation::STATUS_ACCEPTED && $quotation->payments()->exists()) {
            return redirect()
                ->route('finance.quotations.show', $quotation)
                ->withErrors(['quotation' => 'This quotation has been accepted and has associated payments. Direct editing is locked to protect financial payment integrity.']);
        }

        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'title' => ['required', 'string', 'max:255'],
            'quotation_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quotation_date'],
            'job_request_item_id' => ['nullable', 'exists:job_request_items,id'],
            'inspection_id' => ['nullable', 'exists:inspections,id'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $jobItem = !empty($validated['job_request_item_id'])
            ? JobRequestItem::find($validated['job_request_item_id'])
            : null;

        DB::transaction(function () use ($request, $quotation, $validated, $jobItem) {
            $subtotal = 0.00;
            $processedItems = [];

            foreach ($validated['items'] as $index => $itemData) {
                $qty = (float) $itemData['quantity'];
                $unitPrice = (float) $itemData['unit_price'];
                $lineTotal = round($qty * $unitPrice, 2);
                $subtotal += $lineTotal;

                $processedItems[] = [
                    'description' => $itemData['description'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                    'notes' => $itemData['notes'] ?? null,
                    'sort_order' => $index + 1,
                ];
            }

            $discount = (float) ($validated['discount_amount'] ?? 0.00);
            $tax = (float) ($validated['tax_amount'] ?? 0.00);

            $discount = min($subtotal, max(0.00, $discount));
            $grandTotal = max(0.00, round($subtotal - $discount + $tax, 2));

            $quotation->update([
                'client_id' => $validated['client_id'],
                'job_request_id' => $jobItem?->job_request_id ?? $quotation->job_request_id,
                'job_request_item_id' => $jobItem?->id ?? $quotation->job_request_item_id,
                'inspection_id' => $validated['inspection_id'] ?? null,
                'title' => $validated['title'],
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'grand_total' => $grandTotal,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'updated_by' => $request->user()->id,
            ]);

            $quotation->items()->delete();
            foreach ($processedItems as $pItem) {
                $quotation->items()->create($pItem);
            }
        });

        return redirect()
            ->route('finance.quotations.show', $quotation)
            ->with('success', "Quotation {$quotation->quotation_number} updated successfully.");
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        $this->authorizeFinance(FinancePermission::EDIT);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:draft,sent,accepted,rejected,expired,cancelled'],
        ]);

        $quotation->update([
            'status' => $validated['status'],
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', "Quotation status updated to " . strtoupper($validated['status']) . ".");
    }

    public function destroy(Quotation $quotation)
    {
        $this->authorizeFinance(FinancePermission::DELETE);

        if ($quotation->status === Quotation::STATUS_ACCEPTED && $quotation->payments()->exists()) {
            return back()->withErrors(['quotation' => 'Cannot delete an accepted quotation with associated payments. Cancel it instead.']);
        }

        $quotationNumber = $quotation->quotation_number;
        $quotation->delete();

        return redirect()
            ->route('finance.quotations.index')
            ->with('success', "Quotation {$quotationNumber} deleted successfully.");
    }
}
