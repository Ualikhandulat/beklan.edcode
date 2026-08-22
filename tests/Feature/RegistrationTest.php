<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Публичная регистрация: любой посетитель создаёт аккаунт ученика
 * и автоматически получает пробный доступ.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_is_shown(): void
    {
        $this->get(route('register'))->assertOk();
    }

    public function test_visitor_can_register_and_gets_trial_access(): void
    {
        $this->post(route('register'), $this->payload())
            ->assertRedirect(route('student.dashboard'));

        $user = User::where('login', '87001234567')->firstOrFail();

        $this->assertSame(Role::Student, $user->role);
        $this->assertTrue($user->has_trial_access);
        $this->assertNull($user->group_id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_requires_unique_login_and_iin(): void
    {
        User::factory()->create(['login' => '87001234567', 'iin' => '123456789012']);

        $this->post(route('register'), $this->payload())
            ->assertSessionHasErrors(['login', 'iin']);

        $this->assertGuest();
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $this->post(route('register'), $this->payload(['password_confirmation' => 'other-password']))
            ->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_authenticated_user_cannot_open_register_page(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('register'))
            ->assertRedirect(route('student.dashboard'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Тестов Тест',
            'login' => '87001234567',
            'iin' => '123456789012',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ], $overrides);
    }
}
