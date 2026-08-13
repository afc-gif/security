<?php

namespace Tests\Feature\Finance;

use App\Models\Client;
use App\Models\FinanceExpenseCategory;
use App\Models\FinancePermission;
use App\Models\FinancialExpense;
use App\Models\FinancialMaterialCost;
use App\Models\Project;
use App\Models\ProjectFinancial;
use App\Models\ProjectPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceAnalysisTest extends TestCase
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
            'client_code' => 'CLI-ANA-' . uniqid(),
            'client_name' => 'Analysis Enterprise',
            'company_name' => 'Analysis Corp',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);
    }

    public function test_1_finance_user_can_access_analysis(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis'));

        $response->assertOk();
        $response->assertSee('Finance Analysis');
        $response->assertSee('Business Health');
        $response->assertSee('Ask Finance');
    }

    public function test_2_non_finance_user_receives_403(): void
    {
        $response = $this->actingAs($this->normalUser)
            ->get(route('finance.analysis'));

        $response->assertForbidden();
    }

    public function test_3_financial_totals_and_health_badge_are_calculated(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-ANA-01',
            'client_id' => $this->client->id,
            'title' => 'Healthy CCTV Setup',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 10000000.00,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        ProjectPayment::create([
            'project_id' => $project->id,
            'amount' => 8000000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'recorded_by' => $this->financeUser->id,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis'));

        $response->assertOk();
        $response->assertSee('Healthy');
        $response->assertSee('₦10.0M');
        $response->assertSee('₦8.0M');
    }

    public function test_4_outstanding_balance_calculation_is_correct(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-ANA-02',
            'client_id' => $this->client->id,
            'title' => 'Outstanding Test Project',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 5000000.00,
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

        // Outstanding = 5,000,000 - 1,500,000 = 3,500,000.
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis'));

        $response->assertOk();
        $response->assertSee('3,500,000.00');
    }

    public function test_5_overpayment_is_handled_correctly(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-ANA-03',
            'client_id' => $this->client->id,
            'title' => 'Overpaid Project',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 2000000.00,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
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

        // Outstanding should cap at 0, received = 2,500,000 (rendered as 2.5M).
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis'));

        $response->assertOk();
        $response->assertSee('2.5M');
    }

    public function test_6_approved_costs_only_are_included(): void
    {
        $category = FinanceExpenseCategory::firstOrCreate(
            ['slug' => 'cat-analysis-1'],
            ['name' => 'Logistics Cat', 'is_active' => true]
        );

        $project = Project::create([
            'project_code' => 'PRJ-ANA-04',
            'client_id' => $this->client->id,
            'title' => 'Costs Approval Test',
            'created_by' => $this->financeUser->id,
        ]);

        FinancialExpense::create([
            'project_id' => $project->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Approved Expense Item',
            'amount' => 600000.00,
            'status' => FinancialExpense::STATUS_APPROVED,
            'submitted_by' => $this->financeUser->id,
        ]);

        FinancialExpense::create([
            'project_id' => $project->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Pending Expense Item',
            'amount' => 900000.00,
            'status' => FinancialExpense::STATUS_PENDING,
            'submitted_by' => $this->financeUser->id,
        ]);

        // Only 600,000 should be in approved costs.
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis'));

        $response->assertOk();
        $response->assertSee('600,000.00');
    }

    public function test_7_trend_data_is_generated_correctly(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis', ['period' => 'quarter']));

        $response->assertOk();
        $response->assertSee(now()->format('M Y'));
    }

    public function test_8_cost_breakdown_categories_are_correct(): void
    {
        $category = FinanceExpenseCategory::firstOrCreate(
            ['slug' => 'fuel-cat'],
            ['name' => 'Generator Fuel', 'is_active' => true]
        );

        $project = Project::create([
            'project_code' => 'PRJ-ANA-05',
            'client_id' => $this->client->id,
            'title' => 'Cost Breakdown Project',
            'created_by' => $this->financeUser->id,
        ]);

        FinancialExpense::create([
            'project_id'                  => $project->id,
            'finance_expense_category_id' => $category->id,
            'description'                 => 'Diesel Fuel Refill',
            'amount'                      => 350000.00,
            'incurred_on'                 => now()->toDateString(),
            'status'                      => FinancialExpense::STATUS_APPROVED,
            'submitted_by'                => $this->financeUser->id,
        ]);

        FinancialMaterialCost::create([
            'project_id'    => $project->id,
            'material_name' => 'IP Cameras',
            'quantity'      => 5,
            'unit'          => 'pcs',
            'unit_cost'     => 50000.00,
            'total_cost'    => 250000.00,
            'status'        => FinancialMaterialCost::STATUS_APPROVED,
            'submitted_by'  => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis'));

        $response->assertOk();
        $response->assertSee('Generator Fuel');
        $response->assertSee('Materials');
    }

    public function test_9_project_performance_data_is_correct(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-PERF-01',
            'client_id' => $this->client->id,
            'title' => 'Performance Target Project',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 12000000.00,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis', ['metric' => 'revenue']));

        $response->assertOk();
        $response->assertSee('Performance Target Project');
    }

    public function test_10_smart_insights_generated_dynamically(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-INSIGHT-01',
            'client_id' => $this->client->id,
            'title' => 'High Unpaid Project',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 10000000.00,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        // 0 received => 100% outstanding (>= 50%)
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis'));

        $response->assertOk();
        $response->assertSee('50% or more of their contract value outstanding');
    }

    public function test_11_ask_finance_supported_questions_return_answers(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-ASK-PROFIT',
            'client_id' => $this->client->id,
            'title' => 'Golden Profit Shield',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 15000000.00,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        // Test 11a: Most profitable
        $response1 = $this->actingAs($this->financeUser)
            ->postJson(route('finance.analysis.ask'), ['question' => 'Which project is most profitable?']);

        $response1->assertOk();
        $response1->assertJsonStructure(['answer', 'project_id', 'project_url']);
        $response1->assertJsonFragment(['project_id' => $project->id]);
        $this->assertStringContainsString('Golden Profit Shield', $response1->json('answer'));

        // Test 11b: Over budget
        $response2 = $this->actingAs($this->financeUser)
            ->postJson(route('finance.analysis.ask'), ['question' => 'Which projects are over budget?']);

        $response2->assertOk();
        $this->assertStringContainsString('No projects are currently over budget', $response2->json('answer'));

        // Test 11c: received this month — handler matches 'received this month' keyword
        $response3 = $this->actingAs($this->financeUser)
            ->postJson(route('finance.analysis.ask'), ['question' => 'How much have we received this month?']);

        $response3->assertOk();
        $this->assertStringContainsString('IN', $response3->json('answer'));
    }

    public function test_12_ask_finance_unsupported_questions_return_fallback(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->postJson(route('finance.analysis.ask'), ['question' => 'What is the capital of France?']);

        $response->assertOk();
        $this->assertStringContainsString('I can answer questions about', $response->json('answer'));
    }
}
