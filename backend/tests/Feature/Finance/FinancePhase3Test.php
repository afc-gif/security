<?php

namespace Tests\Feature\Finance;

use App\Models\Client;
use App\Models\FinanceExpenseCategory;
use App\Models\FinancePermission;
use App\Models\FinancialExpense;
use App\Models\FinancialMaterialCost;
use App\Models\Inspection;
use App\Models\Project;
use App\Models\ProjectFinancial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancePhase3Test extends TestCase
{
    use RefreshDatabase;

    public function test_project_finance_requires_explicit_finance_permission(): void
    {
        $admin = $this->createAdmin();
        $project = $this->createProject($admin);

        $this->actingAs($admin)
            ->get(route('finance.projects.show', $project))
            ->assertForbidden();

        $this->grantFinance($admin, [FinancePermission::VIEW]);

        $this->actingAs($admin)
            ->get(route('finance.projects.show', $project))
            ->assertOk()
            ->assertSee($project->project_code);
    }

    public function test_project_finance_summary_counts_only_approved_expenses_and_materials(): void
    {
        $financeUser = $this->createAdmin();
        $this->grantFinance($financeUser, [
            FinancePermission::VIEW,
            FinancePermission::CREATE,
            FinancePermission::APPROVE,
        ]);

        $project = $this->createProject($financeUser);
        $category = FinanceExpenseCategory::where('slug', 'transportation')->firstOrFail();

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 10000,
            'approved_budget' => 5000,
            'created_by' => $financeUser->id,
            'updated_by' => $financeUser->id,
        ]);

        FinancialExpense::create([
            'project_id' => $project->id,
            'original_context_type' => Project::class,
            'original_context_id' => $project->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Approved transport',
            'amount' => 1000,
            'status' => FinancialExpense::STATUS_APPROVED,
            'submitted_by' => $financeUser->id,
            'approved_by' => $financeUser->id,
            'approved_at' => now(),
        ]);

        FinancialExpense::create([
            'project_id' => $project->id,
            'original_context_type' => Project::class,
            'original_context_id' => $project->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Pending transport',
            'amount' => 800,
            'status' => FinancialExpense::STATUS_PENDING,
            'submitted_by' => $financeUser->id,
        ]);

        FinancialMaterialCost::create([
            'project_id' => $project->id,
            'original_context_type' => Project::class,
            'original_context_id' => $project->id,
            'material_name' => 'Cable',
            'quantity' => 3,
            'unit' => 'rolls',
            'unit_cost' => 1000,
            'total_cost' => 3000,
            'status' => FinancialMaterialCost::STATUS_APPROVED,
            'submitted_by' => $financeUser->id,
            'approved_by' => $financeUser->id,
            'approved_at' => now(),
        ]);

        FinancialMaterialCost::create([
            'project_id' => $project->id,
            'original_context_type' => Project::class,
            'original_context_id' => $project->id,
            'material_name' => 'Rejected bracket',
            'quantity' => 2,
            'unit_cost' => 600,
            'total_cost' => 1200,
            'status' => FinancialMaterialCost::STATUS_REJECTED,
            'submitted_by' => $financeUser->id,
        ]);

        $this->actingAs($financeUser)
            ->get(route('finance.projects.show', $project))
            ->assertOk()
            ->assertSee('₦4,000.00')
            ->assertSee('Remaining budget ₦1,000.00')
            ->assertSee('₦6,000.00')
            ->assertDontSee('₦5,200.00');
    }

    public function test_project_expense_context_is_private_and_project_scoped(): void
    {
        $financeUser = $this->createAdmin();
        $this->grantFinance($financeUser, [
            FinancePermission::VIEW,
            FinancePermission::CREATE,
        ]);

        $project = $this->createProject($financeUser);
        $category = FinanceExpenseCategory::where('slug', 'transportation')->firstOrFail();

        $this->actingAs($financeUser)
            ->post(route('finance.expenses.store'), [
                'context_type' => 'project',
                'project_id' => $project->id,
                'finance_expense_category_id' => $category->id,
                'description' => 'Project site transport',
                'amount' => 1500,
                'incurred_on' => now()->toDateString(),
                'status' => FinancialExpense::STATUS_PENDING,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('financial_expenses', [
            'project_id' => $project->id,
            'inspection_id' => null,
            'job_request_item_id' => null,
            'original_context_type' => Project::class,
            'original_context_id' => $project->id,
            'description' => 'Project site transport',
        ]);
    }

    public function test_material_cost_rejects_negative_values_and_calculates_total(): void
    {
        $financeUser = $this->createAdmin();
        $this->grantFinance($financeUser, [
            FinancePermission::VIEW,
            FinancePermission::CREATE,
        ]);

        $project = $this->createProject($financeUser);

        $this->actingAs($financeUser)
            ->from(route('finance.material-costs.create', $project))
            ->post(route('finance.material-costs.store', $project), [
                'material_name' => 'Cable',
                'quantity' => -1,
                'unit_cost' => -200,
                'status' => FinancialMaterialCost::STATUS_PENDING,
            ])
            ->assertRedirect(route('finance.material-costs.create', $project))
            ->assertSessionHasErrors(['quantity', 'unit_cost']);

        $this->actingAs($financeUser)
            ->post(route('finance.material-costs.store', $project), [
                'material_name' => 'Cable',
                'quantity' => 2.5,
                'unit' => 'rolls',
                'unit_cost' => 1200,
                'status' => FinancialMaterialCost::STATUS_PENDING,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('financial_material_costs', [
            'project_id' => $project->id,
            'material_name' => 'Cable',
            'quantity' => 2.5,
            'unit_cost' => 1200,
            'total_cost' => 3000,
        ]);
    }

    public function test_inspection_conversion_associates_pre_project_finance_without_losing_original_context(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClient();
        $inspection = Inspection::create([
            'inspection_code' => 'INS-' . uniqid(),
            'client_id' => $client->id,
            'title' => 'Warehouse inspection',
            'location' => 'Lagos',
            'assigned_to' => null,
            'status' => 'approved',
            'priority' => 'high',
            'created_by' => $admin->id,
        ]);
        $category = FinanceExpenseCategory::where('slug', 'transportation')->firstOrFail();

        $expense = FinancialExpense::create([
            'inspection_id' => $inspection->id,
            'original_context_type' => Inspection::class,
            'original_context_id' => $inspection->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Inspection transport',
            'amount' => 2500,
            'status' => FinancialExpense::STATUS_APPROVED,
            'submitted_by' => $admin->id,
        ]);

        $materialCost = FinancialMaterialCost::create([
            'inspection_id' => $inspection->id,
            'original_context_type' => Inspection::class,
            'original_context_id' => $inspection->id,
            'material_name' => 'Test cable',
            'quantity' => 1,
            'unit_cost' => 500,
            'total_cost' => 500,
            'status' => FinancialMaterialCost::STATUS_PENDING,
            'submitted_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.inspections.convert-to-project', $inspection))
            ->assertRedirect();

        $project = Project::where('inspection_id', $inspection->id)->firstOrFail();

        $this->assertDatabaseHas('financial_expenses', [
            'id' => $expense->id,
            'project_id' => $project->id,
            'inspection_id' => $inspection->id,
            'original_context_type' => Inspection::class,
            'original_context_id' => $inspection->id,
        ]);

        $this->assertDatabaseHas('financial_material_costs', [
            'id' => $materialCost->id,
            'project_id' => $project->id,
            'inspection_id' => $inspection->id,
            'original_context_type' => Inspection::class,
            'original_context_id' => $inspection->id,
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
}
