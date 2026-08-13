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
    private JobRequest $jobRequest;
    private JobRequestItem $jobItem;

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

        $category = ServiceCategory::create(['name' => 'CCTV Security', 'description' => 'CCTV Services']);

        $this->jobRequest = JobRequest::create([
            'client_id' => $this->client->id,
            'title' => 'CCTV Installation Job',
            'created_by' => $this->superAdmin->id,
            'status' => 'open',
        ]);

        $this->jobItem = JobRequestItem::create([
            'job_request_id' => $this->jobRequest->id,
            'service_category_id' => $category->id,
            'created_by' => $this->superAdmin->id,
            'status' => 'pending_assignment',
            'title' => 'CCTV Camera Item',
        ]);
    }

    public function test_finance_authorized_user_can_view_quotations_list(): void
    {
        Quotation::create([
            'quotation_number' => 'ART-QTN-2026-0001',
            'client_id' => $this->client->id,
            'job_request_id' => $this->jobRequest->id,
            'job_request_item_id' => $this->jobItem->id,
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

    public function test_new_quotation_requires_a_job_and_derives_client_server_side(): void
    {
        // 1. Trying to create without job fails validation
        $this->actingAs($this->financeUser)
            ->post(route('finance.quotations.store'), [
                'title' => 'No Job Quote',
                'quotation_date' => '2026-08-13',
                'items' => [['description' => 'Camera', 'quantity' => 1, 'unit_price' => 50000]],
            ])
            ->assertSessionHasErrors('job_request_item_id');

        // 2. Creating with Job derives Client server-side
        $response = $this->actingAs($this->financeUser)
            ->post(route('finance.quotations.store'), [
                'job_request_item_id' => $this->jobItem->id,
                'title' => 'Solar Power System Quotation',
                'quotation_date' => '2026-08-13',
                'discount_amount' => 50000,
                'tax_amount' => 10000,
                'items' => [
                    ['description' => '5kVA Inverter', 'quantity' => 2, 'unit_price' => 400000],
                    ['description' => '200Ah Lithium Battery', 'quantity' => 4, 'unit_price' => 350000],
                ],
            ]);

        $quotation = Quotation::where('title', 'Solar Power System Quotation')->firstOrFail();

        $response->assertRedirect(route('finance.quotations.show', $quotation));
        $this->assertSame($this->client->id, $quotation->client_id);
        $this->assertSame($this->jobRequest->id, $quotation->job_request_id);
        $this->assertSame($this->jobItem->id, $quotation->job_request_item_id);
        $this->assertSame('ART-QTN-2026-0001', $quotation->quotation_number);
    }

    public function test_browser_submitted_client_id_cannot_override_job_client(): void
    {
        $otherClient = Client::create(['client_name' => 'Other Client']);

        $this->actingAs($this->financeUser)
            ->post(route('finance.quotations.store'), [
                'job_request_item_id' => $this->jobItem->id,
                'client_id' => $otherClient->id, // Attempt to override
                'title' => 'Override Test Quote',
                'quotation_date' => '2026-08-13',
                'items' => [['description' => 'Test Item', 'quantity' => 1, 'unit_price' => 100000]],
            ]);

        $quotation = Quotation::where('title', 'Override Test Quote')->firstOrFail();

        // Must derive actual client from job, ignoring client_id override
        $this->assertSame($this->client->id, $quotation->client_id);
        $this->assertNotEquals($otherClient->id, $quotation->client_id);
    }

    public function test_existing_standalone_historical_quotations_remain_intact(): void
    {
        $historical = Quotation::create([
            'quotation_number' => 'ART-QTN-2025-9999',
            'client_id' => $this->client->id,
            'title' => 'Historical Standalone Quote',
            'quotation_date' => '2025-01-01',
            'status' => 'draft',
            'created_by' => $this->financeUser->id,
        ]);

        $this->assertNull($historical->job_request_item_id);

        $this->actingAs($this->financeUser)
            ->get(route('finance.quotations.show', $historical))
            ->assertStatus(200)
            ->assertSee('Historical Standalone Quote');
    }

    public function test_valid_until_is_optional_and_quote_without_valid_until_does_not_expire_automatically(): void
    {
        $this->actingAs($this->financeUser)
            ->post(route('finance.quotations.store'), [
                'job_request_item_id' => $this->jobItem->id,
                'title' => 'Indefinite Quote',
                'quotation_date' => '2026-08-13',
                'valid_until' => null, // Optional
                'items' => [['description' => 'Service', 'quantity' => 1, 'unit_price' => 150000]],
            ]);

        $quotation = Quotation::where('title', 'Indefinite Quote')->firstOrFail();

        $this->assertNull($quotation->valid_until);
        $this->assertSame('draft', $quotation->status);
    }

    public function test_authorized_finance_user_can_edit_quotation_and_change_job(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => 'ART-QTN-2026-0010',
            'client_id' => $this->client->id,
            'job_request_id' => $this->jobRequest->id,
            'job_request_item_id' => $this->jobItem->id,
            'title' => 'Initial Fence Quote',
            'quotation_date' => '2026-08-13',
            'status' => 'draft',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'created_by' => $this->financeUser->id,
        ]);

        $newClient = Client::create(['client_name' => 'New Customer']);
        $newJobReq = JobRequest::create([
            'client_id' => $newClient->id,
            'title' => 'New Job Request',
            'created_by' => $this->superAdmin->id,
            'status' => 'open',
        ]);
        $category = ServiceCategory::first();
        $newJobItem = JobRequestItem::create([
            'job_request_id' => $newJobReq->id,
            'service_category_id' => $category->id,
            'created_by' => $this->superAdmin->id,
            'status' => 'pending_assignment',
            'title' => 'New Job Item',
        ]);

        $this->actingAs($this->financeUser)
            ->put(route('finance.quotations.update', $quotation), [
                'job_request_item_id' => $newJobItem->id,
                'title' => 'Updated Job Quote',
                'quotation_date' => '2026-08-14',
                'items' => [['description' => 'New Item', 'quantity' => 2, 'unit_price' => 120000]],
            ])
            ->assertRedirect(route('finance.quotations.show', $quotation));

        $fresh = $quotation->fresh();
        $this->assertSame('Updated Job Quote', $fresh->title);
        $this->assertSame($newJobItem->id, $fresh->job_request_item_id);
        $this->assertSame($newClient->id, $fresh->client_id); // Client updated safely via Job
    }

    public function test_editing_accepted_quotation_with_payments_is_protected(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => 'ART-QTN-2026-0012',
            'client_id' => $this->client->id,
            'job_request_id' => $this->jobRequest->id,
            'job_request_item_id' => $this->jobItem->id,
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
            ->put(route('finance.quotations.update', $quotation), [
                'title' => 'Tampered Title',
                'quotation_date' => '2026-08-13',
                'items' => [['description' => 'Test', 'quantity' => 1, 'unit_price' => 10]],
            ])
            ->assertRedirect(route('finance.quotations.show', $quotation));

        $this->assertSame('Accepted Quote', $quotation->fresh()->title);
    }

    public function test_authorized_finance_user_can_download_quotation_pdf(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => 'ART-QTN-2026-0020',
            'client_id' => $this->client->id,
            'job_request_id' => $this->jobRequest->id,
            'job_request_item_id' => $this->jobItem->id,
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

    public function test_existing_pre_project_payments_and_job_conversion_remain_unbroken(): void
    {
        // 1. Create Quotation for Job Item
        $this->actingAs($this->financeUser)
            ->post(route('finance.quotations.store'), [
                'job_request_item_id' => $this->jobItem->id,
                'title' => 'Integrated Quote',
                'quotation_date' => '2026-08-13',
                'items' => [
                    ['description' => 'Fencing Cables', 'quantity' => 10, 'unit_price' => 50000],
                ],
            ]);

        $quotation = Quotation::where('title', 'Integrated Quote')->firstOrFail();

        // 2. Customer pays money against job before project conversion
        $this->actingAs($this->financeUser)
            ->post(route('finance.jobs.payments.store', $this->jobItem), [
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
            ->post(route('admin.job-items.convert-to-project', $this->jobItem));

        $project = Project::where('job_request_item_id', $this->jobItem->id)->firstOrFail();

        // 4. Verify payment attached to project and quotation preserved
        $this->assertSame($project->id, $payment->fresh()->project_id);
        $this->assertSame($this->jobItem->id, $quotation->fresh()->job_request_item_id);
    }
}
