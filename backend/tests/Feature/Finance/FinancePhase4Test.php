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
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancePhase4Test extends TestCase
{
    use RefreshDatabase;

    public function test_finance_routes_require_explicit_finance_view_permission(): void
    {
        $admin = $this->createAdmin();
        $fieldStaff = $this->createUser(['role' => 'field_staff']);
        $coordinator = $this->createUser(['role' => 'field_coordinator']);
        $financeUser = $this->createAdmin();
        $this->grantFinance($financeUser, [FinancePermission::VIEW]);

        $this->actingAs($admin)->get('/finance')->assertForbidden();
        $this->actingAs($fieldStaff)->get('/finance')->assertForbidden();
        $this->actingAs($coordinator)->get('/finance')->assertForbidden();
        $this->actingAs($financeUser)->get('/finance')->assertOk();
    }

    public function test_finance_navigation_is_visible_only_to_authorized_users(): void
    {
        $admin = $this->createAdmin();
        $financeUser = $this->createAdmin();
        $this->grantFinance($financeUser, [FinancePermission::VIEW]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Finance');

        $this->actingAs($financeUser)
            ->get(route('finance.dashboard'))
            ->assertOk()
            ->assertSee('Finance')
            ->assertDontSee('Products');
    }

    public function test_admin_can_approve_user_as_finance_role(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser(['status' => 'pending']);

        $this->actingAs($admin)
            ->patch(route('admin.users.approve', ['user' => $user, 'role' => 'finance']))
            ->assertRedirect();

        $this->assertSame('finance', $user->fresh()->role);
        $this->assertTrue($user->fresh()->hasFinancePermission(FinancePermission::VIEW));
        $this->assertTrue($user->fresh()->hasFinancePermission(FinancePermission::APPROVE));
    }

    public function test_admin_cannot_approve_or_delete_finance_expenses(): void
    {
        $admin = $this->createAdmin();
        $financeUser = $this->createUser(['role' => 'finance']);
        $expense = $this->createProjectExpense($financeUser, FinancialExpense::STATUS_PENDING);

        $this->actingAs($admin)
            ->post(route('finance.expenses.approve', $expense))
            ->assertForbidden();

        $this->actingAs($admin)
            ->delete(route('finance.expenses.destroy', $expense))
            ->assertForbidden();
    }

    public function test_authorized_user_can_create_and_approver_can_approve_transportation_expense(): void
    {
        $creator = $this->createAdmin();
        $approver = $this->createAdmin();
        $this->grantFinance($creator, [FinancePermission::VIEW, FinancePermission::CREATE]);
        $this->grantFinance($approver, [FinancePermission::VIEW, FinancePermission::APPROVE]);

        $client = $this->createClient();
        $category = FinanceExpenseCategory::where('slug', 'transportation')->firstOrFail();
        $inspection = \App\Models\Inspection::create([
            'inspection_code' => 'INS-' . uniqid(),
            'client_id' => $client->id,
            'title' => 'Transport inspection',
            'location' => 'Lagos',
            'status' => 'assigned',
            'created_by' => $creator->id,
        ]);

        $this->actingAs($creator)
            ->post(route('finance.expenses.store'), [
                'context_type' => 'inspection',
                'inspection_id' => $inspection->id,
                'finance_expense_category_id' => $category->id,
                'description' => 'Transportation',
                'amount' => 3500,
                'incurred_on' => now()->toDateString(),
                'status' => FinancialExpense::STATUS_PENDING,
            ])
            ->assertRedirect();

        $expense = FinancialExpense::where('inspection_id', $inspection->id)->firstOrFail();

        $this->actingAs($approver)
            ->post(route('finance.expenses.approve', $expense))
            ->assertRedirect(route('finance.expenses.show', $expense));

        $this->assertDatabaseHas('financial_expenses', [
            'id' => $expense->id,
            'status' => FinancialExpense::STATUS_APPROVED,
            'approved_by' => $approver->id,
        ]);
    }

    public function test_project_dashboard_calculations_include_only_approved_finance_records_and_exclude_pos_orders(): void
    {
        $financeUser = $this->createAdmin();
        $this->grantFinance($financeUser, [FinancePermission::VIEW]);
        $project = $this->createProject($financeUser);
        $category = FinanceExpenseCategory::where('slug', 'transportation')->firstOrFail();

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 10000,
            'approved_budget' => 7000,
            'created_by' => $financeUser->id,
            'updated_by' => $financeUser->id,
        ]);

        FinancialExpense::create([
            'project_id' => $project->id,
            'original_context_type' => Project::class,
            'original_context_id' => $project->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Approved expense',
            'amount' => 1000,
            'status' => FinancialExpense::STATUS_APPROVED,
        ]);

        FinancialExpense::create([
            'project_id' => $project->id,
            'original_context_type' => Project::class,
            'original_context_id' => $project->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Pending expense',
            'amount' => 2000,
            'status' => FinancialExpense::STATUS_PENDING,
        ]);

        FinancialMaterialCost::create([
            'project_id' => $project->id,
            'original_context_type' => Project::class,
            'original_context_id' => $project->id,
            'material_name' => 'Approved cable',
            'quantity' => 2,
            'unit_cost' => 1500,
            'total_cost' => 3000,
            'status' => FinancialMaterialCost::STATUS_APPROVED,
        ]);

        FinancialMaterialCost::create([
            'project_id' => $project->id,
            'original_context_type' => Project::class,
            'original_context_id' => $project->id,
            'material_name' => 'Rejected cable',
            'quantity' => 1,
            'unit_cost' => 500,
            'total_cost' => 500,
            'status' => FinancialMaterialCost::STATUS_REJECTED,
        ]);

        Order::create([
            'code' => 'SEC-' . uniqid(),
            'user_id' => $financeUser->id,
            'channel' => 'pos',
            'total_amount' => 1000000,
            'status' => 'completed',
        ]);

        $this->actingAs($financeUser)
            ->get(route('finance.projects.show', $project))
            ->assertOk()
            ->assertSee('₦4,000.00')
            ->assertSee('₦6,000.00')
            ->assertDontSee('₦1,006,000.00');
    }

    public function test_job_pre_project_finance_attaches_to_project_on_conversion_without_duplication(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClient();
        $category = FinanceExpenseCategory::where('slug', 'transportation')->firstOrFail();
        $serviceCategory = ServiceCategory::create([
            'name' => 'CCTV',
            'description' => 'CCTV installation',
            'is_active' => true,
        ]);
        $jobRequest = JobRequest::create([
            'client_id' => $client->id,
            'title' => 'Install CCTV',
            'created_by' => $admin->id,
            'status' => 'open',
        ]);
        $jobItem = JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $serviceCategory->id,
            'title' => 'CCTV installation',
            'status' => JobRequestItem::STATUS_APPROVED,
            'priority' => 'medium',
            'created_by' => $admin->id,
        ]);

        $expense = FinancialExpense::create([
            'job_request_item_id' => $jobItem->id,
            'original_context_type' => JobRequestItem::class,
            'original_context_id' => $jobItem->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Transportation',
            'amount' => 4000,
            'status' => FinancialExpense::STATUS_APPROVED,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.job-items.convert-to-project', $jobItem))
            ->assertRedirect();

        $project = Project::where('job_request_item_id', $jobItem->id)->firstOrFail();

        $this->assertSame(1, FinancialExpense::where('job_request_item_id', $jobItem->id)->count());
        $this->assertDatabaseHas('financial_expenses', [
            'id' => $expense->id,
            'project_id' => $project->id,
            'job_request_item_id' => $jobItem->id,
            'original_context_type' => JobRequestItem::class,
            'original_context_id' => $jobItem->id,
        ]);
    }

    private function grantFinance(User $user, array $permissions): void
    {
        $user->update(['role' => 'finance']);

        $ids = FinancePermission::query()
            ->whereIn('slug', $permissions)
            ->pluck('id')
            ->all();

        $user->financePermissions()->syncWithoutDetaching(
            collect($ids)->mapWithKeys(fn (int $id) => [$id => ['granted_at' => now()]])->all()
        );
    }

    private function createClient(array $overrides = []): Client
    {
        return Client::create(array_merge([
            'client_name' => 'Acme Security',
            'phone' => '08000000000',
            'email' => 'client@example.com',
            'status' => 'active',
        ], $overrides));
    }

    private function createProject(User $creator, array $overrides = []): Project
    {
        $client = $overrides['client'] ?? $this->createClient();
        unset($overrides['client']);

        return Project::create(array_merge([
            'project_code' => 'PROJ-' . uniqid(),
            'client_id' => $client->id,
            'title' => 'CCTV Installation',
            'location' => 'Lagos',
            'status' => 'not_started',
            'priority' => 'medium',
            'created_by' => $creator->id,
        ], $overrides));
    }

    private function createProjectExpense(User $user, string $status): FinancialExpense
    {
        $project = $this->createProject($user);
        $category = FinanceExpenseCategory::where('slug', 'transportation')->firstOrFail();

        return FinancialExpense::create([
            'project_id' => $project->id,
            'original_context_type' => Project::class,
            'original_context_id' => $project->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Project transport',
            'amount' => 1500,
            'status' => $status,
            'submitted_by' => $user->id,
        ]);
    }
}
