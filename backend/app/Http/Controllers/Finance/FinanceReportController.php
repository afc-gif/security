<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceExpenseCategory;
use App\Models\FinancePermission;
use App\Models\FinancialExpense;
use App\Models\FinancialMaterialCost;
use App\Models\Project;
use App\Models\ProjectFinancial;
use App\Models\ProjectPayment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\User;

class FinanceReportController extends Controller
{
    private function authorizeView(): void
    {
        $user = auth()->user();
        if (!$user instanceof User || !($user->isFinance() || $user->hasFinancePermission(FinancePermission::VIEW))) {
            abort(403, 'Unauthorized access to Finance Reports.');
        }
    }

    private function viewHelpers(): array
    {
        return [
            'financeMoney' => fn ($amount) => '₦' . number_format((float) ($amount ?? 0), 2),
        ];
    }

    public function index()
    {
        $this->authorizeView();

        return view('finance.reports.index', $this->viewHelpers());
    }

    public function projects(Request $request)
    {
        $this->authorizeView();

        $filters = $request->only(['search', 'status']);

        $query = Project::query()
            ->with(['client', 'financial', 'payments', 'financialExpenses', 'financialMaterialCosts'])
            ->when($filters['search'] ?? null, function (Builder $q, $search) {
                $q->where(function (Builder $sq) use ($search) {
                    $sq->where('title', 'like', "%{$search}%")
                        ->orWhere('project_code', 'like', "%{$search}%")
                        ->orWhereHas('client', fn (Builder $cq) => $cq->where('company_name', 'like', "%{$search}%")->orWhere('client_name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status));

        $filteredProjects = (clone $query)->get();

        $totalProjectValue = 0.0;
        $totalReceived = 0.0;
        $totalApprovedCosts = 0.0;

        foreach ($filteredProjects as $project) {
            $financial = $project->financial;
            if ($financial && $financial->contract_value !== null) {
                $totalProjectValue += (float) $financial->contract_value;
            }
            $totalReceived += (float) $project->payments->sum('amount');
            $approvedExpenses = (float) $project->financialExpenses->where('status', FinancialExpense::STATUS_APPROVED)->sum('amount');
            $approvedMaterials = (float) $project->financialMaterialCosts->where('status', FinancialMaterialCost::STATUS_APPROVED)->sum('total_cost');
            $totalApprovedCosts += ($approvedExpenses + $approvedMaterials);
        }

        $totalOutstanding = max(0, $totalProjectValue - $totalReceived);
        $totalEstimatedProfit = $totalProjectValue - $totalApprovedCosts;

        $projects = $query->latest('id')->paginate(15)->appends($request->query());

        $totals = [
            'project_value' => $totalProjectValue,
            'received' => $totalReceived,
            'outstanding' => $totalOutstanding,
            'approved_costs' => $totalApprovedCosts,
            'estimated_profit' => $totalEstimatedProfit,
        ];

        return view('finance.reports.projects', array_merge(
            compact('projects', 'totals', 'filters'),
            $this->viewHelpers()
        ));
    }

    public function exportProjects(Request $request): StreamedResponse
    {
        $this->authorizeView();

        $filters = $request->only(['search', 'status']);

        $projects = Project::query()
            ->with(['client', 'financial', 'payments', 'financialExpenses', 'financialMaterialCosts'])
            ->when($filters['search'] ?? null, function (Builder $q, $search) {
                $q->where(function (Builder $sq) use ($search) {
                    $sq->where('title', 'like', "%{$search}%")
                        ->orWhere('project_code', 'like', "%{$search}%")
                        ->orWhereHas('client', fn (Builder $cq) => $cq->where('company_name', 'like', "%{$search}%")->orWhere('client_name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status))
            ->latest('id')
            ->get();

        $filename = 'project_financial_report_' . date('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($projects) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Project Code',
                'Project Title',
                'Client',
                'Status',
                'Project Value (NGN)',
                'Received (NGN)',
                'Outstanding (NGN)',
                'Approved Costs (NGN)',
                'Estimated Profit (NGN)',
            ]);

            foreach ($projects as $project) {
                $contractValue = (float) ($project->financial?->contract_value ?? 0);
                $received = (float) $project->payments->sum('amount');
                $outstanding = max(0, $contractValue - $received);
                $approvedExpenses = (float) $project->financialExpenses->where('status', FinancialExpense::STATUS_APPROVED)->sum('amount');
                $approvedMaterials = (float) $project->financialMaterialCosts->where('status', FinancialMaterialCost::STATUS_APPROVED)->sum('total_cost');
                $approvedCosts = $approvedExpenses + $approvedMaterials;
                $profit = $contractValue - $approvedCosts;

                fputcsv($handle, [
                    $project->project_code,
                    $project->title,
                    $project->client?->company_name ?: $project->client?->client_name ?: 'N/A',
                    ucfirst(str_replace('_', ' ', $project->status)),
                    number_format($contractValue, 2, '.', ''),
                    number_format($received, 2, '.', ''),
                    number_format($outstanding, 2, '.', ''),
                    number_format($approvedCosts, 2, '.', ''),
                    number_format($profit, 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function expenses(Request $request)
    {
        $this->authorizeView();

        $filters = $request->only(['date_from', 'date_to', 'category', 'status', 'search']);
        $categories = FinanceExpenseCategory::query()->where('is_active', true)->orderBy('name')->get();

        $query = FinancialExpense::query()
            ->with(['category', 'project.client', 'jobRequestItem.jobRequest.client', 'inspection.client'])
            ->when($filters['date_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('incurred_on', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $date) => $q->whereDate('incurred_on', '<=', $date))
            ->when($filters['category'] ?? null, fn (Builder $q, $cat) => $q->where('finance_expense_category_id', $cat))
            ->when($filters['status'] ?? null, fn (Builder $q, $st) => $q->where('status', $st))
            ->when($filters['search'] ?? null, function (Builder $q, $search) {
                $q->where(function (Builder $sq) use ($search) {
                    $sq->where('description', 'like', "%{$search}%")
                        ->orWhereHas('category', fn (Builder $cq) => $cq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('project', fn (Builder $pq) => $pq->where('title', 'like', "%{$search}%")->orWhere('project_code', 'like', "%{$search}%"))
                        ->orWhereHas('jobRequestItem', fn (Builder $jq) => $jq->where('title', 'like', "%{$search}%"));
                });
            });

        $filteredExpenses = (clone $query)->get();
        $totalExpenses = (float) $filteredExpenses->sum('amount');
        $approvedTotal = (float) $filteredExpenses->where('status', FinancialExpense::STATUS_APPROVED)->sum('amount');
        $pendingTotal = (float) $filteredExpenses->where('status', FinancialExpense::STATUS_PENDING)->sum('amount');

        $expenses = $query->latest('incurred_on')->latest('id')->paginate(15)->appends($request->query());

        $totals = [
            'total' => $totalExpenses,
            'approved' => $approvedTotal,
            'pending' => $pendingTotal,
        ];

        return view('finance.reports.expenses', array_merge(
            compact('expenses', 'categories', 'totals', 'filters'),
            $this->viewHelpers()
        ));
    }

    public function exportExpenses(Request $request): StreamedResponse
    {
        $this->authorizeView();

        $filters = $request->only(['date_from', 'date_to', 'category', 'status', 'search']);

        $expenses = FinancialExpense::query()
            ->with(['category', 'project.client', 'jobRequestItem.jobRequest.client', 'inspection.client'])
            ->when($filters['date_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('incurred_on', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $date) => $q->whereDate('incurred_on', '<=', $date))
            ->when($filters['category'] ?? null, fn (Builder $q, $cat) => $q->where('finance_expense_category_id', $cat))
            ->when($filters['status'] ?? null, fn (Builder $q, $st) => $q->where('status', $st))
            ->when($filters['search'] ?? null, function (Builder $q, $search) {
                $q->where(function (Builder $sq) use ($search) {
                    $sq->where('description', 'like', "%{$search}%")
                        ->orWhereHas('category', fn (Builder $cq) => $cq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('project', fn (Builder $pq) => $pq->where('title', 'like', "%{$search}%")->orWhere('project_code', 'like', "%{$search}%"))
                        ->orWhereHas('jobRequestItem', fn (Builder $jq) => $jq->where('title', 'like', "%{$search}%"));
                });
            })
            ->latest('incurred_on')
            ->latest('id')
            ->get();

        $filename = 'expense_report_' . date('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($expenses) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Date',
                'Project / Job / Entity',
                'Client',
                'Category',
                'Description',
                'Amount (NGN)',
                'Status',
            ]);

            foreach ($expenses as $expense) {
                $entity = $expense->project?->title
                    ?: ($expense->jobRequestItem?->title ?: ($expense->inspection?->title ?: 'General Expense'));

                $client = $expense->project?->client?->company_name
                    ?: ($expense->project?->client?->client_name
                    ?: ($expense->jobRequestItem?->jobRequest?->client?->company_name
                    ?: ($expense->jobRequestItem?->jobRequest?->client?->client_name ?: 'N/A')));

                fputcsv($handle, [
                    $expense->incurred_on ? $expense->incurred_on->format('Y-m-d') : $expense->created_at->format('Y-m-d'),
                    $entity,
                    $client,
                    $expense->category?->name ?? 'Uncategorized',
                    $expense->description,
                    number_format((float) $expense->amount, 2, '.', ''),
                    ucfirst($expense->status),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function payments(Request $request)
    {
        $this->authorizeView();

        $filters = $request->only(['date_from', 'date_to', 'payment_method', 'search']);

        $query = ProjectPayment::query()
            ->with(['project.client', 'recorder'])
            ->when($filters['date_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('payment_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $date) => $q->whereDate('payment_date', '<=', $date))
            ->when($filters['payment_method'] ?? null, fn (Builder $q, $pm) => $q->where('payment_method', $pm))
            ->when($filters['search'] ?? null, function (Builder $q, $search) {
                $q->where(function (Builder $sq) use ($search) {
                    $sq->where('reference', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('project', fn (Builder $pq) => $pq->where('title', 'like', "%{$search}%")->orWhere('project_code', 'like', "%{$search}%"))
                        ->orWhereHas('project.client', fn (Builder $cq) => $cq->where('company_name', 'like', "%{$search}%")->orWhere('client_name', 'like', "%{$search}%"));
                });
            });

        $filteredPayments = (clone $query)->get();
        $totalReceived = (float) $filteredPayments->sum('amount');
        $paymentCount = $filteredPayments->count();

        $payments = $query->latest('payment_date')->latest('id')->paginate(15)->appends($request->query());

        $totals = [
            'total_received' => $totalReceived,
            'payment_count' => $paymentCount,
        ];

        return view('finance.reports.payments', array_merge(
            compact('payments', 'totals', 'filters'),
            $this->viewHelpers()
        ));
    }

    public function exportPayments(Request $request): StreamedResponse
    {
        $this->authorizeView();

        $filters = $request->only(['date_from', 'date_to', 'payment_method', 'search']);

        $payments = ProjectPayment::query()
            ->with(['project.client', 'recorder'])
            ->when($filters['date_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('payment_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $date) => $q->whereDate('payment_date', '<=', $date))
            ->when($filters['payment_method'] ?? null, fn (Builder $q, $pm) => $q->where('payment_method', $pm))
            ->when($filters['search'] ?? null, function (Builder $q, $search) {
                $q->where(function (Builder $sq) use ($search) {
                    $sq->where('reference', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('project', fn (Builder $pq) => $pq->where('title', 'like', "%{$search}%")->orWhere('project_code', 'like', "%{$search}%"))
                        ->orWhereHas('project.client', fn (Builder $cq) => $cq->where('company_name', 'like', "%{$search}%")->orWhere('client_name', 'like', "%{$search}%"));
                });
            })
            ->latest('payment_date')
            ->latest('id')
            ->get();

        $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Payment Date',
                'Project Code',
                'Project Title',
                'Client',
                'Amount (NGN)',
                'Payment Method',
                'Reference',
                'Recorded By',
            ]);

            foreach ($payments as $payment) {
                fputcsv($handle, [
                    $payment->payment_date ? $payment->payment_date->format('Y-m-d') : '',
                    $payment->project?->project_code ?? '',
                    $payment->project?->title ?? '',
                    $payment->project?->client?->company_name ?: $payment->project?->client?->client_name ?: 'N/A',
                    number_format((float) $payment->amount, 2, '.', ''),
                    ucfirst(str_replace('_', ' ', $payment->payment_method)),
                    $payment->reference ?? '',
                    $payment->recorder?->name ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
