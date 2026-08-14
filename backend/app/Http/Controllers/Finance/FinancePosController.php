<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinancePermission;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinancePosController extends Controller
{
    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    private function authorizeView(): void
    {
        $user = auth()->user();
        if (!$user instanceof User || !($user->isFinance() || $user->hasFinancePermission(FinancePermission::VIEW))) {
            abort(403, 'Unauthorized access to Finance POS Sales.');
        }
    }

    // -------------------------------------------------------------------------
    // Period Resolution (mirrors FinanceAnalysisController)
    // -------------------------------------------------------------------------

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
    // Index — Finance read-only POS Sales view
    // -------------------------------------------------------------------------

    public function index(Request $request)
    {
        $this->authorizeView();

        [$from, $to, $periodLabel, $period] = $this->resolvePeriod($request);

        // All completed orders in the period, newest first, paginated
        $orders = Order::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->with('items')
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        // Period total
        $periodTotal = (float) Order::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->sum('total_amount');

        $periodCount = (int) Order::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // All-time total (for context card)
        $allTimeTotal = (float) Order::query()
            ->where('status', 'completed')
            ->sum('total_amount');

        $allTimeCount = (int) Order::query()
            ->where('status', 'completed')
            ->count();

        $financeMoney = fn ($amount) => '₦' . number_format((float) ($amount ?? 0), 2);

        return view('finance.pos-sales.index', compact(
            'orders',
            'periodTotal',
            'periodCount',
            'allTimeTotal',
            'allTimeCount',
            'periodLabel',
            'period',
            'from',
            'to',
            'financeMoney',
        ));
    }
}
