<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_operator_can_login_and_receive_sanctum_token(): void
    {
        $this->seed();

        $response = $this->postJson('/api/auth/login', [
            'email' => 'operator.abidjan@agrici.ci',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', User::ROLE_OPERATOR)
            ->assertJsonStructure(['data' => ['token']]);
    }

    public function test_operator_cannot_manage_supervisors(): void
    {
        $this->seed();

        $operator = User::query()->where('role', User::ROLE_OPERATOR)->firstOrFail();

        $this->actingAs($operator)
            ->getJson('/api/supervisors')
            ->assertForbidden()
            ->assertJsonPath('message', 'Accès interdit.');
    }

    public function test_admin_can_create_supervisor(): void
    {
        $this->seed();

        $admin = User::query()->where('role', User::ROLE_ADMIN)->firstOrFail();

        $this->actingAs($admin)
            ->postJson('/api/supervisors', [
                'name' => 'Nouveau Superviseur',
                'email' => 'supervisor.demo@agrici.ci',
                'password' => 'password',
            ])
            ->assertCreated()
            ->assertJsonPath('data.role', User::ROLE_SUPERVISOR);
    }
}
