<?php

namespace Tests\Feature;

use App\Models\AuthCode;
use App\Models\User;
use App\Notifications\SendAuthCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test api registration.
     */
    public function test_user_can_register_via_api(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'user'])
            ->assertJsonPath('user.email', 'john@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);

        $this->assertDatabaseHas('auth_codes', [
            'email' => 'john@example.com',
            'type' => 'registration',
        ]);

        Notification::assertSentTo(
            new AnonymousNotifiable,
            SendAuthCodeNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === 'john@example.com';
            }
        );
    }

    /**
     * Test registration validation.
     */
    public function test_registration_requires_valid_data(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test api login steps.
     */
    public function test_user_can_login_via_api_and_receives_2fa_code(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Código de verificação enviado para o seu e-mail.')
            ->assertJsonPath('email', 'jane@example.com');

        $this->assertDatabaseHas('auth_codes', [
            'email' => 'jane@example.com',
            'type' => 'api_login',
        ]);

        Notification::assertSentTo(
            new AnonymousNotifiable,
            SendAuthCodeNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === 'jane@example.com';
            }
        );
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test 2FA verification and token retrieval.
     */
    public function test_user_can_verify_2fa_code_and_receives_token(): void
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
        ]);

        $authCode = AuthCode::create([
            'email' => 'jane@example.com',
            'code' => '123456',
            'type' => 'api_login',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/auth/verify-code', [
            'email' => 'jane@example.com',
            'code' => '123456',
            'device_name' => 'iphone_15',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'token', 'user'])
            ->assertJsonPath('user.email', 'jane@example.com');

        $this->assertNotNull($response->json('token'));
        $this->assertTrue($authCode->fresh()->isVerified());
    }

    /**
     * Test verification with invalid code.
     */
    public function test_verification_fails_with_invalid_code(): void
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
        ]);

        $authCode = AuthCode::create([
            'email' => 'jane@example.com',
            'code' => '123456',
            'type' => 'api_login',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/auth/verify-code', [
            'email' => 'jane@example.com',
            'code' => '654321', // wrong code
            'device_name' => 'iphone_15',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    /**
     * Test verification with expired code.
     */
    public function test_verification_fails_with_expired_code(): void
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
        ]);

        $authCode = AuthCode::create([
            'email' => 'jane@example.com',
            'code' => '123456',
            'type' => 'api_login',
            'expires_at' => now()->subMinutes(1), // expired
        ]);

        $response = $this->postJson('/api/auth/verify-code', [
            'email' => 'jane@example.com',
            'code' => '123456',
            'device_name' => 'iphone_15',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    /**
     * Test resending verification code.
     */
    public function test_user_can_resend_code(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/auth/resend-code', [
            'email' => 'jane@example.com',
            'type' => 'api_login',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Novo código de verificação enviado para o seu e-mail.');

        $this->assertDatabaseHas('auth_codes', [
            'email' => 'jane@example.com',
            'type' => 'api_login',
        ]);

        Notification::assertSentTo(
            new AnonymousNotifiable,
            SendAuthCodeNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === 'jane@example.com';
            }
        );
    }

    /**
     * Test getting user details with token.
     */
    public function test_authenticated_user_can_access_user_endpoint(): void
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/user');

        $response->assertStatus(200)
            ->assertJsonPath('user.email', 'jane@example.com');
    }

    /**
     * Test accessing protected endpoint without token.
     */
    public function test_guest_cannot_access_user_endpoint(): void
    {
        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(401);
    }

    /**
     * Test logging out.
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
        ]);

        $token = $user->createToken('test_device')->plainTextToken;

        $this->assertCount(1, $user->tokens);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Logout realizado com sucesso.');

        $this->assertCount(0, $user->fresh()->tokens);
    }
}
