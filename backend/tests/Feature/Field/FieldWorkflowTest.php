<?php

namespace Tests\Feature\Field;

use App\Models\Client;
use App\Models\JobItemAttempt;
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

        $response = $this->actingAs($fieldStaff)->post("/field/jobs/{$jobItem->id}/submit", [
            'notes' => 'Inspection done. Customer needs approved scope.',
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

        $this->actingAs($coordinator)->post("/coordinator/jobs/{$jobItem->id}/assign", [
            'assigned_to' => $fieldStaff->id,
        ])->assertRedirect('/coordinator/jobs');

        $this->assertDatabaseHas('job_request_items', [
            'id' => $jobItem->id,
            'claimed_by' => $fieldStaff->id,
            'status' => JobRequestItem::STATUS_CLAIMED,
        ]);
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
