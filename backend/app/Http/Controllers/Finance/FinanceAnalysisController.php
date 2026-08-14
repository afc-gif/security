<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceExpenseCategory;
use App\Models\FinancePermission;
use App\Models\FinancialExpense;
use App\Models\FinancialMaterialCost;
use App\Models\Order;
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
    // -------------------------------------------------------------------------
    // Auth & Helpers
    // -------------------------------------------------------------------------

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
            'financeMoney'        => fn ($amount) => '₦' . number_format((float) ($amount ?? 0), 2),
            'financeCompactMoney' => fn ($amount) => $this->formatCompactMoney((float) ($amount ?? 0)),
        ];
    }

    // -------------------------------------------------------------------------
    // Period Resolution
    // -------------------------------------------------------------------------

    /**
     * Resolve a date range from a period string or custom from/to dates.
     * Returns [Carbon $from, Carbon $to, string $label, string $period].
     */
    private function resolvePeriod(Request $request): array
    {
        $period   = $request->input('period', 'month');
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        $now = Carbon::now();

        if ($period === 'custom' && $dateFrom && $dateTo) {
            $from  = Carbon::parse($dateFrom)->startOfDay();
            $to    = Carbon::parse($dateTo)->endOfDay();
            $label = $from->format('d M Y') . ' – ' . $to->format('d M Y');
            return [$from, $to, $label, 'custom'];
        }

        switch ($period) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today', 'today'];
            case 'week':
                return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'This Week', 'week'];
            case 'quarter':
                return [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter(), 'This Quarter', 'quarter'];
            case 'year':
                return [$now->copy()->startOfYear(), $now->copy()->endOfYear(), 'This Year', 'year'];
            case 'month':
            default:
                $period = 'month';
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This Month', 'month'];
        }
    }

    // -------------------------------------------------------------------------
    // Revenue Helpers
    // -------------------------------------------------------------------------

    /** Total POS revenue (status=completed) within a date range, using created_at as sale date. */
    private function posRevenue(Carbon $from, Carbon $to): float
    {
        return (float) Order::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->sum('total_amount');
    }

    /** Total POS revenue all-time. */
    private function posRevenueAllTime(): float
    {
        return (float) Order::query()
            ->where('status', 'completed')
            ->sum('total_amount');
    }

    /** Count of completed POS orders within a date range. */
    private function posOrderCount(Carbon $from, Carbon $to): int
    {
        return (int) Order::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    /** Project/job/inspection payments within a date range. */
    private function projectRevenue(Carbon $from, Carbon $to): float
    {
        return (float) ProjectPayment::query()
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    // -------------------------------------------------------------------------
    // Expense Helpers
    // -------------------------------------------------------------------------

    /** Approved FinancialExpense (both office + operational) within a date range. */
    private function totalApprovedExpenses(Carbon $from, Carbon $to): float
    {
        return (float) FinancialExpense::query()
            ->where('status', FinancialExpense::STATUS_APPROVED)
            ->whereBetween('incurred_on', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    /** Approved material costs within a date range (uses incurred_on, excludes NULL incurred_on). */
    private function totalApprovedMaterials(Carbon $from, Carbon $to): float
    {
        return (float) FinancialMaterialCost::query()
            ->where('status', FinancialMaterialCost::STATUS_APPROVED)
            ->whereNotNull('incurred_on')
            ->whereBetween('incurred_on', [$from->toDateString(), $to->toDateString()])
            ->sum('total_cost');
    }

    /**
     * Project/job/inspection payments within a date range, broken down by payment_type.
     * Returns ['deposit' => float, 'part_payment' => float, 'full_payment' => float].
     */
    private function projectRevenueByType(Carbon $from, Carbon $to): array
    {
        $rows = ProjectPayment::query()
            ->selectRaw('payment_type, SUM(amount) as total')
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('payment_type')
            ->pluck('total', 'payment_type');

        return [
            ProjectPayment::TYPE_DEPOSIT       => (float) ($rows[ProjectPayment::TYPE_DEPOSIT] ?? 0),
            ProjectPayment::TYPE_PART_PAYMENT  => (float) ($rows[ProjectPayment::TYPE_PART_PAYMENT] ?? 0),
            ProjectPayment::TYPE_FULL_PAYMENT  => (float) ($rows[ProjectPayment::TYPE_FULL_PAYMENT] ?? 0),
        ];
    }

    // -------------------------------------------------------------------------
    // Main Analysis Page
    // -------------------------------------------------------------------------

    public function index(Request $request)
    {
        $this->authorizeView();

        $metric = $request->input('metric', 'profit');
        [$from, $to, $periodLabel, $period] = $this->resolvePeriod($request);

        // ── MONEY IN ────────────────────────────────────────────────────────
        $posRevenuePeriod         = $this->posRevenue($from, $to);
        $posOrderCountPeriod      = $this->posOrderCount($from, $to);
        $projectRevenuePeriod     = $this->projectRevenue($from, $to);
        $projectRevenueByType     = $this->projectRevenueByType($from, $to);
        $totalIn                  = $posRevenuePeriod + $projectRevenuePeriod;

        // ── MONEY OUT ───────────────────────────────────────────────────────
        $expensesPeriod  = $this->totalApprovedExpenses($from, $to);
        $materialsPeriod = $this->totalApprovedMaterials($from, $to);
        $totalOut        = $expensesPeriod + $materialsPeriod;

        // ── NET ─────────────────────────────────────────────────────────────
        $netCashFlow = $totalIn - $totalOut;

        // ── PROJECT INTELLIGENCE (all-time, for project metrics / health) ───
        $projects = Project::query()
            ->with(['client', 'financial', 'payments', 'financialExpenses', 'financialMaterialCosts'])
            ->get();

        $projectValueTotal   = 0.0;
        $receivedTotal       = 0.0;
        $approvedCostsTotal  = 0.0;
        $overBudgetCount     = 0;
        $projectMetrics      = [];

        foreach ($projects as $project) {
            $value    = (float) ($project->financial?->contract_value ?? 0);
            $received = (float) $project->payments->sum('amount');
            $outstanding = max(0, $value - $received);

            $approvedExpenses  = (float) $project->financialExpenses->where('status', FinancialExpense::STATUS_APPROVED)->sum('amount');
            $approvedMaterials = (float) $project->financialMaterialCosts->where('status', FinancialMaterialCost::STATUS_APPROVED)->sum('total_cost');
            $costs  = $approvedExpenses + $approvedMaterials;
            $profit = $value - $costs;
            $margin = $value > 0 ? ($profit / $value) * 100 : 0.0;

            if ($costs > $value && $value > 0) {
                $overBudgetCount++;
            }

            $projectValueTotal  += $value;
            $receivedTotal      += $received;
            $approvedCostsTotal += $costs;

            $projectMetrics[] = [
                'project'     => $project,
                'id'          => $project->id,
                'title'       => $project->title ?: $project->project_code,
                'client_name' => $project->client?->company_name ?: $project->client?->client_name ?: 'N/A',
                'value'       => $value,
                'received'    => $received,
                'outstanding' => $outstanding,
                'costs'       => $costs,
                'profit'      => $profit,
                'margin'      => $margin,
            ];
        }

        $outstandingTotal       = max(0, $projectValueTotal - $receivedTotal);
        $estimatedProfitTotal   = $projectValueTotal - $approvedCostsTotal;
        $profitMarginPercent    = $projectValueTotal > 0 ? ($estimatedProfitTotal / $projectValueTotal) * 100 : 0.0;

        $pendingExpensesCount  = FinancialExpense::query()->where('status', FinancialExpense::STATUS_PENDING)->count();
        $pendingMaterialsCount = FinancialMaterialCost::query()->where('status', FinancialMaterialCost::STATUS_PENDING)->count();
        $pendingApprovalsCount = $pendingExpensesCount + $pendingMaterialsCount;

        // ── HEALTH (all-time project basis) ─────────────────────────────────
        $outstandingRatio = $projectValueTotal > 0 ? ($outstandingTotal / $projectValueTotal) : 0;
        $costRatio        = $projectValueTotal > 0 ? ($approvedCostsTotal / $projectValueTotal) : 0;

        if ($overBudgetCount > 1 || $costRatio > 0.90 || $profitMarginPercent < 10.0 || $outstandingRatio > 0.60) {
            $healthStatus = 'attention';
            $healthLabel  = 'Attention Required';
        } elseif ($overBudgetCount === 1 || $costRatio > 0.80 || $profitMarginPercent < 20.0 || $outstandingRatio > 0.40 || $pendingApprovalsCount > 3) {
            $healthStatus = 'watch';
            $healthLabel  = 'Watch';
        } else {
            $healthStatus = 'healthy';
            $healthLabel  = 'Healthy';
        }

        $posRevenueAllTime     = $this->posRevenueAllTime();
        $totalAllTimeIn        = $receivedTotal + $posRevenueAllTime;

        $healthSummary = sprintf(
            "All-time: Project value %s · Received %s · POS revenue %s · Total in %s · Approved costs %s · Net project position %s (%s%% margin).",
            $this->formatCompactMoney($projectValueTotal),
            $this->formatCompactMoney($receivedTotal),
            $this->formatCompactMoney($posRevenueAllTime),
            $this->formatCompactMoney($totalAllTimeIn),
            $this->formatCompactMoney($approvedCostsTotal),
            $this->formatCompactMoney($estimatedProfitTotal),
            number_format($profitMarginPercent, 1)
        );

        // ── TREND CHART (period-based buckets) ──────────────────────────────
        $trendSeries = $this->buildTrendSeries($period, $from, $to, $request);

        // ── EXPENSE CATEGORY BREAKDOWN for the selected period ───────────────
        // Office expenses by category
        $officeExpensesByCategory = FinancialExpense::query()
            ->selectRaw('finance_expense_category_id, SUM(amount) as total')
            ->where('status', FinancialExpense::STATUS_APPROVED)
            ->where('is_office_expense', true)
            ->whereBetween('incurred_on', [$from->toDateString(), $to->toDateString()])
            ->groupBy('finance_expense_category_id')
            ->with('category')
            ->get();

        // Operational expenses by category
        $operationalExpensesByCategory = FinancialExpense::query()
            ->selectRaw('finance_expense_category_id, SUM(amount) as total')
            ->where('status', FinancialExpense::STATUS_APPROVED)
            ->where('is_office_expense', false)
            ->whereBetween('incurred_on', [$from->toDateString(), $to->toDateString()])
            ->groupBy('finance_expense_category_id')
            ->with('category')
            ->get();

        // Build category breakdown for donut (all expenses in period)
        $totalCostsForBreakdown = $totalOut;
        $costBreakdown = [];

        if ($materialsPeriod > 0) {
            $costBreakdown[] = [
                'category'   => 'Materials',
                'type'       => 'materials',
                'amount'     => $materialsPeriod,
                'percentage' => $totalCostsForBreakdown > 0 ? round(($materialsPeriod / $totalCostsForBreakdown) * 100, 1) : 0,
            ];
        }

        foreach ($officeExpensesByCategory as $catExp) {
            $catName = $catExp->category?->name ?? 'Other';
            $amount  = (float) $catExp->total;
            if ($amount > 0) {
                $costBreakdown[] = [
                    'category'   => $catName,
                    'type'       => 'office',
                    'amount'     => $amount,
                    'percentage' => $totalCostsForBreakdown > 0 ? round(($amount / $totalCostsForBreakdown) * 100, 1) : 0,
                ];
            }
        }

        foreach ($operationalExpensesByCategory as $catExp) {
            $catName = $catExp->category?->name ?? 'Other';
            $amount  = (float) $catExp->total;
            if ($amount > 0) {
                $costBreakdown[] = [
                    'category'   => $catName,
                    'type'       => 'operational',
                    'amount'     => $amount,
                    'percentage' => $totalCostsForBreakdown > 0 ? round(($amount / $totalCostsForBreakdown) * 100, 1) : 0,
                ];
            }
        }

        usort($costBreakdown, fn ($a, $b) => $b['amount'] <=> $a['amount']);

        // Totals for labelled expense sections
        $officeExpensesTotal      = (float) $officeExpensesByCategory->sum('total');
        $operationalExpensesTotal = (float) $operationalExpensesByCategory->sum('total');

        // ── PROJECT PERFORMANCE Top 5 (all-time) ────────────────────────────
        $performanceMetricKey = in_array($metric, ['revenue', 'costs', 'outstanding']) ? $metric : 'profit';
        $topProjects = collect($projectMetrics)->sortByDesc($performanceMetricKey)->take(5)->values()->all();

        // ── INSIGHTS ────────────────────────────────────────────────────────
        $insights = $this->buildInsights($projectMetrics, $costBreakdown, $pendingApprovalsCount, $overBudgetCount, $posRevenuePeriod, $periodLabel);

        // Attention items (over-budget projects)
        $overBudgetProjects      = collect($projectMetrics)->filter(fn ($p) => $p['value'] > 0 && $p['costs'] > $p['value']);
        $highOutstandingProjects = collect($projectMetrics)->filter(fn ($p) => $p['value'] > 0 && ($p['outstanding'] / $p['value']) >= 0.50);

        $attentionItems = [];
        foreach ($overBudgetProjects as $ob) {
            $pct = round(($ob['costs'] / $ob['value']) * 100, 1);
            $attentionItems[] = [
                'id'          => $ob['id'],
                'title'       => $ob['title'],
                'explanation' => "Approved costs are {$pct}% of contract value (Over budget by " . $this->formatMoney($ob['costs'] - $ob['value']) . ").",
                'link'        => route('finance.projects.show', $ob['id']),
            ];
        }
        foreach ($highOutstandingProjects as $ho) {
            $attentionItems[] = [
                'id'          => $ho['id'],
                'title'       => $ho['title'],
                'explanation' => "High outstanding balance of " . $this->formatMoney($ho['outstanding']) . " (" . round(($ho['outstanding'] / $ho['value']) * 100, 1) . "% unpaid).",
                'link'        => route('finance.projects.show', $ho['id']),
            ];
        }

        return view('finance.analysis.index', array_merge([
            // Period
            'period'                    => $period,
            'periodLabel'               => $periodLabel,
            'metric'                    => $performanceMetricKey,
            'dateFrom'                  => $request->input('date_from', ''),
            'dateTo'                    => $request->input('date_to', ''),

            // MONEY IN / OUT / NET (period)
            'totalIn'                   => $totalIn,
            'totalOut'                  => $totalOut,
            'netCashFlow'               => $netCashFlow,

            // Income sources (period)
            'posRevenuePeriod'          => $posRevenuePeriod,
            'posOrderCountPeriod'       => $posOrderCountPeriod,
            'projectRevenuePeriod'      => $projectRevenuePeriod,
            'projectRevenueByType'      => $projectRevenueByType,  // ['deposit'=>, 'part_payment'=>, 'full_payment'=>]

            // Expense sections (period)
            'officeExpensesTotal'       => $officeExpensesTotal,
            'operationalExpensesTotal'  => $operationalExpensesTotal,
            'materialsPeriod'           => $materialsPeriod,

            // All-time project intelligence
            'projectValueTotal'         => $projectValueTotal,
            'receivedTotal'             => $receivedTotal,
            'outstandingTotal'          => $outstandingTotal,
            'approvedCostsTotal'        => $approvedCostsTotal,
            'estimatedProfitTotal'      => $estimatedProfitTotal,
            'profitMarginPercent'       => $profitMarginPercent,

            // Health
            'healthStatus'              => $healthStatus,
            'healthLabel'               => $healthLabel,
            'healthSummary'             => $healthSummary,

            // Charts / breakdowns
            'trendSeries'               => $trendSeries,
            'costBreakdown'             => $costBreakdown,
            'topProjects'               => $topProjects,

            // Insights
            'insights'                  => $insights,
            'attentionItems'            => $attentionItems,
        ], $this->viewHelpers()));
    }

    // -------------------------------------------------------------------------
    // Trend Series Builder
    // -------------------------------------------------------------------------

    private function buildTrendSeries(string $period, Carbon $from, Carbon $to, Request $request): array
    {
        $series = [];

        // For today: hourly buckets; week/custom<=14days: daily; else monthly
        if ($period === 'today') {
            for ($h = 0; $h < 24; $h++) {
                $bucketStart = $from->copy()->addHours($h);
                $bucketEnd   = $bucketStart->copy()->addHour()->subSecond();
                $label       = $bucketStart->format('H:i');

                $series[] = [
                    'label'    => $label,
                    'value'    => (float) ProjectPayment::whereBetween('created_at', [$bucketStart, $bucketEnd])->sum('amount'),
                    'received' => (float) ProjectPayment::whereBetween('created_at', [$bucketStart, $bucketEnd])->sum('amount'),
                    'pos'      => (float) Order::where('status', 'completed')->whereBetween('created_at', [$bucketStart, $bucketEnd])->sum('total_amount'),
                    'costs'    => (float) FinancialExpense::where('status', FinancialExpense::STATUS_APPROVED)->whereDate('incurred_on', $bucketStart->toDateString())->sum('amount'),
                ];
            }
            return $series;
        }

        $diffInDays = $from->diffInDays($to);

        if ($diffInDays <= 31 && in_array($period, ['week', 'custom'])) {
            // Daily buckets
            $current = $from->copy()->startOfDay();
            while ($current->lte($to)) {
                $dayEnd = $current->copy()->endOfDay();
                $dateStr = $current->toDateString();
                $series[] = [
                    'label'    => $current->format('d M'),
                    'value'    => (float) ProjectFinancial::whereDate('created_at', $dateStr)->sum('contract_value'),
                    'received' => (float) ProjectPayment::whereDate('payment_date', $dateStr)->sum('amount'),
                    'pos'      => (float) Order::where('status', 'completed')->whereDate('created_at', $dateStr)->sum('total_amount'),
                    'costs'    => (float) FinancialExpense::where('status', FinancialExpense::STATUS_APPROVED)->whereDate('incurred_on', $dateStr)->sum('amount')
                                + (float) FinancialMaterialCost::where('status', FinancialMaterialCost::STATUS_APPROVED)->whereDate('created_at', $dateStr)->sum('total_cost'),
                ];
                $current->addDay();
            }
            return $series;
        }

        // Monthly buckets (month / quarter / year / wide custom)
        $monthsCount = match ($period) {
            'month'   => 1,
            'quarter' => 3,
            'year'    => 12,
            default   => max(1, (int) ceil($from->diffInMonths($to)) + 1),
        };

        if ($period === 'month') {
            // Show daily buckets for this-month
            $current = $from->copy()->startOfDay();
            while ($current->lte($to)) {
                $dateStr = $current->toDateString();
                $series[] = [
                    'label'    => $current->format('d M'),
                    'value'    => (float) ProjectFinancial::whereDate('created_at', $dateStr)->sum('contract_value'),
                    'received' => (float) ProjectPayment::whereDate('payment_date', $dateStr)->sum('amount'),
                    'pos'      => (float) Order::where('status', 'completed')->whereDate('created_at', $dateStr)->sum('total_amount'),
                    'costs'    => (float) FinancialExpense::where('status', FinancialExpense::STATUS_APPROVED)->whereDate('incurred_on', $dateStr)->sum('amount')
                                + (float) FinancialMaterialCost::where('status', FinancialMaterialCost::STATUS_APPROVED)->whereDate('created_at', $dateStr)->sum('total_cost'),
                ];
                $current->addDay();
            }
            return $series;
        }

        // Quarter / Year / wide range → monthly
        $startMonth = $from->copy()->startOfMonth();
        for ($i = 0; $i < $monthsCount; $i++) {
            $monthDate = $startMonth->copy()->addMonths($i);
            $series[] = [
                'label'    => $monthDate->format('M Y'),
                'value'    => (float) ProjectFinancial::whereYear('created_at', $monthDate->year)->whereMonth('created_at', $monthDate->month)->sum('contract_value'),
                'received' => (float) ProjectPayment::whereYear('payment_date', $monthDate->year)->whereMonth('payment_date', $monthDate->month)->sum('amount'),
                'pos'      => (float) Order::where('status', 'completed')->whereYear('created_at', $monthDate->year)->whereMonth('created_at', $monthDate->month)->sum('total_amount'),
                'costs'    => (float) FinancialExpense::where('status', FinancialExpense::STATUS_APPROVED)->whereYear('incurred_on', $monthDate->year)->whereMonth('incurred_on', $monthDate->month)->sum('amount')
                            + (float) FinancialMaterialCost::where('status', FinancialMaterialCost::STATUS_APPROVED)->whereYear('created_at', $monthDate->year)->whereMonth('created_at', $monthDate->month)->sum('total_cost'),
            ];
        }

        return $series;
    }

    // -------------------------------------------------------------------------
    // Insights Builder
    // -------------------------------------------------------------------------

    private function buildInsights(array $projectMetrics, array $costBreakdown, int $pendingApprovalsCount, int $overBudgetCount, float $posRevenuePeriod, string $periodLabel): array
    {
        $insights = [];

        if ($posRevenuePeriod > 0) {
            $insights[] = [
                'type' => 'success',
                'text' => "POS generated " . $this->formatCompactMoney($posRevenuePeriod) . " in revenue for {$periodLabel}.",
            ];
        }

        $highOutstandingProjects = collect($projectMetrics)->filter(fn ($p) => $p['value'] > 0 && ($p['outstanding'] / $p['value']) >= 0.50);
        if ($highOutstandingProjects->isNotEmpty()) {
            $count = $highOutstandingProjects->count();
            $insights[] = [
                'type' => 'warning',
                'text' => "{$count} project(s) have 50% or more of their contract value outstanding.",
            ];
        }

        $overBudgetProjects = collect($projectMetrics)->filter(fn ($p) => $p['value'] > 0 && $p['costs'] > $p['value']);
        if ($overBudgetProjects->isNotEmpty()) {
            $firstOver = $overBudgetProjects->first();
            $pct = round(($firstOver['costs'] / $firstOver['value']) * 100, 1);
            $insights[] = [
                'type' => 'danger',
                'text' => "'{$firstOver['title']}' is over budget — approved costs at {$pct}% of contract value.",
            ];
        }

        $lowMarginProjects = collect($projectMetrics)->filter(fn ($p) => $p['value'] > 0 && $p['costs'] <= $p['value'] && $p['margin'] < 15.0);
        if ($lowMarginProjects->isNotEmpty()) {
            $lowest = $lowMarginProjects->sortBy('margin')->first();
            $insights[] = [
                'type' => 'info',
                'text' => "'{$lowest['title']}' has the lowest estimated profit margin at " . number_format($lowest['margin'], 1) . "%.",
            ];
        }

        if (!empty($costBreakdown)) {
            $topCat = $costBreakdown[0];
            if ($topCat['percentage'] >= 15.0) {
                $insights[] = [
                    'type' => 'info',
                    'text' => "{$topCat['category']} accounts for {$topCat['percentage']}% of total approved costs for {$periodLabel}.",
                ];
            }
        }

        if ($pendingApprovalsCount > 0) {
            $insights[] = [
                'type' => 'warning',
                'text' => "There are {$pendingApprovalsCount} pending financial item(s) awaiting approval.",
            ];
        }

        $mostProfitable = collect($projectMetrics)->sortByDesc('profit')->first();
        if ($mostProfitable && $mostProfitable['profit'] > 0) {
            $insights[] = [
                'type' => 'success',
                'text' => "'{$mostProfitable['title']}' is the most profitable project at " . $this->formatCompactMoney($mostProfitable['profit']) . " (" . number_format($mostProfitable['margin'], 1) . "% margin).",
            ];
        }

        if (empty($insights)) {
            $insights[] = [
                'type' => 'success',
                'text' => 'Financial performance is currently stable. No major issues detected.',
            ];
        }

        return $insights;
    }

    // -------------------------------------------------------------------------
    // Ask Finance (AI-like Q&A)
    // -------------------------------------------------------------------------

    public function ask(Request $request): JsonResponse
    {
        $this->authorizeView();

        $question = trim($request->input('question', ''));
        if (!$question) {
            return response()->json([
                'answer' => 'Please type a financial question about projects, payments, POS revenue, expenses or profitability.',
            ]);
        }

        $q = Str::lower($question);

        // Detect period keywords
        $askFrom = Carbon::now()->startOfMonth();
        $askTo   = Carbon::now()->endOfMonth();
        $askPeriodLabel = 'this month';

        if (Str::contains($q, ['this week', 'current week'])) {
            $askFrom = Carbon::now()->startOfWeek();
            $askTo   = Carbon::now()->endOfWeek();
            $askPeriodLabel = 'this week';
        } elseif (Str::contains($q, ['this year', 'current year'])) {
            $askFrom = Carbon::now()->startOfYear();
            $askTo   = Carbon::now()->endOfYear();
            $askPeriodLabel = 'this year';
        } elseif (Str::contains($q, ['today'])) {
            $askFrom = Carbon::now()->startOfDay();
            $askTo   = Carbon::now()->endOfDay();
            $askPeriodLabel = 'today';
        } elseif (Str::contains($q, ['this quarter'])) {
            $askFrom = Carbon::now()->startOfQuarter();
            $askTo   = Carbon::now()->endOfQuarter();
            $askPeriodLabel = 'this quarter';
        }

        // ── POS revenue questions ────────────────────────────────────────────
        // NOTE: do NOT use bare 'pos' — it matches 'net position', 'deposit', etc.
        if (Str::contains($q, ['pos revenue', 'pos sales', 'sales revenue', 'how much from pos', 'pos generated', 'point of sale revenue', 'from the pos'])) {
            $posPeriod   = $this->posRevenue($askFrom, $askTo);
            $posCount    = $this->posOrderCount($askFrom, $askTo);
            $posAllTime  = $this->posRevenueAllTime();
            return response()->json([
                'answer' => sprintf(
                    "POS revenue for %s: %s from %d completed sale(s). All-time POS revenue: %s.",
                    $askPeriodLabel,
                    $this->formatMoney($posPeriod),
                    $posCount,
                    $this->formatMoney($posAllTime)
                ),
            ]);
        }

        // ── Total money in ───────────────────────────────────────────────────
        if (Str::contains($q, ['total in', 'money in', 'total income', 'total revenue', 'how much came in', 'how much received', 'received this month', 'received this week', 'received this year'])) {
            $pos      = $this->posRevenue($askFrom, $askTo);
            $proj     = $this->projectRevenue($askFrom, $askTo);
            $totalIn  = $pos + $proj;
            return response()->json([
                'answer' => sprintf(
                    "Total money IN for %s: %s (Project payments: %s · POS sales: %s).",
                    $askPeriodLabel,
                    $this->formatMoney($totalIn),
                    $this->formatMoney($proj),
                    $this->formatMoney($pos)
                ),
            ]);
        }

        // ── Total money out ──────────────────────────────────────────────────
        if (Str::contains($q, ['total out', 'money out', 'total expenses', 'total spent', 'how much spent', 'how much went out'])) {
            $exp  = $this->totalApprovedExpenses($askFrom, $askTo);
            $mat  = $this->totalApprovedMaterials($askFrom, $askTo);
            $totalOut = $exp + $mat;
            return response()->json([
                'answer' => sprintf(
                    "Total money OUT for %s: %s (Expenses: %s · Materials: %s).",
                    $askPeriodLabel,
                    $this->formatMoney($totalOut),
                    $this->formatMoney($exp),
                    $this->formatMoney($mat)
                ),
            ]);
        }

        // ── Net / profit question ────────────────────────────────────────────
        if (Str::contains($q, ['net', 'net position', 'net cash', 'overall profit', 'estimated profit', 'total profit', 'how much profit'])) {
            $in  = $this->posRevenue($askFrom, $askTo) + $this->projectRevenue($askFrom, $askTo);
            $out = $this->totalApprovedExpenses($askFrom, $askTo) + $this->totalApprovedMaterials($askFrom, $askTo);
            $net = $in - $out;
            return response()->json([
                'answer' => sprintf(
                    "Net cash flow for %s: %s (Total in: %s · Total out: %s).",
                    $askPeriodLabel,
                    $this->formatMoney($net),
                    $this->formatMoney($in),
                    $this->formatMoney($out)
                ),
            ]);
        }

        // ── Expense category spending (e.g. "how much on diesel", "salary this month") ─
        if (Str::contains($q, ['spent on', 'expense for', 'cost for', 'expenses for', 'how much on', 'how much did we spend on', 'spending on'])) {
            $categories = FinanceExpenseCategory::query()->get();
            $matchedCategory = null;

            foreach ($categories as $cat) {
                if (Str::contains($q, Str::lower($cat->name))) {
                    $matchedCategory = $cat;
                    break;
                }
            }

            if ($matchedCategory) {
                $totalCatPeriod = (float) FinancialExpense::query()
                    ->where('finance_expense_category_id', $matchedCategory->id)
                    ->where('status', FinancialExpense::STATUS_APPROVED)
                    ->whereBetween('incurred_on', [$askFrom->toDateString(), $askTo->toDateString()])
                    ->sum('amount');

                $totalCatAllTime = (float) FinancialExpense::query()
                    ->where('finance_expense_category_id', $matchedCategory->id)
                    ->where('status', FinancialExpense::STATUS_APPROVED)
                    ->sum('amount');

                return response()->json([
                    'answer' => sprintf(
                        "%s — approved spending for %s: %s · All-time: %s.",
                        $matchedCategory->name,
                        $askPeriodLabel,
                        $this->formatMoney($totalCatPeriod),
                        $this->formatMoney($totalCatAllTime)
                    ),
                ]);
            }

            if (Str::contains($q, ['material', 'materials'])) {
                $mat = $this->totalApprovedMaterials($askFrom, $askTo);
                return response()->json([
                    'answer' => sprintf(
                        "Approved material costs for %s: %s.",
                        $askPeriodLabel,
                        $this->formatMoney($mat)
                    ),
                ]);
            }
        }

        // ── Project questions ────────────────────────────────────────────────
        $projects = Project::query()
            ->with(['client', 'financial', 'payments', 'financialExpenses', 'financialMaterialCosts'])
            ->get();

        $projectMetrics = [];
        foreach ($projects as $project) {
            $value    = (float) ($project->financial?->contract_value ?? 0);
            $received = (float) $project->payments->sum('amount');
            $outstanding = max(0, $value - $received);
            $costs    = (float) $project->financialExpenses->where('status', FinancialExpense::STATUS_APPROVED)->sum('amount')
                      + (float) $project->financialMaterialCosts->where('status', FinancialMaterialCost::STATUS_APPROVED)->sum('total_cost');
            $profit   = $value - $costs;
            $margin   = $value > 0 ? ($profit / $value) * 100 : 0.0;
            $projectMetrics[] = compact('project', 'value', 'received', 'outstanding', 'costs', 'profit', 'margin')
                + ['id' => $project->id, 'title' => $project->title ?: $project->project_code, 'client_name' => $project->client?->company_name ?: $project->client?->client_name ?: 'N/A'];
        }

        if (Str::contains($q, ['most profitable', 'best profit', 'highest profit', 'top profit'])) {
            $top = collect($projectMetrics)->sortByDesc('profit')->first();
            if ($top && $top['profit'] > 0) {
                return response()->json([
                    'answer' => sprintf("'%s' has the highest estimated profit at %s (%s%% margin).", $top['title'], $this->formatMoney($top['profit']), number_format($top['margin'], 1)),
                    'project_id'  => $top['id'],
                    'project_url' => route('finance.projects.show', $top['id']),
                ]);
            }
            return response()->json(['answer' => 'No projects with positive estimated profit found.']);
        }

        if (Str::contains($q, ['largest outstanding', 'top outstanding', 'unpaid', 'highest balance', 'clients have the largest'])) {
            $sorted = collect($projectMetrics)->where('outstanding', '>', 0)->sortByDesc('outstanding')->values();
            if ($sorted->isNotEmpty()) {
                $top = $sorted->first();
                return response()->json([
                    'answer' => sprintf("'%s' (%s) has the largest outstanding balance at %s.", $top['title'], $top['client_name'], $this->formatMoney($top['outstanding'])),
                    'project_url' => route('finance.projects.show', $top['id']),
                ]);
            }
            return response()->json(['answer' => 'All client project balances are fully paid.']);
        }

        if (Str::contains($q, ['over budget', 'exceeded budget', 'budget overflow'])) {
            $overList = collect($projectMetrics)->filter(fn ($p) => $p['value'] > 0 && $p['costs'] > $p['value'])->values();
            if ($overList->isNotEmpty()) {
                $first = $overList->first();
                return response()->json([
                    'answer' => sprintf("%d project(s) are over budget. '%s' has approved costs of %s against a contract value of %s.", $overList->count(), $first['title'], $this->formatMoney($first['costs']), $this->formatMoney($first['value'])),
                    'project_url' => route('finance.projects.show', $first['id']),
                ]);
            }
            return response()->json(['answer' => 'No projects are currently over budget.']);
        }

        // Fallback
        return response()->json([
            'answer' => 'I can answer questions about: POS revenue, money in/out, net position, diesel/salary/electricity/any expense category, project profitability, outstanding balances, and over-budget projects. Try adding "this week", "this month", or "this year" for period-specific answers.',
        ]);
    }
}
