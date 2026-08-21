<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinancePermission;
use App\Models\FinancialDocument;
use App\Models\FinancialProcurement;
use App\Models\InventoryProduct;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FinanceProcurementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeFinance(FinancePermission::VIEW);

        $activeTab = $request->query('tab', 'procurements');

        $procurements = FinancialProcurement::with(['supplier', 'product', 'creator', 'documents'])
            ->orderBy('purchase_date', 'desc')
            ->paginate(15, ['*'], 'procurements_page')
            ->withQueryString();

        $suppliers = Supplier::withCount('procurements')
            ->orderBy('name')
            ->paginate(15, ['*'], 'suppliers_page')
            ->withQueryString();

        $products = InventoryProduct::orderBy('name')
            ->paginate(15, ['*'], 'products_page')
            ->withQueryString();

        // ── Monthly stats ────────────────────────────────────────────────────
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $monthlySpend = (float) FinancialProcurement::whereBetween('purchase_date', [$startOfMonth, $endOfMonth])
            ->sum('total_cost');

        $monthlyItemsCount = (float) FinancialProcurement::whereBetween('purchase_date', [$startOfMonth, $endOfMonth])
            ->sum('quantity');

        $activeSuppliersCount = Supplier::count();

        // Helpers
        $financeMoney = fn ($amount) => '₦' . number_format($amount, 2);

        return view('finance.procurement.index', compact(
            'activeTab',
            'procurements',
            'suppliers',
            'products',
            'monthlySpend',
            'monthlyItemsCount',
            'activeSuppliersCount',
            'financeMoney'
        ));
    }

    public function create()
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        $suppliers = Supplier::orderBy('name')->get();
        $products = InventoryProduct::orderBy('name')->get();

        return view('finance.procurement.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:finance_suppliers,id'],
            'inventory_product_id' => ['required', 'exists:finance_inventory_products,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['required', 'numeric', 'min:0.00'],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $procurement = DB::transaction(function () use ($request, $validated) {
            $validated['total_cost'] = number_format(((float) $validated['quantity']) * ((float) $validated['unit_cost']), 2, '.', '');
            $validated['created_by'] = $request->user()->id;

            $procurement = FinancialProcurement::create($validated);

            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $path = $file->store('financial-documents/' . now()->format('Y/m'), 'local');

                $procurement->documents()->create([
                    'uploaded_by' => $request->user()->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'visibility' => FinancialDocument::VISIBILITY_PRIVATE,
                ]);
            }

            return $procurement;
        });

        return redirect()
            ->route('finance.procurements.index', ['tab' => 'procurements'])
            ->with('success', 'Procurement purchase recorded successfully.');
    }

    public function destroy(FinancialProcurement $procurement)
    {
        $this->authorizeFinance(FinancePermission::DELETE);

        DB::transaction(function () use ($procurement) {
            $procurement->delete();
        });

        return redirect()
            ->route('finance.procurements.index', ['tab' => 'procurements'])
            ->with('success', 'Procurement record deleted successfully.');
    }

    // ── Supplier Management ──────────────────────────────────────────────────
    public function storeSupplier(Request $request)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:finance_suppliers,name'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        Supplier::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('finance.procurements.index', ['tab' => 'suppliers'])
            ->with('success', 'Supplier created successfully.');
    }

    public function editSupplier(Supplier $supplier)
    {
        $this->authorizeFinance(FinancePermission::EDIT);
        return view('finance.procurement.suppliers.edit', compact('supplier'));
    }

    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $this->authorizeFinance(FinancePermission::EDIT);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('finance_suppliers', 'name')->ignore($supplier->id)],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $supplier->update($validated);

        return redirect()
            ->route('finance.procurements.index', ['tab' => 'suppliers'])
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroySupplier(Supplier $supplier)
    {
        $this->authorizeFinance(FinancePermission::DELETE);

        if ($supplier->procurements()->exists() || $supplier->materialCosts()->exists()) {
            return redirect()
                ->route('finance.procurements.index', ['tab' => 'suppliers'])
                ->with('error', 'Cannot delete supplier with active purchases or material records.');
        }

        $supplier->delete();

        return redirect()
            ->route('finance.procurements.index', ['tab' => 'suppliers'])
            ->with('success', 'Supplier deleted successfully.');
    }

    // ── Inventory Product Catalog ────────────────────────────────────────────
    public function storeProduct(Request $request)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:finance_inventory_products,name'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:finance_inventory_products,sku'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        InventoryProduct::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('finance.procurements.index', ['tab' => 'products'])
            ->with('success', 'Product added to inventory catalog successfully.');
    }

    public function editProduct(InventoryProduct $product)
    {
        $this->authorizeFinance(FinancePermission::EDIT);
        return view('finance.procurement.products.edit', compact('product'));
    }

    public function updateProduct(Request $request, InventoryProduct $product)
    {
        $this->authorizeFinance(FinancePermission::EDIT);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('finance_inventory_products', 'name')->ignore($product->id)],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('finance_inventory_products', 'sku')->ignore($product->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $product->update($validated);

        return redirect()
            ->route('finance.procurements.index', ['tab' => 'products'])
            ->with('success', 'Inventory product updated successfully.');
    }

    public function destroyProduct(InventoryProduct $product)
    {
        $this->authorizeFinance(FinancePermission::DELETE);

        if ($product->procurements()->exists() || $product->materialCosts()->exists()) {
            return redirect()
                ->route('finance.procurements.index', ['tab' => 'products'])
                ->with('error', 'Cannot delete product currently linked to procurements or material records.');
        }

        $product->delete();

        return redirect()
            ->route('finance.procurements.index', ['tab' => 'products'])
            ->with('success', 'Product removed from catalog successfully.');
    }

    private function authorizeFinance(string $permission): void
    {
        $user = auth()->user();
        abort_unless(
            $user instanceof User && $user->hasFinancePermission($permission),
            403,
            'Unauthorized'
        );
    }
}
