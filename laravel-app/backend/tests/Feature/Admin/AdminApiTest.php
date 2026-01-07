<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_categories(): void
    {
        $admin = $this->createAdmin();

        $createResponse = $this->actingAs($admin)->postJson('/api/categories', [
            'name' => 'Access Control',
            'description' => 'Access control solutions',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $createResponse->assertCreated();
        $categoryId = $createResponse->json('id');

        $updateResponse = $this->actingAs($admin)->putJson("/api/categories/{$categoryId}", [
            'name' => 'Access Control Updated',
            'is_active' => false,
        ]);

        $updateResponse->assertOk()
            ->assertJson(['name' => 'Access Control Updated', 'is_active' => false]);

        $deleteResponse = $this->actingAs($admin)->deleteJson("/api/categories/{$categoryId}");
        $deleteResponse->assertNoContent();
        $this->assertDatabaseMissing('solutions', ['id' => $categoryId]);
    }

    public function test_admin_can_manage_menu_items(): void
    {
        $admin = $this->createAdmin();
        $solution = $this->createSolution();

        $createResponse = $this->actingAs($admin)->postJson('/api/menu-items', [
            'category_id' => $solution->id,
            'name' => 'Door Sensor',
            'price' => 1200,
            'stock' => 5,
            'is_active' => true,
            'is_sold_out' => false,
        ]);

        $createResponse->assertCreated();
        $menuItemId = $createResponse->json('id');

        $updateResponse = $this->actingAs($admin)->putJson("/api/menu-items/{$menuItemId}", [
            'name' => 'Door Sensor Updated',
            'price' => 1500,
            'is_sold_out' => true,
        ]);

        $updateResponse->assertOk()
            ->assertJson(['name' => 'Door Sensor Updated', 'is_sold_out' => true]);

        $toggleResponse = $this->actingAs($admin)->postJson("/api/menu-items/{$menuItemId}/toggle-sold-out");
        $toggleResponse->assertOk();

        $barcodeResponse = $this->actingAs($admin)->postJson("/api/menu-items/{$menuItemId}/regenerate-barcode");
        $barcodeResponse->assertOk()
            ->assertJsonStructure(['barcode']);

        $deleteResponse = $this->actingAs($admin)->deleteJson("/api/menu-items/{$menuItemId}");
        $deleteResponse->assertNoContent();
        $this->assertDatabaseMissing('solution_items', ['id' => $menuItemId]);
    }

    public function test_admin_can_manage_users_via_api(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser(['status' => 'pending', 'role' => 'user']);

        $indexResponse = $this->actingAs($admin)->getJson('/api/users');
        $indexResponse->assertOk()
            ->assertJsonFragment(['email' => $user->email]);

        $updateResponse = $this->actingAs($admin)->putJson("/api/users/{$user->id}", [
            'role' => 'pos',
            'is_active' => true,
        ]);

        $updateResponse->assertOk()
            ->assertJson(['role' => 'pos', 'is_active' => true]);

        $denyDeleteSelf = $this->actingAs($admin)->deleteJson("/api/users/{$admin->id}");
        $denyDeleteSelf->assertStatus(422);

        $deleteResponse = $this->actingAs($admin)->deleteJson("/api/users/{$user->id}");
        $deleteResponse->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_order_summary_export_and_purge(): void
    {
        $admin = $this->createAdmin();
        $solution = $this->createSolution();
        $item = $this->createSolutionItem($solution);
        $order = $this->createOrder($admin, ['status' => 'paid', 'total_amount' => 5000]);
        $this->createOrderItem($order, $item);

        $summaryResponse = $this->actingAs($admin)->getJson('/api/orders/summary');
        $summaryResponse->assertOk()
            ->assertJsonStructure(['today_orders', 'today_revenue', 'series']);

        $exportResponse = $this->actingAs($admin)->get('/api/orders/export');
        $exportResponse->assertOk();
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));

        $purgeResponse = $this->actingAs($admin)->postJson('/api/orders/purge');
        $purgeResponse->assertOk()
            ->assertJson(['message' => 'All orders cleared.']);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_non_admin_cannot_access_admin_api(): void
    {
        $posUser = $this->createPosUser();

        $this->actingAs($posUser)
            ->postJson('/api/categories', ['name' => 'Blocked'])
            ->assertForbidden();

        $this->actingAs($posUser)
            ->getJson('/api/orders/summary')
            ->assertForbidden();
    }
}
