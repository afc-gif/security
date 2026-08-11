@php
    $editing = isset($expense);
    $contextType = old('context_type', $editing
        ? ($expense->project_id ? 'project' : ($expense->inspection_id ? 'inspection' : 'job'))
        : ($selectedContextType ?: 'inspection'));
    $selectedInspectionId = old('inspection_id', $editing ? $expense->inspection_id : ($contextType === 'inspection' ? $selectedContextId : null));
    $selectedJobItemId = old('job_request_item_id', $editing ? $expense->job_request_item_id : ($contextType === 'job' ? $selectedContextId : null));
    $selectedProjectId = old('project_id', $editing ? $expense->project_id : ($contextType === 'project' ? $selectedContextId : null));
    $selectedCategoryId = old('finance_expense_category_id', $editing ? $expense->finance_expense_category_id : optional($categories->firstWhere('slug', 'transportation'))->id);
    $selectedStatus = old('status', $editing ? $expense->status : \App\Models\FinancialExpense::STATUS_PENDING);
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
        <h2 class="text-lg font-extrabold text-gray-950">{{ $editing ? 'Expense Details' : 'New Expense Details' }}</h2>
        <p class="mt-1 text-sm text-gray-600">For transportation before a project exists, choose Inspection or Job as the context.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="context_type" class="block text-sm font-medium text-gray-700 mb-1">Context</label>
            <select id="context_type" name="context_type" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                <option value="inspection" @selected($contextType === 'inspection')>Inspection</option>
                <option value="job" @selected($contextType === 'job')>Job</option>
                <option value="project" @selected($contextType === 'project')>Project</option>
            </select>
            <div class="text-xs text-gray-500 mt-1">Inspection/job costs are pre-project records. Project costs appear on the project finance page.</div>
        </div>

        <div>
            <label for="finance_expense_category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select id="finance_expense_category_id" name="finance_expense_category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label for="inspection_id" class="block text-sm font-medium text-gray-700 mb-1">Inspection</label>
            <select id="inspection_id" name="inspection_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <option value="">Select inspection</option>
                @foreach($inspections as $inspection)
                    <option value="{{ $inspection->id }}" @selected((string) $selectedInspectionId === (string) $inspection->id)>
                        {{ $inspection->inspection_code }} - {{ $inspection->client?->client_name ?? 'Client unavailable' }} - {{ $inspection->title }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label for="job_request_item_id" class="block text-sm font-medium text-gray-700 mb-1">Job</label>
            <select id="job_request_item_id" name="job_request_item_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <option value="">Select job</option>
                @foreach($jobItems as $jobItem)
                    <option value="{{ $jobItem->id }}" @selected((string) $selectedJobItemId === (string) $jobItem->id)>
                        Job Item #{{ $jobItem->id }} - {{ $jobItem->jobRequest?->client?->client_name ?? 'Client unavailable' }} - {{ $jobItem->jobRequest?->title ?? $jobItem->title }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1">Project</label>
            <select id="project_id" name="project_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <option value="">Select project</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" @selected((string) $selectedProjectId === (string) $project->id)>
                        {{ $project->project_code }} - {{ $project->client?->client_name ?? 'Client unavailable' }} - {{ $project->title }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <input id="description" type="text" name="description" value="{{ old('description', $editing ? $expense->description : 'Transport for site visit') }}" maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
        </div>

        <div>
            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
            <input id="amount" type="number" name="amount" value="{{ old('amount', $editing ? $expense->amount : '') }}" min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
        </div>

        <div>
            <label for="incurred_on" class="block text-sm font-medium text-gray-700 mb-1">Date Incurred</label>
            <input id="incurred_on" type="date" name="incurred_on" value="{{ old('incurred_on', $editing ? $expense->incurred_on?->format('Y-m-d') : now()->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            @can(\App\Models\FinancePermission::APPROVE)
                <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    @foreach([\App\Models\FinancialExpense::STATUS_PENDING, \App\Models\FinancialExpense::STATUS_APPROVED, \App\Models\FinancialExpense::STATUS_REJECTED] as $status)
                        <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ str_replace('_', ' ', \Illuminate\Support\Str::title($status)) }}</option>
                    @endforeach
                </select>
            @else
                <input type="hidden" name="status" value="{{ \App\Models\FinancialExpense::STATUS_PENDING }}">
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
            <textarea id="notes" name="notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('notes', $editing ? $expense->notes : '') }}</textarea>
        </div>
    </div>

    <div class="mt-6 flex flex-col sm:flex-row gap-3">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold">
            {{ $editing ? 'Save Changes' : 'Record Expense' }}
        </button>
        <a href="{{ $editing ? route('finance.expenses.show', $expense) : route('finance.expenses.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-5 py-2.5 rounded-lg font-semibold text-center">Cancel</a>
    </div>
</div>
