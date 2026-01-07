<?php

namespace Tests\Feature\Pos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_create_and_view_orders(): void
    {
        $posUser = $this->createPosUser();
        $solution = $this->createSolution();
        $item = $this->createSolutionItem($solution, ['price' => 1500]);

        $createResponse = $this->actingAs($posUser)->postJson('/api/orders', [
            'customer_name' => 'Test Customer',
            'items' => [
                [
                    'menu_item_id' => $item->id,
                    'quantity' => 2,
                ],
            ],
            'tax' => 0,
            'discount' => 0,
        ]);

        $createResponse->assertOk()
            ->assertJsonFragment(['customer_name' => 'Test Customer']);

        $orderId = $createResponse->json('id');

        $indexResponse = $this->actingAs($posUser)->getJson('/api/orders?all=1');
        $indexResponse->assertOk()
            ->assertJsonFragment(['id' => $orderId]);

        $showResponse = $this->actingAs($posUser)->getJson("/api/orders/{$orderId}");
        $showResponse->assertOk()
            ->assertJsonFragment(['id' => $orderId]);
    }

    public function test_staff_can_update_kitchen_and_approve_orders(): void
    {
        $posUser = $this->createPosUser();
        $solution = $this->createSolution();
        $item = $this->createSolutionItem($solution, ['price' => 1000]);
        $order = $this->createOrder($posUser, ['status' => 'pending']);
        $this->createOrderItem($order, $item);

        $sendResponse = $this->actingAs($posUser)->postJson("/api/orders/{$order->id}/send-to-kitchen", [
            'note' => 'Rush order',
            'eta_minutes' => 15,
        ]);

        $sendResponse->assertOk()
            ->assertJsonFragment(['kitchen_status' => 'queued']);

        $updateResponse = $this->actingAs($posUser)->postJson("/api/orders/{$order->id}/kitchen-status", [
            'kitchen_status' => 'prepping',
            'eta_minutes' => 10,
        ]);

        $updateResponse->assertOk()
            ->assertJsonFragment(['kitchen_status' => 'prepping']);

        $approveResponse = $this->actingAs($posUser)->postJson("/api/orders/{$order->id}/approve", [
            'note' => 'Paid',
            'send_to_kitchen' => false,
        ]);

        $approveResponse->assertOk()
            ->assertJsonFragment(['status' => 'paid']);
    }
}
