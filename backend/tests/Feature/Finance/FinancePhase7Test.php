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

class FinancePhase7Test extends TestCase
{
    use RefreshDatabase;

    private User $financeUser;
    private User $normalUser;
    private Project $project;

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

        $client = Client::create([
            'client_code' => 'CLI-' . uniqid(),
            'client_name' => 'Alpha Client',
            'company_name' => 'Alpha Corp',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);

        $this->project = Project::create([
            'project_code' => 'PRJ-ALPHA-01',
            'client_id' => $client->id,
            'title' => 'Alpha Tower Surveillance',
            'status' => 'ongoing',
            'created_by' => $this->financeUser->id,
        ]);

        ProjectFinancial::create([
            'project_id' => $this->project->id,
            'contract_value' => 5000000.00,
            'approved_budget' => 4000000.00,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ]);
    }

    private function createPayment(array $attributes = []): ProjectPayment
    {
        return ProjectPayment::create(array_merge([
            'project_id' => $this->project->id,
            'amount' => 1000000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'recorded_by' => $this->financeUser->id,
            'created_by' => $this->financeUser->id,
            'updated_by' => $this->financeUser->id,
        ], $attributes));
    }

    public function test_1_finance_user_can_view_project_payments(): void
    {
        $this->createPayment([
            'amount' => 1000000.00,
            'reference' => 'REF-1001',
        ]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.projects.show', $this->project));

        $response->assertOk();
        $response->assertSee('REF-1001');
        $response->assertSee('1,000,000.00');
    }

    public function test_2_non_finance_user_cannot_view_project_payments(): void
    {
        $response = $this->actingAs($this->normalUser)
            ->get(route('finance.projects.show', $this->project));

        $response->assertForbidden();
    }

    public function test_3_finance_user_can_record_a_payment(): void
    {
        $response = $this->actingAs($this->financeUser)
            ->post(route('finance.projects.payments.store', $this->project), [
                'amount' => 1500000.00,
                'payment_date' => '2026-08-12',
                'payment_method' => 'bank_transfer',
                'reference' => 'DEP-2026-88',
                'notes' => 'Initial 30% advance payment',
            ]);

        $response->assertRedirect(route('finance.projects.show', $this->project));

        $this->assertDatabaseHas('project_payments', [
            'project_id' => $this->project->id,
            'amount' => 1500000.00,
            'payment_method' => 'bank_transfer',
            'reference' => 'DEP-2026-88',
            'recorded_by' => $this->financeUser->id,
        ]);
    }

    public function test_4_payment_total_is_calculated_correctly(): void
    {
        $this->createPayment(['amount' => 1200000.00, 'payment_method' => 'bank_transfer']);
        $this->createPayment(['amount' => 800000.00, 'payment_method' => 'cash']);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.projects.show', $this->project));

        $response->assertOk();
        $response->assertSee('2,000,000.00');
    }

    public function test_5_balance_due_is_calculated_correctly(): void
    {
        $this->createPayment(['amount' => 3000000.00]);

        // Contract value = 5,000,000. Total paid = 3,000,000. Balance due = 2,000,000.
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.projects.show', $this->project));

        $response->assertOk();
        $response->assertSee('2,000,000.00');
    }

    public function test_6_multiple_payments_are_summed_correctly(): void
    {
        $this->createPayment(['amount' => 1000000.00, 'payment_date' => '2026-08-01', 'payment_method' => 'bank_transfer']);
        $this->createPayment(['amount' => 1500000.00, 'payment_date' => '2026-08-05', 'payment_method' => 'pos']);
        $this->createPayment(['amount' => 500000.00, 'payment_date' => '2026-08-10', 'payment_method' => 'cash']);

        $this->assertEquals(3000000.00, $this->project->payments()->sum('amount'));
    }

    public function test_7_editing_a_payment_updates_totals(): void
    {
        $payment = $this->createPayment(['amount' => 1000000.00]);

        $response = $this->actingAs($this->financeUser)
            ->put(route('finance.projects.payments.update', [$this->project, $payment]), [
                'amount' => 2500000.00,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'bank_transfer',
                'reference' => 'CORRECTED-REF',
            ]);

        $response->assertRedirect(route('finance.projects.show', $this->project));

        $this->assertDatabaseHas('project_payments', [
            'id' => $payment->id,
            'amount' => 2500000.00,
            'reference' => 'CORRECTED-REF',
        ]);

        $this->assertEquals(2500000.00, $this->project->payments()->sum('amount'));
    }

    public function test_8_deleting_a_payment_updates_totals(): void
    {
        $payment1 = $this->createPayment(['amount' => 1000000.00]);
        $payment2 = $this->createPayment(['amount' => 500000.00]);

        $response = $this->actingAs($this->financeUser)
            ->delete(route('finance.projects.payments.destroy', [$this->project, $payment2]));

        $response->assertRedirect(route('finance.projects.show', $this->project));

        $this->assertDatabaseMissing('project_payments', ['id' => $payment2->id]);
        $this->assertEquals(1000000.00, $this->project->payments()->sum('amount'));
    }

    public function test_9_overpayment_is_handled_correctly(): void
    {
        // Contract value = 5,000,000. Total paid = 5,500,000 (Overpaid by 500,000).
        $this->createPayment(['amount' => 5500000.00]);

        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.projects.show', $this->project));

        $response->assertOk();
        $response->assertSee('Overpaid');
        $response->assertSee('500,000.00');
    }

    public function test_10_existing_project_expense_material_calculations_still_work(): void
    {
        $category = FinanceExpenseCategory::firstOrCreate(
            ['slug' => 'logistics'],
            ['name' => 'Logistics', 'is_active' => true]
        );

        FinancialExpense::create([
            'project_id' => $this->project->id,
            'finance_expense_category_id' => $category->id,
            'description' => 'Site Logistics',
            'amount' => 250000.00,
            'status' => FinancialExpense::STATUS_APPROVED,
            'submitted_by' => $this->financeUser->id,
        ]);

        FinancialMaterialCost::create([
            'project_id' => $this->project->id,
            'material_name' => 'CCTV Cables',
            'quantity' => 10,
            'unit' => 'coils',
            'unit_cost' => 15000.00,
            'total_cost' => 150000.00,
            'status' => FinancialMaterialCost::STATUS_APPROVED,
            'submitted_by' => $this->financeUser->id,
        ]);

        // Total cost = 250,000 + 150,000 = 400,000.
        // Contract value = 5,000,000. Estimated profit = 4,600,000.
        $response = $this->actingAs($this->financeUser)
            ->get(route('finance.projects.show', $this->project));

        $response->assertOk();
        $response->assertSee('400,000.00');
        $response->assertSee('4,600,000.00');
    }
}
