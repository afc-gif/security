<?php

namespace Tests\Feature\Finance;

use App\Models\Client;
use App\Models\FinanceExpenseCategory;
use App\Models\FinancePermission;
use App\Models\FinancialExpense;
use App\Models\FinancialMaterialCost;
use App\Models\JobRequest;
use App\Models\JobRequestItem;
use App\Models\Project;
use App\Models\ProjectFinancial;
use App\Models\ProjectPayment;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinancePhase6Test extends TestCase
{
    use RefreshDatabase;

    private function grantFinanceUser(): User
    {
        foreach ([FinancePermission::VIEW, FinancePermission::CREATE, FinancePermission::EDIT, FinancePermission::APPROVE, FinancePermission::DELETE] as $perm) {
            FinancePermission::firstOrCreate(
                ['slug' => $perm],
                ['name' => ucfirst(str_replace('.', ' ', $perm))]
            );
        }

        FinanceExpenseCategory::firstOrCreate(
            ['slug' => 'transportation'],
            ['name' => 'Transportation', 'is_active' => true, 'sort_order' => 1]
        );

        $user = $this->createUser(['role' => 'finance']);
        $ids = FinancePermission::query()->pluck('id')->all();
        $user->financePermissions()->syncWithoutDetaching(
            collect($ids)->mapWithKeys(fn (int $id) => [$id => ['granted_at' => now()]])->all()
        );

        return $user;
    }

    private function createProject(array $overrides = []): Project
    {
        $client = Client::create([
            'client_code' => 'CLI-' . uniqid(),
            'client_name' => 'Acme Security Client',
            'company_name' => 'Acme Corp',
            'status' => 'active',
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        return Project::create(array_merge([
            'project_code' => 'PRJ-' . uniqid(),
            'title' => 'CCTV & Perimeter Project',
            'client_id' => $client->id,
            'status' => 'ongoing',
            'created_by' => 1,
            'updated_by' => 1,
        ], $overrides));
    }

    public function test_finance_user_can_view_projects_list_with_pagination_and_no_technical_ids(): void
    {
        $financeUser = $this->grantFinanceUser();
        $project = $this->createProject(['title' => 'HQ CCTV Project']);

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 5000000.00,
            'created_by' => $financeUser->id,
            'updated_by' => $financeUser->id,
        ]);

        $this->actingAs($financeUser)
            ->get(route('finance.projects.index'))
            ->assertOk()
            ->assertSee('HQ CCTV Project')
            ->assertSee('Acme Corp')
            ->assertSee('₦5,000,000.00')
            ->assertDontSee('job_request_item_id')
            ->assertDontSee('original_context_type');
    }

    public function test_finance_user_can_view_project_show_page_financial_summary(): void
    {
        $financeUser = $this->grantFinanceUser();
        $project = $this->createProject();

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 5000000.00,
            'created_by' => $financeUser->id,
            'updated_by' => $financeUser->id,
        ]);

        $this->actingAs($financeUser)
            ->get(route('finance.projects.show', $project))
            ->assertOk()
            ->assertSee($project->title)
            ->assertSee('Project Value')
            ->assertSee('₦5,000,000.00')
            ->assertSee('Amount Paid')
            ->assertSee('Balance Due')
            ->assertSee('Total Spent')
            ->assertSee('Estimated Profit');
    }

    public function test_finance_user_can_enter_and_update_project_value(): void
    {
        $financeUser = $this->grantFinanceUser();
        $project = $this->createProject();

        $this->actingAs($financeUser)
            ->post(route('finance.projects.financial.save', $project), [
                'contract_value' => 7500000.00,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('project_financials', [
            'project_id' => $project->id,
            'contract_value' => 7500000.00,
        ]);
    }

    public function test_finance_user_can_record_payment_and_calculate_balance_due(): void
    {
        Storage::fake('local');
        $financeUser = $this->grantFinanceUser();
        $project = $this->createProject();

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 5000000.00,
            'created_by' => $financeUser->id,
            'updated_by' => $financeUser->id,
        ]);

        $receipt = UploadedFile::fake()->create('receipt.pdf', 500, 'application/pdf');

        $this->actingAs($financeUser)
            ->post(route('finance.projects.payments.store', $project), [
                'amount' => 2000000.00,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'bank_transfer',
                'reference' => 'INV-2026-001',
                'notes' => 'First milestone payment',
                'receipt' => $receipt,
            ])
            ->assertRedirect(route('finance.projects.show', $project));

        $this->assertDatabaseHas('project_payments', [
            'project_id' => $project->id,
            'amount' => 2000000.00,
            'payment_method' => 'bank_transfer',
            'reference' => 'INV-2026-001',
        ]);

        // Balance due should now be 5,000,000 - 2,000,000 = 3,000,000
        $this->actingAs($financeUser)
            ->get(route('finance.projects.show', $project))
            ->assertOk()
            ->assertSee('₦2,000,000.00') // Amount Paid
            ->assertSee('₦3,000,000.00'); // Balance Due
    }

    public function test_overpayment_is_handled_gracefully(): void
    {
        $financeUser = $this->grantFinanceUser();
        $project = $this->createProject();

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 1000000.00,
            'created_by' => $financeUser->id,
            'updated_by' => $financeUser->id,
        ]);

        ProjectPayment::create([
            'project_id' => $project->id,
            'amount' => 1200000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'recorded_by' => $financeUser->id,
            'created_by' => $financeUser->id,
            'updated_by' => $financeUser->id,
        ]);

        $this->actingAs($financeUser)
            ->get(route('finance.projects.show', $project))
            ->assertOk()
            ->assertSee('Overpaid (₦200,000.00)');
    }

    public function test_total_spent_and_estimated_profit_are_calculated_correctly(): void
    {
        $financeUser = $this->grantFinanceUser();
        $project = $this->createProject();
        $category = FinanceExpenseCategory::where('slug', 'transportation')->firstOrFail();

        ProjectFinancial::create([
            'project_id' => $project->id,
            'contract_value' => 5000000.00,
            'created_by' => $financeUser->id,
            'updated_by' => $financeUser->id,
        ]);

        FinancialExpense::create([
            'project_id' => $project->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Site Logistics',
            'amount' => 350000.00,
            'status' => FinancialExpense::STATUS_APPROVED,
            'submitted_by' => $financeUser->id,
            'created_by' => $financeUser->id,
            'updated_by' => $financeUser->id,
        ]);

        FinancialMaterialCost::create([
            'project_id' => $project->id,
            'material_name' => 'Cat6 Outdoor Cable',
            'quantity' => 10,
            'unit' => 'rolls',
            'unit_cost' => 150000.00,
            'total_cost' => 1500000.00,
            'status' => FinancialMaterialCost::STATUS_APPROVED,
            'submitted_by' => $financeUser->id,
            'created_by' => $financeUser->id,
            'updated_by' => $financeUser->id,
        ]);

        // Total Spent = 350,000 + 1,500,000 = 1,850,000
        // Estimated Profit = 5,000,000 - 1,850,000 = 3,150,000
        $this->actingAs($financeUser)
            ->get(route('finance.projects.show', $project))
            ->assertOk()
            ->assertSee('₦1,850,000.00') // Total Spent
            ->assertSee('₦3,150,000.00'); // Estimated Profit
    }

    public function test_job_expenses_are_preserved_when_job_converts_to_project(): void
    {
        $financeUser = $this->grantFinanceUser();
        $adminUser = $this->createAdmin();

        $client = Client::create([
            'client_code' => 'CLI-' . uniqid(),
            'client_name' => 'Conversion Client',
            'company_name' => 'Convert Inc',
            'status' => 'active',
            'created_by' => $adminUser->id,
            'updated_by' => $adminUser->id,
        ]);

        $serviceCategory = ServiceCategory::create([
            'name' => 'Security Survey',
            'slug' => 'security-survey-' . uniqid(),
            'is_active' => true,
        ]);

        $jobRequest = JobRequest::create([
            'request_number' => 'REQ-' . uniqid(),
            'client_id' => $client->id,
            'title' => 'Conversion Job Request',
            'status' => 'approved',
            'created_by' => $adminUser->id,
            'updated_by' => $adminUser->id,
        ]);

        $jobItem = JobRequestItem::create([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $serviceCategory->id,
            'title' => 'Initial Site Setup Job',
            'status' => JobRequestItem::STATUS_APPROVED,
            'created_by' => $adminUser->id,
            'updated_by' => $adminUser->id,
        ]);

        $category = FinanceExpenseCategory::where('slug', 'transportation')->firstOrFail();

        $expense = FinancialExpense::create([
            'job_request_item_id' => $jobItem->id,
            'original_context_type' => JobRequestItem::class,
            'original_context_id' => $jobItem->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Initial Job Transport',
            'amount' => 80000.00,
            'status' => FinancialExpense::STATUS_APPROVED,
            'submitted_by' => $financeUser->id,
            'created_by' => $financeUser->id,
            'updated_by' => $financeUser->id,
        ]);

        // Convert Job to Project by acting as admin and calling conversion route
        $this->actingAs($adminUser)
            ->post(route('admin.job-items.convert-to-project', $jobItem))
            ->assertRedirect();

        $project = Project::latest('id')->first();
        $this->assertNotNull($project);

        // Expense should now belong to Project while retaining job_request_item_id
        $expense->refresh();
        $this->assertEquals($project->id, $expense->project_id);
        $this->assertEquals($jobItem->id, $expense->job_request_item_id);

        // Verify expense total under project is still 80,000 (not duplicated)
        $this->actingAs($financeUser)
            ->get(route('finance.projects.show', $project))
            ->assertOk()
            ->assertSee('₦80,000.00');
    }

    public function test_non_finance_users_are_forbidden_from_finance_projects(): void
    {
        $fieldStaff = $this->createUser(['role' => 'field_staff']);
        $coordinator = $this->createUser(['role' => 'field_coordinator']);
        $posUser = $this->createPosUser();
        $project = $this->createProject();

        $this->actingAs($fieldStaff)->get(route('finance.projects.index'))->assertForbidden();
        $this->actingAs($coordinator)->get(route('finance.projects.index'))->assertForbidden();
        $this->actingAs($posUser)->get(route('finance.projects.index'))->assertForbidden();

        $this->actingAs($fieldStaff)->get(route('finance.projects.show', $project))->assertForbidden();
        $this->actingAs($coordinator)->get(route('finance.projects.show', $project))->assertForbidden();
        $this->actingAs($posUser)->get(route('finance.projects.show', $project))->assertForbidden();
    }
}
