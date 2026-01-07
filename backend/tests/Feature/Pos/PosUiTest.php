<?php

namespace Tests\Feature\Pos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_page_requires_staff_role(): void
    {
        $user = $this->createUser(['role' => 'user']);

        $this->actingAs($user)
            ->get('/pos')
            ->assertForbidden();

        $posUser = $this->createPosUser();
        $this->actingAs($posUser)
            ->get('/pos')
            ->assertOk();

        $admin = $this->createAdmin();
        $this->actingAs($admin)
            ->get('/pos')
            ->assertOk();
    }
}
