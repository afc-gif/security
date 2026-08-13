<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\FinanceExpenseCategory;
use App\Models\FinancialExpense;
use App\Models\Inspection;
use App\Models\JobRequest;
use App\Models\JobRequestItem;
use App\Models\Project;
use App\Models\ProjectPayment;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FinancePreProjectPaymentTest extends TestCase
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
            'client_name' => 'PreProject Test Client',
            'company_name' => 'Acme Test Corp',
            'status' => 'active',
        ]);
    }

    private function createServiceCategory(): ServiceCategory
    {
        return ServiceCategory::create([
            'name' => 'Test Service ' . Str::random(5),
            'description' => 'Test category description',
        ]);
    }

    public function test_finance_user_can_record_money_received_against_inspection(): void
    {
        $inspection = Inspection::create([
            'inspection_code' => 'INS-TEST-1',
            'client_id' => $this->client->id,
            'title' => 'Pre-payment Inspection',
            'location' => 'Lagos',
            'inspection_type' => 'Surveillance',
            'status' => 'pending',
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->post(route('finance.inspections.payments.store', $inspection), [
                'amount' => 500000,
                'payment_date' => '2026-08-13',
                'payment_method' => 'bank_transfer',
                'payment_type' => 'deposit',
                'reference' => 'TXN-INS-001',
                'notes' => 'Inspection deposit',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_payments', [
            'inspection_id' => $inspection->id,
            'client_id' => $this->client->id,
            'amount' => 500000.00,
            'payment_method' => 'bank_transfer',
            'payment_type' => 'deposit',
            'reference' => 'TXN-INS-001',
        ]);
    }

    public function test_finance_user_can_record_money_received_against_job_request(): void
    {
        $category = $this->createServiceCategory();
        $jobRequest = JobRequest::create([
            'client_id' => $this->client->id,
            'title' => 'Solar Job',
            'created_by' => $this->superAdmin->id,
            'status' => 'open',
        ]);
        $jobItem = JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $category->id,
            'created_by' => $this->superAdmin->id,
            'status' => 'pending_assignment',
            'title' => 'Solar Installation',
        ]);

        $response = $this->actingAs($this->financeUser)
            ->post(route('finance.jobs.payments.store', $jobItem), [
                'amount' => 1000000,
                'payment_date' => '2026-08-13',
                'payment_method' => 'bank_transfer',
                'payment_type' => 'part_payment',
                'reference' => 'TXN-JOB-001',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_payments', [
            'job_request_item_id' => $jobItem->id,
            'job_request_id' => $jobRequest->id,
            'client_id' => $this->client->id,
            'amount' => 1000000.00,
        ]);
    }

    public function test_money_received_can_be_recorded_before_project_exists(): void
    {
        $category = $this->createServiceCategory();
        $jobRequest = JobRequest::create([
            'client_id' => $this->client->id,
            'title' => 'CCTV Job',
            'created_by' => $this->superAdmin->id,
            'status' => 'open',
        ]);
        $jobItem = JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $category->id,
            'created_by' => $this->superAdmin->id,
            'status' => 'pending_assignment',
            'title' => 'CCTV Setup',
        ]);

        $this->assertNull($jobItem->project);

        $this->actingAs($this->financeUser)
            ->post(route('finance.jobs.payments.store', $jobItem), [
                'amount' => 750000,
                'payment_date' => '2026-08-13',
                'payment_method' => 'cash',
                'payment_type' => 'deposit',
            ]);

        $payment = ProjectPayment::where('job_request_item_id', $jobItem->id)->firstOrFail();
        $this->assertNull($payment->project_id);
        $this->assertSame(750000.00, (float) $payment->amount);
    }

    public function test_payment_amount_method_date_reference_and_client_stored_correctly(): void
    {
        $inspection = Inspection::create([
            'inspection_code' => 'INS-TEST-2',
            'client_id' => $this->client->id,
            'title' => 'Security Audit',
            'location' => 'Abuja',
            'inspection_type' => 'Access Control',
            'status' => 'pending',
            'created_by' => $this->superAdmin->id,
        ]);

        $this->actingAs($this->financeUser)
            ->post(route('finance.inspections.payments.store', $inspection), [
                'amount' => 350000.50,
                'payment_date' => '2026-08-12',
                'payment_method' => 'pos',
                'payment_type' => 'advance',
                'reference' => 'POS-REF-9921',
                'notes' => 'Advance POS payment',
            ]);

        $payment = ProjectPayment::where('inspection_id', $inspection->id)->firstOrFail();
        $this->assertSame(350000.50, (float) $payment->amount);
        $this->assertSame('pos', $payment->payment_method);
        $this->assertSame('2026-08-12', $payment->payment_date->toDateString());
        $this->assertSame('POS-REF-9921', $payment->reference);
        $this->assertSame($this->client->id, $payment->client_id);
    }

    public function test_total_received_calculated_correctly_with_multiple_payments(): void
    {
        $category = $this->createServiceCategory();
        $jobRequest = JobRequest::create([
            'client_id' => $this->client->id,
            'title' => 'Multi-Payment Job',
            'created_by' => $this->superAdmin->id,
            'status' => 'open',
        ]);
        $jobItem = JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $category->id,
            'created_by' => $this->superAdmin->id,
            'status' => 'pending_assignment',
            'title' => 'Item 1',
        ]);

        $this->actingAs($this->financeUser)
            ->post(route('finance.jobs.payments.store', $jobItem), [
                'amount' => 400000,
                'payment_date' => '2026-08-10',
                'payment_method' => 'bank_transfer',
            ]);

        $this->actingAs($this->financeUser)
            ->post(route('finance.jobs.payments.store', $jobItem), [
                'amount' => 600000,
                'payment_date' => '2026-08-12',
                'payment_method' => 'bank_transfer',
            ]);

        $this->assertSame(1000000.00, (float) ProjectPayment::where('job_request_item_id', $jobItem->id)->sum('amount'));
    }

    public function test_inspection_can_have_expenses_and_money_received_simultaneously(): void
    {
        $inspection = Inspection::create([
            'inspection_code' => 'INS-TEST-3',
            'client_id' => $this->client->id,
            'title' => 'Fencing Audit',
            'location' => 'Port Harcourt',
            'inspection_type' => 'Perimeter',
            'status' => 'pending',
            'created_by' => $this->superAdmin->id,
        ]);

        $expenseCategory = FinanceExpenseCategory::firstOrCreate(
            ['slug' => 'travel'],
            ['name' => 'Travel & Logistics']
        );

        // Add expense
        FinancialExpense::create([
            'inspection_id' => $inspection->id,
            'finance_expense_category_id' => $expenseCategory->id,
            'amount' => 45000,
            'description' => 'Travel fuel',
            'status' => 'approved',
            'submitted_by' => $this->financeUser->id,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        // Add money received
        $this->actingAs($this->financeUser)
            ->post(route('finance.inspections.payments.store', $inspection), [
                'amount' => 300000,
                'payment_date' => '2026-08-13',
                'payment_method' => 'bank_transfer',
            ]);

        $this->assertSame(45000.00, (float) FinancialExpense::where('inspection_id', $inspection->id)->sum('amount'));
        $this->assertSame(300000.00, (float) ProjectPayment::where('inspection_id', $inspection->id)->sum('amount'));
    }

    public function test_super_admin_can_access_and_record_financial_payments(): void
    {
        $inspection = Inspection::create([
            'inspection_code' => 'INS-TEST-4',
            'client_id' => $this->client->id,
            'title' => 'Super Admin Test',
            'location' => 'Lagos',
            'inspection_type' => 'Surveillance',
            'status' => 'pending',
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('finance.inspections.payments.store', $inspection), [
                'amount' => 150000,
                'payment_date' => '2026-08-13',
                'payment_method' => 'cash',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_payments', [
            'inspection_id' => $inspection->id,
            'amount' => 150000.00,
        ]);
    }

    public function test_unauthorized_users_cannot_create_or_view_payments(): void
    {
        $category = $this->createServiceCategory();
        $jobRequest = JobRequest::create([
            'client_id' => $this->client->id,
            'title' => 'Restricted Job',
            'created_by' => $this->superAdmin->id,
            'status' => 'open',
        ]);
        $jobItem = JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $category->id,
            'created_by' => $this->superAdmin->id,
            'status' => 'pending_assignment',
            'title' => 'Restricted Item',
        ]);

        $unauthorizedUsers = [$this->manager, $this->fieldStaff];

        foreach ($unauthorizedUsers as $user) {
            $response = $this->actingAs($user)
                ->post(route('finance.jobs.payments.store', $jobItem), [
                    'amount' => 500000,
                    'payment_date' => '2026-08-13',
                    'payment_method' => 'bank_transfer',
                ]);

            $response->assertStatus(403);

            $responseView = $this->actingAs($user)
                ->get(route('finance.jobs.show', $jobItem));

            $responseView->assertStatus(403);
        }
    }

    public function test_when_job_with_payment_is_converted_to_project_payment_remains_traceable_and_not_duplicated(): void
    {
        $category = $this->createServiceCategory();
        $jobRequest = JobRequest::create([
            'client_id' => $this->client->id,
            'title' => 'Convertible Job',
            'created_by' => $this->superAdmin->id,
            'status' => 'open',
        ]);
        $jobItem = JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $category->id,
            'created_by' => $this->superAdmin->id,
            'status' => 'pending_assignment',
            'title' => 'Convertible Item',
        ]);

        // Record pre-project payment
        $this->actingAs($this->financeUser)
            ->post(route('finance.jobs.payments.store', $jobItem), [
                'amount' => 1200000,
                'payment_date' => '2026-08-11',
                'payment_method' => 'bank_transfer',
                'reference' => 'PAY-PRE-PROJ-01',
            ]);

        $paymentBefore = ProjectPayment::where('reference', 'PAY-PRE-PROJ-01')->firstOrFail();
        $this->assertNull($paymentBefore->project_id);

        // Super Admin converts to project
        $this->actingAs($this->superAdmin)
            ->post(route('admin.job-items.convert-to-project', $jobItem));

        $project = Project::where('job_request_item_id', $jobItem->id)->firstOrFail();

        // Check payment is attached to project and NOT duplicated
        $this->assertSame(1, ProjectPayment::where('reference', 'PAY-PRE-PROJ-01')->count());
        $paymentAfter = ProjectPayment::where('reference', 'PAY-PRE-PROJ-01')->firstOrFail();
        $this->assertSame($project->id, $paymentAfter->project_id);
        $this->assertSame($jobItem->id, $paymentAfter->job_request_item_id);
        $this->assertSame(1200000.00, (float) $paymentAfter->amount);
    }
}
