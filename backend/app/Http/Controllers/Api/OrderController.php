<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SolutionItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
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

            foreach ($data['items'] as $itemInput) {
                $menuItem = SolutionItem::findOrFail($itemInput['menu_item_id']);
                if (! $menuItem->active) {
                    throw ValidationException::withMessages([
                        'items' => ["{$menuItem->name} is inactive."],
                    ]);
                }

                if ($menuItem->is_sold_out) {
                    throw ValidationException::withMessages([
                        'items' => ["{$menuItem->name} is sold out."],
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

            foreach ($itemsData as $item) {
                $order->items()->create($item);
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
        $user = auth()->user();
        if (! $user || $user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }

    private function ensureStaff(): void
    {
        $user = auth()->user();
        if (! $user || ! in_array($user->role, ['admin', 'pos'], true)) {
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
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'subtotal' => $order->subtotal,
            'tax' => $order->tax,
            'discount' => $order->discount,
            'total' => $order->total_amount,
            'kitchen_status' => $order->kitchen_status,
            'kitchen_eta_minutes' => $order->kitchen_eta_minutes,
            'kitchen_eta_at' => $order->kitchen_eta_at,
            'kitchen_note' => $order->kitchen_note,
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
