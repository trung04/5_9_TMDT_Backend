<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_a_customer_and_returns_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'full_name' => 'Nguyen Van A',
            'email' => 'NEWUSER@EXAMPLE.COM',
            'phone' => '0912345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'access_token',
                'token_type',
                'user' => [
                    'id',
                    'full_name',
                    'email',
                    'phone',
                    'role',
                    'status',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'newuser@example.com')
            ->assertJsonPath('user.role', User::ROLE_CUSTOMER)
            ->assertJsonPath('user.status', User::STATUS_ACTIVE)
            ->assertJsonPath('user.is_active', true);

        $user = User::query()->where('email', 'newuser@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('password123', $user->password_hash));
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_register_requires_unique_email_and_phone(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
            'phone' => '0911111111',
        ]);

        $response = $this->postJson('/api/register', [
            'full_name' => 'Nguyen Van B',
            'email' => 'existing@example.com',
            'phone' => '0911111111',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'phone']);
    }

    public function test_register_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/register', [
            'full_name' => 'Nguyen Van C',
            'email' => 'confirm@example.com',
            'phone' => '0922222222',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_returns_a_token_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password_hash' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'LOGIN@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', 'login@example.com');

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'wrongpass@example.com',
            'password_hash' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'wrongpass@example.com',
            'password' => 'bad-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Invalid credentials.');
    }

    public function test_login_rejects_accounts_that_cannot_authenticate(): void
    {
        $cases = [
            ['email' => 'inactive@example.com', 'status' => User::STATUS_INACTIVE, 'is_active' => true],
            ['email' => 'blocked@example.com', 'status' => User::STATUS_BLOCKED, 'is_active' => true],
            ['email' => 'disabled@example.com', 'status' => User::STATUS_ACTIVE, 'is_active' => false],
        ];

        foreach ($cases as $case) {
            User::factory()->create([
                'email' => $case['email'],
                'status' => $case['status'],
                'is_active' => $case['is_active'],
                'password_hash' => Hash::make('password123'),
            ]);

            $response = $this->postJson('/api/login', [
                'email' => $case['email'],
                'password' => 'password123',
            ]);

            $response->assertStatus(403)
                ->assertJsonPath('message', 'Your account is not allowed to sign in.');
        }
    }

    public function test_me_requires_a_valid_token(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    public function test_me_returns_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/me');

        $response->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_logout_revokes_only_the_current_token(): void
    {
        $user = User::factory()->create();
        $firstToken = $user->createToken('first')->plainTextToken;
        $secondToken = $user->createToken('second')->plainTextToken;

        $logoutResponse = $this->withToken($firstToken)->postJson('/api/logout');

        $logoutResponse->assertOk()
            ->assertJsonPath('message', 'Logged out successfully.');

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $meResponse = $this->withToken($firstToken)->getJson('/api/me');
        $meResponse->assertStatus(401);

        $stillValidResponse = $this->withToken($secondToken)->getJson('/api/me');
        $stillValidResponse->assertOk()
            ->assertJsonPath('user.id', $user->id);
    }
}
