<?php

namespace Tests\Feature\Admin;

use App\Models\Client;
use App\Models\JobRequestItem;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_pages(): void
    {
        $user = $this->createUser(['role' => 'user']);

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_admin_can_view_dashboard_and_products(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/products')
            ->assertOk();
    }

    public function test_admin_can_create_update_and_delete_product(): void
    {
        $admin = $this->createAdmin();
        $solution = $this->createSolution(['name' => 'CCTV']);

        $createResponse = $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Test Product',
            'description' => 'Product description',
            'price' => 2500,
            'stock' => 4,
            'category' => $solution->name,
        ]);

        $createResponse->assertRedirect('/admin/products');
        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
        $this->assertDatabaseHas('solution_items', ['name' => 'Test Product']);

        $product = \App\Models\Product::firstOrFail();

        $updateResponse = $this->actingAs($admin)->put("/admin/products/{$product->id}", [
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => 3000,
            'stock' => 10,
            'category' => $solution->name,
        ]);

        $updateResponse->assertRedirect('/admin/products');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated Product']);

        $deleteResponse = $this->actingAs($admin)->delete("/admin/products/{$product->id}");
        $deleteResponse->assertRedirect('/admin/products');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_admin_can_manage_users(): void
    {
        $admin = $this->createAdmin();
        $pendingUser = $this->createUser(['status' => 'pending', 'role' => 'user']);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/users/pending')
            ->assertOk();

        $approveResponse = $this->actingAs($admin)
            ->patch("/admin/users/{$pendingUser->id}/approve/pos");

        $approveResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $pendingUser->id,
            'role' => 'pos',
            'status' => 'approved',
        ]);

        $rejectUser = $this->createUser(['status' => 'pending', 'role' => 'user']);
        $rejectResponse = $this->actingAs($admin)
            ->patch("/admin/users/{$rejectUser->id}/reject");

        $rejectResponse->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $rejectUser->id]);

        $deleteUser = $this->createUser(['status' => 'approved', 'role' => 'user']);
        $deleteResponse = $this->actingAs($admin)
            ->delete("/admin/users/{$deleteUser->id}");

        $deleteResponse->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users', ['id' => $deleteUser->id]);
    }

    public function test_admin_can_assign_created_job_request_items_to_field_staff(): void
    {
        $admin = $this->createAdmin();
        $fieldStaff = $this->createUser([
            'name' => 'Field Staff One',
            'role' => 'field_staff',
            'status' => 'approved',
        ]);
        $client = Client::create(['client_name' => 'Acme Client']);
        $category = ServiceCategory::create(['name' => 'CCTV Inspection', 'is_active' => true]);

        $response = $this->actingAs($admin)->post('/admin/job-requests', [
            'client_id' => $client->id,
            'title' => 'Inspect Acme site',
            'description' => 'Initial assessment',
            'assigned_field_staff_id' => $fieldStaff->id,
            'categories' => [$category->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_request_items', [
            'service_category_id' => $category->id,
            'claimed_by' => $fieldStaff->id,
            'status' => JobRequestItem::STATUS_CLAIMED,
        ]);
        $this->assertNotNull(JobRequestItem::firstOrFail()->claimed_at);
    }

    public function test_admin_can_leave_created_job_request_items_open_for_claim(): void
    {
        $admin = $this->createAdmin();
        $client = Client::create(['client_name' => 'Open Claim Client']);
        $category = ServiceCategory::create(['name' => 'Access Control Survey', 'is_active' => true]);

        $response = $this->actingAs($admin)->post('/admin/job-requests', [
            'client_id' => $client->id,
            'title' => 'Survey access points',
            'categories' => [$category->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_request_items', [
            'service_category_id' => $category->id,
            'claimed_by' => null,
            'status' => JobRequestItem::STATUS_OPEN,
        ]);
        $this->assertTrue(JobRequestItem::firstOrFail()->isClaimable());
    }

    public function test_admin_can_manage_solutions_and_items(): void
    {
        $admin = $this->createAdmin();

        $createSolutionResponse = $this->actingAs($admin)->post('/admin/solutions', [
            'name' => 'Perimeter Security',
            'icon' => 'SEC',
            'description' => 'Outdoor security systems',
            'sort_order' => 1,
        ]);

        $createSolutionResponse->assertRedirect('/admin/solutions');
        $solution = \App\Models\Solution::firstOrFail();

        $updateSolutionResponse = $this->actingAs($admin)->put("/admin/solutions/{$solution->id}", [
            'name' => 'Perimeter Security Updated',
            'icon' => 'SEC',
            'description' => 'Updated description',
            'sort_order' => 2,
        ]);

        $updateSolutionResponse->assertRedirect('/admin/solutions');
        $this->assertDatabaseHas('solutions', ['id' => $solution->id, 'name' => 'Perimeter Security Updated']);

        $createItemResponse = $this->actingAs($admin)->post("/admin/solutions/{$solution->id}/items", [
            'name' => 'Fence Sensor',
            'description' => 'Intrusion sensor',
            'price' => 1500,
            'stock' => 8,
            'sort_order' => 1,
        ]);

        $createItemResponse->assertRedirect("/admin/solutions/{$solution->id}");
        $item = \App\Models\SolutionItem::firstOrFail();

        $updateItemResponse = $this->actingAs($admin)->put("/admin/solutions/{$solution->id}/items/{$item->id}", [
            'name' => 'Fence Sensor Pro',
            'description' => 'Updated sensor',
            'price' => 2000,
            'stock' => 6,
            'sort_order' => 2,
        ]);

        $updateItemResponse->assertRedirect("/admin/solutions/{$solution->id}");
        $this->assertDatabaseHas('solution_items', ['id' => $item->id, 'name' => 'Fence Sensor Pro']);

        $deleteItemResponse = $this->actingAs($admin)
            ->delete("/admin/solutions/{$solution->id}/items/{$item->id}");

        $deleteItemResponse->assertRedirect("/admin/solutions/{$solution->id}");
        $this->assertDatabaseMissing('solution_items', ['id' => $item->id]);

        $deleteSolutionResponse = $this->actingAs($admin)
            ->delete("/admin/solutions/{$solution->id}");

        $deleteSolutionResponse->assertRedirect('/admin/solutions');
        $this->assertDatabaseMissing('solutions', ['id' => $solution->id]);
    }
}
