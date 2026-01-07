<?php

namespace Tests\Feature\Pos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PosApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_products_endpoint_returns_active_items(): void
    {
        $solution = $this->createSolution();
        $item = $this->createSolutionItem($solution, ['active' => true]);

        $response = $this->getJson('/api/pos/products');
        $response->assertOk()
            ->assertJsonFragment(['id' => $item->id, 'name' => $item->name]);
    }

    public function test_lookup_and_search_require_authentication(): void
    {
        $solution = $this->createSolution();
        $barcode = 'BC-' . Str::random(8);
        $item = $this->createSolutionItem($solution, ['barcode' => $barcode]);
        $searchTerm = substr($barcode, 3, 4);

        $this->get("/api/pos/barcode/{$item->barcode}")
            ->assertRedirect('/login');

        $this->get("/api/pos/search/{$searchTerm}")
            ->assertRedirect('/login');
    }

    public function test_authenticated_lookup_and_search_return_data(): void
    {
        $posUser = $this->createPosUser();
        $solution = $this->createSolution();
        $barcode = 'BC-' . Str::random(8);
        $item = $this->createSolutionItem($solution, ['barcode' => $barcode]);
        $searchTerm = substr($barcode, 3, 4);

        $this->actingAs($posUser)
            ->getJson("/api/pos/barcode/{$item->barcode}")
            ->assertOk()
            ->assertJson(['barcode' => $item->barcode]);

        $this->actingAs($posUser)
            ->getJson("/api/pos/search/{$searchTerm}")
            ->assertOk()
            ->assertJsonFragment(['barcode' => $item->barcode]);
    }

    public function test_complete_sale_creates_order_and_decrements_stock(): void
    {
        $posUser = $this->createPosUser();
        $solution = $this->createSolution();
        $item = $this->createSolutionItem($solution, ['stock' => 5, 'price' => 2000]);

        $payload = [
            'items' => [
                [
                    'id' => $item->id,
                    'quantity' => 2,
                    'price' => 2000,
                ],
            ],
            'total' => 4000,
            'payment_method' => 'cash',
        ];

        $response = $this->actingAs($posUser)
            ->postJson('/api/pos/complete-sale', $payload);

        $response->assertOk()
            ->assertJson(['success' => true, 'total' => 4000]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $posUser->id,
            'status' => 'completed',
            'total_amount' => 4000,
        ]);

        $this->assertDatabaseHas('order_items', [
            'solution_item_id' => $item->id,
            'quantity' => 2,
            'price' => 2000,
        ]);

        $this->assertDatabaseHas('solution_items', [
            'id' => $item->id,
            'stock' => 3,
        ]);
    }
}
