<?php

namespace Tests\Feature\Finance;

use App\Models\Client;
use App\Models\FinanceExpenseCategory;
use App\Models\FinancialExpense;
use App\Models\JobRequest;
use App\Models\JobRequestItem;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceJobsRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_user_can_find_and_open_existing_jobs(): void
    {
        $financeUser = $this->createUser(['role' => 'finance']);
        $jobItem = $this->createJobItem(['title' => 'ABC Residence Inspection']);

        $this->actingAs($financeUser)
            ->get(route('finance.jobs.index', ['search' => 'ABC']))
            ->assertOk()
            ->assertSee('ABC Residence Inspection')
            ->assertSee('Open')
            ->assertDontSee('Context Type')
            ->assertDontSee('Job Request Item ID');

        $this->actingAs($financeUser)
            ->get(route('finance.jobs.show', $jobItem))
            ->assertOk()
            ->assertSee('ABC Residence Inspection')
            ->assertSee('Add Expense')
            ->assertDontSee('context_type')
            ->assertDontSee('inspection_id')
            ->assertDontSee('project_id');
    }

    public function test_job_finance_total_uses_all_job_expenses(): void
    {
        $financeUser = $this->createUser(['role' => 'finance']);
        $jobItem = $this->createJobItem();
        $category = FinanceExpenseCategory::where('slug', 'transportation')->firstOrFail();

        FinancialExpense::create([
            'job_request_item_id' => $jobItem->id,
            'original_context_type' => JobRequestItem::class,
            'original_context_id' => $jobItem->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Approved transport',
            'amount' => 20000,
            'status' => FinancialExpense::STATUS_APPROVED,
            'submitted_by' => $financeUser->id,
        ]);

        FinancialExpense::create([
            'job_request_item_id' => $jobItem->id,
            'original_context_type' => JobRequestItem::class,
            'original_context_id' => $jobItem->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Pending fuel',
            'amount' => 10000,
            'status' => FinancialExpense::STATUS_PENDING,
            'submitted_by' => $financeUser->id,
        ]);

        $this->actingAs($financeUser)
            ->get(route('finance.jobs.show', $jobItem))
            ->assertOk()
            ->assertSee('₦30,000.00')
            ->assertSee('Approved')
            ->assertSee('Pending')
            ->assertDontSee('Pending: ₦10,000.00');
    }

    public function test_finance_user_can_add_expense_directly_to_opened_job(): void
    {
        $financeUser = $this->createUser(['role' => 'finance']);
        $jobItem = $this->createJobItem();
        $category = FinanceExpenseCategory::where('slug', 'transportation')->firstOrFail();

        $this->actingAs($financeUser)
            ->post(route('finance.jobs.expenses.store', $jobItem), [
                'finance_expense_category_id' => $category->id,
                'description' => 'Transportation to site',
                'amount' => 20000,
                'incurred_on' => now()->toDateString(),
            ])
            ->assertRedirect(route('finance.jobs.show', $jobItem));

        $this->assertDatabaseHas('financial_expenses', [
            'job_request_item_id' => $jobItem->id,
            'project_id' => null,
            'inspection_id' => null,
            'original_context_type' => JobRequestItem::class,
            'original_context_id' => $jobItem->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Transportation to site',
            'amount' => 20000,
            'status' => FinancialExpense::STATUS_PENDING,
            'submitted_by' => $financeUser->id,
        ]);
    }

    public function test_non_finance_users_cannot_access_finance_jobs(): void
    {
        $jobItem = $this->createJobItem();

        foreach (['admin', 'field_staff', 'field_coordinator', 'user'] as $role) {
            $this->actingAs($this->createUser(['role' => $role]))
                ->get(route('finance.jobs.show', $jobItem))
                ->assertForbidden();
        }
    }

    public function test_overview_shows_only_three_recent_jobs(): void
    {
        $financeUser = $this->createUser(['role' => 'finance']);

        $this->createJobItem(['title' => 'Old Job'])
            ->forceFill(['created_at' => now()->subDays(4), 'updated_at' => now()->subDays(4)])
            ->save();
        $this->createJobItem(['title' => 'Recent Job One'])
            ->forceFill(['created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)])
            ->save();
        $this->createJobItem(['title' => 'Recent Job Two'])
            ->forceFill(['created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)])
            ->save();
        $this->createJobItem(['title' => 'Recent Job Three'])
            ->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])
            ->save();

        $this->actingAs($financeUser)
            ->get(route('finance.dashboard'))
            ->assertOk()
            ->assertSee('Recent Job One')
            ->assertSee('Recent Job Two')
            ->assertSee('Recent Job Three')
            ->assertDontSee('Old Job');
    }

    public function test_finance_user_can_add_expense_directly_to_opened_project(): void
    {
        $financeUser = $this->createUser(['role' => 'finance']);
        $project = $this->createProject($financeUser);
        $category = FinanceExpenseCategory::where('slug', 'fuel')->firstOrFail();

        $this->actingAs($financeUser)
            ->post(route('finance.projects.expenses.store', $project), [
                'finance_expense_category_id' => $category->id,
                'description' => 'Generator fuel',
                'amount' => 15000,
                'incurred_on' => now()->toDateString(),
            ])
            ->assertRedirect(route('finance.projects.show', $project));

        $this->assertDatabaseHas('financial_expenses', [
            'project_id' => $project->id,
            'job_request_item_id' => null,
            'inspection_id' => null,
            'original_context_type' => Project::class,
            'original_context_id' => $project->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Generator fuel',
            'amount' => 15000,
            'status' => FinancialExpense::STATUS_PENDING,
            'submitted_by' => $financeUser->id,
        ]);
    }

    private function createJobItem(array $overrides = []): JobRequestItem
    {
        $admin = $this->createAdmin();
        $fieldStaff = $this->createUser(['role' => 'field_staff', 'name' => 'Chinedu']);
        $client = Client::create([
            'client_name' => 'ABC Limited',
            'company_name' => 'ABC Limited',
            'contact_person' => 'Ada',
            'phone' => '08000000000',
            'email' => 'client@example.com',
            'address' => '12 Marina Road',
            'city_state' => 'Lagos',
            'status' => 'active',
        ]);
        $serviceCategory = ServiceCategory::firstOrCreate(
            ['name' => 'Inspection'],
            [
                'description' => 'Site inspection',
                'is_active' => true,
            ]
        );
        $jobRequest = JobRequest::create([
            'client_id' => $client->id,
            'title' => 'ABC Residence',
            'created_by' => $admin->id,
            'status' => 'open',
        ]);

        return JobRequestItem::create(array_merge([
            'job_request_id' => $jobRequest->id,
            'service_category_id' => $serviceCategory->id,
            'title' => 'ABC Residence Inspection',
            'description' => 'Inspect residence security requirements',
            'claimed_by' => $fieldStaff->id,
            'status' => JobRequestItem::STATUS_CLAIMED,
            'priority' => JobRequestItem::PRIORITY_MEDIUM,
            'created_by' => $admin->id,
        ], $overrides));
    }

    private function createProject(User $creator, array $overrides = []): Project
    {
        $client = Client::create([
            'client_name' => 'XYZ Limited',
            'company_name' => 'XYZ Limited',
            'phone' => '08000000001',
            'email' => 'xyz@example.com',
            'status' => 'active',
        ]);

        return Project::create(array_merge([
            'project_code' => 'PROJ-' . uniqid(),
            'client_id' => $client->id,
            'title' => 'Network Installation',
            'location' => 'Lagos',
            'status' => 'ongoing',
            'priority' => 'medium',
            'created_by' => $creator->id,
        ], $overrides));
    }
}
