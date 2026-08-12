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
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FinanceAnalysisController extends Controller
{
    private function authorizeView(): void
    {
        $user = auth()->user();
        if (!$user instanceof User || !($user->isFinance() || $user->hasFinancePermission(FinancePermission::VIEW))) {
            abort(403, 'Unauthorized access to Finance Analysis.');
        }
    }

    private function formatMoney(float $amount): string
    {
        return '₦' . number_format($amount, 2);
    }

    private function formatCompactMoney(float $amount): string
    {
        if ($amount >= 1000000) {
            return '₦' . number_format($amount / 1000000, 1) . 'M';
        }
        if ($amount >= 1000) {
            return '₦' . number_format($amount / 1000, 1) . 'K';
        }
        return '₦' . number_format($amount, 0);
    }

    private function viewHelpers(): array
    {
        return [
            'financeMoney' => fn ($amount) => '₦' . number_format((float) ($amount ?? 0), 2),
            'financeCompactMoney' => fn ($amount) => $this->formatCompactMoney((float) ($amount ?? 0)),
        ];
    }

    public function index(Request $request)
    {
        $this->authorizeView();

        $period = $request->input('period', '6M');
        $metric = $request->input('metric', 'profit');

        $projects = Project::query()
            ->with(['client', 'financial', 'payments', 'financialExpenses', 'financialMaterialCosts'])
            ->get();

        $projectValueTotal = 0.0;
        $receivedTotal = 0.0;
        $approvedCostsTotal = 0.0;
        $overBudgetCount = 0;

        $projectMetrics = [];

        foreach ($projects as $project) {
            $value = (float) ($project->financial?->contract_value ?? 0);
            $received = (float) $project->payments->sum('amount');
            $outstanding = max(0, $value - $received);

            $approvedExpenses = (float) $project->financialExpenses->where('status', FinancialExpense::STATUS_APPROVED)->sum('amount');
            $approvedMaterials = (float) $project->financialMaterialCosts->where('status', FinancialMaterialCost::STATUS_APPROVED)->sum('total_cost');
            $costs = $approvedExpenses + $approvedMaterials;

            $profit = $value - $costs;
            $margin = $value > 0 ? ($profit / $value) * 100 : 0.0;

            if ($costs > $value && $value > 0) {
                $overBudgetCount++;
            }

            $projectValueTotal += $value;
            $receivedTotal += $received;
            $approvedCostsTotal += $costs;

            $projectMetrics[] = [
                'project' => $project,
                'id' => $project->id,
                'title' => $project->title ?: $project->project_code,
                'client_name' => $project->client?->company_name ?: $project->client?->client_name ?: 'N/A',
                'value' => $value,
                'received' => $received,
                'outstanding' => $outstanding,
                'costs' => $costs,
                'profit' => $profit,
                'margin' => $margin,
            ];
        }

        $outstandingTotal = max(0, $projectValueTotal - $receivedTotal);
        $estimatedProfitTotal = $projectValueTotal - $approvedCostsTotal;
        $profitMarginPercent = $projectValueTotal > 0 ? ($estimatedProfitTotal / $projectValueTotal) * 100 : 0.0;

        $pendingExpensesCount = FinancialExpense::query()->where('status', FinancialExpense::STATUS_PENDING)->count();
        $pendingMaterialsCount = FinancialMaterialCost::query()->where('status', FinancialMaterialCost::STATUS_PENDING)->count();
        $pendingApprovalsCount = $pendingExpensesCount + $pendingMaterialsCount;

        // Health evaluation
        $outstandingRatio = $projectValueTotal > 0 ? ($outstandingTotal / $projectValueTotal) : 0;
        $costRatio = $projectValueTotal > 0 ? ($approvedCostsTotal / $projectValueTotal) : 0;

        if ($overBudgetCount > 1 || $costRatio > 0.90 || $profitMarginPercent < 10.0 || $outstandingRatio > 0.60) {
            $healthStatus = 'attention'; // RED
            $healthLabel = 'Attention';
        } elseif ($overBudgetCount === 1 || $costRatio > 0.80 || $profitMarginPercent < 20.0 || $outstandingRatio > 0.40 || $pendingApprovalsCount > 3) {
            $healthStatus = 'watch'; // YELLOW
            $healthLabel = 'Watch';
        } else {
            $healthStatus = 'healthy'; // GREEN
            $healthLabel = 'Healthy';
        }

        $healthSummary = sprintf(
            "Project value is %s with %s received. Approved costs are %s, leaving an estimated profit of %s (%s%% margin).",
            $this->formatCompactMoney($projectValueTotal),
            $this->formatCompactMoney($receivedTotal),
            $this->formatCompactMoney($approvedCostsTotal),
            $this->formatCompactMoney($estimatedProfitTotal),
            number_format($profitMarginPercent, 1)
        );

        // Trend Chart Data
        $monthsCount = match ($period) {
            '30D' => 1,
            '3M' => 3,
            '1Y' => 12,
            default => 6,
        };

        $trendSeries = [];
        for ($i = $monthsCount - 1; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthKey = $monthDate->format('Y-m');
            $label = $monthDate->format('M Y');

            $monthValue = ProjectFinancial::query()
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->sum('contract_value');

            $monthReceived = ProjectPayment::query()
                ->whereYear('payment_date', $monthDate->year)
                ->whereMonth('payment_date', $monthDate->month)
                ->sum('amount');

            $monthExpenses = FinancialExpense::query()
                ->where('status', FinancialExpense::STATUS_APPROVED)
                ->whereYear('incurred_on', $monthDate->year)
                ->whereMonth('incurred_on', $monthDate->month)
                ->sum('amount');

            $monthMaterials = FinancialMaterialCost::query()
                ->where('status', FinancialMaterialCost::STATUS_APPROVED)
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->sum('total_cost');

            $trendSeries[] = [
                'label' => $label,
                'value' => (float) $monthValue,
                'received' => (float) $monthReceived,
                'costs' => (float) ($monthExpenses + $monthMaterials),
            ];
        }

        // Cost Breakdown Donut Data
        $categoryExpenses = FinancialExpense::query()
            ->selectRaw('finance_expense_category_id, SUM(amount) as total')
            ->where('status', FinancialExpense::STATUS_APPROVED)
            ->groupBy('finance_expense_category_id')
            ->with('category')
            ->get();

        $approvedMaterialsTotal = (float) FinancialMaterialCost::query()
            ->where('status', FinancialMaterialCost::STATUS_APPROVED)
            ->sum('total_cost');

        $costBreakdown = [];
        $totalCostsForBreakdown = $approvedCostsTotal;

        if ($approvedMaterialsTotal > 0) {
            $costBreakdown[] = [
                'category' => 'Materials',
                'amount' => $approvedMaterialsTotal,
                'percentage' => $totalCostsForBreakdown > 0 ? round(($approvedMaterialsTotal / $totalCostsForBreakdown) * 100, 1) : 0,
            ];
        }

        foreach ($categoryExpenses as $catExp) {
            $catName = $catExp->category?->name ?? 'Other Expenses';
            $amount = (float) $catExp->total;
            $costBreakdown[] = [
                'category' => $catName,
                'amount' => $amount,
                'percentage' => $totalCostsForBreakdown > 0 ? round(($amount / $totalCostsForBreakdown) * 100, 1) : 0,
            ];
        }

        usort($costBreakdown, fn ($a, $b) => $b['amount'] <=> $a['amount']);

        // Project Performance Top 5
        $performanceMetricKey = in_array($metric, ['revenue', 'costs', 'outstanding']) ? $metric : 'profit';
        $topProjects = collect($projectMetrics)->sortByDesc($performanceMetricKey)->take(5)->values()->all();

        // Smart Insights Generation
        $insights = [];

        // Insight 1: Outstanding balances
        $highOutstandingProjects = collect($projectMetrics)->filter(fn ($p) => $p['value'] > 0 && ($p['outstanding'] / $p['value']) >= 0.50);
        if ($highOutstandingProjects->isNotEmpty()) {
            $count = $highOutstandingProjects->count();
            $insights[] = [
                'type' => 'warning',
                'text' => "{$count} project(s) currently have 50% or more of their contract value outstanding.",
            ];
        }

        // Insight 2: Over budget
        $overBudgetProjects = collect($projectMetrics)->filter(fn ($p) => $p['value'] > 0 && $p['costs'] > $p['value']);
        if ($overBudgetProjects->isNotEmpty()) {
            $firstOver = $overBudgetProjects->first();
            $pct = round(($firstOver['costs'] / $firstOver['value']) * 100, 1);
            $insights[] = [
                'type' => 'danger',
                'text' => "'{$firstOver['title']}' is currently over budget with approved costs at {$pct}% of contract value.",
            ];
        }

        // Insight 3: Low margin
        $lowMarginProjects = collect($projectMetrics)->filter(fn ($p) => $p['value'] > 0 && $p['costs'] <= $p['value'] && $p['margin'] < 15.0);
        if ($lowMarginProjects->isNotEmpty()) {
            $lowest = $lowMarginProjects->sortBy('margin')->first();
            $insights[] = [
                'type' => 'info',
                'text' => "'{$lowest['title']}' has the lowest estimated profit margin at " . number_format($lowest['margin'], 1) . "%.",
            ];
        }

        // Insight 4: Category concentration
        if (!empty($costBreakdown)) {
            $topCat = $costBreakdown[0];
            if ($topCat['percentage'] >= 15.0) {
                $insights[] = [
                    'type' => 'info',
                    'text' => "{$topCat['category']} accounts for {$topCat['percentage']}% of total approved project costs.",
                ];
            }
        }

        // Insight 5: Pending approvals
        if ($pendingApprovalsCount > 0) {
            $insights[] = [
                'type' => 'warning',
                'text' => "There are {$pendingApprovalsCount} pending financial expense or material item(s) awaiting approval.",
            ];
        }

        // Insight 6: High profit project
        $mostProfitable = collect($projectMetrics)->sortByDesc('profit')->first();
        if ($mostProfitable && $mostProfitable['profit'] > 0) {
            $insights[] = [
                'type' => 'success',
                'text' => "'{$mostProfitable['title']}' is the most profitable project with an estimated profit of " . $this->formatCompactMoney($mostProfitable['profit']) . " (" . number_format($mostProfitable['margin'], 1) . "% margin).",
            ];
        }

        if (empty($insights)) {
            $insights[] = [
                'type' => 'success',
                'text' => 'Financial performance is currently stable. No major issues were detected.',
            ];
        }

        // Attention Section Items
        $attentionItems = [];
        foreach ($overBudgetProjects as $ob) {
            $pct = round(($ob['costs'] / $ob['value']) * 100, 1);
            $attentionItems[] = [
                'id' => $ob['id'],
                'title' => $ob['title'],
                'explanation' => "Approved costs are {$pct}% of contract value (Over budget by " . $this->formatMoney($ob['costs'] - $ob['value']) . ").",
                'link' => route('finance.projects.show', $ob['id']),
            ];
        }

        foreach ($highOutstandingProjects as $ho) {
            $attentionItems[] = [
                'id' => $ho['id'],
                'title' => $ho['title'],
                'explanation' => "High outstanding balance of " . $this->formatMoney($ho['outstanding']) . " (" . round(($ho['outstanding'] / $ho['value']) * 100, 1) . "% unpaid).",
                'link' => route('finance.projects.show', $ho['id']),
            ];
        }

        return view('finance.analysis.index', array_merge([
            'projectValueTotal' => $projectValueTotal,
            'receivedTotal' => $receivedTotal,
            'outstandingTotal' => $outstandingTotal,
            'approvedCostsTotal' => $approvedCostsTotal,
            'estimatedProfitTotal' => $estimatedProfitTotal,
            'profitMarginPercent' => $profitMarginPercent,
            'healthStatus' => $healthStatus,
            'healthLabel' => $healthLabel,
            'healthSummary' => $healthSummary,
            'period' => $period,
            'metric' => $performanceMetricKey,
            'trendSeries' => $trendSeries,
            'costBreakdown' => $costBreakdown,
            'topProjects' => $topProjects,
            'insights' => $insights,
            'attentionItems' => $attentionItems,
        ], $this->viewHelpers()));
    }

    public function ask(Request $request): JsonResponse
    {
        $this->authorizeView();

        $question = trim($request->input('question', ''));
        if (!$question) {
            return response()->json([
                'answer' => 'Please type a financial question about your projects, payments, costs or profitability.',
            ]);
        }

        $q = Str::lower($question);

        $projects = Project::query()
            ->with(['client', 'financial', 'payments', 'financialExpenses', 'financialMaterialCosts'])
            ->get();

        $projectMetrics = [];
        foreach ($projects as $project) {
            $value = (float) ($project->financial?->contract_value ?? 0);
            $received = (float) $project->payments->sum('amount');
            $outstanding = max(0, $value - $received);

            $approvedExpenses = (float) $project->financialExpenses->where('status', FinancialExpense::STATUS_APPROVED)->sum('amount');
            $approvedMaterials = (float) $project->financialMaterialCosts->where('status', FinancialMaterialCost::STATUS_APPROVED)->sum('total_cost');
            $costs = $approvedExpenses + $approvedMaterials;
            $profit = $value - $costs;
            $margin = $value > 0 ? ($profit / $value) * 100 : 0.0;

            $projectMetrics[] = [
                'project' => $project,
                'id' => $project->id,
                'title' => $project->title ?: $project->project_code,
                'client_name' => $project->client?->company_name ?: $project->client?->client_name ?: 'N/A',
                'value' => $value,
                'received' => $received,
                'outstanding' => $outstanding,
                'costs' => $costs,
                'profit' => $profit,
                'margin' => $margin,
            ];
        }

        // Question 1: Most profitable project
        if (Str::contains($q, ['most profitable', 'best profit', 'highest profit', 'top profit'])) {
            $top = collect($projectMetrics)->sortByDesc('profit')->first();
            if ($top && $top['profit'] > 0) {
                return response()->json([
                    'answer' => sprintf(
                        "The '%s' project currently has the highest estimated profit at %s, with an estimated margin of %s%%.",
                        $top['title'],
                        $this->formatMoney($top['profit']),
                        number_format($top['margin'], 1)
                    ),
                    'project_id' => $top['id'],
                    'project_url' => route('finance.projects.show', $top['id']),
                ]);
            }
            return response()->json(['answer' => 'There are currently no projects with positive estimated profit logged in the database.']);
        }

        // Question 2: Largest outstanding client balances / top outstanding
        if (Str::contains($q, ['largest outstanding', 'top outstanding', 'unpaid', 'highest balance', 'clients have the largest'])) {
            $sortedByOutstanding = collect($projectMetrics)->where('outstanding', '>', 0)->sortByDesc('outstanding')->values();
            if ($sortedByOutstanding->isNotEmpty()) {
                $top = $sortedByOutstanding->first();
                return response()->json([
                    'answer' => sprintf(
                        "'%s' (%s) has the largest outstanding balance at %s out of a contract value of %s.",
                        $top['title'],
                        $top['client_name'],
                        $this->formatMoney($top['outstanding']),
                        $this->formatMoney($top['value'])
                    ),
                    'project_id' => $top['id'],
                    'project_url' => route('finance.projects.show', $top['id']),
                ]);
            }
            return response()->json(['answer' => 'All client project contract values have been fully paid. There are zero outstanding balances.']);
        }

        // Question 3: Expense category spending (e.g. transportation, fuel, materials, accommodation)
        if (Str::contains($q, ['spent on', 'expense for', 'cost for', 'expenses for', 'how much on'])) {
            $categories = FinanceExpenseCategory::query()->get();
            $matchedCategory = null;

            foreach ($categories as $cat) {
                if (Str::contains($q, Str::lower($cat->name))) {
                    $matchedCategory = $cat;
                    break;
                }
            }

            if ($matchedCategory) {
                $totalCatSpent = (float) FinancialExpense::query()
                    ->where('finance_expense_category_id', $matchedCategory->id)
                    ->where('status', FinancialExpense::STATUS_APPROVED)
                    ->sum('amount');

                return response()->json([
                    'answer' => sprintf(
                        "Total approved spending on %s is %s.",
                        $matchedCategory->name,
                        $this->formatMoney($totalCatSpent)
                    ),
                ]);
            }

            if (Str::contains($q, ['material', 'materials'])) {
                $totalMaterialSpent = (float) FinancialMaterialCost::query()
                    ->where('status', FinancialMaterialCost::STATUS_APPROVED)
                    ->sum('total_cost');

                return response()->json([
                    'answer' => sprintf(
                        "Total approved spending on project materials is %s.",
                        $this->formatMoney($totalMaterialSpent)
                    ),
                ]);
            }
        }

        // Question 4: Over budget projects
        if (Str::contains($q, ['over budget', 'exceeded budget', 'budget overflow'])) {
            $overBudgetList = collect($projectMetrics)->filter(fn ($p) => $p['value'] > 0 && $p['costs'] > $p['value'])->values();
            if ($overBudgetList->isNotEmpty()) {
                $first = $overBudgetList->first();
                $count = $overBudgetList->count();
                return response()->json([
                    'answer' => sprintf(
                        "%d project(s) are over budget. For example, '%s' has approved costs of %s against a contract value of %s.",
                        $count,
                        $first['title'],
                        $this->formatMoney($first['costs']),
                        $this->formatMoney($first['value'])
                    ),
                    'project_id' => $first['id'],
                    'project_url' => route('finance.projects.show', $first['id']),
                ]);
            }
            return response()->json(['answer' => 'Great news! No projects are currently over budget. All approved costs remain within contract values.']);
        }

        // Question 5: Period receipts / received this month
        if (Str::contains($q, ['received this month', 'received recently', 'collected this month', 'payments received'])) {
            $currentMonthReceived = (float) ProjectPayment::query()
                ->whereYear('payment_date', now()->year)
                ->whereMonth('payment_date', now()->month)
                ->sum('amount');

            $totalAllReceived = (float) ProjectPayment::query()->sum('amount');

            return response()->json([
                'answer' => sprintf(
                    "We have received %s in client payments this month (%s). Total received across all projects to date is %s.",
                    $this->formatMoney($currentMonthReceived),
                    now()->format('F Y'),
                    $this->formatMoney($totalAllReceived)
                ),
            ]);
        }

        // Question 6: Overall profit / estimated profit
        if (Str::contains($q, ['estimated profit', 'total profit', 'overall profit', 'how much profit'])) {
            $valTotal = collect($projectMetrics)->sum('value');
            $costTotal = collect($projectMetrics)->sum('costs');
            $profitTotal = $valTotal - $costTotal;
            $marginTotal = $valTotal > 0 ? ($profitTotal / $valTotal) * 100 : 0;

            return response()->json([
                'answer' => sprintf(
                    "Total project value is %s with approved costs of %s, giving a total estimated profit of %s (%s%% overall margin).",
                    $this->formatMoney($valTotal),
                    $this->formatMoney($costTotal),
                    $this->formatMoney($profitTotal),
                    number_format($marginTotal, 1)
                ),
            ]);
        }

        // Safe Fallback
        return response()->json([
            'answer' => 'I can currently answer questions about projects, payments, expenses, costs, outstanding balances and profitability.',
        ]);
    }
}
