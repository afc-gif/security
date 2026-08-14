<?php

namespace Tests\Feature\Admin;

use App\Models\CategoryChecklistTemplate;
use App\Models\Client;
use App\Models\JobChecklistItem;
use App\Models\JobItemAttempt;
use App\Models\JobItemMedia;
use App\Models\JobItemRequirement;
use App\Models\JobRequest;
use App\Models\JobRequestItem;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\CreatesTestModels;
use Tests\TestCase;

/**
 * Regression tests for the HTTP 500 on the admin job-item show page.
 *
 * The OperationsStabilizationTest suite passes but the real app returns 500.
 * These tests cover ADDITIONAL real-world data shapes:
 *
 * 1. Job submitted directly (STATUS_SUBMITTED, no coordinator) with checklist data
 * 2. Job with coordinator-approved attempt (STATUS_PENDING_ADMIN_REVIEW)
 * 3. Job where the claimer user has been soft-deleted / nulled (legacy data)
 * 4. Job with checklist items that have null added_by / completed_by (optional FK)
 * 5. Job with media attached to checklist items (checklist photo path)
 * 6. Job with NO attempt at all (open/claimed state)
 * 7. Job with a STATUS_SUBMITTED item — review form must appear for admin
 * 8. Admin review of STATUS_SUBMITTED item works (controller accepts it)
 */
class JobItemShow500RegressionTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    private User $admin;
    private User $fieldStaff;
    private Client $client;
    private ServiceCategory $category;
    private JobRequest $jobRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdmin();
        $this->fieldStaff = $this->createUser(['role' => 'field_staff']);

        $this->client = Client::create([
            'client_code'  => 'CLI-REG-' . uniqid(),
            'client_name'  => 'Regression Corp',
            'company_name' => 'Regression Inc',
            'email'        => 'reg@example.com',
            'phone'        => '08011112222',
        ]);

        $this->category = ServiceCategory::create([
            'name'      => 'Electrical',
            'is_active' => true,
        ]);

        $this->jobRequest = JobRequest::create([
            'client_id'  => $this->client->id,
            'created_by' => $this->admin->id,
            'title'      => 'Regression Job Request',
            'status'     => 'open',
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeJobItem(array $attrs = []): JobRequestItem
    {
        return JobRequestItem::create(array_merge([
            'job_request_id'      => $this->jobRequest->id,
            'service_category_id' => $this->category->id,
            'created_by'          => $this->admin->id,
            'title'               => 'Electrical Item',
            'status'              => JobRequestItem::STATUS_OPEN,
        ], $attrs));
    }

    private function makeAttempt(JobRequestItem $jobItem, array $attrs = []): JobItemAttempt
    {
        return JobItemAttempt::create(array_merge([
            'job_request_item_id' => $jobItem->id,
            'user_id'             => $this->fieldStaff->id,
            'status'              => JobItemAttempt::STATUS_SUBMITTED,
            'notes'               => 'Test submission notes',
        ], $attrs));
    }

    private function makeChecklistItem(JobRequestItem $jobItem, array $attrs = []): JobChecklistItem
    {
        return JobChecklistItem::create(array_merge([
            'job_request_item_id' => $jobItem->id,
            'title'               => 'Check wiring',
            'status'              => JobChecklistItem::STATUS_PENDING,
            'is_required'         => true,
            'is_custom'           => false,
            'sort_order'          => 0,
        ], $attrs));
    }

    private function showUrl(JobRequestItem $jobItem): string
    {
        return route('admin.job-items.show', $jobItem);
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /**
     * SCENARIO 1 — Direct field-staff submission (no coordinator).
     * Status is STATUS_SUBMITTED. This is the most common real-world path.
     * The review form MUST appear for admin on STATUS_SUBMITTED items.
     */
    public function test_show_does_not_500_for_directly_submitted_job_with_checklist(): void
    {
        $jobItem = $this->makeJobItem([
            'claimed_by'   => $this->fieldStaff->id,
            'claimed_at'   => now(),
            'status'       => JobRequestItem::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $attempt = $this->makeAttempt($jobItem);
        $attempt->requirements()->create([
            'type'       => 'material',
            'name'       => 'Wire 2.5mm',
            'quantity'   => '50m',
            'sort_order' => 0,
        ]);

        // Checklist item — completed by field staff, null added_by (template-generated)
        $this->makeChecklistItem($jobItem, [
            'status'       => JobChecklistItem::STATUS_DONE,
            'response'     => 'Wiring complete',
            'completed_by' => $this->fieldStaff->id,
            'added_by'     => null,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get($this->showUrl($jobItem));

        $response->assertOk();
        $response->assertSee('Job Item Review');
        $response->assertSee('Regression Corp');
        $response->assertSee('Test submission notes');
    }

    /**
     * SCENARIO 2 — Admin review form must be visible for STATUS_SUBMITTED jobs.
     * The Blade on line 257 only showed the form for STATUS_PENDING_ADMIN_REVIEW.
     * That means submitted-but-no-coordinator jobs couldn't be reviewed through UI.
     */
    public function test_review_form_is_visible_for_status_submitted_jobs(): void
    {
        $jobItem = $this->makeJobItem([
            'claimed_by'   => $this->fieldStaff->id,
            'claimed_at'   => now(),
            'status'       => JobRequestItem::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $attempt = $this->makeAttempt($jobItem);
        $attempt->requirements()->create([
            'type'       => 'material',
            'name'       => 'Test Material',
            'quantity'   => '1',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->admin)
            ->get($this->showUrl($jobItem));

        $response->assertOk();
        // The review form (Approve/Return/Reject buttons) must be present
        // for STATUS_SUBMITTED items — not just STATUS_PENDING_ADMIN_REVIEW
        $response->assertSee('Admin Final Review');
        $response->assertSee('Approve');
        $response->assertSee('Return');
        $response->assertSee('Reject');
    }

    /**
     * SCENARIO 3 — Coordinator-approved path.
     * Status is STATUS_PENDING_ADMIN_REVIEW, attempt is STATUS_COORDINATOR_APPROVED.
     */
    public function test_show_does_not_500_for_coordinator_approved_job(): void
    {
        $coordinator = $this->createUser(['role' => 'field_coordinator']);

        $jobItem = $this->makeJobItem([
            'claimed_by'   => $coordinator->id,
            'claimed_at'   => now(),
            'status'       => JobRequestItem::STATUS_PENDING_ADMIN_REVIEW,
            'submitted_at' => now(),
        ]);

        $attempt = $this->makeAttempt($jobItem, [
            'user_id' => $this->fieldStaff->id,
            'status'  => JobItemAttempt::STATUS_COORDINATOR_APPROVED,
            'notes'   => "Field staff notes\n\nCoordinator note: Looks fine",
        ]);
        $attempt->requirements()->create([
            'type'       => 'material',
            'name'       => 'Panel Box',
            'quantity'   => '1',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->admin)
            ->get($this->showUrl($jobItem));

        $response->assertOk();
        $response->assertSee('Admin Final Review');
    }

    /**
     * SCENARIO 4 — Legacy: claimer was deleted (claimed_by references a non-existent user).
     * The `claimer` relationship returns null. View must handle this gracefully.
     */
    public function test_show_does_not_500_when_claimer_user_was_deleted(): void
    {
        // Create a temp user, create item with their ID, then hard-delete the user
        $tempStaff = $this->createUser(['role' => 'field_staff']);
        $staffId   = $tempStaff->id;

        $jobItem = $this->makeJobItem([
            'claimed_by'   => $staffId,
            'claimed_at'   => now(),
            'status'       => JobRequestItem::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $attempt = $this->makeAttempt($jobItem, ['user_id' => $this->fieldStaff->id]);
        $attempt->requirements()->create([
            'type'       => 'material',
            'name'       => 'Cable',
            'quantity'   => '10m',
            'sort_order' => 0,
        ]);

        // Force-null the claimed_by to simulate a legacy record where user was removed
        DB::table('job_request_items')
            ->where('id', $jobItem->id)
            ->update(['claimed_by' => null]);

        $response = $this->actingAs($this->admin)
            ->get($this->showUrl($jobItem));

        $response->assertOk();
        // Claimed by should show a fallback, not crash
        $response->assertSee('Job Item Review');
    }

    /**
     * SCENARIO 5 — Checklist items with null added_by AND null completed_by.
     * These are FK columns that are nullable — real data may have nulls.
     */
    public function test_show_does_not_500_with_null_checklist_user_fks(): void
    {
        $jobItem = $this->makeJobItem([
            'claimed_by'   => $this->fieldStaff->id,
            'claimed_at'   => now(),
            'status'       => JobRequestItem::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $this->makeAttempt($jobItem);

        // Checklist item with BOTH nullable user FKs as null (common for template-seeded items)
        $this->makeChecklistItem($jobItem, [
            'added_by'     => null,
            'completed_by' => null,
            'status'       => JobChecklistItem::STATUS_DONE,
            'response'     => 'Done',
        ]);

        $response = $this->actingAs($this->admin)
            ->get($this->showUrl($jobItem));

        $response->assertOk();
        $response->assertSee('Checklist Report');
    }

    /**
     * SCENARIO 6 — Job with NO attempt (open / claimed state).
     * latestAttempt is null; view must gracefully show "No submissions yet."
     */
    public function test_show_does_not_500_for_unclaimed_job_with_no_attempt(): void
    {
        $jobItem = $this->makeJobItem([
            'status' => JobRequestItem::STATUS_OPEN,
        ]);

        $response = $this->actingAs($this->admin)
            ->get($this->showUrl($jobItem));

        $response->assertOk();
        $response->assertSee('No submissions yet');
    }

    /**
     * SCENARIO 7 — Job has checklist items with media attached.
     * media.uploader is eager-loaded; must not crash if uploader is null.
     */
    public function test_show_does_not_500_with_checklist_media(): void
    {
        $jobItem = $this->makeJobItem([
            'claimed_by'   => $this->fieldStaff->id,
            'claimed_at'   => now(),
            'status'       => JobRequestItem::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $attempt = $this->makeAttempt($jobItem);

        $checklistItem = $this->makeChecklistItem($jobItem, [
            'completed_by' => $this->fieldStaff->id,
            'status'       => JobChecklistItem::STATUS_DONE,
        ]);

        // Attach media to both the attempt (general) and the checklist item
        JobItemMedia::create([
            'job_item_attempt_id'  => $attempt->id,
            'job_checklist_item_id'=> $checklistItem->id,
            'file_name'            => 'photo.jpg',
            'file_path'            => 'job-media/photo.jpg',
            'file_type'            => 'image/jpeg',
            'file_size'            => 204800,
            'uploaded_by'          => $this->fieldStaff->id,
        ]);

        // General media (no checklist item)
        JobItemMedia::create([
            'job_item_attempt_id'  => $attempt->id,
            'job_checklist_item_id'=> null,
            'file_name'            => 'general.jpg',
            'file_path'            => 'job-media/general.jpg',
            'file_type'            => 'image/jpeg',
            'file_size'            => 102400,
            'uploaded_by'          => $this->fieldStaff->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get($this->showUrl($jobItem));

        $response->assertOk();
    }

    /**
     * SCENARIO 8 — Admin can POST review for STATUS_SUBMITTED item.
     * This verifies the controller allows STATUS_SUBMITTED (not just STATUS_PENDING_ADMIN_REVIEW).
     */
    public function test_admin_can_approve_directly_submitted_job_item(): void
    {
        $jobItem = $this->makeJobItem([
            'claimed_by'   => $this->fieldStaff->id,
            'claimed_at'   => now(),
            'status'       => JobRequestItem::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $attempt = $this->makeAttempt($jobItem);
        $attempt->requirements()->create([
            'type'       => 'material',
            'name'       => 'DVR Unit',
            'quantity'   => '1',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.job-items.review', $jobItem), [
                'action'       => 'approve',
                'admin_note'   => '',
                'requirements' => [
                    ['include' => '1', 'type' => 'material', 'name' => 'DVR Unit', 'quantity' => '1', 'notes' => ''],
                ],
            ]);

        $response->assertRedirect();
        $jobItem->refresh();
        $this->assertEquals(JobRequestItem::STATUS_APPROVED, $jobItem->status);
    }

    /**
     * SCENARIO 9 — Template-seeded checklist items (via ensureChecklistFromCategory).
     * Ensures the checklist seeding doesn't cause duplicate or broken state on show.
     */
    public function test_show_does_not_500_with_template_seeded_checklist(): void
    {
        $template = CategoryChecklistTemplate::create([
            'service_category_id' => $this->category->id,
            'title'               => 'Inspect earthing',
            'description'         => 'Check all earth connections',
            'input_type'          => 'textarea',
            'is_required'         => true,
            'is_active'           => true,
            'sort_order'          => 0,
        ]);

        $jobItem = $this->makeJobItem([
            'claimed_by'   => $this->fieldStaff->id,
            'claimed_at'   => now(),
            'status'       => JobRequestItem::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        // Simulate ensureChecklistFromCategory having run during field submission
        $jobItem->ensureChecklistFromCategory();

        $attempt = $this->makeAttempt($jobItem);

        $response = $this->actingAs($this->admin)
            ->get($this->showUrl($jobItem));

        $response->assertOk();
        $response->assertSee('Checklist Report');
        $response->assertSee('Inspect earthing');
    }
}
