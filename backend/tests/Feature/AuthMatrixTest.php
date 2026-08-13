<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_redirects_and_grants_admin_access(): void
    {
        $user = $this->createUser([
            'role' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);

        // Access admin dashboard
        $this->get(route('admin.dashboard'))->assertOk();

        // Ordinary admin is restricted from finance without grant
        $this->get(route('finance.dashboard'))->assertStatus(403);

        // Forbidden areas
        $this->get(route('field.dashboard'))->assertStatus(403);
    }

    public function test_executive_and_super_admin_roles_grant_executive_and_finance_access(): void
    {
        foreach (['executive', 'super_admin'] as $role) {
            $user = $this->createUser([
                'role' => $role,
                'password' => bcrypt('password123'),
            ]);

            $this->actingAs($user);
            $this->get(route('admin.dashboard'))
                ->assertOk()
                ->assertSee(route('finance.dashboard'));
            $this->get(route('finance.dashboard'))->assertOk();
            $this->get(route('field.dashboard'))->assertStatus(403);
        }
    }

    public function test_manager_login_redirects_and_grants_admin_access(): void
    {
        $user = $this->createUser([
            'role' => 'manager',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);

        // Access admin dashboard & confirm Finance link is NOT visible
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee(route('finance.dashboard'));

        // Forbidden areas
        $this->get(route('field.dashboard'))->assertStatus(403);
        $this->get(route('finance.dashboard'))->assertStatus(403);
    }

    public function test_finance_login_redirects_and_grants_finance_access(): void
    {
        $user = $this->createUser([
            'role' => 'finance',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('finance.dashboard'));
        $this->assertAuthenticatedAs($user);

        // Access finance dashboard
        $this->get(route('finance.dashboard'))->assertOk();

        // Forbidden areas
        $this->get(route('admin.dashboard'))->assertStatus(403);
        $this->get(route('field.dashboard'))->assertStatus(403);
    }

    public function test_field_coordinator_login_redirects_and_grants_field_access(): void
    {
        $user = $this->createUser([
            'role' => 'field_coordinator',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('field.dashboard'));
        $this->assertAuthenticatedAs($user);

        // Access field dashboard and coordinator route
        $this->get(route('field.dashboard'))->assertOk();
        $this->get(route('coordinator.jobs.index'))->assertOk();

        // Forbidden areas
        $this->get(route('admin.dashboard'))->assertStatus(403);
        $this->get(route('finance.dashboard'))->assertStatus(403);
    }

    public function test_field_staff_login_redirects_and_grants_field_access(): void
    {
        $user = $this->createUser([
            'role' => 'field_staff',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('field.dashboard'));
        $this->assertAuthenticatedAs($user);

        // Access field dashboard
        $this->get(route('field.dashboard'))->assertOk();

        // Forbidden areas (coordinator jobs, admin, finance)
        $this->get(route('coordinator.jobs.index'))->assertStatus(403);
        $this->get(route('admin.dashboard'))->assertStatus(403);
        $this->get(route('finance.dashboard'))->assertStatus(403);
    }

    public function test_pos_login_redirects_and_grants_pos_access(): void
    {
        $user = $this->createUser([
            'role' => 'pos',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('pos.index'));
        $this->assertAuthenticatedAs($user);

        // Access POS view
        $this->get(route('pos.index'))->assertOk();

        // Forbidden areas
        $this->get(route('admin.dashboard'))->assertStatus(403);
        $this->get(route('finance.dashboard'))->assertStatus(403);
        $this->get(route('field.dashboard'))->assertStatus(403);
    }

    public function test_pending_user_login_is_blocked(): void
    {
        $user = $this->createUser([
            'role' => 'user',
            'status' => 'pending',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
