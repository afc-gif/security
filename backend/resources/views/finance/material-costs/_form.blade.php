@php
    $editing = isset($materialCost);
    $selectedStatus = old('status', $editing ? $materialCost->status : \App\Models\FinancialMaterialCost::STATUS_PENDING);
@endphp

@if($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
    <div class="mb-5 border-b border-gray-200 pb-4">
        <h2 class="text-lg font-extrabold text-gray-950">{{ $editing ? 'Material Cost Details' : 'New Material Cost Details' }}</h2>
        <p class="mt-1 text-sm text-gray-600">Quantity multiplied by unit cost becomes the total material cost for this project.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
            <label for="material_name" class="block text-sm font-medium text-gray-700 mb-1">Material</label>
            <input id="material_name" type="text" name="material_name" value="{{ old('material_name', $editing ? $materialCost->material_name : '') }}" maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
        </div>

        <div>
            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
            <input id="quantity" type="number" name="quantity" value="{{ old('quantity', $editing ? $materialCost->quantity : '') }}" min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
        </div>

        <div>
            <label for="unit" class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
            <input id="unit" type="text" name="unit" value="{{ old('unit', $editing ? $materialCost->unit : '') }}" maxlength="50" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="pcs, meters, rolls">
        </div>

        <div>
            <label for="unit_cost" class="block text-sm font-medium text-gray-700 mb-1">Unit Cost</label>
            <input id="unit_cost" type="number" name="unit_cost" value="{{ old('unit_cost', $editing ? $materialCost->unit_cost : '') }}" min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
        </div>

        <div>
            <label for="incurred_on" class="block text-sm font-medium text-gray-700 mb-1">Date Incurred</label>
            <input id="incurred_on" type="date" name="incurred_on" value="{{ old('incurred_on', $editing ? $materialCost->incurred_on?->format('Y-m-d') : now()->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            @can(\App\Models\FinancePermission::APPROVE)
                <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    @foreach([\App\Models\FinancialMaterialCost::STATUS_PENDING, \App\Models\FinancialMaterialCost::STATUS_APPROVED, \App\Models\FinancialMaterialCost::STATUS_REJECTED] as $status)
                        <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ str_replace('_', ' ', \Illuminate\Support\Str::title($status)) }}</option>
                    @endforeach
                </select>
            @else
                <input type="hidden" name="status" value="{{ \App\Models\FinancialMaterialCost::STATUS_PENDING }}">
                <div class="border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700">Pending</div>
            @endcan
        </div>

        <div>
            <label for="receipt" class="block text-sm font-medium text-gray-700 mb-1">Receipt / Document</label>
            <input id="receipt" type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white">
            <div class="text-xs text-gray-500 mt-1">JPG, PNG, or PDF. Maximum 5 MB.</div>
        </div>

        <div class="md:col-span-2">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea id="notes" name="notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('notes', $editing ? $materialCost->notes : '') }}</textarea>
        </div>
    </div>

    <div class="mt-6 flex flex-col sm:flex-row gap-3">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold">
            {{ $editing ? 'Save Changes' : 'Record Material Cost' }}
        </button>
        <a href="{{ $editing ? route('finance.material-costs.show', $materialCost) : route('finance.projects.show', $project) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-5 py-2.5 rounded-lg font-semibold text-center">Cancel</a>
    </div>
</div>
