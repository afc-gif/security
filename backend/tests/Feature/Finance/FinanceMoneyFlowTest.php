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
use Illuminate\Support\Facades\DB;
use Tests\TestCase;


/**
 * FinanceMoneyFlowTest
 *
 * Verifies the authoritative financial aggregation rules:
 *  - Money IN  = ProjectPayment (all types) + Order (status=completed)
 *  - Money OUT = FinancialExpense (status=approved) + FinancialMaterialCost (status=approved, incurred_on not null)
 *  - No double-counting; pending/rejected records are excluded.
 */
class FinanceMoneyFlowTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private ?User $financeUserInstance = null;

    private function financeUser(): User
    {
        if ($this->financeUserInstance === null) {
            $user = User::create([
                'name'     => 'Finance Tester',
                'email'    => 'ftest_' . uniqid() . '@example.com',
                'password' => bcrypt('password'),
                'role'     => 'finance',
                'status'   => 'approved',
            ]);

            $ids = FinancePermission::query()
                ->whereIn('slug', [FinancePermission::VIEW])
                ->pluck('id')
                ->all();

            $user->financePermissions()->syncWithoutDetaching(
                collect($ids)->mapWithKeys(fn (int $id) => [$id => ['granted_at' => now()]])->all()
            );

            $this->financeUserInstance = $user;
        }

        return $this->financeUserInstance;
    }

    private function makeCategory(string $name = 'General'): FinanceExpenseCategory
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $name));
        return FinanceExpenseCategory::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'description' => $name]
        );
    }

    private function makeClient(): Client
    {
        return Client::create([
            'client_name' => 'Test Client ' . uniqid(),
            'phone'       => '08000000000',
            'status'      => 'active',
        ]);
    }

    private function makeProject(): Project
    {
        $user = $this->financeUser();
        return Project::create([
            'project_code' => 'PROJ-' . uniqid(),
            'title'        => 'Test Project',
            'client_id'    => $this->makeClient()->id,
            'status'       => 'active',
            'created_by'   => $user->id,
        ]);
    }

    private function makePayment(Project $project, float $amount, string $type, string $date): ProjectPayment
    {
        $user = $this->financeUser();
        return ProjectPayment::create([
            'project_id'   => $project->id,
            'client_id'    => $project->client_id,
            'amount'       => $amount,
            'payment_type' => $type,
            'payment_date' => $date,
            'recorded_by'  => $user->id,
            'created_by'   => $user->id,
            'updated_by'   => $user->id,
        ]);
    }

    private function makeExpense(
        float $amount,
        string $status,
        bool $isOffice,
        string $date,
        string $categoryName = 'General'
    ): FinancialExpense {
        $cat = $this->makeCategory($categoryName);
        return FinancialExpense::create([
            'description'                => 'Test expense',
            'amount'                     => $amount,
            'status'                     => $status,
            'is_office_expense'          => $isOffice,
            'incurred_on'                => $date,
            'finance_expense_category_id' => $cat->id,
        ]);
    }

    private function makeMaterial(float $amount, string $status, ?string $incurredOn): FinancialMaterialCost
    {
        $project = $this->makeProject();
        return FinancialMaterialCost::create([
            'project_id'   => $project->id,
            'material_name' => 'Test material',
            'quantity'     => 1,
            'unit_cost'    => $amount,
            'total_cost'   => $amount,
            'status'       => $status,
            'incurred_on'  => $incurredOn,
        ]);
    }

    private function makeOrder(float $amount, string $status, string $createdAt): Order
    {
        $order = Order::create([
            'code'         => 'ORD-' . uniqid(),
            'user_id'      => $this->financeUser()->id,
            'total_amount' => $amount,
            'status'       => $status,
            'channel'      => 'pos',
        ]);

        // Eloquent's automatic timestamps overwrite created_at — force it via raw query
        DB::table('orders')
            ->where('id', $order->id)
            ->update(['created_at' => $createdAt, 'updated_at' => $createdAt]);

        return $order->fresh();
    }

    private function analysisUrl(string $period = 'month'): string
    {
        return route('finance.analysis', ['period' => $period]);
    }

    private function today(): string
    {
        return now()->toDateString();
    }

    // -------------------------------------------------------------------------
    // 1. Money IN — Project Payments (all types)
    // -------------------------------------------------------------------------

    public function test_project_deposit_appears_in_money_in(): void
    {
        $user    = $this->financeUser();
        $project = $this->makeProject();
        $this->makePayment($project, 5000.00, ProjectPayment::TYPE_DEPOSIT, $this->today());

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('projectRevenuePeriod', fn ($v) => (float) $v === 5000.0);
        $response->assertViewHas('totalIn', fn ($v) => (float) $v >= 5000.0);
    }

    public function test_project_part_payment_appears_in_money_in(): void
    {
        $user    = $this->financeUser();
        $project = $this->makeProject();
        $this->makePayment($project, 3000.00, ProjectPayment::TYPE_PART_PAYMENT, $this->today());

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('projectRevenuePeriod', fn ($v) => (float) $v === 3000.0);
    }

    public function test_project_full_payment_appears_in_money_in(): void
    {
        $user    = $this->financeUser();
        $project = $this->makeProject();
        $this->makePayment($project, 10000.00, ProjectPayment::TYPE_FULL_PAYMENT, $this->today());

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('projectRevenuePeriod', fn ($v) => (float) $v === 10000.0);
    }

    public function test_money_in_breakdown_by_payment_type(): void
    {
        $user    = $this->financeUser();
        $project = $this->makeProject();
        $this->makePayment($project, 1000.00, ProjectPayment::TYPE_DEPOSIT, $this->today());
        $this->makePayment($project, 2000.00, ProjectPayment::TYPE_PART_PAYMENT, $this->today());
        $this->makePayment($project, 4000.00, ProjectPayment::TYPE_FULL_PAYMENT, $this->today());

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('projectRevenueByType', function (array $breakdown) {
            return (float) $breakdown[ProjectPayment::TYPE_DEPOSIT]      === 1000.0
                && (float) $breakdown[ProjectPayment::TYPE_PART_PAYMENT] === 2000.0
                && (float) $breakdown[ProjectPayment::TYPE_FULL_PAYMENT] === 4000.0;
        });
        $response->assertViewHas('projectRevenuePeriod', fn ($v) => (float) $v === 7000.0);
    }

    // -------------------------------------------------------------------------
    // 2. Money IN — POS Orders
    // -------------------------------------------------------------------------

    public function test_pos_completed_sale_appears_in_money_in(): void
    {
        $user = $this->financeUser();
        $this->makeOrder(2500.00, 'completed', now()->toDateTimeString());

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('posRevenuePeriod', fn ($v) => (float) $v === 2500.0);
        $response->assertViewHas('posOrderCountPeriod', fn ($v) => (int) $v === 1);
    }

    public function test_pos_pending_sale_excluded_from_money_in(): void
    {
        $user = $this->financeUser();
        $this->makeOrder(9999.00, 'pending', now()->toDateTimeString());

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('posRevenuePeriod', fn ($v) => (float) $v === 0.0);
        $response->assertViewHas('posOrderCountPeriod', fn ($v) => (int) $v === 0);
    }

    public function test_pos_cancelled_sale_excluded_from_money_in(): void
    {
        $user = $this->financeUser();
        $this->makeOrder(8888.00, 'cancelled', now()->toDateTimeString());

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('posRevenuePeriod', fn ($v) => (float) $v === 0.0);
    }

    // -------------------------------------------------------------------------
    // 3. Money OUT — Expenses
    // -------------------------------------------------------------------------

    public function test_approved_office_expense_appears_in_money_out(): void
    {
        $user = $this->financeUser();
        $this->makeExpense(500.00, FinancialExpense::STATUS_APPROVED, true, $this->today());

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('officeExpensesTotal', fn ($v) => (float) $v === 500.0);
    }

    public function test_approved_operational_expense_appears_in_money_out(): void
    {
        $user = $this->financeUser();
        $this->makeExpense(750.00, FinancialExpense::STATUS_APPROVED, false, $this->today());

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('operationalExpensesTotal', fn ($v) => (float) $v === 750.0);
    }

    public function test_approved_material_cost_with_incurred_on_appears_in_money_out(): void
    {
        $user = $this->financeUser();
        $this->makeMaterial(1200.00, FinancialMaterialCost::STATUS_APPROVED, $this->today());

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('materialsPeriod', fn ($v) => (float) $v === 1200.0);
    }

    public function test_pending_expense_excluded_from_money_out(): void
    {
        $user = $this->financeUser();
        $this->makeExpense(9999.00, FinancialExpense::STATUS_PENDING, true, $this->today());

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('officeExpensesTotal', fn ($v) => (float) $v === 0.0);
        $response->assertViewHas('totalOut', fn ($v) => (float) $v === 0.0);
    }

    public function test_rejected_expense_excluded_from_money_out(): void
    {
        $user = $this->financeUser();
        $this->makeExpense(7777.00, FinancialExpense::STATUS_REJECTED, false, $this->today());

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('operationalExpensesTotal', fn ($v) => (float) $v === 0.0);
    }

    public function test_material_with_null_incurred_on_excluded_from_period_filter(): void
    {
        $user = $this->financeUser();
        // NULL incurred_on — must NOT appear in period totals (fix: we use whereNotNull)
        $this->makeMaterial(9999.00, FinancialMaterialCost::STATUS_APPROVED, null);

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('materialsPeriod', fn ($v) => (float) $v === 0.0);
    }

    // -------------------------------------------------------------------------
    // 4. Net Cash Flow formula
    // -------------------------------------------------------------------------

    public function test_net_cash_flow_is_total_in_minus_total_out(): void
    {
        $user    = $this->financeUser();
        $project = $this->makeProject();

        $this->makePayment($project, 5000.00, ProjectPayment::TYPE_FULL_PAYMENT, $this->today());
        $this->makeOrder(2000.00, 'completed', now()->toDateTimeString());
        $this->makeExpense(1000.00, FinancialExpense::STATUS_APPROVED, true, $this->today());
        $this->makeMaterial(500.00, FinancialMaterialCost::STATUS_APPROVED, $this->today());

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('totalIn',    fn ($v) => (float) $v === 7000.0);
        $response->assertViewHas('totalOut',   fn ($v) => (float) $v === 1500.0);
        $response->assertViewHas('netCashFlow', fn ($v) => (float) $v === 5500.0);
    }

    // -------------------------------------------------------------------------
    // 5. Date filter correctness
    // -------------------------------------------------------------------------

    public function test_date_filter_excludes_out_of_period_payments(): void
    {
        $user    = $this->financeUser();
        $project = $this->makeProject();

        // Payment last month — must NOT appear in current month filter
        $lastMonth = now()->subMonth()->toDateString();
        $this->makePayment($project, 9000.00, ProjectPayment::TYPE_DEPOSIT, $lastMonth);

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('projectRevenuePeriod', fn ($v) => (float) $v === 0.0);
    }

    public function test_date_filter_excludes_out_of_period_pos_orders(): void
    {
        $user = $this->financeUser();

        // Order from well outside the current month (one year ago) — must NOT appear
        $oldDate = now()->subYear()->toDateTimeString();
        $this->makeOrder(8000.00, 'completed', $oldDate);

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('posRevenuePeriod', fn ($v) => (float) $v === 0.0);
    }

    // -------------------------------------------------------------------------
    // 6. No double-counting
    // -------------------------------------------------------------------------

    public function test_no_double_counting_between_office_and_operational_expenses(): void
    {
        $user = $this->financeUser();

        $this->makeExpense(500.00, FinancialExpense::STATUS_APPROVED, true, $this->today(), 'Admin');
        $this->makeExpense(300.00, FinancialExpense::STATUS_APPROVED, false, $this->today(), 'Diesel');

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        $response->assertViewHas('officeExpensesTotal',      fn ($v) => (float) $v === 500.0);
        $response->assertViewHas('operationalExpensesTotal', fn ($v) => (float) $v === 300.0);
        $response->assertViewHas('totalOut',                 fn ($v) => (float) $v === 800.0);
    }

    public function test_project_contract_value_does_not_count_as_money_in(): void
    {
        $user    = $this->financeUser();
        $project = $this->makeProject();

        // Contract value set — but NO actual payment recorded
        ProjectFinancial::create([
            'project_id'     => $project->id,
            'contract_value' => 100000.00,
            'created_by'     => $user->id,
        ]);

        $response = $this->actingAs($user)->get($this->analysisUrl('month'));

        $response->assertStatus(200);
        // Money In must be 0 — contract value is NOT income until paid
        $response->assertViewHas('projectRevenuePeriod', fn ($v) => (float) $v === 0.0);
        $response->assertViewHas('totalIn',              fn ($v) => (float) $v === 0.0);
        // But all-time project value IS tracked for outstanding balance display
        $response->assertViewHas('projectValueTotal',    fn ($v) => (float) $v === 100000.0);
    }

    // -------------------------------------------------------------------------
    // 7. POS Sales Finance page
    // -------------------------------------------------------------------------

    public function test_pos_sales_page_shows_completed_orders_only(): void
    {
        $user = $this->financeUser();

        $this->makeOrder(1500.00, 'completed',  now()->toDateTimeString());
        $this->makeOrder(5000.00, 'pending',    now()->toDateTimeString());
        $this->makeOrder(3000.00, 'cancelled',  now()->toDateTimeString());

        $response = $this->actingAs($user)->get(route('finance.pos-sales.index', ['period' => 'month']));

        $response->assertStatus(200);
        $response->assertViewHas('periodTotal', fn ($v) => (float) $v === 1500.0);
        $response->assertViewHas('periodCount', fn ($v) => (int) $v === 1);
    }

    public function test_pos_sales_page_requires_finance_permission(): void
    {
        $user = User::create([
            'name'     => 'Regular User',
            'email'    => 'regular_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'user',
            'status'   => 'approved',
        ]);

        $response = $this->actingAs($user)->get(route('finance.pos-sales.index'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_pos_sales_page(): void
    {
        $response = $this->get(route('finance.pos-sales.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_pos_sales_all_time_total_includes_all_completed_orders(): void
    {
        $user = $this->financeUser();

        // Order from last year (outside current month)
        $this->makeOrder(10000.00, 'completed', now()->subYear()->toDateTimeString());
        // Order this month
        $this->makeOrder(2000.00, 'completed', now()->toDateTimeString());

        $response = $this->actingAs($user)->get(route('finance.pos-sales.index', ['period' => 'month']));

        $response->assertStatus(200);
        // Period total should only include the order from this month
        $response->assertViewHas('periodTotal', fn ($v) => abs((float) $v - 2000.0) < 0.01);
        // All-time total must include both (last year + this month)
        $response->assertViewHas('allTimeTotal', fn ($v) => abs((float) $v - 12000.0) < 0.01);
    }
}
