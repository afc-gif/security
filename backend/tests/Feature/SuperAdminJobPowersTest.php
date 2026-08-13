<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\JobRequest;
use App\Models\JobRequestItem;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminJobPowersTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $executive;
    private User $admin;
    private User $manager;
    private User $finance;
    private User $pos;
    private User $fieldStaff;
    private User $fieldCoordinator;
    private Client $client;
    private ServiceCategory $serviceCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = $this->createUser(['role' => 'super_admin', 'status' => 'approved']);
        $this->executive = $this->createUser(['role' => 'executive', 'status' => 'approved']);
        $this->admin = $this->createUser(['role' => 'admin', 'status' => 'approved']);
        $this->manager = $this->createUser(['role' => 'manager', 'status' => 'approved']);
        $this->finance = $this->createUser(['role' => 'finance', 'status' => 'approved']);
        $this->pos = $this->createUser(['role' => 'pos', 'status' => 'approved']);
        $this->fieldStaff = $this->createUser(['role' => 'field_staff', 'status' => 'approved']);
        $this->fieldCoordinator = $this->createUser(['role' => 'field_coordinator', 'status' => 'approved']);

        $this->client = Client::create([
            'client_name' => 'SuperAdmin Test Client',
            'company_name' => 'SuperAdmin Corp',
            'status' => 'active',
        ]);
        $this->serviceCategory = ServiceCategory::create([
            'name' => 'General Security Services',
            'description' => 'Security Services Category',
        ]);
    }

    private function createJobItem(array $attributes = []): JobRequestItem
    {
        $jobRequest = JobRequest::create([
            'client_id' => $this->client->id,
            'title' => 'Test Job Request',
            'description' => 'Test Description',
            'created_by' => $this->superAdmin->id,
            'status' => 'open',
        ]);

        return JobRequestItem::create(array_merge([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $this->serviceCategory->id,
            'created_by' => $this->superAdmin->id,
            'claimed_by' => null,
            'claimed_at' => null,
            'status' => JobRequestItem::STATUS_PENDING_ASSIGNMENT,
            'title' => 'Test Job Item',
            'due_date' => now()->addDays(5),
        ], $attributes));
    }

    public function test_super_admin_can_assign_a_job(): void
    {
        $jobItem = $this->createJobItem();

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.job-items.assign', $jobItem), [
                'assigned_to' => $this->fieldStaff->id,
            ]);

        $response->assertRedirect();
        $this->assertSame($this->fieldStaff->id, $jobItem->fresh()->claimed_by);
        $this->assertSame(JobRequestItem::STATUS_CLAIMED, $jobItem->fresh()->status);
    }

    public function test_super_admin_can_convert_unassigned_eligible_job_into_project(): void
    {
        $jobItem = $this->createJobItem(['claimed_by' => null, 'status' => JobRequestItem::STATUS_PENDING_ASSIGNMENT]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.job-items.convert-to-project', $jobItem));

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'job_request_item_id' => $jobItem->id,
            'client_id' => $this->client->id,
            'created_by' => $this->superAdmin->id,
            'status' => 'not_started',
        ]);
    }

    public function test_super_admin_can_convert_job_without_field_completion(): void
    {
        $jobItem = $this->createJobItem(['status' => JobRequestItem::STATUS_CLAIMED, 'claimed_by' => $this->fieldStaff->id]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.job-items.convert-to-project', $jobItem));

        $response->assertRedirect();
        $project = Project::where('job_request_item_id', $jobItem->id)->first();
        $this->assertNotNull($project);
    }

    public function test_super_admin_can_convert_job_without_field_report(): void
    {
        $jobItem = $this->createJobItem(['status' => JobRequestItem::STATUS_OPEN]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.job-items.convert-to-project', $jobItem));

        $response->assertRedirect();
        $this->assertSame(1, Project::where('job_request_item_id', $jobItem->id)->count());
    }

    public function test_super_admin_conversion_creates_exactly_one_project(): void
    {
        $jobItem = $this->createJobItem(['status' => JobRequestItem::STATUS_PENDING_ASSIGNMENT]);

        $this->actingAs($this->superAdmin)
            ->post(route('admin.job-items.convert-to-project', $jobItem));

        $this->assertSame(1, Project::where('job_request_item_id', $jobItem->id)->count());
    }

    public function test_source_job_remains_traceable_after_conversion(): void
    {
        $jobItem = $this->createJobItem(['status' => JobRequestItem::STATUS_PENDING_ASSIGNMENT]);

        $this->actingAs($this->superAdmin)
            ->post(route('admin.job-items.convert-to-project', $jobItem));

        $project = Project::where('job_request_item_id', $jobItem->id)->firstOrFail();
        $this->assertSame($jobItem->id, $project->job_request_item_id);
        $this->assertSame($jobItem->job_request_id, $project->jobRequestItem->job_request_id);
        $this->assertNotNull($jobItem->fresh()->project);
    }

    public function test_created_project_retains_correct_client_and_info(): void
    {
        $jobItem = $this->createJobItem(['status' => JobRequestItem::STATUS_PENDING_ASSIGNMENT]);

        $this->actingAs($this->superAdmin)
            ->post(route('admin.job-items.convert-to-project', $jobItem));

        $project = Project::where('job_request_item_id', $jobItem->id)->firstOrFail();
        $this->assertSame($this->client->id, $project->client_id);
        $this->assertStringContainsString($jobItem->serviceCategory->name, $project->title);
        $this->assertSame('not_started', $project->status);
    }

    public function test_conversion_records_super_admin_audit_info(): void
    {
        $jobItem = $this->createJobItem(['status' => JobRequestItem::STATUS_PENDING_ASSIGNMENT]);

        $this->actingAs($this->superAdmin)
            ->post(route('admin.job-items.convert-to-project', $jobItem));

        $project = Project::where('job_request_item_id', $jobItem->id)->firstOrFail();
        $this->assertSame($this->superAdmin->id, $project->created_by);
    }

    public function test_source_job_cannot_be_converted_twice(): void
    {
        $jobItem = $this->createJobItem(['status' => JobRequestItem::STATUS_PENDING_ASSIGNMENT]);

        // First conversion
        $this->actingAs($this->superAdmin)
            ->post(route('admin.job-items.convert-to-project', $jobItem));

        $firstProjectCount = Project::where('job_request_item_id', $jobItem->id)->count();
        $this->assertSame(1, $firstProjectCount);

        // Second conversion attempt
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.job-items.convert-to-project', $jobItem));

        $response->assertRedirect();
        $this->assertSame(1, Project::where('job_request_item_id', $jobItem->id)->count());
    }

    public function test_unauthorized_roles_cannot_use_direct_conversion_bypass(): void
    {
        $unauthorizedRoles = [
            'executive' => $this->executive,
            'admin' => $this->admin,
            'manager' => $this->manager,
            'finance' => $this->finance,
            'pos' => $this->pos,
            'field_staff' => $this->fieldStaff,
            'field_coordinator' => $this->fieldCoordinator,
        ];

        foreach ($unauthorizedRoles as $roleName => $user) {
            $jobItem = $this->createJobItem(['status' => JobRequestItem::STATUS_PENDING_ASSIGNMENT]);

            $response = $this->actingAs($user)
                ->post(route('admin.job-items.convert-to-project', $jobItem));

            $this->assertTrue(in_array($response->getStatusCode(), [403, 302], true));
            $this->assertDatabaseMissing('projects', ['job_request_item_id' => $jobItem->id]);
        }
    }

    public function test_normal_approved_job_conversion_continues_to_work_for_admin(): void
    {
        $jobItem = $this->createJobItem(['status' => JobRequestItem::STATUS_APPROVED]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.job-items.convert-to-project', $jobItem));

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'job_request_item_id' => $jobItem->id,
            'client_id' => $this->client->id,
        ]);
    }
}
