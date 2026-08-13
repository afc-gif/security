<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Inspection;
use App\Models\InspectionRevision;
use App\Models\JobChecklistItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InspectionReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $fieldStaff;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@artsci.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->fieldStaff = User::create([
            'name' => 'Field Technician',
            'email' => 'tech@artsci.test',
            'password' => bcrypt('password'),
            'role' => 'field_staff',
        ]);

        $this->client = Client::create([
            'client_name' => 'Test Client Corp',
            'email' => 'contact@testclient.test',
            'phone' => '1234567890',
            'address' => '123 Main St',
        ]);
    }

    /** @test */
    public function admin_can_open_inspection_review_without_errors(): void
    {
        $inspection = Inspection::create([
            'inspection_code' => 'INSP-20260813-0001',
            'client_id' => $this->client->id,
            'title' => 'Structural Safety Audit',
            'location' => 'Building A',
            'inspection_type' => 'Safety',
            'assigned_to' => $this->fieldStaff->id,
            'status' => 'completed',
            'review_status' => 'pending_review',
            'findings' => 'Initial foundation cracks observed.',
            'risks_identified' => 'Minor water leakage risk.',
            'recommendations' => 'Seal wall joints.',
            'submitted_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $inspection->checklistItems()->create([
            'title' => 'Check Exterior Wall Stability',
            'description' => 'Inspect for cracks > 2mm',
            'input_type' => 'textarea',
            'status' => 'done',
            'response' => 'Minor hairline cracks present',
            'notes' => 'Wall joint A-4',
            'completed_by' => $this->fieldStaff->id,
            'completed_at' => now(),
        ]);

        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->admin)
            ->get(route('admin.inspections.show', $inspection));

        $response->assertOk()
            ->assertSee('INSP-20260813-0001')
            ->assertSee('Test Client Corp')
            ->assertSee('Structural Safety Audit')
            ->assertSee('Initial foundation cracks observed.')
            ->assertSee('Check Exterior Wall Stability')
            ->assertSee('Minor hairline cracks present');
    }

    /** @test */
    public function admin_can_approve_an_inspection(): void
    {
        $inspection = Inspection::create([
            'inspection_code' => 'INSP-20260813-0002',
            'client_id' => $this->client->id,
            'title' => 'Roof Alignment Check',
            'location' => 'Building B',
            'assigned_to' => $this->fieldStaff->id,
            'status' => 'completed',
            'review_status' => 'pending_review',
            'findings' => 'Roof beams intact.',
            'submitted_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.inspections.review', $inspection), [
                'review_status' => 'approved',
                'review_notes' => 'All criteria met.',
            ]);

        $response->assertRedirect(route('admin.inspections.show', $inspection));

        $inspection->refresh();
        $this->assertEquals('completed', $inspection->status);
        $this->assertEquals('approved', $inspection->review_status);
        $this->assertEquals($this->admin->id, $inspection->reviewed_by);
        $this->assertNotNull($inspection->reviewed_at);
        $this->assertEquals('All criteria met.', $inspection->review_notes);

        $this->assertDatabaseHas('inspection_revisions', [
            'inspection_id' => $inspection->id,
            'user_id' => $this->admin->id,
            'action' => InspectionRevision::ACTION_APPROVED,
            'admin_notes' => 'All criteria met.',
        ]);
    }

    /** @test */
    public function returning_an_inspection_requires_a_reason(): void
    {
        $inspection = Inspection::create([
            'inspection_code' => 'INSP-20260813-0003',
            'client_id' => $this->client->id,
            'title' => 'HVAC Inspection',
            'location' => 'Building C',
            'assigned_to' => $this->fieldStaff->id,
            'status' => 'completed',
            'review_status' => 'pending_review',
            'submitted_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.inspections.review', $inspection), [
                'review_status' => 'returned',
                'return_reason' => '',
            ]);

        $response->assertSessionHasErrors(['return_reason']);

        $inspection->refresh();
        $this->assertEquals('pending_review', $inspection->review_status);
    }

    /** @test */
    public function admin_can_return_inspection_to_field_staff_with_reason(): void
    {
        $inspection = Inspection::create([
            'inspection_code' => 'INSP-20260813-0004',
            'client_id' => $this->client->id,
            'title' => 'Electrical Duct Check',
            'location' => 'Building D',
            'assigned_to' => $this->fieldStaff->id,
            'status' => 'completed',
            'review_status' => 'pending_review',
            'findings' => 'Incomplete wiring photo.',
            'submitted_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $inspection->checklistItems()->create([
            'title' => 'Voltage Check',
            'status' => 'done',
            'response' => '220V',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.inspections.review', $inspection), [
                'review_status' => 'returned',
                'return_reason' => 'Please provide clear close-up photos of circuit breaker panel 3.',
            ]);

        $response->assertRedirect(route('admin.inspections.show', $inspection));

        $inspection->refresh();
        $this->assertEquals('returned', $inspection->status);
        $this->assertEquals('returned', $inspection->review_status);
        $this->assertEquals('Please provide clear close-up photos of circuit breaker panel 3.', $inspection->return_reason);
        $this->assertEquals($this->admin->id, $inspection->returned_by);
        $this->assertNotNull($inspection->returned_at);

        $this->assertDatabaseHas('inspection_revisions', [
            'inspection_id' => $inspection->id,
            'user_id' => $this->admin->id,
            'action' => InspectionRevision::ACTION_RETURNED,
            'return_reason' => 'Please provide clear close-up photos of circuit breaker panel 3.',
        ]);
    }

    /** @test */
    public function field_staff_can_view_returned_inspection_and_resubmit_it(): void
    {
        $inspection = Inspection::create([
            'inspection_code' => 'INSP-20260813-0005',
            'client_id' => $this->client->id,
            'title' => 'Fire Hydrant Pressure Audit',
            'location' => 'Facility Yard',
            'assigned_to' => $this->fieldStaff->id,
            'status' => 'returned',
            'review_status' => 'returned',
            'findings' => 'Initial pressure reading 45 PSI.',
            'return_reason' => 'Please measure pressure at Node 2 as well.',
            'returned_by' => $this->admin->id,
            'returned_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $checklistItem = $inspection->checklistItems()->create([
            'title' => 'Node 2 Pressure Reading',
            'status' => 'pending',
        ]);

        // 1. Field Staff views returned inspection
        $viewResponse = $this->actingAs($this->fieldStaff)
            ->get(route('field.inspections.show', $inspection));

        $viewResponse->assertOk()
            ->assertSee('Returned for Additional Details')
            ->assertSee('Please measure pressure at Node 2 as well.');

        // 2. Field Staff updates findings & checklist and resubmits
        $submitResponse = $this->actingAs($this->fieldStaff)
            ->post(route('field.inspections.submit', $inspection), [
                'findings' => 'Pressure at Node 1: 45 PSI. Node 2: 52 PSI.',
                'risks_identified' => 'None',
                'recommendations' => 'Regular maintenance',
                'checklist' => [
                    $checklistItem->id => [
                        'status' => 'done',
                        'response' => '52 PSI',
                        'notes' => 'Measured with digital gauge B-12',
                    ],
                ],
            ]);

        $submitResponse->assertRedirect(route('field.inspections.show', $inspection));

        $inspection->refresh();
        $this->assertEquals('completed', $inspection->status);
        $this->assertEquals('pending_review', $inspection->review_status);
        $this->assertEquals('Pressure at Node 1: 45 PSI. Node 2: 52 PSI.', $inspection->findings);

        $checklistItem->refresh();
        $this->assertEquals('done', $checklistItem->status);
        $this->assertEquals('52 PSI', $checklistItem->response);

        // 3. Admin can now approve the resubmitted inspection
        $approveResponse = $this->actingAs($this->admin)
            ->post(route('admin.inspections.review', $inspection), [
                'review_status' => 'approved',
                'review_notes' => 'Updated pressure readings confirmed.',
            ]);

        $approveResponse->assertRedirect(route('admin.inspections.show', $inspection));

        $inspection->refresh();
        $this->assertEquals('completed', $inspection->status);
        $this->assertEquals('approved', $inspection->review_status);
    }
}
