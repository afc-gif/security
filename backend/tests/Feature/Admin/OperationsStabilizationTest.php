<?php

namespace Tests\Feature\Admin;

use App\Models\Client;
use App\Models\JobItemAttempt;
use App\Models\JobRequest;
use App\Models\JobRequestItem;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationsStabilizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $fieldStaff;
    private Client $client;
    private ServiceCategory $category;
    private JobRequest $jobRequest;
    private JobRequestItem $jobItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdmin();
        $this->fieldStaff = $this->createUser(['role' => 'field_staff']);

        $this->client = Client::create([
            'client_code' => 'CLI-TEST-' . uniqid(),
            'client_name' => 'Acme Corporation',
            'company_name' => 'Acme Inc',
            'email' => 'contact@acme.com',
            'phone' => '08012345678',
        ]);

        $this->category = ServiceCategory::create([
            'name' => 'CCTV Installation',
            'is_active' => true,
        ]);

        $this->jobRequest = JobRequest::create([
            'client_id' => $this->client->id,
            'created_by' => $this->admin->id,
            'title' => 'CCTV Setup Request',
            'description' => 'Install 4 cameras',
            'status' => 'open',
        ]);

        $this->jobItem = JobRequestItem::create([
            'job_request_id' => $this->jobRequest->id,
            'service_category_id' => $this->category->id,
            'created_by' => $this->admin->id,
            'title' => $this->category->name,
            'status' => JobRequestItem::STATUS_OPEN,
        ]);
    }

    public function test_admin_can_view_submitted_job_item_without_500(): void
    {
        // Set item to submitted status with attempt
        $this->jobItem->update([
            'claimed_by' => $this->fieldStaff->id,
            'claimed_at' => now(),
            'status' => JobRequestItem::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $attempt = JobItemAttempt::create([
            'job_request_item_id' => $this->jobItem->id,
            'user_id' => $this->fieldStaff->id,
            'status' => JobItemAttempt::STATUS_SUBMITTED,
            'notes' => 'Completed installation',
        ]);

        $attempt->requirements()->create([
            'type' => 'material',
            'name' => 'RG6 Cable',
            'quantity' => '100m',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.job-items.show', $this->jobItem));

        $response->assertOk();
        $response->assertSee('Job Item Review');
        $response->assertSee('Acme Corporation');
        $response->assertSee('Completed installation');
    }

    public function test_admin_can_reopen_overdue_job_item_without_500(): void
    {
        // Set item as overdue with past due_date
        $this->jobItem->update([
            'claimed_by' => $this->fieldStaff->id,
            'claimed_at' => now()->subDays(5),
            'due_date' => now()->subDays(2),
            'status' => JobRequestItem::STATUS_OVERDUE,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.job-items.reopen', $this->jobItem), [
                'admin_note' => 'Reopening for re-assignment due to delay',
            ]);

        $response->assertRedirect(route('admin.job-items.show', $this->jobItem));
        $response->assertSessionHas('success');

        $this->jobItem->refresh();
        $this->assertEquals(JobRequestItem::STATUS_REOPENED, $this->jobItem->status);
        $this->assertNull($this->jobItem->claimed_by);
        $this->assertNull($this->jobItem->claimed_at);
        $this->assertNull($this->jobItem->due_date);

        // Verify attempt actor user_id represents the authenticated admin
        $latestAttempt = JobItemAttempt::where('job_request_item_id', $this->jobItem->id)->latest('id')->first();
        $this->assertNotNull($latestAttempt);
        $this->assertEquals($this->admin->id, $latestAttempt->user_id);

        // Verify that loading show view after reopen does not revert it to overdue
        $viewResponse = $this->actingAs($this->admin)
            ->get(route('admin.job-items.show', $this->jobItem));

        $viewResponse->assertOk();
        $this->jobItem->refresh();
        $this->assertEquals(JobRequestItem::STATUS_REOPENED, $this->jobItem->status);
    }

    public function test_full_operations_workflow_from_reopen_to_project_conversion(): void
    {
        // 1. Mark overdue
        $this->jobItem->update([
            'due_date' => now()->subDay(),
            'status' => JobRequestItem::STATUS_OVERDUE,
        ]);

        // 2. Admin reopens item
        $this->actingAs($this->admin)
            ->post(route('admin.job-items.reopen', $this->jobItem), [
                'admin_note' => 'Reopened by admin',
            ])
            ->assertRedirect();

        $this->jobItem->refresh();
        $this->assertEquals(JobRequestItem::STATUS_REOPENED, $this->jobItem->status);

        // 3. Field staff claims reopened item
        $this->actingAs($this->fieldStaff)
            ->post(route('field.jobs.claim', $this->jobItem))
            ->assertRedirect();

        $this->jobItem->refresh();
        $this->assertEquals(JobRequestItem::STATUS_CLAIMED, $this->jobItem->status);
        $this->assertEquals($this->fieldStaff->id, $this->jobItem->claimed_by);

        // 4. Field staff submits report
        $this->actingAs($this->fieldStaff)
            ->post(route('field.jobs.submit', $this->jobItem), [
                'notes' => 'Re-submitted job successfully',
                'requirements' => [
                    [
                        'type' => 'material',
                        'name' => 'DVR Unit',
                        'quantity' => '1',
                        'notes' => 'Main DVR',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->jobItem->refresh();
        $this->assertEquals(JobRequestItem::STATUS_SUBMITTED, $this->jobItem->status);

        // 5. Admin opens submitted item view
        $this->actingAs($this->admin)
            ->get(route('admin.job-items.show', $this->jobItem))
            ->assertOk()
            ->assertSee('Re-submitted job successfully');

        // 6. Admin approves job item
        $this->actingAs($this->admin)
            ->post(route('admin.job-items.review', $this->jobItem), [
                'action' => 'approve',
                'admin_note' => 'Looks good',
                'requirements' => [
                    [
                        'include' => '1',
                        'type' => 'material',
                        'name' => 'DVR Unit',
                        'quantity' => '1',
                        'notes' => 'Main DVR',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->jobItem->refresh();
        $this->assertEquals(JobRequestItem::STATUS_APPROVED, $this->jobItem->status);

        // 7. Admin converts approved item to project
        $this->actingAs($this->admin)
            ->post(route('admin.job-items.convert-to-project', $this->jobItem))
            ->assertRedirect();

        $project = Project::where('job_request_item_id', $this->jobItem->id)->first();
        $this->assertNotNull($project);
        $this->assertEquals($this->client->id, $project->client_id);
    }

    public function test_unauthorized_users_are_blocked_from_admin_operations_actions(): void
    {
        $normalUser = $this->createUser(['role' => 'user']);

        // Unauthorized view
        $this->actingAs($normalUser)
            ->get(route('admin.job-items.show', $this->jobItem))
            ->assertStatus(403);

        // Unauthorized reopen
        $this->actingAs($normalUser)
            ->post(route('admin.job-items.reopen', $this->jobItem), [
                'admin_note' => 'Hacker note',
            ])
            ->assertStatus(403);

        // Field staff cannot access admin review or reopen
        $this->actingAs($this->fieldStaff)
            ->get(route('admin.job-items.show', $this->jobItem))
            ->assertStatus(403);

        $this->actingAs($this->fieldStaff)
            ->post(route('admin.job-items.reopen', $this->jobItem))
            ->assertStatus(403);
    }
}
