<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_approve_pending_user_as_super_admin(): void
    {
        $superAdmin = $this->createUser(['role' => 'super_admin', 'status' => 'approved']);
        $pendingUser = $this->createUser(['status' => 'pending']);

        $response = $this->actingAs($superAdmin)
            ->patch(route('admin.users.approve', ['user' => $pendingUser, 'role' => 'super_admin']));

        $response->assertRedirect();
        $this->assertSame('super_admin', $pendingUser->fresh()->role);
        $this->assertSame('approved', $pendingUser->fresh()->status);
    }

    public function test_super_admin_can_promote_existing_user_to_super_admin(): void
    {
        $superAdmin = $this->createUser(['role' => 'super_admin', 'status' => 'approved']);
        $targetUser = $this->createUser(['role' => 'admin', 'status' => 'approved']);

        $response = $this->actingAs($superAdmin)
            ->putJson("/api/users/{$targetUser->id}", [
                'role' => 'super_admin',
            ]);

        $response->assertOk();
        $this->assertSame('super_admin', $targetUser->fresh()->role);
    }

    public function test_super_admin_can_demote_another_super_admin_if_not_last(): void
    {
        $superAdmin1 = $this->createUser(['role' => 'super_admin', 'status' => 'approved']);
        $superAdmin2 = $this->createUser(['role' => 'super_admin', 'status' => 'approved']);

        $response = $this->actingAs($superAdmin1)
            ->putJson("/api/users/{$superAdmin2->id}", [
                'role' => 'admin',
            ]);

        $response->assertOk();
        $this->assertSame('admin', $superAdmin2->fresh()->role);
    }

    public function test_ordinary_admin_cannot_create_or_approve_super_admin(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'status' => 'approved']);
        $pendingUser = $this->createUser(['status' => 'pending']);

        $response = $this->actingAs($admin)
            ->patch(route('admin.users.approve', ['user' => $pendingUser, 'role' => 'super_admin']));

        $response->assertStatus(403);
        $this->assertNotEquals('super_admin', $pendingUser->fresh()->role);
    }

    public function test_ordinary_admin_cannot_promote_themselves_to_super_admin(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'status' => 'approved']);

        $response = $this->actingAs($admin)
            ->putJson("/api/users/{$admin->id}", [
                'role' => 'super_admin',
            ]);

        $response->assertStatus(403);
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_ordinary_admin_cannot_promote_another_user_to_super_admin(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'status' => 'approved']);
        $targetUser = $this->createUser(['role' => 'user', 'status' => 'approved']);

        $response = $this->actingAs($admin)
            ->putJson("/api/users/{$targetUser->id}", [
                'role' => 'super_admin',
            ]);

        $response->assertStatus(403);
        $this->assertSame('user', $targetUser->fresh()->role);
    }

    public function test_manager_cannot_create_or_promote_super_admin(): void
    {
        $manager = $this->createUser(['role' => 'manager', 'status' => 'approved']);
        $targetUser = $this->createUser(['role' => 'user', 'status' => 'approved']);

        $response = $this->actingAs($manager)
            ->putJson("/api/users/{$targetUser->id}", [
                'role' => 'super_admin',
            ]);

        $response->assertStatus(403);
        $this->assertSame('user', $targetUser->fresh()->role);
    }

    public function test_finance_user_cannot_create_or_promote_super_admin(): void
    {
        $financeUser = $this->createUser(['role' => 'finance', 'status' => 'approved']);
        $targetUser = $this->createUser(['role' => 'user', 'status' => 'approved']);

        $response = $this->actingAs($financeUser)
            ->putJson("/api/users/{$targetUser->id}", [
                'role' => 'super_admin',
            ]);

        $response->assertStatus(403);
        $this->assertSame('user', $targetUser->fresh()->role);
    }

    public function test_pos_user_cannot_create_or_promote_super_admin(): void
    {
        $posUser = $this->createUser(['role' => 'pos', 'status' => 'approved']);
        $targetUser = $this->createUser(['role' => 'user', 'status' => 'approved']);

        $response = $this->actingAs($posUser)
            ->putJson("/api/users/{$targetUser->id}", [
                'role' => 'super_admin',
            ]);

        $response->assertStatus(403);
        $this->assertSame('user', $targetUser->fresh()->role);
    }

    public function test_field_users_cannot_create_or_promote_super_admin(): void
    {
        foreach (['field_staff', 'field_coordinator'] as $role) {
            $fieldUser = $this->createUser(['role' => $role, 'status' => 'approved']);
            $targetUser = $this->createUser(['role' => 'user', 'status' => 'approved']);

            $response = $this->actingAs($fieldUser)
                ->putJson("/api/users/{$targetUser->id}", [
                    'role' => 'super_admin',
                ]);

            $response->assertStatus(403);
            $this->assertSame('user', $targetUser->fresh()->role);
        }
    }

    public function test_directly_submitting_super_admin_role_is_rejected_for_unauthorized_users(): void
    {
        $user = $this->createUser(['role' => 'user', 'status' => 'approved']);
        $target = $this->createUser(['role' => 'user', 'status' => 'approved']);

        $response = $this->actingAs($user)
            ->patch(route('admin.users.approve', ['user' => $target, 'role' => 'super_admin']));

        $response->assertStatus(403);
        $this->assertSame('user', $target->fresh()->role);
    }

    public function test_the_last_super_admin_cannot_be_demoted(): void
    {
        $superAdmin = $this->createUser(['role' => 'super_admin', 'status' => 'approved']);
        $anotherSuperAdmin = $this->createUser(['role' => 'super_admin', 'status' => 'approved']);

        // Demoting one of two super admins works
        $response = $this->actingAs($superAdmin)
            ->patch(route('admin.users.approve', ['user' => $anotherSuperAdmin, 'role' => 'admin']));

        $response->assertRedirect();
        $this->assertSame('admin', $anotherSuperAdmin->fresh()->role);

        // Trying to demote the sole remaining super admin fails
        $response = $this->actingAs($superAdmin)
            ->patch(route('admin.users.approve', ['user' => $superAdmin, 'role' => 'admin']));

        $response->assertStatus(403);
        $this->assertSame('super_admin', $superAdmin->fresh()->role);
    }

    public function test_the_last_super_admin_cannot_be_deleted(): void
    {
        $superAdmin = $this->createUser(['role' => 'super_admin', 'status' => 'approved']);

        $response = $this->actingAs($superAdmin)
            ->delete(route('admin.users.delete', $superAdmin));

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    public function test_existing_role_assignments_continue_to_work(): void
    {
        $admin = $this->createUser(['role' => 'admin', 'status' => 'approved']);
        $pendingUser = $this->createUser(['status' => 'pending']);

        $response = $this->actingAs($admin)
            ->patch(route('admin.users.approve', ['user' => $pendingUser, 'role' => 'manager']));

        $response->assertRedirect();
        $this->assertSame('manager', $pendingUser->fresh()->role);
        $this->assertSame('approved', $pendingUser->fresh()->status);
    }
}
