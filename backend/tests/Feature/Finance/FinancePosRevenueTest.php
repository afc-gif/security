<?php

namespace Tests\Feature\Finance;

use App\Models\FinancePermission;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests that POS revenue (completed Orders) is correctly integrated
 * into the Finance module analysis and dashboard endpoints.
 */
class FinancePosRevenueTest extends TestCase
{
    use RefreshDatabase;

    private User $financeUser;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            FinancePermission::VIEW,
            FinancePermission::CREATE,
            FinancePermission::EDIT,
            FinancePermission::DELETE,
            FinancePermission::APPROVE,
        ] as $perm) {
            FinancePermission::firstOrCreate(
                ['slug' => $perm],
                ['name' => ucfirst(str_replace('.', ' ', $perm))]
            );
        }

        $this->financeUser = $this->createUser(['role' => 'finance']);
        $permIds = FinancePermission::query()->pluck('id')->all();
        $this->financeUser->financePermissions()->syncWithoutDetaching(
            collect($permIds)->mapWithKeys(fn (int $id) => [$id => ['granted_at' => now()]])->all()
        );
    }

    // -------------------------------------------------------------------------
    // Dashboard — POS revenue visible
    // -------------------------------------------------------------------------

    /** @test */
    public function dashboard_includes_pos_revenue_total(): void
    {
        $this->createOrder($this->financeUser, ['status' => 'completed', 'total_amount' => 5000.00]);
        $this->createOrder($this->financeUser, ['status' => 'completed', 'total_amount' => 3500.00]);
        $this->createOrder($this->financeUser, ['status' => 'cancelled', 'total_amount' => 1000.00]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.dashboard'));

        $response->assertOk();
        $response->assertSee('POS Revenue');
        $response->assertSee('8,500.00');
    }

    /** @test */
    public function dashboard_total_in_includes_pos_and_project_payments(): void
    {
        $this->createOrder($this->financeUser, ['status' => 'completed', 'total_amount' => 10000.00]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.dashboard'));

        $response->assertOk();
        $response->assertSee('Money IN');
        $response->assertSee('POS Sales');
    }

    /** @test */
    public function cancelled_orders_are_excluded_from_pos_revenue(): void
    {
        $this->createOrder($this->financeUser, ['status' => 'cancelled', 'total_amount' => 55555.00]);
        $this->createOrder($this->financeUser, ['status' => 'pending',   'total_amount' => 5000.00]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.dashboard'));

        $response->assertOk();
        // Neither cancelled nor pending orders should contribute to revenue
        $response->assertDontSee('55,555.00');
    }

    // -------------------------------------------------------------------------
    // Analysis — period filters + POS
    // -------------------------------------------------------------------------

    /** @test */
    public function analysis_page_loads_with_default_period(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis'));

        $response->assertOk();
        $response->assertSee('Money IN');
        $response->assertSee('Money OUT');
        $response->assertSee('Net Position');
        $response->assertSee('POS Sales');
        $response->assertSee('Project Payments');
    }

    /** @test */
    public function analysis_page_renders_period_filter_buttons(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis'));

        $response->assertOk();
        $response->assertSee('Today');
        $response->assertSee('This Week');
        $response->assertSee('This Month');
        $response->assertSee('This Quarter');
        $response->assertSee('This Year');
    }

    /** @test */
    public function analysis_period_today_filter_works(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis', ['period' => 'today']));

        $response->assertOk();
        $response->assertSee('Today');
    }

    /** @test */
    public function analysis_period_week_filter_works(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis', ['period' => 'week']));

        $response->assertOk();
        $response->assertSee('This Week');
    }

    /** @test */
    public function analysis_period_quarter_filter_works(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis', ['period' => 'quarter']));

        $response->assertOk();
        $response->assertSee('This Quarter');
    }

    /** @test */
    public function analysis_period_year_filter_works(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis', ['period' => 'year']));

        $response->assertOk();
        $response->assertSee('This Year');
    }

    /** @test */
    public function analysis_custom_period_filter_works(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis', [
                'period'    => 'custom',
                'date_from' => '2026-01-01',
                'date_to'   => '2026-01-31',
            ]));

        $response->assertOk();
        $response->assertSee('01 Jan 2026');
    }

    /** @test */
    public function analysis_shows_pos_revenue_in_income_breakdown(): void
    {
        $this->createOrder($this->financeUser, [
            'status'       => 'completed',
            'total_amount' => 7500.00,
            'created_at'   => now(),
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis', ['period' => 'month']));

        $response->assertOk();
        $response->assertSee('POS Sales');
        $response->assertSee('7,500.00');
    }

    /** @test */
    public function analysis_excludes_cancelled_orders_from_pos_revenue(): void
    {
        $this->createOrder($this->financeUser, [
            'status'       => 'cancelled',
            'total_amount' => 50000.00,
            'created_at'   => now(),
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis', ['period' => 'month']));

        $response->assertOk();
        // Cancelled order amount should not appear as revenue
        $response->assertDontSee('50,000.00');
    }

    /** @test */
    public function analysis_shows_expense_category_section_labels(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.analysis'));

        $response->assertOk();
        $response->assertSee('Expense Breakdown');
        $response->assertSee('Office Expenses');
        $response->assertSee('Operational Expenses');
    }

    // -------------------------------------------------------------------------
    // Ask Finance — POS questions
    // -------------------------------------------------------------------------

    /** @test */
    public function ask_returns_pos_revenue_answer(): void
    {
        $this->createOrder($this->financeUser, ['status' => 'completed', 'total_amount' => 12000.00]);

        $response = $this->actingAs($this->financeUser)
            ->postJson(route('finance.analysis.ask'), ['question' => 'How much POS revenue this month?']);

        $response->assertOk();
        $response->assertJsonStructure(['answer']);
        $this->assertStringContainsString('POS', $response->json('answer'));
    }

    /** @test */
    public function ask_returns_total_in_answer(): void
    {
        $this->createOrder($this->financeUser, ['status' => 'completed', 'total_amount' => 5000.00]);

        $response = $this->actingAs($this->financeUser)
            ->postJson(route('finance.analysis.ask'), ['question' => 'What is the total money in this month?']);

        $response->assertOk();
        $response->assertJsonStructure(['answer']);
        $this->assertStringContainsString('IN', $response->json('answer'));
    }

    /** @test */
    public function ask_returns_net_position_answer(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->postJson(route('finance.analysis.ask'), ['question' => 'What is the net position this month?']);

        $response->assertOk();
        $response->assertJsonStructure(['answer']);
        $this->assertStringContainsString('net', strtolower($response->json('answer')));
    }

    /** @test */
    public function ask_returns_fallback_for_unknown_question(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->postJson(route('finance.analysis.ask'), ['question' => 'What is the meaning of life?']);

        $response->assertOk();
        $response->assertJsonStructure(['answer']);
        $this->assertNotEmpty($response->json('answer'));
    }

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    /** @test */
    public function unauthorized_user_cannot_access_analysis(): void
    {
        $nonFinanceUser = $this->createUser(['role' => 'user']);

        $this->actingAs($nonFinanceUser)
            ->get(route('finance.analysis'))
            ->assertStatus(403);
    }

    /** @test */
    public function unauthorized_user_cannot_use_ask_endpoint(): void
    {
        $nonFinanceUser = $this->createUser(['role' => 'user']);

        $this->actingAs($nonFinanceUser)
            ->postJson(route('finance.analysis.ask'), ['question' => 'pos revenue'])
            ->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_analysis(): void
    {
        $this->get(route('finance.analysis'))
            ->assertRedirect(route('login'));
    }
}
