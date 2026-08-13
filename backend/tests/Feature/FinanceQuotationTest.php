<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Inspection;
use App\Models\JobRequest;
use App\Models\JobRequestItem;
use App\Models\Project;
use App\Models\ProjectPayment;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceQuotationTest extends TestCase
{
    use RefreshDatabase;

    private User $financeUser;
    private User $superAdmin;
    private User $admin;
    private User $manager;
    private User $fieldStaff;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->financeUser = $this->createUser(['role' => 'finance', 'status' => 'approved']);
        $this->superAdmin = $this->createUser(['role' => 'super_admin', 'status' => 'approved']);
        $this->admin = $this->createUser(['role' => 'admin', 'status' => 'approved']);
        $this->manager = $this->createUser(['role' => 'manager', 'status' => 'approved']);
        $this->fieldStaff = $this->createUser(['role' => 'field_staff', 'status' => 'approved']);

        $this->client = Client::create([
            'client_name' => 'Test Client',
            'company_name' => 'Acme Global Security',
            'contact_person' => 'John Doe',
            'email' => 'john@acme.test',
            'phone' => '08012345678',
            'status' => 'active',
        ]);
    }

    public function test_finance_authorized_user_can_view_quotations_list(): void
    {
        Quotation::create([
            'quotation_number' => 'ART-QTN-2026-0001',
            'client_id' => $this->client->id,
            'title' => 'CCTV Project Quote',
            'quotation_date' => '2026-08-13',
            'status' => 'draft',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'created_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.quotations.index'));

        $response->assertStatus(200);
        $response->assertSee('ART-QTN-2026-0001');
        $response->assertSee('CCTV Project Quote');
    }

    public function test_unauthorized_user_receives_403_when_accessing_quotations(): void
    {
        $unauthorizedUsers = [$this->manager, $this->fieldStaff];

        foreach ($unauthorizedUsers as $user) {
            $response = $this->actingAs($user)->get(route('finance.quotations.index'));
            $response->assertStatus(403);

            $responseCreate = $this->actingAs($user)->get(route('finance.quotations.create'));
            $responseCreate->assertStatus(403);
        }
    }

    public function test_quotation_can_be_created_with_server_side_number_generation(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->post(route('finance.quotations.store'), [
                'client_id' => $this->client->id,
                'title' => 'Solar Power System Quotation',
                'quotation_date' => '2026-08-13',
                'valid_until' => '2026-09-13',
                'discount_amount' => 50000,
                'tax_amount' => 10000,
                'notes' => 'Customer discount applied.',
                'terms' => '70% advance.',
                'items' => [
                    [
                        'description' => '5kVA Inverter',
                        'quantity' => 2,
                        'unit_price' => 400000,
                    ],
                    [
                        'description' => '200Ah Lithium Battery',
                        'quantity' => 4,
                        'unit_price' => 350000,
                    ],
                ],
            ]);

        $quotation = Quotation::where('client_id', $this->client->id)->firstOrFail();

        $response->assertRedirect(route('finance.quotations.show', $quotation));

        $this->assertSame('ART-QTN-2026-0001', $quotation->quotation_number);
        $this->assertSame(2200000.00, (float) $quotation->subtotal);
        $this->assertSame(50000.00, (float) $quotation->discount_amount);
        $this->assertSame(10000.00, (float) $quotation->tax_amount);
        $this->assertSame(2160000.00, (float) $quotation->grand_total);
        $this->assertSame(2, $quotation->items()->count());
    }

    public function test_authorized_finance_user_can_edit_quotation(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => 'ART-QTN-2026-0010',
            'client_id' => $this->client->id,
            'title' => 'Initial Fence Quote',
            'quotation_date' => '2026-08-13',
            'status' => 'draft',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'created_by' => $this->financeUser->id,
        ]);

        $quotation->items()->create([
            'description' => 'Electric Fence Wire',
            'quantity' => 1,
            'unit_price' => 100000,
            'total_price' => 100000,
        ]);

        $this->actingAs($this->financeUser)
            ->get(route('finance.quotations.edit', $quotation))
            ->assertStatus(200);

        $this->actingAs($this->financeUser)
            ->put(route('finance.quotations.update', $quotation), [
                'client_id' => $this->client->id,
                'title' => 'Updated Fence Quote',
                'quotation_date' => '2026-08-14',
                'discount_amount' => 10000,
                'tax_amount' => 5000,
                'items' => [
                    [
                        'description' => 'Electric Fence Wire Heavy Duty',
                        'quantity' => 2,
                        'unit_price' => 120000,
                    ],
                ],
            ])
            ->assertRedirect(route('finance.quotations.show', $quotation));

        $fresh = $quotation->fresh();
        $this->assertSame('Updated Fence Quote', $fresh->title);
        $this->assertSame(240000.00, (float) $fresh->subtotal);
        $this->assertSame(235000.00, (float) $fresh->grand_total);
    }

    public function test_unauthorized_user_cannot_edit_quotation(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => 'ART-QTN-2026-0011',
            'client_id' => $this->client->id,
            'title' => 'Protected Quote',
            'quotation_date' => '2026-08-13',
            'status' => 'draft',
            'created_by' => $this->financeUser->id,
        ]);

        $this->actingAs($this->manager)
            ->get(route('finance.quotations.edit', $quotation))
            ->assertStatus(403);

        $this->actingAs($this->fieldStaff)
            ->put(route('finance.quotations.update', $quotation), [
                'client_id' => $this->client->id,
                'title' => 'Hacked Title',
                'quotation_date' => '2026-08-13',
                'items' => [['description' => 'Test', 'quantity' => 1, 'unit_price' => 10]],
            ])
            ->assertStatus(403);
    }

    public function test_editing_accepted_quotation_with_payments_is_protected(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => 'ART-QTN-2026-0012',
            'client_id' => $this->client->id,
            'title' => 'Accepted Quote',
            'quotation_date' => '2026-08-13',
            'status' => 'accepted',
            'subtotal' => 500000,
            'grand_total' => 500000,
            'created_by' => $this->financeUser->id,
        ]);

        ProjectPayment::create([
            'quotation_id' => $quotation->id,
            'client_id' => $this->client->id,
            'payment_type' => 'deposit',
            'amount' => 250000,
            'payment_date' => '2026-08-13',
            'payment_method' => 'bank_transfer',
            'recorded_by' => $this->financeUser->id,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        $this->actingAs($this->financeUser)
            ->get(route('finance.quotations.edit', $quotation))
            ->assertRedirect(route('finance.quotations.show', $quotation));

        $this->actingAs($this->financeUser)
            ->put(route('finance.quotations.update', $quotation), [
                'client_id' => $this->client->id,
                'title' => 'Tampered Title',
                'quotation_date' => '2026-08-13',
                'items' => [['description' => 'Test', 'quantity' => 1, 'unit_price' => 10]],
            ])
            ->assertRedirect(route('finance.quotations.show', $quotation));

        $this->assertSame('Accepted Quote', $quotation->fresh()->title);
    }

    public function test_authorized_finance_user_can_download_quotation(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => 'ART-QTN-2026-0020',
            'client_id' => $this->client->id,
            'title' => 'Download Document Test Quote',
            'quotation_date' => '2026-08-13',
            'status' => 'sent',
            'subtotal' => 300000,
            'grand_total' => 300000,
            'created_by' => $this->financeUser->id,
        ]);

        $quotation->items()->create([
            'description' => 'IP Camera 4K',
            'quantity' => 3,
            'unit_price' => 100000,
            'total_price' => 300000,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.quotations.download', $quotation));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename=Quotation_ART-QTN-2026-0020.pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_unauthorized_user_cannot_download_quotation(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => 'ART-QTN-2026-0021',
            'client_id' => $this->client->id,
            'title' => 'Secure Download Quote',
            'quotation_date' => '2026-08-13',
            'status' => 'sent',
            'created_by' => $this->financeUser->id,
        ]);

        $this->actingAs($this->manager)
            ->get(route('finance.quotations.download', $quotation))
            ->assertStatus(403);
    }

    public function test_editing_recalculates_totals_and_does_not_corrupt_payments(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => 'ART-QTN-2026-0030',
            'client_id' => $this->client->id,
            'title' => 'Draft Quote With Payment',
            'quotation_date' => '2026-08-13',
            'status' => 'draft',
            'subtotal' => 200000,
            'grand_total' => 200000,
            'created_by' => $this->financeUser->id,
        ]);

        $payment = ProjectPayment::create([
            'quotation_id' => $quotation->id,
            'client_id' => $this->client->id,
            'payment_type' => 'advance',
            'amount' => 100000,
            'payment_date' => '2026-08-13',
            'payment_method' => 'cash',
            'recorded_by' => $this->financeUser->id,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        $this->actingAs($this->financeUser)
            ->put(route('finance.quotations.update', $quotation), [
                'client_id' => $this->client->id,
                'title' => 'Revised Draft Quote',
                'quotation_date' => '2026-08-13',
                'discount_amount' => 20000,
                'items' => [
                    ['description' => 'Item A', 'quantity' => 2, 'unit_price' => 150000],
                ],
            ]);

        $freshQ = $quotation->fresh();
        $this->assertSame(300000.00, (float) $freshQ->subtotal);
        $this->assertSame(280000.00, (float) $freshQ->grand_total);
        $this->assertSame(100000.00, (float) $payment->fresh()->amount);
        $this->assertSame($quotation->id, $payment->fresh()->quotation_id);
    }

    public function test_existing_pre_project_payments_and_job_conversion_remain_unbroken(): void
    {
        $category = ServiceCategory::create(['name' => 'Fence Security', 'description' => 'Electric Fence']);
        $jobRequest = JobRequest::create([
            'client_id' => $this->client->id,
            'title' => 'Integrated Job',
            'created_by' => $this->superAdmin->id,
            'status' => 'open',
        ]);
        $jobItem = JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $category->id,
            'created_by' => $this->superAdmin->id,
            'status' => 'pending_assignment',
            'title' => 'Integrated Item',
        ]);

        // 1. Create Quotation for Job Item
        $this->actingAs($this->financeUser)
            ->post(route('finance.quotations.store'), [
                'client_id' => $this->client->id,
                'title' => 'Integrated Quote',
                'job_request_item_id' => $jobItem->id,
                'quotation_date' => '2026-08-13',
                'items' => [
                    ['description' => 'Fencing Cables', 'quantity' => 10, 'unit_price' => 50000],
                ],
            ]);

        $quotation = Quotation::where('title', 'Integrated Quote')->firstOrFail();

        // 2. Customer pays money against job before project conversion
        $this->actingAs($this->financeUser)
            ->post(route('finance.jobs.payments.store', $jobItem), [
                'amount' => 200000,
                'payment_date' => '2026-08-13',
                'payment_method' => 'bank_transfer',
                'payment_type' => 'deposit',
                'reference' => 'PAY-INTEG-001',
            ]);

        $payment = ProjectPayment::where('reference', 'PAY-INTEG-001')->firstOrFail();
        $this->assertNull($payment->project_id);

        // 3. Super Admin converts job directly to project
        $this->actingAs($this->superAdmin)
            ->post(route('admin.job-items.convert-to-project', $jobItem));

        $project = Project::where('job_request_item_id', $jobItem->id)->firstOrFail();

        // 4. Verify payment attached to project and quotation preserved
        $this->assertSame($project->id, $payment->fresh()->project_id);
        $this->assertSame($jobItem->id, $quotation->fresh()->job_request_item_id);
    }
}
