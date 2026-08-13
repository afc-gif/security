<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceExpenseCategory;
use App\Models\FinancePermission;
use App\Models\FinancialDocument;
use App\Models\FinancialExpense;
use App\Models\FinancialMaterialCost;
use App\Models\Inspection;
use App\Models\JobRequestItem;
use App\Models\Project;
use App\Models\ProjectFinancial;
use App\Models\ProjectPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FinanceController extends Controller
{
    public function dashboard()
    {
        $this->authorizeFinance(FinancePermission::VIEW);

        $projectValueTotal = (float) ProjectFinancial::query()->sum('contract_value');
        $receivedTotal = (float) ProjectPayment::query()->sum('amount');
        $outstandingTotal = max(0, $projectValueTotal - $receivedTotal);

        $approvedExpensesTotal = (float) FinancialExpense::query()
            ->where('status', FinancialExpense::STATUS_APPROVED)
            ->sum('amount');

        $approvedMaterialsTotal = (float) FinancialMaterialCost::query()
            ->where('status', FinancialMaterialCost::STATUS_APPROVED)
            ->sum('total_cost');

        $approvedCostsTotal = $approvedExpensesTotal + $approvedMaterialsTotal;
        $estimatedProfitTotal = $projectValueTotal - $approvedCostsTotal;

        $pendingMaterialCostsCount = FinancialMaterialCost::query()
            ->where('status', FinancialMaterialCost::STATUS_PENDING)
            ->count();

        $pendingExpensesCount = FinancialExpense::query()
            ->where('status', FinancialExpense::STATUS_PENDING)
            ->count();

        $pendingReviewCount = $pendingExpensesCount + $pendingMaterialCostsCount;

        $recentJobs = JobRequestItem::query()
            ->with(['jobRequest.client', 'claimer'])
            ->latest('id')
            ->limit(3)
            ->get();

        $recentProjects = Project::query()
            ->with(['client', 'financial'])
            ->latest('id')
            ->limit(3)
            ->get();

        $attentionItems = [];
        if ($pendingReviewCount > 0) {
            $attentionItems[] = [
                'type' => 'warning',
                'title' => 'Pending Financial Approvals',
                'count' => $pendingReviewCount,
                'description' => "{$pendingReviewCount} pending expense or material cost item(s) awaiting review.",
                'link' => route('finance.jobs.index'),
                'link_text' => 'Review Pending',
            ];
        }

        return view('finance.dashboard', array_merge([
            'projectValueTotal' => $projectValueTotal,
            'receivedTotal' => $receivedTotal,
            'outstandingTotal' => $outstandingTotal,
            'approvedCostsTotal' => $approvedCostsTotal,
            'estimatedProfitTotal' => $estimatedProfitTotal,
            'recentJobs' => $recentJobs,
            'recentProjects' => $recentProjects,
            'attentionItems' => $attentionItems,
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

    public function jobs(Request $request)
    {
        $filters = $request->only(['search', 'status']);
        $search = trim((string) ($filters['search'] ?? ''));

        $jobs = JobRequestItem::query()
            ->with(['jobRequest.client', 'serviceCategory', 'claimer'])
            ->withSum([
                'financialExpenses as approved_expenses_total' => fn (Builder $query) => $query
                    ->where('status', FinancialExpense::STATUS_APPROVED),
            ], 'amount')
            ->withSum('financialExpenses as expenses_total', 'amount')
            ->withCount('financialExpenses')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $searchQuery) use ($search) {
                    $searchQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('jobRequest', fn (Builder $jobRequestQuery) => $jobRequestQuery
                            ->where('title', 'like', "%{$search}%")
                            ->orWhereHas('client', fn (Builder $clientQuery) => $clientQuery
                                ->where('client_name', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%")
                                ->orWhere('contact_person', 'like', "%{$search}%")
                            )
                        )
                        ->orWhereHas('serviceCategory', fn (Builder $categoryQuery) => $categoryQuery
                            ->where('name', 'like', "%{$search}%")
                        );
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10);

        $jobs->appends($request->query());

        $statuses = JobRequestItem::query()
            ->select('status')
            ->distinct()
            ->whereNotNull('status')
            ->orderBy('status')
            ->pluck('status');

        return view('finance.jobs.index', array_merge(
            compact('jobs', 'filters', 'statuses'),
            $this->viewHelpers()
        ));
    }

    public function jobShow(JobRequestItem $job)
    {
        $job->load([
            'jobRequest.client',
            'serviceCategory',
            'claimer',
            'financialExpenses.category',
            'financialExpenses.submitter',
            'financialExpenses.documents.uploader',
            'payments.recorder',
            'payments.documents.uploader',
        ]);

        $expenses = $job->financialExpenses
            ->sortByDesc(fn (FinancialExpense $expense) => $expense->incurred_on?->getTimestamp() ?? $expense->created_at?->getTimestamp() ?? 0)
            ->values();

        $payments = $job->payments
            ->sortByDesc(fn (ProjectPayment $payment) => $payment->payment_date?->getTimestamp() ?? $payment->created_at?->getTimestamp() ?? 0)
            ->values();

        $summary = [
            'total' => (float) $expenses
                ->sum(fn (FinancialExpense $expense) => (float) $expense->amount),
            'approved_total' => (float) $expenses
                ->where('status', FinancialExpense::STATUS_APPROVED)
                ->sum(fn (FinancialExpense $expense) => (float) $expense->amount),
            'pending_total' => (float) $expenses
                ->where('status', FinancialExpense::STATUS_PENDING)
                ->sum(fn (FinancialExpense $expense) => (float) $expense->amount),
            'expense_count' => $expenses->count(),
            'total_received' => (float) $payments->sum(fn (ProjectPayment $payment) => (float) $payment->amount),
            'payment_count' => $payments->count(),
        ];

        $categories = FinanceExpenseCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('finance.jobs.show', array_merge(
            compact('job', 'expenses', 'payments', 'summary', 'categories'),
            $this->viewHelpers()
        ));
    }

    public function storeJobExpense(Request $request, JobRequestItem $job)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        $validated = $this->validateJobExpense($request);

        $expense = DB::transaction(function () use ($request, $job, $validated) {
            $payload = [
                'project_id' => null,
                'inspection_id' => null,
                'job_request_item_id' => $job->id,
                'original_context_type' => JobRequestItem::class,
                'original_context_id' => $job->id,
                'finance_expense_category_id' => $validated['finance_expense_category_id'],
                'description' => $this->expenseDescription($validated),
                'amount' => $validated['amount'],
                'incurred_on' => $validated['incurred_on'] ?? null,
                'status' => FinancialExpense::STATUS_PENDING,
                'submitted_by' => $request->user()->id,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ];

            $expense = FinancialExpense::create($payload);
            $this->storeFinancialDocument($request, $expense);

            return $expense;
        });

        return redirect()
            ->route('finance.jobs.show', $job)
            ->with('success', 'Expense added to this job.');
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
            $this->storeFinancialDocument($request, $expense);

            return $expense;
        });

        return redirect()
            ->route('finance.expenses.show', $expense)
            ->with('success', 'Expense recorded.');
    }

    public function show(FinancialExpense $expense)
    {
        $this->ensureFinanceExpense($expense);

        $expense->load(array_merge($this->expenseRelations(), ['documents.uploader']));

        return view('finance.expenses.show', array_merge(compact('expense'), $this->viewHelpers()));
    }

    public function edit(Request $request, FinancialExpense $expense)
    {
        $this->authorizeFinance(FinancePermission::EDIT);
        $this->ensureFinanceExpense($expense);
        $this->ensurePending($expense);

        return view('finance.expenses.edit', array_merge(
            ['expense' => $expense->load($this->expenseRelations())],
            $this->formData($request)
        ));
    }

    public function update(Request $request, FinancialExpense $expense)
    {
        $this->authorizeFinance(FinancePermission::EDIT);
        $this->ensureFinanceExpense($expense);
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
            $this->storeFinancialDocument($request, $expense);
        });

        return redirect()
            ->route('finance.expenses.show', $expense)
            ->with('success', 'Expense updated.');
    }

    public function approve(Request $request, FinancialExpense $expense)
    {
        $this->authorizeFinance(FinancePermission::APPROVE);
        $this->ensureFinanceExpense($expense);
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
        $this->ensureFinanceExpense($expense);
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
        $this->ensureFinanceExpense($expense);
        $this->ensurePending($expense);

        $project = $expense->project;
        $job = $expense->jobRequestItem;

        DB::transaction(function () use ($expense) {
            foreach ($expense->documents as $document) {
                Storage::disk('local')->delete($document->file_path);
                $document->delete();
            }

            $expense->delete();
        });

        return redirect()
            ->route($project ? 'finance.projects.show' : ($job ? 'finance.jobs.show' : 'finance.expenses.index'), $project ?: ($job ?: []))
            ->with('success', 'Pending expense deleted.');
    }

    public function downloadDocument(FinancialDocument $document)
    {
        $documentable = $document->documentable;

        if ($documentable instanceof FinancialExpense) {
            $this->ensureFinanceExpense($documentable);
        } elseif ($documentable instanceof FinancialMaterialCost) {
            $this->ensureProjectMaterialCost($documentable);
        } elseif ($documentable instanceof ProjectPayment) {
            // Accessible to finance users
        } else {
            abort(404);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless($disk->exists($document->file_path), 404);

        return response()->download(
            $disk->path($document->file_path),
            $document->file_name
        );
    }

    public function projects(Request $request)
    {
        $filters = $request->only(['status', 'search']);

        $projects = Project::query()
            ->with(['client', 'financial'])
            ->withCount([
                'financialExpenses',
                'financialMaterialCosts',
            ])
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('project_code', 'like', "%{$search}%")
                        ->orWhereHas('client', fn (Builder $cq) => $cq->where('company_name', 'like', "%{$search}%")->orWhere('client_name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10);

        $projects->appends($request->query());

        $summaries = collect($projects->items())
            ->mapWithKeys(fn (Project $project) => [$project->id => $this->projectFinancialSummary($project)])
            ->all();

        return view('finance.projects.index', array_merge(
            compact('projects', 'filters', 'summaries'),
            $this->viewHelpers()
        ));
    }

    public function projectShow(Project $project)
    {
        $project->load([
            'client',
            'financial',
            'financialExpenses.category',
            'financialExpenses.submitter',
            'financialExpenses.approver',
            'financialExpenses.documents.uploader',
            'financialMaterialCosts.submitter',
            'financialMaterialCosts.approver',
            'financialMaterialCosts.documents.uploader',
            'payments.recorder',
            'payments.documents.uploader',
        ]);

        $summary = $this->projectFinancialSummary($project);
        $categories = FinanceExpenseCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $financialDocuments = $project->financialExpenses
            ->flatMap(fn (FinancialExpense $expense) => $expense->documents->map(fn (FinancialDocument $document) => [
                'document' => $document,
                'record_type' => 'Expense',
                'record_label' => $expense->description,
            ]))
            ->concat($project->financialMaterialCosts->flatMap(fn (FinancialMaterialCost $materialCost) => $materialCost->documents->map(fn (FinancialDocument $document) => [
                'document' => $document,
                'record_type' => 'Material',
                'record_label' => $materialCost->material_name,
            ])))
            ->sortByDesc(fn (array $item) => $item['document']->created_at?->getTimestamp() ?? 0)
            ->values();

        return view('finance.projects.show', array_merge(
            compact('project', 'summary', 'categories', 'financialDocuments'),
            $this->viewHelpers()
        ));
    }

    public function storeProjectExpense(Request $request, Project $project)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        $validated = $this->validateJobExpense($request);

        DB::transaction(function () use ($request, $project, $validated) {
            $payload = [
                'project_id' => $project->id,
                'inspection_id' => null,
                'job_request_item_id' => null,
                'original_context_type' => Project::class,
                'original_context_id' => $project->id,
                'finance_expense_category_id' => $validated['finance_expense_category_id'],
                'description' => $this->expenseDescription($validated),
                'amount' => $validated['amount'],
                'incurred_on' => $validated['incurred_on'] ?? null,
                'status' => FinancialExpense::STATUS_PENDING,
                'submitted_by' => $request->user()->id,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ];

            $expense = FinancialExpense::create($payload);
            $this->storeFinancialDocument($request, $expense);
        });

        return redirect()
            ->route('finance.projects.show', $project)
            ->with('success', 'Expense added to this project.');
    }

    public function saveProjectFinancial(Request $request, Project $project)
    {
        $project->load('financial');
        $this->authorizeFinance($project->financial ? FinancePermission::EDIT : FinancePermission::CREATE);

        $validated = $request->validate([
            'contract_value' => ['nullable', 'numeric', 'min:0'],
            'approved_budget' => ['nullable', 'numeric', 'min:0'],
            'financial_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $payload = [
            'contract_value' => $validated['contract_value'] ?? null,
            'approved_budget' => $validated['approved_budget'] ?? null,
            'financial_notes' => $validated['financial_notes'] ?? null,
            'updated_by' => $request->user()->id,
        ];

        if ($project->financial) {
            $project->financial->update($payload);
        } else {
            ProjectFinancial::create(array_merge($payload, [
                'project_id' => $project->id,
                'created_by' => $request->user()->id,
            ]));
        }

        return redirect()
            ->route('finance.projects.show', $project)
            ->with('success', 'Project financial profile saved.');
    }

    public function createMaterialCost(Project $project)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        return view('finance.material-costs.create', array_merge(
            compact('project'),
            $this->viewHelpers()
        ));
    }

    public function storeMaterialCost(Request $request, Project $project)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        $validated = $this->validateMaterialCost($request);
        $status = $validated['status'] ?? FinancialMaterialCost::STATUS_PENDING;

        if ($status !== FinancialMaterialCost::STATUS_PENDING) {
            $this->authorizeFinance(FinancePermission::APPROVE);
        }

        $materialCost = DB::transaction(function () use ($request, $project, $validated, $status) {
            $payload = $this->materialCostPayload($project, $validated);
            $payload['status'] = $status;
            $payload['submitted_by'] = $request->user()->id;
            $payload['created_by'] = $request->user()->id;
            $payload['updated_by'] = $request->user()->id;

            if ($status === FinancialMaterialCost::STATUS_APPROVED) {
                $payload['approved_by'] = $request->user()->id;
                $payload['approved_at'] = now();
            }

            $materialCost = FinancialMaterialCost::create($payload);
            $this->storeFinancialDocument($request, $materialCost);

            return $materialCost;
        });

        return redirect()
            ->route('finance.material-costs.show', $materialCost)
            ->with('success', 'Material cost recorded.');
    }

    public function showMaterialCost(FinancialMaterialCost $materialCost)
    {
        $this->ensureProjectMaterialCost($materialCost);
        $materialCost->load(['project.client', 'submitter', 'approver', 'documents.uploader']);

        return view('finance.material-costs.show', array_merge(
            compact('materialCost'),
            $this->viewHelpers()
        ));
    }

    public function editMaterialCost(FinancialMaterialCost $materialCost)
    {
        $this->authorizeFinance(FinancePermission::EDIT);
        $this->ensureProjectMaterialCost($materialCost);
        $this->ensurePendingMaterialCost($materialCost);
        $materialCost->load('project.client');

        return view('finance.material-costs.edit', array_merge(
            compact('materialCost'),
            $this->viewHelpers()
        ));
    }

    public function updateMaterialCost(Request $request, FinancialMaterialCost $materialCost)
    {
        $this->authorizeFinance(FinancePermission::EDIT);
        $this->ensureProjectMaterialCost($materialCost);
        $this->ensurePendingMaterialCost($materialCost);

        $validated = $this->validateMaterialCost($request);
        $status = $validated['status'] ?? FinancialMaterialCost::STATUS_PENDING;

        if ($status !== FinancialMaterialCost::STATUS_PENDING) {
            $this->authorizeFinance(FinancePermission::APPROVE);
        }

        DB::transaction(function () use ($request, $materialCost, $validated, $status) {
            $payload = $this->materialCostPayload($materialCost->project, $validated);
            $payload['status'] = $status;
            $payload['updated_by'] = $request->user()->id;

            if ($status === FinancialMaterialCost::STATUS_APPROVED) {
                $payload['approved_by'] = $request->user()->id;
                $payload['approved_at'] = now();
            }

            if ($status !== FinancialMaterialCost::STATUS_APPROVED) {
                $payload['approved_by'] = null;
                $payload['approved_at'] = null;
            }

            $materialCost->update($payload);
            $this->storeFinancialDocument($request, $materialCost);
        });

        return redirect()
            ->route('finance.material-costs.show', $materialCost)
            ->with('success', 'Material cost updated.');
    }

    public function approveMaterialCost(Request $request, FinancialMaterialCost $materialCost)
    {
        $this->authorizeFinance(FinancePermission::APPROVE);
        $this->ensureProjectMaterialCost($materialCost);
        $this->ensurePendingMaterialCost($materialCost);

        $materialCost->update([
            'status' => FinancialMaterialCost::STATUS_APPROVED,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('finance.material-costs.show', $materialCost)
            ->with('success', 'Material cost approved.');
    }

    public function rejectMaterialCost(Request $request, FinancialMaterialCost $materialCost)
    {
        $this->authorizeFinance(FinancePermission::APPROVE);
        $this->ensureProjectMaterialCost($materialCost);
        $this->ensurePendingMaterialCost($materialCost);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:5000',
        ]);

        $materialCost->update([
            'status' => FinancialMaterialCost::STATUS_REJECTED,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'notes' => $this->appendReviewNote($materialCost->notes, $validated['notes'] ?? null, 'Rejection note'),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('finance.material-costs.show', $materialCost)
            ->with('success', 'Material cost rejected.');
    }

    public function destroyMaterialCost(FinancialMaterialCost $materialCost)
    {
        $this->authorizeFinance(FinancePermission::DELETE);
        $this->ensureProjectMaterialCost($materialCost);
        $this->ensurePendingMaterialCost($materialCost);

        $project = $materialCost->project;

        DB::transaction(function () use ($materialCost) {
            foreach ($materialCost->documents as $document) {
                Storage::disk('local')->delete($document->file_path);
                $document->delete();
            }

            $materialCost->delete();
        });

        return redirect()
            ->route('finance.projects.show', $project)
            ->with('success', 'Pending material cost deleted.');
    }

    public function officeExpenses(Request $request)
    {
        $this->authorizeFinance(FinancePermission::VIEW);

        $filters = $request->only(['category', 'status', 'date_from', 'date_to']);
        $categories = FinanceExpenseCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $expenses = FinancialExpense::query()
            ->office()
            ->with(['category', 'submitter', 'approver'])
            ->when($filters['category'] ?? null, fn (Builder $q, $cat) => $q->where('finance_expense_category_id', $cat))
            ->when($filters['status'] ?? null, fn (Builder $q, $st) => $q->where('status', $st))
            ->when($filters['date_from'] ?? null, fn (Builder $q, $d) => $q->whereDate('incurred_on', '>=', $d))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $d) => $q->whereDate('incurred_on', '<=', $d))
            ->latest()
            ->paginate(20);

        $expenses->appends($request->query());

        $summary = [
            'total_approved' => (float) FinancialExpense::office()
                ->where('status', FinancialExpense::STATUS_APPROVED)
                ->sum('amount'),
            'total_pending' => (float) FinancialExpense::office()
                ->where('status', FinancialExpense::STATUS_PENDING)
                ->sum('amount'),
            'count' => FinancialExpense::office()->count(),
        ];

        return view('finance.office-expenses.index', array_merge(
            compact('expenses', 'categories', 'filters', 'summary'),
            $this->viewHelpers()
        ));
    }

    public function createOfficeExpense(Request $request)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        $categories = FinanceExpenseCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('finance.office-expenses.create', array_merge(
            compact('categories'),
            $this->viewHelpers()
        ));
    }

    public function storeOfficeExpense(Request $request)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        $validated = $this->validateOfficeExpense($request);
        $status = $validated['status'] ?? FinancialExpense::STATUS_PENDING;

        if ($status !== FinancialExpense::STATUS_PENDING) {
            $this->authorizeFinance(FinancePermission::APPROVE);
        }

        $expense = DB::transaction(function () use ($request, $validated, $status) {
            $payload = [
                'is_office_expense' => true,
                'project_id' => null,
                'inspection_id' => null,
                'job_request_item_id' => null,
                'original_context_type' => 'office',
                'original_context_id' => null,
                'finance_expense_category_id' => $validated['finance_expense_category_id'],
                'description' => $this->expenseDescription($validated),
                'amount' => $validated['amount'],
                'incurred_on' => $validated['incurred_on'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => $status,
                'submitted_by' => $request->user()->id,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ];

            if ($status === FinancialExpense::STATUS_APPROVED) {
                $payload['approved_by'] = $request->user()->id;
                $payload['approved_at'] = now();
            }

            $expense = FinancialExpense::create($payload);
            $this->storeFinancialDocument($request, $expense);

            return $expense;
        });

        return redirect()
            ->route('finance.office-expenses.show', $expense)
            ->with('success', 'Office expense recorded.');
    }

    public function showOfficeExpense(FinancialExpense $expense)
    {
        $this->ensureOfficeExpense($expense);
        $expense->load(['category', 'submitter', 'approver', 'documents.uploader']);

        return view('finance.office-expenses.show', array_merge(
            compact('expense'),
            $this->viewHelpers()
        ));
    }

    public function editOfficeExpense(FinancialExpense $expense)
    {
        $this->authorizeFinance(FinancePermission::EDIT);
        $this->ensureOfficeExpense($expense);
        $this->ensurePending($expense);

        $categories = FinanceExpenseCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('finance.office-expenses.edit', array_merge(
            compact('expense', 'categories'),
            $this->viewHelpers()
        ));
    }

    public function updateOfficeExpense(Request $request, FinancialExpense $expense)
    {
        $this->authorizeFinance(FinancePermission::EDIT);
        $this->ensureOfficeExpense($expense);
        $this->ensurePending($expense);

        $validated = $this->validateOfficeExpense($request);
        $status = $validated['status'] ?? FinancialExpense::STATUS_PENDING;

        if ($status !== FinancialExpense::STATUS_PENDING) {
            $this->authorizeFinance(FinancePermission::APPROVE);
        }

        DB::transaction(function () use ($request, $expense, $validated, $status) {
            $payload = [
                'finance_expense_category_id' => $validated['finance_expense_category_id'],
                'description' => $this->expenseDescription($validated),
                'amount' => $validated['amount'],
                'incurred_on' => $validated['incurred_on'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => $status,
                'updated_by' => $request->user()->id,
            ];

            if ($status === FinancialExpense::STATUS_APPROVED) {
                $payload['approved_by'] = $request->user()->id;
                $payload['approved_at'] = now();
            } else {
                $payload['approved_by'] = null;
                $payload['approved_at'] = null;
            }

            $expense->update($payload);
            $this->storeFinancialDocument($request, $expense);
        });

        return redirect()
            ->route('finance.office-expenses.show', $expense)
            ->with('success', 'Office expense updated.');
    }

    public function destroyOfficeExpense(Request $request, FinancialExpense $expense)
    {
        $this->authorizeFinance(FinancePermission::DELETE);
        $this->ensureOfficeExpense($expense);
        $this->ensurePending($expense);

        DB::transaction(function () use ($expense) {
            foreach ($expense->documents as $document) {
                Storage::disk('local')->delete($document->file_path);
                $document->delete();
            }
            $expense->delete();
        });

        return redirect()
            ->route('finance.office-expenses.index')
            ->with('success', 'Office expense deleted.');
    }

    public function approveOfficeExpense(Request $request, FinancialExpense $expense)
    {
        $this->authorizeFinance(FinancePermission::APPROVE);
        $this->ensureOfficeExpense($expense);
        $this->ensurePending($expense);

        $expense->update([
            'status' => FinancialExpense::STATUS_APPROVED,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('finance.office-expenses.show', $expense)
            ->with('success', 'Office expense approved.');
    }

    public function rejectOfficeExpense(Request $request, FinancialExpense $expense)
    {
        $this->authorizeFinance(FinancePermission::APPROVE);
        $this->ensureOfficeExpense($expense);
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
            ->route('finance.office-expenses.show', $expense)
            ->with('success', 'Office expense rejected.');
    }

    private function preProjectExpensesQuery(): Builder
    {
        return FinancialExpense::query()
            ->operational()
            ->whereNull('project_id')
            ->where(function (Builder $query) {
                $query->whereNotNull('inspection_id')
                    ->orWhereNotNull('job_request_item_id');
            });
    }

    private function expenseRelations(): array
    {
        return [
            'project.client',
            'project.fieldStaff',
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
                if ($expense->is_office_expense) {
                    return 'Office';
                }

                if ($expense->project_id) {
                    return 'Project';
                }

                if ($expense->inspection_id) {
                    return 'Inspection';
                }

                if ($expense->job_request_item_id) {
                    return 'Job';
                }

                return 'Context';
            },
            'financeContextReference' => function (FinancialExpense $expense): string {
                if ($expense->project_id) {
                    return $expense->project?->project_code ?? ('Project #' . $expense->project_id);
                }

                if ($expense->inspection_id) {
                    return $expense->inspection?->inspection_code ?? ('Inspection #' . $expense->inspection_id);
                }

                if ($expense->job_request_item_id) {
                    return 'Job Item #' . $expense->job_request_item_id;
                }

                return '—';
            },
            'financeContextTitle' => function (FinancialExpense $expense): string {
                if ($expense->project_id) {
                    return $expense->project?->title ?? 'Project unavailable';
                }

                if ($expense->inspection_id) {
                    return $expense->inspection?->title ?? 'Inspection unavailable';
                }

                if ($expense->job_request_item_id) {
                    return $expense->jobRequestItem?->jobRequest?->title ?? $expense->jobRequestItem?->title ?? 'Job unavailable';
                }

                return '—';
            },
            'financeContextClient' => function (FinancialExpense $expense): string {
                if ($expense->project_id) {
                    return $expense->project?->client?->client_name ?? '—';
                }

                if ($expense->inspection_id) {
                    return $expense->inspection?->client?->client_name ?? '—';
                }

                if ($expense->job_request_item_id) {
                    return $expense->jobRequestItem?->jobRequest?->client?->client_name ?? '—';
                }

                return '—';
            },
            'financeAssignedStaff' => function (FinancialExpense $expense): string {
                if ($expense->project_id) {
                    return $expense->project?->fieldStaff?->name ?? 'Unassigned';
                }

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
            'projects' => Project::query()
                ->with(['client', 'fieldStaff'])
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
            'context_type' => ['required', Rule::in(['inspection', 'job', 'project'])],
            'inspection_id' => ['required_if:context_type,inspection', 'nullable', 'exists:inspections,id'],
            'job_request_item_id' => ['required_if:context_type,job', 'nullable', 'exists:job_request_items,id'],
            'project_id' => ['required_if:context_type,project', 'nullable', 'exists:projects,id'],
            'finance_expense_category_id' => ['required', 'exists:finance_expense_categories,id'],
            'description' => ['nullable', 'string', 'max:255'],
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

    private function validateJobExpense(Request $request): array
    {
        return $request->validate([
            'finance_expense_category_id' => ['required', 'exists:finance_expense_categories,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'incurred_on' => ['nullable', 'date'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);
    }

    private function validateOfficeExpense(Request $request): array
    {
        return $request->validate([
            'finance_expense_category_id' => ['required', 'exists:finance_expense_categories,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'incurred_on' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:255'],
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
        $projectId = $contextType === 'project' ? (int) $validated['project_id'] : null;

        return [
            'project_id' => $projectId,
            'inspection_id' => $inspectionId,
            'job_request_item_id' => $jobItemId,
            'original_context_type' => match ($contextType) {
                'inspection' => Inspection::class,
                'job' => JobRequestItem::class,
                default => Project::class,
            },
            'original_context_id' => match ($contextType) {
                'inspection' => $inspectionId,
                'job' => $jobItemId,
                default => $projectId,
            },
            'finance_expense_category_id' => $validated['finance_expense_category_id'],
            'description' => $this->expenseDescription($validated),
            'amount' => $validated['amount'],
            'incurred_on' => $validated['incurred_on'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    private function expenseDescription(array $validated): string
    {
        $description = trim((string) ($validated['description'] ?? ''));

        if ($description !== '') {
            return $description;
        }

        return FinanceExpenseCategory::query()
            ->whereKey($validated['finance_expense_category_id'])
            ->value('name') ?? 'Expense';
    }

    private function validateMaterialCost(Request $request): array
    {
        return $request->validate([
            'material_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'incurred_on' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in([
                FinancialMaterialCost::STATUS_PENDING,
                FinancialMaterialCost::STATUS_APPROVED,
                FinancialMaterialCost::STATUS_REJECTED,
            ])],
            'notes' => ['nullable', 'string', 'max:5000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);
    }

    private function materialCostPayload(Project $project, array $validated): array
    {
        return [
            'project_id' => $project->id,
            'inspection_id' => null,
            'job_request_item_id' => null,
            'original_context_type' => Project::class,
            'original_context_id' => $project->id,
            'material_name' => $validated['material_name'],
            'quantity' => $validated['quantity'],
            'unit' => $validated['unit'] ?? null,
            'unit_cost' => $validated['unit_cost'],
            'total_cost' => $this->calculateTotalCost($validated['quantity'], $validated['unit_cost']),
            'incurred_on' => $validated['incurred_on'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    private function calculateTotalCost(string|int|float $quantity, string|int|float $unitCost): string
    {
        return number_format(((float) $quantity) * ((float) $unitCost), 2, '.', '');
    }

    private function projectFinancialSummary(Project $project): array
    {
        $financial = $project->relationLoaded('financial')
            ? $project->financial
            : $project->financial()->first();

        $approvedExpenses = (float) $project->financialExpenses()
            ->where('status', FinancialExpense::STATUS_APPROVED)
            ->sum('amount');

        $approvedMaterials = (float) $project->financialMaterialCosts()
            ->where('status', FinancialMaterialCost::STATUS_APPROVED)
            ->sum('total_cost');

        $totalPaid = (float) $project->payments()->sum('amount');
        $approvedCost = $approvedExpenses + $approvedMaterials;
        $approvedBudget = $financial?->approved_budget !== null ? (float) $financial->approved_budget : null;
        $contractValue = $financial?->contract_value !== null ? (float) $financial->contract_value : null;
        $isOverpaid = $contractValue !== null && $totalPaid > $contractValue;
        $overpaidAmount = $isOverpaid ? $totalPaid - $contractValue : 0;
        $balanceDue = $contractValue !== null ? max(0, $contractValue - $totalPaid) : null;

        return [
            'contract_value' => $contractValue,
            'approved_budget' => $approvedBudget,
            'approved_expenses' => $approvedExpenses,
            'approved_materials' => $approvedMaterials,
            'approved_cost' => $approvedCost,
            'remaining_budget' => $approvedBudget === null ? null : $approvedBudget - $approvedCost,
            'estimated_profit' => $contractValue === null ? null : $contractValue - $approvedCost,
            'is_over_budget' => $approvedBudget !== null && $approvedCost > $approvedBudget,
            'total_paid' => $totalPaid,
            'balance_due' => $balanceDue,
            'is_overpaid' => $isOverpaid,
            'overpaid_amount' => $overpaidAmount,
        ];
    }

    private function projectFinancialActivity(Project $project)
    {
        $expenses = $project->financialExpenses
            ->map(fn (FinancialExpense $expense) => [
                'type' => 'Expense',
                'label' => $expense->description,
                'amount' => (float) $expense->amount,
                'status' => $expense->status,
                'date' => $expense->incurred_on,
                'created_at' => $expense->created_at,
                'url' => route('finance.expenses.show', $expense),
                'meta' => $expense->category?->name ?? 'Expense',
            ]);

        $materials = $project->financialMaterialCosts
            ->map(fn (FinancialMaterialCost $materialCost) => [
                'type' => 'Material',
                'label' => $materialCost->material_name,
                'amount' => (float) $materialCost->total_cost,
                'status' => $materialCost->status,
                'date' => $materialCost->incurred_on,
                'created_at' => $materialCost->created_at,
                'url' => route('finance.material-costs.show', $materialCost),
                'meta' => trim(($materialCost->quantity ?? '0') . ' ' . ($materialCost->unit ?? 'units')),
            ]);

        return $expenses
            ->concat($materials)
            ->sortByDesc(fn (array $item) => $item['date']?->getTimestamp() ?? $item['created_at']?->getTimestamp() ?? 0)
            ->values();
    }

    private function storeFinancialDocument(Request $request, Model $documentable): void
    {
        if (!$request->hasFile('receipt')) {
            return;
        }

        $file = $request->file('receipt');
        $path = $file->store('financial-documents/' . now()->format('Y/m'), 'local');

        $documentable->documents()->create([
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

    private function ensureFinanceExpense(FinancialExpense $expense): void
    {
        // Allow office expenses OR expenses with a linked operational context
        abort_unless(
            $expense->is_office_expense ||
            $expense->project_id !== null ||
            $expense->inspection_id !== null ||
            $expense->job_request_item_id !== null,
            404
        );
    }

    private function ensureOfficeExpense(FinancialExpense $expense): void
    {
        abort_unless($expense->is_office_expense, 404);
    }

    private function ensureProjectMaterialCost(FinancialMaterialCost $materialCost): void
    {
        abort_unless(
            $materialCost->project_id !== null,
            404
        );
    }

    private function ensurePending(FinancialExpense $expense): void
    {
        abort_if($expense->status !== FinancialExpense::STATUS_PENDING, 409, 'Only pending expenses can be changed by this action.');
    }

    private function ensurePendingMaterialCost(FinancialMaterialCost $materialCost): void
    {
        abort_if($materialCost->status !== FinancialMaterialCost::STATUS_PENDING, 409, 'Only pending material costs can be changed by this action.');
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

    public function storeJobPayment(Request $request, JobRequestItem $job)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_type' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if (!empty($validated['reference'])) {
            $existing = ProjectPayment::where('reference', $validated['reference'])->first();
            if ($existing) {
                return back()->withErrors(['reference' => 'A payment with this reference number has already been recorded.'])->withInput();
            }
        }

        $job->load(['jobRequest', 'project']);

        DB::transaction(function () use ($request, $job, $validated) {
            $payment = ProjectPayment::create([
                'project_id' => $job->project?->id,
                'inspection_id' => null,
                'job_request_id' => $job->job_request_id,
                'job_request_item_id' => $job->id,
                'client_id' => $job->jobRequest?->client_id,
                'original_context_type' => JobRequestItem::class,
                'original_context_id' => $job->id,
                'payment_type' => $validated['payment_type'] ?? ProjectPayment::TYPE_PART_PAYMENT,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => $request->user()->id,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $this->storeFinancialDocument($request, $payment);
        });

        return back()->with('success', 'Money received recorded successfully.');
    }

    public function storeInspectionPayment(Request $request, Inspection $inspection)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_type' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if (!empty($validated['reference'])) {
            $existing = ProjectPayment::where('reference', $validated['reference'])->first();
            if ($existing) {
                return back()->withErrors(['reference' => 'A payment with this reference number has already been recorded.'])->withInput();
            }
        }

        $inspection->load(['project']);

        DB::transaction(function () use ($request, $inspection, $validated) {
            $payment = ProjectPayment::create([
                'project_id' => $inspection->project?->id,
                'inspection_id' => $inspection->id,
                'job_request_id' => null,
                'job_request_item_id' => null,
                'client_id' => $inspection->client_id,
                'original_context_type' => Inspection::class,
                'original_context_id' => $inspection->id,
                'payment_type' => $validated['payment_type'] ?? ProjectPayment::TYPE_PART_PAYMENT,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => $request->user()->id,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $this->storeFinancialDocument($request, $payment);
        });

        return back()->with('success', 'Money received recorded successfully.');
    }

    public function storeProjectPayment(Request $request, Project $project)
    {
        $this->authorizeFinance(FinancePermission::CREATE);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_type' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if (!empty($validated['reference'])) {
            $existing = ProjectPayment::where('reference', $validated['reference'])->first();
            if ($existing) {
                return back()->withErrors(['reference' => 'A payment with this reference number has already been recorded.'])->withInput();
            }
        }

        DB::transaction(function () use ($request, $project, $validated) {
            $payment = ProjectPayment::create([
                'project_id' => $project->id,
                'client_id' => $project->client_id,
                'original_context_type' => Project::class,
                'original_context_id' => $project->id,
                'payment_type' => $validated['payment_type'] ?? ProjectPayment::TYPE_PART_PAYMENT,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => $request->user()->id,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $this->storeFinancialDocument($request, $payment);
        });

        return redirect()
            ->route('finance.projects.show', $project)
            ->with('success', 'Payment recorded successfully.');
    }

    public function updateProjectPayment(Request $request, Project $project, ProjectPayment $payment)
    {
        $this->authorizeFinance(FinancePermission::EDIT);

        abort_unless($payment->project_id === $project->id, 404);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        DB::transaction(function () use ($request, $payment, $validated) {
            $payment->update([
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => $request->user()->id,
            ]);

            $this->storeFinancialDocument($request, $payment);
        });

        return redirect()
            ->route('finance.projects.show', $project)
            ->with('success', 'Payment updated successfully.');
    }

    public function destroyProjectPayment(Request $request, Project $project, ProjectPayment $payment)
    {
        $this->authorizeFinance(FinancePermission::DELETE);

        abort_unless($payment->project_id === $project->id, 404);

        $payment->delete();

        return redirect()
            ->route('finance.projects.show', $project)
            ->with('success', 'Payment deleted.');
    }
}
