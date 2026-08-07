<?php

namespace Tests\Feature\Field;

use App\Models\Client;
use App\Models\CategoryChecklistTemplate;
use App\Models\JobItemAttempt;
use App\Models\JobChecklistItem;
use App\Models\JobRequest;
use App\Models\JobRequestItem;
use App\Models\Project;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_field_staff_submits_job_report_with_material_and_task_requirements(): void
    {
        $admin = $this->createAdmin();
        $fieldStaff = $this->createUser(['role' => 'field_staff']);
        $client = Client::create(['client_name' => 'Field Client']);
        $category = ServiceCategory::create(['name' => 'Inspection', 'is_active' => true]);
        $template = CategoryChecklistTemplate::create([
            'service_category_id' => $category->id,
            'title' => 'Confirm site access',
            'input_type' => 'single_choice',
            'options' => ['Yes', 'No'],
            'sort_order' => 0,
        ]);
        $jobRequest = JobRequest::create([
            'client_id' => $client->id,
            'title' => 'Inspect site',
            'created_by' => $admin->id,
            'status' => 'open',
        ]);
        $jobItem = JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $category->id,
            'created_by' => $admin->id,
            'claimed_by' => $fieldStaff->id,
            'claimed_at' => now(),
            'status' => JobRequestItem::STATUS_CLAIMED,
            'title' => $category->name,
        ]);
        $jobItem->ensureChecklistFromCategory();
        $checklistItem = $jobItem->checklistItems()->firstOrFail();

        $response = $this->actingAs($fieldStaff)->post("/field/jobs/{$jobItem->id}/submit", [
            'notes' => 'Inspection done. Customer needs approved scope.',
            'checklist' => [
                $checklistItem->id => [
                    'status' => JobChecklistItem::STATUS_DONE,
                    'response' => 'Yes',
                    'notes' => 'Gate opened by client.',
                ],
            ],
            'custom_checklist' => [
                [
                    'title' => 'Confirm network rack location',
                    'status' => JobChecklistItem::STATUS_DONE,
                    'notes' => 'Rack is in the server room.',
                ],
            ],
            'requirements' => [
                ['type' => 'material', 'name' => 'CCTV Camera', 'quantity' => '4', 'notes' => 'Outdoor cameras'],
                ['type' => 'task', 'name' => 'Configure remote viewing', 'quantity' => '', 'notes' => ''],
            ],
        ]);

        $response->assertRedirect("/field/jobs/{$jobItem->id}");
        $this->assertDatabaseHas('job_request_items', [
            'id' => $jobItem->id,
            'status' => JobRequestItem::STATUS_SUBMITTED,
        ]);
        $attempt = JobItemAttempt::firstOrFail();
        $this->assertDatabaseHas('job_item_requirements', [
            'job_item_attempt_id' => $attempt->id,
            'type' => 'material',
            'name' => 'CCTV Camera',
            'quantity' => '4',
        ]);
        $this->assertDatabaseHas('job_item_requirements', [
            'job_item_attempt_id' => $attempt->id,
            'type' => 'task',
            'name' => 'Configure remote viewing',
        ]);
        $this->assertDatabaseHas('job_checklist_items', [
            'category_checklist_template_id' => $template->id,
            'status' => JobChecklistItem::STATUS_DONE,
            'response' => 'Yes',
            'notes' => 'Gate opened by client.',
        ]);
        $this->assertDatabaseHas('job_checklist_items', [
            'job_request_item_id' => $jobItem->id,
            'title' => 'Confirm network rack location',
            'is_custom' => true,
            'status' => JobChecklistItem::STATUS_DONE,
        ]);
    }

    public function test_field_coordinator_assigns_pending_job_to_field_staff(): void
    {
        $admin = $this->createAdmin();
        $coordinator = $this->createUser(['role' => 'field_coordinator']);
        $fieldStaff = $this->createUser(['role' => 'field_staff']);
        $client = Client::create(['client_name' => 'Dispatch Client']);
        $category = ServiceCategory::create(['name' => 'Inspection', 'is_active' => true]);
        $jobRequest = JobRequest::create([
            'client_id' => $client->id,
            'title' => 'Dispatch inspection',
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

        $this->actingAs($fieldStaff)
            ->get('/field/jobs')
            ->assertOk()
            ->assertDontSee('Dispatch inspection');

        $response = $this->actingAs($coordinator)->post("/coordinator/jobs/{$jobItem->id}/assign", [
            'assigned_to' => $fieldStaff->id,
        ]);

        $response->assertRedirect('/coordinator/jobs')
            ->assertSessionHas('whatsapp_url');

        $this->assertStringContainsString('https://wa.me/2349160450776', session('whatsapp_url'));

        $this->assertDatabaseHas('job_request_items', [
            'id' => $jobItem->id,
            'claimed_by' => $fieldStaff->id,
            'status' => JobRequestItem::STATUS_CLAIMED,
        ]);
        $this->assertDatabaseHas('admin_notifications', [
            'user_id' => $admin->id,
            'type' => 'transport_fare_required',
            'title' => 'Transport fare required',
        ]);
    }

    public function test_field_coordinator_dashboard_shows_pending_assignment_notification(): void
    {
        $admin = $this->createAdmin();
        $coordinator = $this->createUser(['role' => 'field_coordinator']);
        $client = Client::create(['client_name' => 'Notification Client']);
        $category = ServiceCategory::create(['name' => 'CCTV Survey', 'is_active' => true]);
        $jobRequest = JobRequest::create([
            'client_id' => $client->id,
            'title' => 'Notify coordinator',
            'created_by' => $admin->id,
            'status' => 'open',
        ]);

        JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $category->id,
            'created_by' => $admin->id,
            'status' => JobRequestItem::STATUS_PENDING_ASSIGNMENT,
            'title' => $category->name,
        ]);

        $this->actingAs($coordinator)
            ->get('/field/dashboard')
            ->assertOk()
            ->assertSee('1 job waiting for assignment')
            ->assertSee('Notify coordinator')
            ->assertSee('Assign Jobs');

        $this->actingAs($coordinator)
            ->getJson('/field/dashboard/pending-assignments')
            ->assertOk()
            ->assertJson(['count' => 1]);
    }

    public function test_field_coordinator_can_add_and_remove_job_checklist_items(): void
    {
        $admin = $this->createAdmin();
        $coordinator = $this->createUser(['role' => 'field_coordinator']);
        $client = Client::create(['client_name' => 'Coordinator Checklist Client']);
        $category = ServiceCategory::create(['name' => 'Coordinator Checklist Category', 'is_active' => true]);
        $jobRequest = JobRequest::create([
            'client_id' => $client->id,
            'title' => 'Coordinator checklist job',
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

        $this->actingAs($coordinator)
            ->post("/coordinator/jobs/{$jobItem->id}/checklist", [
                'title' => 'Coordinator added checklist item',
            ])
            ->assertRedirect('/coordinator/jobs');

        $checklistItem = JobChecklistItem::firstOrFail();

        $this->actingAs($coordinator)
            ->delete("/coordinator/jobs/{$jobItem->id}/checklist/{$checklistItem->id}")
            ->assertRedirect('/coordinator/jobs');

        $this->assertDatabaseMissing('job_checklist_items', [
            'id' => $checklistItem->id,
        ]);
    }

    public function test_field_staff_dashboard_does_not_show_coordinator_assignment_notification(): void
    {
        $admin = $this->createAdmin();
        $fieldStaff = $this->createUser(['role' => 'field_staff']);
        $client = Client::create(['client_name' => 'Hidden Notification Client']);
        $category = ServiceCategory::create(['name' => 'Access Control Survey', 'is_active' => true]);
        $jobRequest = JobRequest::create([
            'client_id' => $client->id,
            'title' => 'Hidden coordinator job',
            'created_by' => $admin->id,
            'status' => 'open',
        ]);

        JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $category->id,
            'created_by' => $admin->id,
            'status' => JobRequestItem::STATUS_PENDING_ASSIGNMENT,
            'title' => $category->name,
        ]);

        $this->actingAs($fieldStaff)
            ->get('/field/dashboard')
            ->assertOk()
            ->assertDontSee('job waiting for assignment')
            ->assertDontSee('Hidden coordinator job');

        $this->actingAs($fieldStaff)
            ->getJson('/field/dashboard/pending-assignments')
            ->assertForbidden();
    }

    public function test_field_coordinator_can_release_pending_job_for_open_claim(): void
    {
        $admin = $this->createAdmin();
        $coordinator = $this->createUser(['role' => 'field_coordinator']);
        $fieldStaff = $this->createUser(['role' => 'field_staff']);
        $client = Client::create(['client_name' => 'Open Claim Dispatch Client']);
        $category = ServiceCategory::create(['name' => 'Inspection', 'is_active' => true]);
        $jobRequest = JobRequest::create([
            'client_id' => $client->id,
            'title' => 'Release inspection',
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

        $this->actingAs($coordinator)
            ->post("/coordinator/jobs/{$jobItem->id}/release")
            ->assertRedirect('/coordinator/jobs');

        $this->assertDatabaseHas('job_request_items', [
            'id' => $jobItem->id,
            'claimed_by' => null,
            'status' => JobRequestItem::STATUS_OPEN,
        ]);

        $this->actingAs($fieldStaff)
            ->get('/field/jobs')
            ->assertOk()
            ->assertSee('Release inspection');
    }

    public function test_field_coordinator_approves_submitted_report_for_admin_review(): void
    {
        $admin = $this->createAdmin();
        $coordinator = $this->createUser(['role' => 'field_coordinator']);
        $fieldStaff = $this->createUser(['role' => 'field_staff']);
        $client = Client::create(['client_name' => 'Coordinator Review Client']);
        $category = ServiceCategory::create(['name' => 'Inspection', 'is_active' => true]);
        $jobRequest = JobRequest::create([
            'client_id' => $client->id,
            'title' => 'Review inspection',
            'created_by' => $admin->id,
            'status' => 'open',
        ]);
        $jobItem = JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $category->id,
            'created_by' => $admin->id,
            'claimed_by' => $fieldStaff->id,
            'claimed_at' => now(),
            'status' => JobRequestItem::STATUS_SUBMITTED,
            'title' => $category->name,
            'submitted_at' => now(),
        ]);
        $attempt = JobItemAttempt::create([
            'job_request_item_id' => $jobItem->id,
            'user_id' => $fieldStaff->id,
            'status' => JobItemAttempt::STATUS_SUBMITTED,
            'notes' => 'Ready for coordinator review.',
        ]);

        $this->actingAs($coordinator)->post("/coordinator/jobs/{$jobItem->id}/review", [
            'action' => 'approve',
            'coordinator_note' => 'Looks good.',
        ])->assertRedirect('/coordinator/jobs');

        $this->assertDatabaseHas('job_request_items', [
            'id' => $jobItem->id,
            'status' => JobRequestItem::STATUS_PENDING_ADMIN_REVIEW,
        ]);
        $this->assertDatabaseHas('job_item_attempts', [
            'id' => $attempt->id,
            'status' => JobItemAttempt::STATUS_COORDINATOR_APPROVED,
        ]);
    }

    public function test_field_coordinator_returns_submitted_report_for_correction(): void
    {
        $admin = $this->createAdmin();
        $coordinator = $this->createUser(['role' => 'field_coordinator']);
        $fieldStaff = $this->createUser(['role' => 'field_staff']);
        $client = Client::create(['client_name' => 'Correction Client']);
        $category = ServiceCategory::create(['name' => 'Inspection', 'is_active' => true]);
        $jobRequest = JobRequest::create([
            'client_id' => $client->id,
            'title' => 'Correction inspection',
            'created_by' => $admin->id,
            'status' => 'open',
        ]);
        $jobItem = JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $category->id,
            'created_by' => $admin->id,
            'claimed_by' => $fieldStaff->id,
            'claimed_at' => now(),
            'status' => JobRequestItem::STATUS_SUBMITTED,
            'title' => $category->name,
            'submitted_at' => now(),
        ]);
        $attempt = JobItemAttempt::create([
            'job_request_item_id' => $jobItem->id,
            'user_id' => $fieldStaff->id,
            'status' => JobItemAttempt::STATUS_SUBMITTED,
            'notes' => 'Needs review.',
        ]);

        $this->actingAs($coordinator)->post("/coordinator/jobs/{$jobItem->id}/review", [
            'action' => 'return',
            'coordinator_note' => 'Add clearer pictures.',
        ])->assertRedirect('/coordinator/jobs');

        $this->assertDatabaseHas('job_request_items', [
            'id' => $jobItem->id,
            'status' => JobRequestItem::STATUS_RETURNED,
            'claimed_by' => $fieldStaff->id,
            'submitted_at' => null,
        ]);
        $this->assertDatabaseHas('job_item_attempts', [
            'id' => $attempt->id,
            'status' => JobItemAttempt::STATUS_RETURNED,
        ]);
    }

    public function test_field_staff_can_tick_project_requirement_done_and_progress_updates(): void
    {
        $admin = $this->createAdmin();
        $fieldStaff = $this->createUser(['role' => 'field_staff']);
        $client = Client::create(['client_name' => 'Project Client']);
        $project = Project::create([
            'project_code' => 'PROJ-TEST-0001',
            'client_id' => $client->id,
            'title' => 'Approved CCTV Project',
            'status' => 'not_started',
            'created_by' => $admin->id,
        ]);
        $camera = $project->requirements()->create([
            'type' => 'material',
            'name' => 'CCTV Camera',
            'quantity' => '4',
            'sort_order' => 0,
        ]);
        $project->requirements()->create([
            'type' => 'task',
            'name' => 'Configure remote viewing',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($fieldStaff)->patch("/field/projects/{$project->id}/requirements/{$camera->id}", [
            'is_done' => '1',
        ]);

        $response->assertRedirect("/field/projects/{$project->id}");
        $this->assertDatabaseHas('project_requirements', [
            'id' => $camera->id,
            'is_done' => true,
            'completed_by' => $fieldStaff->id,
        ]);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'ongoing',
            'progress_percentage' => 50,
        ]);
    }

    public function test_project_waits_for_admin_completion_after_field_staff_finishes_checklist(): void
    {
        $admin = $this->createAdmin();
        $fieldStaff = $this->createUser(['role' => 'field_staff']);
        $client = Client::create(['client_name' => 'Review Client']);
        $project = Project::create([
            'project_code' => 'PROJ-TEST-0002',
            'client_id' => $client->id,
            'title' => 'Review Required Project',
            'status' => 'not_started',
            'created_by' => $admin->id,
        ]);
        $requirement = $project->requirements()->create([
            'type' => 'task',
            'name' => 'Complete installation',
            'sort_order' => 0,
        ]);

        $this->actingAs($fieldStaff)->patch("/field/projects/{$project->id}/requirements/{$requirement->id}", [
            'is_done' => '1',
        ])->assertRedirect("/field/projects/{$project->id}");

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'ready_for_review',
            'progress_percentage' => 100,
        ]);

        $this->actingAs($admin)
            ->post("/admin/projects/{$project->id}/complete")
            ->assertRedirect("/admin/projects/{$project->id}");

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'completed',
            'progress_percentage' => 100,
        ]);

        $this->actingAs($fieldStaff)->post("/field/projects/{$project->id}/start-update")
            ->assertSessionHasErrors('project');
    }
}
