<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SolutionItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\ValidationException;

class ApiOrderController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureStaff();

        $query = Order::with(['items.solutionItem', 'user'])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->input('channel'));
        }

        if ($request->boolean('all', false)) {
            return $query->get()->map(fn (Order $order) => $this->transformOrder($order));
        }

        return $query->paginate(25)->through(fn (Order $order) => $this->transformOrder($order));
    }

    public function show(Order $order)
    {
        $this->ensureStaff();

        return $this->transformOrder($order->load(['items.solutionItem', 'user']));
    }

    public function store(Request $request)
    {
        $this->ensureStaff();

        $data = $request->validate([
            'channel' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:32',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:solution_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
            'payment' => 'nullable|array',
            'payment.amount' => 'required_with:payment|numeric|min:0',
            'payment.method' => 'required_with:payment|string|max:50',
            'payment.reference' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($data, $request) {
            $itemsData = [];
            $subtotal = 0;
            $hasStockTransactionsTable = Schema::hasTable('stock_transactions');
            $quantityByItem = [];

            $menuItemIds = collect($data['items'])
                ->pluck('menu_item_id')
                ->unique()
                ->values();

            $menuItems = SolutionItem::whereIn('id', $menuItemIds)
                ->get()
                ->keyBy('id');

            foreach ($data['items'] as $itemInput) {
                $menuItem = $menuItems->get($itemInput['menu_item_id']);
                if (! $menuItem) {
                    throw ValidationException::withMessages([
                        'items' => ['One or more selected items no longer exist. Refresh and try again.'],
                    ]);
                }
                if (! $menuItem->active) {
                    throw ValidationException::withMessages([
                        'items' => ["{$menuItem->name} is inactive."],
                    ]);
                }

                $price = (float) $menuItem->price;
                $lineTotal = $price * $itemInput['quantity'];

                $itemsData[] = [
                    'solution_item_id' => $menuItem->id,
                    'name' => $menuItem->name,
                    'quantity' => $itemInput['quantity'],
                    'unit_price' => $price,
                    'price' => $price,
                    'total' => $lineTotal,
                ];

                $quantityByItem[$menuItem->id] = ($quantityByItem[$menuItem->id] ?? 0) + (int) $itemInput['quantity'];
                $subtotal += $lineTotal;
            }

            $discount = $data['discount'] ?? 0;
            $tax = $data['tax'] ?? 0;
            $total = max(0, $subtotal + $tax - $discount);
            $hasPayment = ! empty($data['payment']);

            $order = Order::create([
                'code' => $this->generateOrderCode(),
                'channel' => $data['channel'] ?? 'pos',
                'user_id' => $request->user()?->id,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total_amount' => $total,
                'status' => $hasPayment ? 'paid' : 'pending',
                'payment_method' => $hasPayment ? ($data['payment']['method'] ?? null) : null,
                'paid_at' => $hasPayment ? now() : null,
                'notes' => $data['note'] ?? null,
            ]);

            $now = now();
            $orderItemsInsert = array_map(function (array $item) use ($order, $now) {
                return [
                    'order_id' => $order->id,
                    'solution_item_id' => $item['solution_item_id'],
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'price' => $item['price'],
                    'total' => $item['total'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $itemsData);
            OrderItem::insert($orderItemsInsert);

            $stockTransactions = [];
            foreach ($quantityByItem as $solutionItemId => $totalQty) {
                $solutionItem = $menuItems->get($solutionItemId);
                if (! $solutionItem) {
                    continue;
                }

                DB::table('solution_items')
                    ->where('id', $solutionItemId)
                    ->decrement('stock', $totalQty);

                $currentStock = (int) DB::table('solution_items')
                    ->where('id', $solutionItemId)
                    ->value('stock');

                DB::table('solution_items')
                    ->where('id', $solutionItemId)
                    ->update(['is_sold_out' => $currentStock <= 0]);

                if ($currentStock <= 2) {
                    $alertType = $currentStock <= 0 ? 'out_of_stock' : 'low_stock';
                    $threshold = $currentStock <= 0 ? 0 : 2;
                    $hasOpenAlert = DB::table('stock_alerts')
                        ->where('solution_item_id', $solutionItemId)
                        ->where('alert_type', $alertType)
                        ->whereNull('acknowledged_at')
                        ->exists();

                    if (! $hasOpenAlert) {
                        DB::table('stock_alerts')->insert([
                            'solution_item_id' => $solutionItemId,
                            'alert_type' => $alertType,
                            'threshold' => $threshold,
                            'current_stock' => $currentStock,
                            'created_by' => $request->user()?->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }

                if ($hasStockTransactionsTable) {
                    $stockTransactions[] = [
                        'solution_item_id' => $solutionItemId,
                        'quantity_changed' => -$totalQty,
                        'transaction_type' => 'sale',
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'user_id' => $request->user()?->id,
                        'notes' => "Sale in order #{$order->code}",
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if ($hasStockTransactionsTable && ! empty($stockTransactions)) {
                DB::table('stock_transactions')->insert($stockTransactions);
            }

            return $this->transformOrder($order->load(['items.solutionItem', 'user']));
        });
    }

    public function approve(Request $request, Order $order)
    {
        $this->ensureStaff();

        $order->fill([
            'status' => 'paid',
            'paid_at' => $order->paid_at ?? now(),
        ])->save();

        return $this->transformOrder($order->fresh(['items.solutionItem', 'user']));
    }

    public function summary()
    {
        $this->ensureAdmin();

        $today = Carbon::today();

        $todayOrders = Order::whereDate('created_at', $today)->count();
        $todayRevenue = Order::whereDate('created_at', $today)->sum('total_amount');

        $since = Carbon::today()->subDays(6);
        $seriesRaw = Order::selectRaw('DATE(created_at) as day, COUNT(*) as orders, SUM(total_amount) as revenue')
            ->whereDate('created_at', '>=', $since)
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $series = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $since->copy()->addDays($i)->format('Y-m-d');
            $found = $seriesRaw->firstWhere('day', $day);
            $series[] = [
                'day' => $day,
                'orders' => (int) ($found->orders ?? 0),
                'revenue' => (float) ($found->revenue ?? 0),
            ];
        }

        return [
            'today_orders' => $todayOrders,
            'today_revenue' => (float) $todayRevenue,
            'series' => $series,
        ];
    }

    public function purge()
    {
        $this->ensureAdmin();

        DB::transaction(function () {
            OrderItem::query()->delete();
            Order::query()->delete();
        });

        return response()->json(['message' => 'All orders cleared.']);
    }

    public function destroy(Order $order)
    {
        $this->ensureAdmin();

        DB::transaction(function () use ($order) {
            $order->items()->delete();
            $order->delete();
        });

        return response()->json(['message' => 'Order deleted.']);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->ensureAdmin();

        $range = $request->get('range', 'monthly');
        $from = match ($range) {
            'weekly' => Carbon::today()->subDays(6),
            default => Carbon::today()->subDays(29),
        };

        $orders = Order::with(['items', 'user'])
            ->whereDate('created_at', '>=', $from)
            ->orderByDesc('created_at')
            ->get();

        $filename = 'orders-' . $range . '-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Code',
                'Status',
                'Total',
                'Channel',
                'Customer Name',
                'Customer Phone',
                'Seller',
                'Items Count',
                'Created At',
            ]);

            foreach ($orders as $order) {
                fputcsv($out, [
                    $order->code,
                    $order->status,
                    $order->total_amount,
                    $order->channel,
                    $order->customer_name,
                    $order->customer_phone,
                    $order->user->name ?? '',
                    $order->items->count(),
                    $order->created_at,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function ensureAdmin(): void
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Unauthorized');
        }
    }

    private function ensureStaff(): void
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if (! $user || ! ($user->isAdmin() || $user->isPos())) {
            abort(403, 'Unauthorized');
        }
    }

    private function generateOrderCode(): string
    {
        $attempts = 0;
        do {
            $code = 'SEC-' . strtoupper(Str::random(6));
            $attempts++;
        } while ($attempts < 10 && Order::where('code', $code)->exists());

        if ($attempts >= 10) {
            $code = 'SEC-' . now()->format('ymdHis');
        }

        return $code;
    }

    private function transformOrder(Order $order): array
    {
        $order->loadMissing(['items.solutionItem', 'user']);

        return [
            'id' => $order->id,
            'code' => $order->code,
            'status' => $order->status,
            'channel' => $order->channel,
            'payment_method' => $order->payment_method,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'subtotal' => $order->subtotal,
            'tax' => $order->tax,
            'discount' => $order->discount,
            'total' => $order->total_amount,
            'total_amount' => $order->total_amount,
            'created_at' => $order->created_at,
            'creator' => $order->user ? [
                'id' => $order->user->id,
                'name' => $order->user->name,
            ] : null,
            'items' => $order->items->map(function (OrderItem $item) {
                return [
                    'id' => $item->id,
                    'menu_item_id' => $item->solution_item_id,
                    'name' => $item->name ?? $item->solutionItem?->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price ?? $item->price,
                    'price' => $item->unit_price ?? $item->price,
                    'total' => $item->total ?? ((float) $item->price * (int) $item->quantity),
                ];
            })->values(),
            'payments' => [],
        ];
    }
}
