<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceExpenseCategory;
use App\Models\FinancePermission;
use App\Models\FinancialDocument;
use App\Models\FinancialExpense;
use App\Models\Inspection;
use App\Models\JobRequestItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FinanceController extends Controller
{
    public function dashboard()
    {
        $baseQuery = $this->preProjectExpensesQuery();

        $pendingQuery = (clone $baseQuery)->where('status', FinancialExpense::STATUS_PENDING);
        $approvedQuery = (clone $baseQuery)->where('status', FinancialExpense::STATUS_APPROVED);
        $rejectedQuery = (clone $baseQuery)->where('status', FinancialExpense::STATUS_REJECTED);

        $transportCategory = FinanceExpenseCategory::query()
            ->where('slug', 'transportation')
            ->first();

        $transportQuery = $transportCategory
            ? (clone $baseQuery)->where('finance_expense_category_id', $transportCategory->id)
            : null;

        $recentExpenses = (clone $baseQuery)
            ->with($this->expenseRelations())
            ->latest()
            ->limit(8)
            ->get();

        return view('finance.dashboard', array_merge([
            'pendingCount' => $pendingQuery->count(),
            'pendingTotal' => $pendingQuery->sum('amount'),
            'approvedCount' => $approvedQuery->count(),
            'approvedTotal' => $approvedQuery->sum('amount'),
            'rejectedCount' => $rejectedQuery->count(),
            'transportCount' => $transportQuery?->count() ?? 0,
            'transportTotal' => $transportQuery?->sum('amount') ?? 0,
            'recentExpenses' => $recentExpenses,
        ], $this->viewHelpers()));
    }

    public function expenses(Request $request)
    {
        $filters = $request->only(['context', 'category', 'status', 'date_from', 'date_to']);
        $categories = FinanceExpenseCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $expenses = $this->preProjectExpensesQuery()
            ->with($this->expenseRelations())
            ->when(($filters['context'] ?? null) === 'inspection', fn (Builder $query) => $query->whereNotNull('inspection_id'))
            ->when(($filters['context'] ?? null) === 'job', fn (Builder $query) => $query->whereNotNull('job_request_item_id'))
            ->when($filters['category'] ?? null, fn (Builder $query, $category) => $query->where('finance_expense_category_id', $category))
            ->when($filters['status'] ?? null, fn (Builder $query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, $date) => $query->whereDate('incurred_on', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $date) => $query->whereDate('incurred_on', '<=', $date))
            ->latest()
            ->paginate(20);

        $expenses->appends($request->query());

        return view('finance.expenses.index', array_merge(
            compact('expenses', 'categories', 'filters'),
            $this->viewHelpers()
        ));
    }

    public function create(Request $request)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        return view('finance.expenses.create', $this->formData($request));
    }

    public function store(Request $request)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        $validated = $this->validateExpense($request);
        $status = $validated['status'] ?? FinancialExpense::STATUS_PENDING;

        if ($status !== FinancialExpense::STATUS_PENDING) {
            $this->authorizeFinance(FinancePermission::APPROVE);
        }

        $expense = DB::transaction(function () use ($request, $validated, $status) {
            $payload = $this->expensePayload($validated);
            $payload['status'] = $status;
            $payload['submitted_by'] = $request->user()->id;
            $payload['created_by'] = $request->user()->id;
            $payload['updated_by'] = $request->user()->id;

            if ($status === FinancialExpense::STATUS_APPROVED) {
                $payload['approved_by'] = $request->user()->id;
                $payload['approved_at'] = now();
            }

            $expense = FinancialExpense::create($payload);
            $this->storeReceipt($request, $expense);

            return $expense;
        });

        return redirect()
            ->route('finance.expenses.show', $expense)
            ->with('success', 'Expense recorded.');
    }

    public function show(FinancialExpense $expense)
    {
        $this->ensurePreProjectExpense($expense);

        $expense->load(array_merge($this->expenseRelations(), ['documents.uploader']));

        return view('finance.expenses.show', array_merge(compact('expense'), $this->viewHelpers()));
    }

    public function edit(Request $request, FinancialExpense $expense)
    {
        $this->authorizeFinance(FinancePermission::EDIT);
        $this->ensurePreProjectExpense($expense);
        $this->ensurePending($expense);

        return view('finance.expenses.edit', array_merge(
            ['expense' => $expense->load($this->expenseRelations())],
            $this->formData($request)
        ));
    }

    public function update(Request $request, FinancialExpense $expense)
    {
        $this->authorizeFinance(FinancePermission::EDIT);
        $this->ensurePreProjectExpense($expense);
        $this->ensurePending($expense);

        $validated = $this->validateExpense($request);
        $status = $validated['status'] ?? FinancialExpense::STATUS_PENDING;

        if ($status !== FinancialExpense::STATUS_PENDING) {
            $this->authorizeFinance(FinancePermission::APPROVE);
        }

        DB::transaction(function () use ($request, $expense, $validated, $status) {
            $payload = $this->expensePayload($validated);
            $payload['status'] = $status;
            $payload['updated_by'] = $request->user()->id;

            if ($status === FinancialExpense::STATUS_APPROVED) {
                $payload['approved_by'] = $request->user()->id;
                $payload['approved_at'] = now();
            }

            if ($status !== FinancialExpense::STATUS_APPROVED) {
                $payload['approved_by'] = null;
                $payload['approved_at'] = null;
            }

            $expense->update($payload);
            $this->storeReceipt($request, $expense);
        });

        return redirect()
            ->route('finance.expenses.show', $expense)
            ->with('success', 'Expense updated.');
    }

    public function approve(Request $request, FinancialExpense $expense)
    {
        $this->authorizeFinance(FinancePermission::APPROVE);
        $this->ensurePreProjectExpense($expense);
        $this->ensurePending($expense);

        $expense->update([
            'status' => FinancialExpense::STATUS_APPROVED,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('finance.expenses.show', $expense)
            ->with('success', 'Expense approved.');
    }

    public function reject(Request $request, FinancialExpense $expense)
    {
        $this->authorizeFinance(FinancePermission::APPROVE);
        $this->ensurePreProjectExpense($expense);
        $this->ensurePending($expense);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:5000',
        ]);

        $expense->update([
            'status' => FinancialExpense::STATUS_REJECTED,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'notes' => $this->appendReviewNote($expense->notes, $validated['notes'] ?? null, 'Rejection note'),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('finance.expenses.show', $expense)
            ->with('success', 'Expense rejected.');
    }

    public function destroy(Request $request, FinancialExpense $expense)
    {
        $this->authorizeFinance(FinancePermission::DELETE);
        $this->ensurePreProjectExpense($expense);
        $this->ensurePending($expense);

        DB::transaction(function () use ($expense) {
            foreach ($expense->documents as $document) {
                Storage::disk('local')->delete($document->file_path);
                $document->delete();
            }

            $expense->delete();
        });

        return redirect()
            ->route('finance.expenses.index')
            ->with('success', 'Pending expense deleted.');
    }

    public function downloadDocument(FinancialDocument $document)
    {
        $expense = $document->documentable;

        abort_unless($expense instanceof FinancialExpense, 404);
        $this->ensurePreProjectExpense($expense);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless($disk->exists($document->file_path), 404);

        return response()->download(
            $disk->path($document->file_path),
            $document->file_name
        );
    }

    private function preProjectExpensesQuery(): Builder
    {
        return FinancialExpense::query()
            ->whereNull('project_id')
            ->where(function (Builder $query) {
                $query->whereNotNull('inspection_id')
                    ->orWhereNotNull('job_request_item_id');
            });
    }

    private function expenseRelations(): array
    {
        return [
            'category',
            'inspection.client',
            'inspection.assignedUser',
            'jobRequestItem.jobRequest.client',
            'jobRequestItem.serviceCategory',
            'jobRequestItem.claimer',
            'submitter',
            'approver',
        ];
    }

    private function viewHelpers(): array
    {
        return [
            'financeMoney' => fn ($amount) => '₦' . number_format((float) $amount, 2),
            'financeStatusClass' => fn ($status) => match ($status) {
                FinancialExpense::STATUS_APPROVED => 'bg-green-100 text-green-800',
                FinancialExpense::STATUS_REJECTED => 'bg-red-100 text-red-800',
                default => 'bg-yellow-100 text-yellow-800',
            },
            'financeStatusLabel' => fn ($status) => str_replace('_', ' ', Str::title($status ?? 'pending')),
            'financeContextType' => function (FinancialExpense $expense): string {
                if ($expense->inspection_id) {
                    return 'Inspection';
                }

                if ($expense->job_request_item_id) {
                    return 'Job';
                }

                return 'Context';
            },
            'financeContextReference' => function (FinancialExpense $expense): string {
                if ($expense->inspection_id) {
                    return $expense->inspection?->inspection_code ?? ('Inspection #' . $expense->inspection_id);
                }

                if ($expense->job_request_item_id) {
                    return 'Job Item #' . $expense->job_request_item_id;
                }

                return '—';
            },
            'financeContextTitle' => function (FinancialExpense $expense): string {
                if ($expense->inspection_id) {
                    return $expense->inspection?->title ?? 'Inspection unavailable';
                }

                if ($expense->job_request_item_id) {
                    return $expense->jobRequestItem?->jobRequest?->title ?? $expense->jobRequestItem?->title ?? 'Job unavailable';
                }

                return '—';
            },
            'financeContextClient' => function (FinancialExpense $expense): string {
                if ($expense->inspection_id) {
                    return $expense->inspection?->client?->client_name ?? '—';
                }

                if ($expense->job_request_item_id) {
                    return $expense->jobRequestItem?->jobRequest?->client?->client_name ?? '—';
                }

                return '—';
            },
            'financeAssignedStaff' => function (FinancialExpense $expense): string {
                if ($expense->inspection_id) {
                    return $expense->inspection?->assignedUser?->name ?? 'Unassigned';
                }

                if ($expense->job_request_item_id) {
                    return $expense->jobRequestItem?->claimer?->name ?? 'Unassigned';
                }

                return '—';
            },
        ];
    }

    private function formData(Request $request): array
    {
        return [
            'categories' => FinanceExpenseCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'inspections' => Inspection::query()
                ->with(['client', 'assignedUser'])
                ->latest()
                ->limit(100)
                ->get(),
            'jobItems' => JobRequestItem::query()
                ->with(['jobRequest.client', 'serviceCategory', 'claimer'])
                ->latest()
                ->limit(100)
                ->get(),
            'selectedContextType' => $request->query('context_type'),
            'selectedContextId' => $request->query('context_id'),
        ];
    }

    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'context_type' => ['required', Rule::in(['inspection', 'job'])],
            'inspection_id' => ['required_if:context_type,inspection', 'nullable', 'exists:inspections,id'],
            'job_request_item_id' => ['required_if:context_type,job', 'nullable', 'exists:job_request_items,id'],
            'finance_expense_category_id' => ['required', 'exists:finance_expense_categories,id'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'incurred_on' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in([
                FinancialExpense::STATUS_PENDING,
                FinancialExpense::STATUS_APPROVED,
                FinancialExpense::STATUS_REJECTED,
            ])],
            'notes' => ['nullable', 'string', 'max:5000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);
    }

    private function expensePayload(array $validated): array
    {
        $contextType = $validated['context_type'];
        $inspectionId = $contextType === 'inspection' ? (int) $validated['inspection_id'] : null;
        $jobItemId = $contextType === 'job' ? (int) $validated['job_request_item_id'] : null;

        return [
            'project_id' => null,
            'inspection_id' => $inspectionId,
            'job_request_item_id' => $jobItemId,
            'original_context_type' => $contextType === 'inspection' ? Inspection::class : JobRequestItem::class,
            'original_context_id' => $contextType === 'inspection' ? $inspectionId : $jobItemId,
            'finance_expense_category_id' => $validated['finance_expense_category_id'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'incurred_on' => $validated['incurred_on'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    private function storeReceipt(Request $request, FinancialExpense $expense): void
    {
        if (!$request->hasFile('receipt')) {
            return;
        }

        $file = $request->file('receipt');
        $path = $file->store('financial-documents/' . now()->format('Y/m'), 'local');

        $expense->documents()->create([
            'uploaded_by' => $request->user()->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'visibility' => FinancialDocument::VISIBILITY_PRIVATE,
        ]);
    }

    private function authorizeFinance(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user instanceof User && $user->hasFinancePermission($permission),
            403,
            'Unauthorized financial access'
        );
    }

    private function ensurePreProjectExpense(FinancialExpense $expense): void
    {
        abort_unless(
            $expense->project_id === null && ($expense->inspection_id !== null || $expense->job_request_item_id !== null),
            404
        );
    }

    private function ensurePending(FinancialExpense $expense): void
    {
        abort_if($expense->status !== FinancialExpense::STATUS_PENDING, 409, 'Only pending expenses can be changed by this action.');
    }

    private function appendReviewNote(?string $existing, ?string $note, string $label): ?string
    {
        $note = trim((string) $note);

        if ($note === '') {
            return $existing;
        }

        $existing = trim((string) $existing);

        if ($existing === '') {
            return "{$label}: {$note}";
        }

        return "{$existing}\n\n{$label}: {$note}";
    }
}
