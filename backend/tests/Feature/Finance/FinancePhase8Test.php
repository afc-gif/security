<?php

namespace Tests\Feature\Finance;

use App\Models\Client;
use App\Models\FinanceExpenseCategory;
use App\Models\FinancePermission;
use App\Models\FinancialExpense;
use App\Models\FinancialMaterialCost;
use App\Models\JobRequest;
use App\Models\JobRequestItem;
use App\Models\Order;
use App\Models\Project;
use App\Models\ProjectFinancial;
use App\Models\ProjectPayment;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancePhase8Test extends TestCase
{
    use RefreshDatabase;

    private User $financeUser;
    private User $normalUser;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([FinancePermission::VIEW, FinancePermission::CREATE, FinancePermission::EDIT, FinancePermission::APPROVE, FinancePermission::DELETE] as $perm) {
            FinancePermission::firstOrCreate(
                ['slug' => $perm],
                ['name' => ucfirst(str_replace('.', ' ', $perm))]
            );
        }

        $this->financeUser = $this->createUser(['role' => 'finance']);
        $ids = FinancePermission::query()->pluck('id')->all();
        $this->financeUser->financePermissions()->syncWithoutDetaching(
            collect($ids)->mapWithKeys(fn (int $id) => [$id => ['granted_at' => now()]])->all()
        );

        $this->normalUser = $this->createUser(['role' => 'user']);

        $this->client = Client::create([
            'client_code' => 'CLI-' . uniqid(),
            'client_name' => 'Acme Corp',
            'company_name' => 'Acme Security Ltd',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);
    }

    public function test_1_finance_user_can_access_overview(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.dashboard'));

        $response->assertOk();
        $response->assertSee('Financial Overview');
    }

    public function test_2_non_finance_user_cannot_access_overview(): void
    {
        $response = $this->actingAs($this->normalUser)
            ->get(route('finance.dashboard'));

        $response->assertForbidden();
    }

    public function test_3_project_value_total_is_calculated_from_existing_project_financial_records(): void
    {
        $project1 = Project::create([
            'project_code' => 'PRJ-001',
            'client_id' => $this->client->id,
            'title' => 'Project One',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectFinancial::create([
            'project_id' => $project1->id,
            'contract_value' => 3000000.00,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        $project2 = Project::create([
            'project_code' => 'PRJ-002',
            'client_id' => $this->client->id,
            'title' => 'Project Two',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectFinancial::create([
            'project_id' => $project2->id,
            'contract_value' => 2000000.00,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        // Total contract value = 5,000,000.00
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.dashboard'));

        $response->assertOk();
        $response->assertSee('5,000,000.00');
    }

    public function test_4_total_received_comes_from_existing_project_payments(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-003',
            'client_id' => $this->client->id,
            'title' => 'Project Payments Test',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectPayment::create([
            'project_id' => $project->id,
            'amount' => 1500000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'recorded_by' => $this->financeUser->id,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        ProjectPayment::create([
            'project_id' => $project->id,
            'amount' => 500000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'recorded_by' => $this->financeUser->id,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        // Total received = 2,000,000.00
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.dashboard'));

        $response->assertOk();
        $response->assertSee('2,000,000.00');
    }

    public function test_5_outstanding_amount_is_calculated_correctly(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-004',
            'client_id' => $this->client->id,
            'title' => 'Outstanding Test Project',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 4000000.00,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        ProjectPayment::create([
            'project_id' => $project->id,
            'amount' => 1500000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'recorded_by' => $this->financeUser->id,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        // Project value = 4,000,000. Received = 1,500,000. Outstanding = 2,500,000.
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.dashboard'));

        $response->assertOk();
        $response->assertSee('2,500,000.00');
    }

    public function test_6_approved_costs_include_approved_expenses_and_approved_material_costs(): void
    {
        $category = FinanceExpenseCategory::firstOrCreate(
            ['slug' => 'travel'],
            ['name' => 'Travel & Logistics', 'is_active' => true]
        );

        $project = Project::create([
            'project_code' => 'PRJ-005',
            'client_id' => $this->client->id,
            'title' => 'Costs Test Project',
            'created_by' => $this->financeUser->id,
        ]);

        FinancialExpense::create([
            'project_id' => $project->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Site Visit Travel',
            'amount' => 300000.00,
            'status' => FinancialExpense::STATUS_APPROVED,
            'submitted_by' => $this->financeUser->id,
        ]);

        // Pending expense should NOT be included in approved costs
        FinancialExpense::create([
            'project_id' => $project->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Pending Expense',
            'amount' => 900000.00,
            'status' => FinancialExpense::STATUS_PENDING,
            'submitted_by' => $this->financeUser->id,
        ]);

        FinancialMaterialCost::create([
            'project_id' => $project->id,
            'material_name' => 'IP Cameras',
            'quantity' => 5,
            'unit' => 'pcs',
            'unit_cost' => 40000.00,
            'total_cost' => 200000.00,
            'status' => FinancialMaterialCost::STATUS_APPROVED,
            'submitted_by' => $this->financeUser->id,
        ]);

        // Approved costs = 300,000 + 200,000 = 500,000.00
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.dashboard'));

        $response->assertOk();
        $response->assertSee('500,000.00');
    }

    public function test_7_estimated_profit_is_calculated_correctly(): void
    {
        $category = FinanceExpenseCategory::firstOrCreate(
            ['slug' => 'equipment'],
            ['name' => 'Equipment', 'is_active' => true]
        );

        $project = Project::create([
            'project_code' => 'PRJ-006',
            'client_id' => $this->client->id,
            'title' => 'Profit Test Project',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 5000000.00,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        FinancialExpense::create([
            'project_id' => $project->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Installation Racks',
            'amount' => 1000000.00,
            'status' => FinancialExpense::STATUS_APPROVED,
            'submitted_by' => $this->financeUser->id,
        ]);

        // Contract value = 5,000,000. Approved costs = 1,000,000. Estimated Profit = 4,000,000.
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.dashboard'));

        $response->assertOk();
        $response->assertSee('4,000,000.00');
    }

    public function test_8_only_the_intended_recent_3_jobs_appear(): void
    {
        $serviceCat = ServiceCategory::firstOrCreate(
            ['slug' => 'cctv'],
            ['name' => 'CCTV Installation']
        );

        $jobRequest = JobRequest::create([
            'job_number' => 'JOB-REQ-001',
            'client_id' => $this->client->id,
            'title' => 'Site Request',
            'created_by' => $this->financeUser->id,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            JobRequestItem::create([
                'job_request_id' => $jobRequest->id,
                'service_category_id' => $serviceCat->id,
                'title' => "Job Item #{$i}",
                'status' => 'pending_assignment',
                'created_by' => $this->financeUser->id,
            ]);
        }

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.dashboard'));

        $response->assertOk();
        $response->assertSee('Job Item #5');
        $response->assertSee('Job Item #4');
        $response->assertSee('Job Item #3');
        $response->assertDontSee('Job Item #1');
    }

    public function test_9_only_the_intended_recent_3_projects_appear(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Project::create([
                'project_code' => "PRJ-00{$i}",
                'client_id' => $this->client->id,
                'title' => "Alpha Project #{$i}",
                'created_by' => $this->financeUser->id,
            ]);
        }

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.dashboard'));

        $response->assertOk();
        $response->assertSee('Alpha Project #5');
        $response->assertSee('Alpha Project #4');
        $response->assertSee('Alpha Project #3');
        $response->assertDontSee('Alpha Project #1');
    }

    public function test_10_dashboard_includes_pos_order_revenue_in_total_in(): void
    {
        // POS revenue is now intentionally visible in the Finance dashboard
        // as part of the unified TOTAL IN / TOTAL OUT / NET view.
        Order::create([
            'user_id'        => $this->financeUser->id,
            'order_number'   => 'ORD-99999',
            'total_amount'   => 99999999.00,
            'status'         => 'completed',
            'payment_method' => 'card',
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.dashboard'));

        $response->assertOk();
        // POS Revenue card and Total IN summary card should appear
        $response->assertSee('POS Revenue');
        $response->assertSee('Money IN');
    }

    public function test_11_empty_state_renders_correctly(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.dashboard'));

        $response->assertOk();
        $response->assertSee('No jobs yet');
        $response->assertSee('No projects yet');
    }

    public function test_12_existing_jobs_and_projects_routes_still_work(): void
    {
        $jobsResponse = $this->actingAs($this->financeUser)
            ->get(route('finance.jobs.index'));

        $jobsResponse->assertOk();

        $projectsResponse = $this->actingAs($this->financeUser)
            ->get(route('finance.projects.index'));

        $projectsResponse->assertOk();
    }
}
