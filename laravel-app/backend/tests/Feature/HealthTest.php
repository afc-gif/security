<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_returns_ok(): void
    {
        $this->get('/api/health')
            ->assertOk()
            ->assertJson(['status' => 'ok']);
    }

    public function test_homepage_loads(): void
    {
        $this->get('/')
            ->assertOk();
    }
}
