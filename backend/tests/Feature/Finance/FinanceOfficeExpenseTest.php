<?php

namespace Tests\Feature\Finance;

use App\Models\FinanceExpenseCategory;
use App\Models\FinancePermission;
use App\Models\FinancialExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceOfficeExpenseTest extends TestCase
{
    use RefreshDatabase;

    private User $financeUser;
    private User $financeApprover;
    private FinanceExpenseCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure finance permission records exist
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

        // Finance user with view / create / edit / delete
        $this->financeUser = $this->createUser(['role' => 'finance']);
        $basicPermIds = FinancePermission::whereIn('slug', [
            FinancePermission::VIEW,
            FinancePermission::CREATE,
            FinancePermission::EDIT,
            FinancePermission::DELETE,
        ])->pluck('id')->all();
        $this->financeUser->financePermissions()->syncWithoutDetaching(
            collect($basicPermIds)->mapWithKeys(fn (int $id) => [$id => ['granted_at' => now()]])->all()
        );

        // Approver — all permissions
        $this->financeApprover = $this->createUser(['role' => 'finance']);
        $allPermIds = FinancePermission::pluck('id')->all();
        $this->financeApprover->financePermissions()->syncWithoutDetaching(
            collect($allPermIds)->mapWithKeys(fn (int $id) => [$id => ['granted_at' => now()]])->all()
        );

        $this->category = FinanceExpenseCategory::firstOrCreate(
            ['slug' => 'diesel'],
            ['name' => 'Diesel', 'is_active' => true, 'sort_order' => 200]
        );
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function makeOfficeExpense(array $overrides = []): FinancialExpense
    {
        return FinancialExpense::create(array_merge([
            'is_office_expense' => true,
            'finance_expense_category_id' => $this->category->id,
            'description' => 'Test office expense',
            'amount' => 10000,
            'incurred_on' => now()->format('Y-m-d'),
            'status' => FinancialExpense::STATUS_PENDING,
            'submitted_by' => $this->financeUser->id,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_finance_user_can_view_office_expenses_index(): void
    {
        $this->makeOfficeExpense(['description' => 'Monthly diesel supply']);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.office-expenses.index'));

        $response->assertOk();
        $response->assertSee('Monthly diesel supply');
    }

    public function test_unauthenticated_user_cannot_view_office_expenses(): void
    {
        $response = $this->get(route('finance.office-expenses.index'));
        $response->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Create / Store
    // -------------------------------------------------------------------------

    public function test_finance_user_can_access_create_form(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.office-expenses.create'));

        $response->assertOk();
        $response->assertSee('Record Office Expense');
    }

    public function test_finance_user_can_store_office_expense(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->post(route('finance.office-expenses.store'), [
                'finance_expense_category_id' => $this->category->id,
                'description' => 'Generator diesel - August',
                'amount' => 32000,
                'incurred_on' => now()->format('Y-m-d'),
                'payment_method' => 'cash',
                'reference' => 'REF-001',
                'notes' => 'Purchased from Total station',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('financial_expenses', [
            'is_office_expense' => true,
            'description' => 'Generator diesel - August',
            'amount' => 32000,
            'payment_method' => 'cash',
            'reference' => 'REF-001',
            'status' => 'pending',
            'project_id' => null,
            'inspection_id' => null,
            'job_request_item_id' => null,
            'original_context_type' => 'office',
        ]);
    }

    public function test_store_requires_category_and_amount(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->post(route('finance.office-expenses.store'), [
                'description' => 'Missing category',
            ]);

        $response->assertSessionHasErrors(['finance_expense_category_id', 'amount']);
    }

    public function test_non_finance_user_cannot_store_office_expense(): void
    {
        $nonFinance = $this->createAdmin(); // admin without finance.create

        $response = $this->actingAs($nonFinance)
            ->post(route('finance.office-expenses.store'), [
                'finance_expense_category_id' => $this->category->id,
                'amount' => 5000,
            ]);

        $this->assertTrue(in_array($response->getStatusCode(), [302, 403], true));
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_finance_user_can_view_office_expense_detail(): void
    {
        $expense = $this->makeOfficeExpense([
            'description' => 'Office rent payment',
            'amount' => 120000,
            'payment_method' => 'bank_transfer',
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.office-expenses.show', $expense));

        $response->assertOk();
        $response->assertSee('Office rent payment');
    }

    public function test_cannot_view_non_office_expense_via_office_route(): void
    {
        // A FinancialExpense with is_office_expense=false should 404 via office route
        $nonOffice = FinancialExpense::create([
            'is_office_expense' => false,
            'finance_expense_category_id' => $this->category->id,
            'description' => 'Project transport',
            'amount' => 5000,
            'status' => 'pending',
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.office-expenses.show', $nonOffice));

        $response->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Edit / Update
    // -------------------------------------------------------------------------

    public function test_finance_user_can_edit_pending_office_expense(): void
    {
        $expense = $this->makeOfficeExpense(['description' => 'Draft expense']);

        $response = $this->actingAs($this->financeUser)
            ->put(route('finance.office-expenses.update', $expense), [
                'finance_expense_category_id' => $this->category->id,
                'description' => 'Updated expense description',
                'amount' => 15000,
                'incurred_on' => now()->format('Y-m-d'),
                'payment_method' => 'pos',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('financial_expenses', [
            'id' => $expense->id,
            'description' => 'Updated expense description',
            'amount' => 15000,
            'payment_method' => 'pos',
        ]);
    }

    public function test_cannot_edit_approved_office_expense(): void
    {
        $expense = $this->makeOfficeExpense([
            'description' => 'Approved salary',
            'amount' => 200000,
            'status' => FinancialExpense::STATUS_APPROVED,
            'approved_by' => $this->financeApprover->id,
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($this->financeUser)
            ->put(route('finance.office-expenses.update', $expense), [
                'finance_expense_category_id' => $this->category->id,
                'description' => 'Trying to update',
                'amount' => 250000,
            ]);

        $response->assertStatus(409);
    }

    // -------------------------------------------------------------------------
    // Approve / Reject
    // -------------------------------------------------------------------------

    public function test_approver_can_approve_pending_office_expense(): void
    {
        $expense = $this->makeOfficeExpense(['description' => 'Security patrol']);

        $response = $this->actingAs($this->financeApprover)
            ->post(route('finance.office-expenses.approve', $expense));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('financial_expenses', [
            'id' => $expense->id,
            'status' => 'approved',
            'approved_by' => $this->financeApprover->id,
        ]);
    }

    public function test_approver_can_reject_pending_office_expense_with_note(): void
    {
        $expense = $this->makeOfficeExpense(['description' => 'Questionable expense', 'amount' => 9999]);

        $response = $this->actingAs($this->financeApprover)
            ->post(route('finance.office-expenses.reject', $expense), [
                'notes' => 'No receipt provided',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('financial_expenses', [
            'id' => $expense->id,
            'status' => 'rejected',
        ]);

        $this->assertStringContainsString(
            'No receipt provided',
            FinancialExpense::find($expense->id)->notes
        );
    }

    public function test_non_finance_user_cannot_approve_office_expense(): void
    {
        // A plain admin (role=admin) without any finance permissions cannot approve
        $adminWithoutFinance = $this->createAdmin();

        $expense = $this->makeOfficeExpense(['description' => 'Pending expense']);

        $response = $this->actingAs($adminWithoutFinance)
            ->post(route('finance.office-expenses.approve', $expense));

        $this->assertTrue(in_array($response->getStatusCode(), [302, 403], true));

        $this->assertDatabaseHas('financial_expenses', [
            'id' => $expense->id,
            'status' => 'pending',
        ]);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function test_finance_user_can_delete_pending_office_expense(): void
    {
        $expense = $this->makeOfficeExpense(['description' => 'To be deleted', 'amount' => 100]);

        $response = $this->actingAs($this->financeUser)
            ->delete(route('finance.office-expenses.destroy', $expense));

        $response->assertRedirect(route('finance.office-expenses.index'));
        $this->assertDatabaseMissing('financial_expenses', ['id' => $expense->id]);
    }

    // -------------------------------------------------------------------------
    // Scope isolation: office expenses must NOT appear in pre-project expense list
    // -------------------------------------------------------------------------

    public function test_office_expenses_do_not_appear_in_pre_project_expenses_list(): void
    {
        $this->makeOfficeExpense(['description' => 'Office electricity bill']);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.expenses.index'));

        $response->assertOk();
        $response->assertDontSee('Office electricity bill');
    }

    // -------------------------------------------------------------------------
    // Navigation visibility
    // -------------------------------------------------------------------------

    public function test_office_expenses_nav_link_appears_in_finance_sidebar(): void
    {
        // The Finance sidebar is rendered inside the admin layout on any finance page.
        // The easiest surface to assert it on is the Finance dashboard.
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.dashboard'));

        $response->assertOk();
        // The sidebar must contain the "Office Expenses" label
        $response->assertSee('Office Expenses');
        // And the href pointing to the index route
        $response->assertSee(route('finance.office-expenses.index'));
    }

    public function test_office_expenses_index_is_reachable_via_named_route(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.office-expenses.index'));

        $response->assertOk();
    }
}
