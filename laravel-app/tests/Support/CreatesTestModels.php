<?php

namespace Tests\Support;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Solution;
use App\Models\SolutionItem;
use App\Models\User;
use Illuminate\Support\Str;

trait CreatesTestModels
{
    protected function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'email' => 'user_' . Str::random(6) . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
            'status' => 'approved',
        ], $overrides));
    }

    protected function createAdmin(array $overrides = []): User
    {
        return $this->createUser(array_merge([
            'name' => 'Admin User',
            'role' => 'admin',
            'status' => 'approved',
        ], $overrides));
    }

    protected function createPosUser(array $overrides = []): User
    {
        return $this->createUser(array_merge([
            'name' => 'POS User',
            'role' => 'pos',
            'status' => 'approved',
        ], $overrides));
    }

    protected function createSolution(array $overrides = []): Solution
    {
        return Solution::create(array_merge([
            'name' => 'Solution ' . Str::random(4),
            'icon' => 'SEC',
            'description' => 'Test solution',
            'sort_order' => 0,
            'active' => true,
        ], $overrides));
    }

    protected function createSolutionItem(Solution $solution, array $overrides = []): SolutionItem
    {
        return SolutionItem::create(array_merge([
            'solution_id' => $solution->id,
            'name' => 'Solution Item ' . Str::random(4),
            'barcode' => 'BC-' . Str::random(8),
            'description' => 'Test item',
            'price' => 1000,
            'stock' => 10,
            'sort_order' => 0,
            'active' => true,
            'is_sold_out' => false,
        ], $overrides));
    }

    protected function createProduct(Solution $solution, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Product ' . Str::random(4),
            'description' => 'Test product',
            'price' => 2000,
            'stock' => 5,
            'category' => $solution->name,
        ], $overrides));
    }

    protected function createOrder(User $user, array $overrides = []): Order
    {
        return Order::create(array_merge([
            'code' => 'SEC-' . strtoupper(Str::random(6)),
            'user_id' => $user->id,
            'channel' => 'pos',
            'total_amount' => 1000,
            'status' => 'pending',
        ], $overrides));
    }

    protected function createOrderItem(Order $order, SolutionItem $item, array $overrides = []): OrderItem
    {
        return OrderItem::create(array_merge([
            'order_id' => $order->id,
            'solution_item_id' => $item->id,
            'name' => $item->name,
            'quantity' => 1,
            'price' => $item->price,
            'unit_price' => $item->price,
            'total' => $item->price,
        ], $overrides));
    }
}
