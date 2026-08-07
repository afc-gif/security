<?php

namespace Tests\Feature\Admin;

use App\Models\CategoryChecklistTemplate;
use App\Models\Client;
use App\Models\JobChecklistItem;
use App\Models\JobItemAttempt;
use App\Models\JobRequest;
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

    public function test_admin_created_job_request_items_wait_for_coordinator_assignment(): void
    {
        $admin = $this->createAdmin();
        $client = Client::create(['client_name' => 'Acme Client']);
        $category = ServiceCategory::create(['name' => 'CCTV Inspection', 'is_active' => true]);

        $response = $this->actingAs($admin)->post('/admin/job-requests', [
            'client_id' => $client->id,
            'title' => 'Inspect Acme site',
            'description' => 'Initial assessment',
            'categories' => [$category->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_request_items', [
            'service_category_id' => $category->id,
            'claimed_by' => null,
            'status' => JobRequestItem::STATUS_PENDING_ASSIGNMENT,
        ]);
        $this->assertNull(JobRequestItem::firstOrFail()->claimed_at);
    }

    public function test_admin_created_job_request_items_are_not_open_for_claim(): void
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
            'status' => JobRequestItem::STATUS_PENDING_ASSIGNMENT,
        ]);
        $this->assertFalse(JobRequestItem::firstOrFail()->isClaimable());
    }

    public function test_admin_can_manage_field_service_category_checklist_templates(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post('/admin/service-categories', [
                'name' => 'CCTV Maintenance',
                'description' => 'Maintenance visits for existing CCTV systems',
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/service-categories');

        $category = ServiceCategory::where('name', 'CCTV Maintenance')->firstOrFail();

        $this->actingAs($admin)
            ->post("/admin/service-categories/{$category->id}/checklist-templates", [
                'title' => 'Confirm existing camera status',
                'description' => 'Check each installed camera',
                'is_required' => '1',
            ])
            ->assertRedirect('/admin/service-categories');

        $template = CategoryChecklistTemplate::firstOrFail();

        $this->assertDatabaseHas('category_checklist_templates', [
            'id' => $template->id,
            'service_category_id' => $category->id,
            'title' => 'Confirm existing camera status',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put("/admin/checklist-templates/{$template->id}", [
                'title' => 'Confirm camera health',
                'description' => 'Check each installed camera',
                'is_required' => '1',
                'is_active' => '1',
                'sort_order' => 2,
            ])
            ->assertRedirect('/admin/service-categories');

        $this->assertDatabaseHas('category_checklist_templates', [
            'id' => $template->id,
            'title' => 'Confirm camera health',
            'sort_order' => 2,
        ]);
    }

    public function test_job_request_create_uses_field_categories(): void
    {
        $admin = $this->createAdmin();
        $category = ServiceCategory::create([
            'name' => 'CCTV',
            'description' => 'CCTV field work',
            'is_active' => true,
        ]);
        CategoryChecklistTemplate::create([
            'service_category_id' => $category->id,
            'title' => 'Property Type',
            'input_type' => 'multi_choice',
            'options' => ['Residential', 'Commercial'],
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/job-requests/create')
            ->assertOk()
            ->assertSee('Field Categories')
            ->assertSee('CCTV')
            ->assertSee('1 checklist template item');
    }

    public function test_admin_can_add_and_remove_job_specific_checklist_items(): void
    {
        $admin = $this->createAdmin();
        $client = Client::create(['client_name' => 'Checklist Admin Client']);
        $category = ServiceCategory::create(['name' => 'Admin Checklist Category', 'is_active' => true]);
        $jobRequest = JobRequest::create([
            'client_id' => $client->id,
            'title' => 'Admin checklist job',
            'created_by' => $admin->id,
            'status' => 'open',
        ]);
        $jobItem = JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $category->id,
            'created_by' => $admin->id,
            'status' => JobRequestItem::STATUS_PENDING_ASSIGNMENT,
            'title' => $category->name,
        ]);

        $this->actingAs($admin)
            ->post("/admin/job-items/{$jobItem->id}/checklist", [
                'title' => 'Admin added checklist item',
            ])
            ->assertRedirect("/admin/job-requests/{$jobRequest->id}");

        $checklistItem = JobChecklistItem::firstOrFail();

        $this->actingAs($admin)
            ->delete("/admin/job-items/{$jobItem->id}/checklist/{$checklistItem->id}")
            ->assertRedirect("/admin/job-requests/{$jobRequest->id}");

        $this->assertDatabaseMissing('job_checklist_items', [
            'id' => $checklistItem->id,
        ]);
    }

    public function test_admin_edits_requirements_before_project_conversion(): void
    {
        $admin = $this->createAdmin();
        $fieldStaff = $this->createUser(['role' => 'field_staff']);
        $client = Client::create(['client_name' => 'Checklist Client']);
        $category = ServiceCategory::create(['name' => 'Inspection', 'is_active' => true]);
        $jobRequest = JobRequest::create([
            'client_id' => $client->id,
            'title' => 'Site inspection',
            'created_by' => $admin->id,
            'status' => 'open',
        ]);
        $jobItem = JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $category->id,
            'created_by' => $admin->id,
            'claimed_by' => $fieldStaff->id,
            'claimed_at' => now(),
            'status' => JobRequestItem::STATUS_PENDING_ADMIN_REVIEW,
            'title' => $category->name,
            'submitted_at' => now(),
        ]);
        $attempt = JobItemAttempt::create([
            'job_request_item_id' => $jobItem->id,
            'user_id' => $fieldStaff->id,
            'status' => JobItemAttempt::STATUS_COORDINATOR_APPROVED,
            'notes' => 'Inspection completed.',
        ]);
        $attempt->requirements()->createMany([
            ['type' => 'material', 'name' => 'Camera', 'quantity' => '4', 'sort_order' => 0],
            ['type' => 'material', 'name' => 'Customer rejected item', 'quantity' => '1', 'sort_order' => 1],
        ]);

        $approveResponse = $this->actingAs($admin)->post("/admin/job-items/{$jobItem->id}/review", [
            'action' => 'approve',
            'admin_note' => '',
            'requirements' => [
                ['include' => '1', 'type' => 'material', 'name' => 'Camera', 'quantity' => '2', 'notes' => 'Approved by customer'],
                ['include' => '0', 'type' => 'material', 'name' => 'Customer rejected item', 'quantity' => '1', 'notes' => ''],
            ],
        ]);

        $approveResponse->assertRedirect();
        $this->assertDatabaseHas('job_item_requirements', [
            'job_item_attempt_id' => $attempt->id,
            'name' => 'Camera',
            'quantity' => '2',
            'notes' => 'Approved by customer',
        ]);
        $this->assertDatabaseMissing('job_item_requirements', [
            'job_item_attempt_id' => $attempt->id,
            'name' => 'Customer rejected item',
        ]);

        $convertResponse = $this->actingAs($admin)->post("/admin/job-items/{$jobItem->id}/convert-to-project");

        $convertResponse->assertRedirect();
        $this->assertDatabaseHas('project_requirements', [
            'name' => 'Camera',
            'quantity' => '2',
            'is_done' => false,
        ]);
        $this->assertDatabaseMissing('project_requirements', [
            'name' => 'Customer rejected item',
        ]);
    }

    public function test_admin_can_open_overdue_job_item_review_page_without_submission(): void
    {
        $admin = $this->createAdmin();
        $client = Client::create(['client_name' => 'Overdue Client']);
        $category = ServiceCategory::create(['name' => 'Inspection', 'is_active' => true]);
        $jobRequest = JobRequest::create([
            'client_id' => $client->id,
            'title' => 'Overdue inspection',
            'created_by' => $admin->id,
            'status' => 'open',
        ]);
        $jobItem = JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $category->id,
            'created_by' => $admin->id,
            'status' => JobRequestItem::STATUS_OVERDUE,
            'title' => $category->name,
            'due_date' => now()->subDay(),
        ]);

        $this->actingAs($admin)
            ->get("/admin/job-items/{$jobItem->id}")
            ->assertOk()
            ->assertSee('Reopen Job');
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
