<?php

namespace Tests\Feature\Finance;

use App\Models\Client;
use App\Models\FinanceExpenseCategory;
use App\Models\FinancePermission;
use App\Models\FinancialExpense;
use App\Models\FinancialMaterialCost;
use App\Models\Order;
use App\Models\Project;
use App\Models\ProjectFinancial;
use App\Models\ProjectPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancePhase9Test extends TestCase
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
            'client_name' => 'Omega Security Client',
            'company_name' => 'Omega Global',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);
    }

    public function test_1_finance_user_can_access_reports(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.index'));

        $response->assertOk();
        $response->assertSee('Project Financials');
        $response->assertSee('Expenses');
        $response->assertSee('Payments');
    }

    public function test_2_non_finance_user_cannot_access_reports(): void
    {
        $response = $this->actingAs($this->normalUser)
            ->get(route('finance.reports.index'));

        $response->assertForbidden();
    }

    public function test_3_project_financial_report_loads(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.projects'));

        $response->assertOk();
        $response->assertSee('Project Financial Report');
    }

    public function test_4_project_value_is_calculated_correctly(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-REP-01',
            'client_id' => $this->client->id,
            'title' => 'CCTV Project Alpha',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 8500000.00,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.projects'));

        $response->assertOk();
        $response->assertSee('8,500,000.00');
    }

    public function test_5_payments_are_calculated_correctly(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-REP-02',
            'client_id' => $this->client->id,
            'title' => 'Payment Report Project',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectPayment::create([
            'project_id' => $project->id,
            'amount' => 2500000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'recorded_by' => $this->financeUser->id,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.projects'));

        $response->assertOk();
        $response->assertSee('2,500,000.00');
    }

    public function test_6_outstanding_balance_is_calculated_correctly(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-REP-03',
            'client_id' => $this->client->id,
            'title' => 'Outstanding Report Project',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 6000000.00,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        ProjectPayment::create([
            'project_id' => $project->id,
            'amount' => 2000000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'recorded_by' => $this->financeUser->id,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        // Outstanding = 6,000,000 - 2,000,000 = 4,000,000.
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.projects'));

        $response->assertOk();
        $response->assertSee('4,000,000.00');
    }

    public function test_7_approved_costs_are_calculated_correctly(): void
    {
        $category = FinanceExpenseCategory::firstOrCreate(
            ['slug' => 'logistics'],
            ['name' => 'Logistics & Transport', 'is_active' => true]
        );

        $project = Project::create([
            'project_code' => 'PRJ-REP-04',
            'client_id' => $this->client->id,
            'title' => 'Costs Project Test',
            'created_by' => $this->financeUser->id,
        ]);

        FinancialExpense::create([
            'project_id' => $project->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Site Logistics',
            'amount' => 400000.00,
            'status' => FinancialExpense::STATUS_APPROVED,
            'submitted_by' => $this->financeUser->id,
        ]);

        FinancialMaterialCost::create([
            'project_id' => $project->id,
            'material_name' => 'Solar Batteries',
            'quantity' => 2,
            'unit' => 'pcs',
            'unit_cost' => 150000.00,
            'total_cost' => 300000.00,
            'status' => FinancialMaterialCost::STATUS_APPROVED,
            'submitted_by' => $this->financeUser->id,
        ]);

        // Approved costs = 400,000 + 300,000 = 700,000.
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.projects'));

        $response->assertOk();
        $response->assertSee('700,000.00');
    }

    public function test_8_estimated_profit_is_calculated_correctly(): void
    {
        $category = FinanceExpenseCategory::firstOrCreate(
            ['slug' => 'site-costs'],
            ['name' => 'Site Costs', 'is_active' => true]
        );

        $project = Project::create([
            'project_code' => 'PRJ-REP-05',
            'client_id' => $this->client->id,
            'title' => 'Profit Report Project',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 10000000.00,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        FinancialExpense::create([
            'project_id' => $project->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Installation Expense',
            'amount' => 2000000.00,
            'status' => FinancialExpense::STATUS_APPROVED,
            'submitted_by' => $this->financeUser->id,
        ]);

        // Contract value = 10,000,000. Approved costs = 2,000,000. Est profit = 8,000,000.
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.projects'));

        $response->assertOk();
        $response->assertSee('8,000,000.00');
    }

    public function test_9_project_search_works(): void
    {
        Project::create([
            'project_code' => 'PRJ-UNIQUE-101',
            'client_id' => $this->client->id,
            'title' => 'Hyperion Surveillance Complex',
            'created_by' => $this->financeUser->id,
        ]);

        Project::create([
            'project_code' => 'PRJ-OTHER-202',
            'client_id' => $this->client->id,
            'title' => 'Ordinary Fence Gate',
            'created_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.projects', ['search' => 'Hyperion']));

        $response->assertOk();
        $response->assertSee('Hyperion Surveillance Complex');
        $response->assertDontSee('Ordinary Fence Gate');
    }

    public function test_10_expense_report_loads(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.expenses'));

        $response->assertOk();
        $response->assertSee('Expense Report');
    }

    public function test_11_expense_filters_work(): void
    {
        $category1 = FinanceExpenseCategory::firstOrCreate(
            ['slug' => 'cat-travel'],
            ['name' => 'Travel Cat', 'is_active' => true]
        );

        $category2 = FinanceExpenseCategory::firstOrCreate(
            ['slug' => 'cat-hardware'],
            ['name' => 'Hardware Cat', 'is_active' => true]
        );

        FinancialExpense::create([
            'finance_expense_category_id' => $category1->id,
            'description' => 'Travel Fuel Costs',
            'amount' => 50000.00,
            'status' => FinancialExpense::STATUS_APPROVED,
            'submitted_by' => $this->financeUser->id,
        ]);

        FinancialExpense::create([
            'finance_expense_category_id' => $category2->id,
            'description' => 'Switch Board Router',
            'amount' => 120000.00,
            'status' => FinancialExpense::STATUS_PENDING,
            'submitted_by' => $this->financeUser->id,
        ]);

        // Filter by category 1
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.expenses', ['category' => $category1->id]));

        $response->assertOk();
        $response->assertSee('Travel Fuel Costs');
        $response->assertDontSee('Switch Board Router');

        // Filter by status pending
        $response2 = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.expenses', ['status' => 'pending']));

        $response2->assertOk();
        $response2->assertSee('Switch Board Router');
        $response2->assertDontSee('Travel Fuel Costs');
    }

    public function test_12_payment_report_loads(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.payments'));

        $response->assertOk();
        $response->assertSee('Payment Report');
    }

    public function test_13_payment_filters_work(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-PAY-FILTER',
            'client_id' => $this->client->id,
            'title' => 'Payment Filter Project',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectPayment::create([
            'project_id' => $project->id,
            'amount' => 900000.00,
            'payment_date' => '2026-08-01',
            'payment_method' => 'bank_transfer',
            'reference' => 'TRANS-BANK-99',
            'recorded_by' => $this->financeUser->id,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        ProjectPayment::create([
            'project_id' => $project->id,
            'amount' => 200000.00,
            'payment_date' => '2026-08-10',
            'payment_method' => 'cash',
            'reference' => 'CASH-REC-11',
            'recorded_by' => $this->financeUser->id,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.payments', ['payment_method' => 'bank_transfer']));

        $response->assertOk();
        $response->assertSee('TRANS-BANK-99');
        $response->assertDontSee('CASH-REC-11');
    }

    public function test_14_pagination_works(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Project::create([
                'project_code' => sprintf('PRJ-PAG-%03d', $i),
                'client_id' => $this->client->id,
                'title' => "Paginated Project #{$i}",
                'created_by' => $this->financeUser->id,
            ]);
        }

        // Page 1 should contain 15 items
        $responsePage1 = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.projects', ['page' => 1]));

        $responsePage1->assertOk();
        $responsePage1->assertSee('Paginated Project #20');

        // Page 2 should contain remaining 5 items
        $responsePage2 = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.projects', ['page' => 2]));

        $responsePage2->assertOk();
        $responsePage2->assertSee('Paginated Project #1');
    }

    public function test_15_csv_export_works(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-CSV-01',
            'client_id' => $this->client->id,
            'title' => 'CSV Export Project',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 7500000.00,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.projects.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('CSV Export Project', $response->streamedContent());
        $this->assertStringContainsString('7500000.00', $response->streamedContent());
    }

    public function test_16_export_respects_filters(): void
    {
        Project::create([
            'project_code' => 'PRJ-EXP-MATCH',
            'client_id' => $this->client->id,
            'title' => 'Target Project Export',
            'created_by' => $this->financeUser->id,
        ]);

        Project::create([
            'project_code' => 'PRJ-EXP-OTHER',
            'client_id' => $this->client->id,
            'title' => 'Ignored Project Export',
            'created_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.projects.export', ['search' => 'Target']));

        $response->assertOk();
        $this->assertStringContainsString('Target Project Export', $response->streamedContent());
        $this->assertStringNotContainsString('Ignored Project Export', $response->streamedContent());
    }

    public function test_17_reports_do_not_use_pos_order_revenue(): void
    {
        Order::create([
            'user_id' => $this->financeUser->id,
            'order_number' => 'ORD-REV-888',
            'total_amount' => 88888888.00,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'card',
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.projects'));

        $response->assertOk();
        $response->assertDontSee('88,888,888.00');
    }

    public function test_18_empty_states_work(): void
    {
        $projectsResponse = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.projects'));
        $projectsResponse->assertOk();
        $projectsResponse->assertSee('No projects found.');

        $expensesResponse = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.expenses'));
        $expensesResponse->assertOk();
        $expensesResponse->assertSee('No expenses found.');

        $paymentsResponse = $this->actingAs($this->financeUser)
            ->get(route('finance.reports.payments'));
        $paymentsResponse->assertOk();
        $paymentsResponse->assertSee('No payments found.');
    }
}
