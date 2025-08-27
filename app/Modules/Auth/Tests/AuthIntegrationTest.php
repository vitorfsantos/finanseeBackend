<?php

namespace App\Modules\Auth\Tests;

use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuthIntegrationTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  protected function setUp(): void
  {
    parent::setUp();
  }

  #[Test]
  public function user_can_login_with_valid_credentials()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $user = User::factory()->create([
      'email' => 'test@example.com',
      'password' => Hash::make('password123'),
      'user_level_id' => $userLevel->id
    ]);

    $credentials = [
      'email' => 'test@example.com',
      'password' => 'password123'
    ];

    // Act
    $response = $this->postJson('/api/auth/login', $credentials);

    // Assert
    $response->assertStatus(200)
      ->assertJsonStructure([
        'message',
        'user' => [
          'id',
          'name',
          'email',
          'user_level_id'
        ],
        'token'
      ])
      ->assertJson([
        'message' => 'Login successful',
        'user' => [
          'email' => 'test@example.com'
        ]
      ]);

    $this->assertNotEmpty($response->json('token'));
  }

  #[Test]
  public function user_cannot_login_with_invalid_email()
  {
    // Arrange
    $credentials = [
      'email' => 'nonexistent@example.com',
      'password' => 'password123'
    ];

    // Act
    $response = $this->postJson('/api/auth/login', $credentials);

    // Assert
    $response->assertStatus(401)
      ->assertJson([
        'message' => 'Credenciais inválidas'
      ]);
  }

  #[Test]
  public function user_cannot_login_with_invalid_password()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();
    $user = User::factory()->create([
      'email' => 'test@example.com',
      'password' => Hash::make('correctpassword'),
      'user_level_id' => $userLevel->id
    ]);

    $credentials = [
      'email' => 'test@example.com',
      'password' => 'wrongpassword'
    ];

    // Act
    $response = $this->postJson('/api/auth/login', $credentials);

    // Assert
    $response->assertStatus(401)
      ->assertJson([
        'message' => 'Credenciais inválidas'
      ]);
  }

  #[Test]
  public function login_validates_required_fields()
  {
    // Act
    $response = $this->postJson('/api/auth/login', []);

    // Assert
    $response->assertStatus(422)
      ->assertJsonValidationErrors(['email', 'password']);
  }

  #[Test]
  public function login_validates_email_format()
  {
    // Arrange
    $credentials = [
      'email' => 'invalid-email',
      'password' => 'password123'
    ];

    // Act
    $response = $this->postJson('/api/auth/login', $credentials);

    // Assert
    $response->assertStatus(422)
      ->assertJsonValidationErrors(['email']);
  }

  #[Test]
  public function authenticated_user_can_logout()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();
    $user = User::factory()->create(['user_level_id' => $userLevel->id]);
    $token = $user->createToken('auth-token')->plainTextToken;

    // Act
    $response = $this->postJson('/api/auth/logout', [], [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(200)
      ->assertJson([
        'message' => 'Logout successful'
      ]);

    // Verify token was revoked
    $this->assertDatabaseMissing('personal_access_tokens', [
      'tokenable_id' => $user->id
    ]);
  }

  #[Test]
  public function unauthenticated_user_cannot_logout()
  {
    // Act
    $response = $this->postJson('/api/auth/logout');

    // Assert
    $response->assertStatus(401);
  }

  #[Test]
  public function user_can_login_and_logout_multiple_times()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();
    $user = User::factory()->create([
      'email' => 'test@example.com',
      'password' => Hash::make('password123'),
      'user_level_id' => $userLevel->id
    ]);

    $credentials = [
      'email' => 'test@example.com',
      'password' => 'password123'
    ];

    // Act & Assert - First login/logout cycle
    $loginResponse1 = $this->postJson('/api/auth/login', $credentials);
    $loginResponse1->assertStatus(200);
    $token1 = $loginResponse1->json('token');

    $logoutResponse1 = $this->postJson('/api/auth/logout', [], [
      'Authorization' => "Bearer $token1"
    ]);
    $logoutResponse1->assertStatus(200);

    // Act & Assert - Second login/logout cycle
    $loginResponse2 = $this->postJson('/api/auth/login', $credentials);
    $loginResponse2->assertStatus(200);
    $token2 = $loginResponse2->json('token');

    $logoutResponse2 = $this->postJson('/api/auth/logout', [], [
      'Authorization' => "Bearer $token2"
    ]);
    $logoutResponse2->assertStatus(200);

    // Tokens should be different
    $this->assertNotEquals($token1, $token2);
  }

  #[Test]
  public function user_data_does_not_include_sensitive_information()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();
    $user = User::factory()->create([
      'email' => 'test@example.com',
      'password' => Hash::make('password123'),
      'user_level_id' => $userLevel->id
    ]);

    $credentials = [
      'email' => 'test@example.com',
      'password' => 'password123'
    ];

    // Act
    $response = $this->postJson('/api/auth/login', $credentials);

    // Assert
    $response->assertStatus(200);
    $userData = $response->json('user');

    $this->assertArrayNotHasKey('password', $userData);
    $this->assertArrayNotHasKey('remember_token', $userData);
  }

  #[Test]
  public function token_is_required_for_protected_routes()
  {
    // Act
    $response = $this->getJson('/api/users');

    // Assert
    $response->assertStatus(401);
  }

  #[Test]
  public function valid_token_allows_access_to_protected_routes()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create(['id' => 1]); // Admin Master
    $user = User::factory()->create(['user_level_id' => $userLevel->id]);
    $token = $user->createToken('auth-token')->plainTextToken;

    // Act
    $response = $this->getJson('/api/users', [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(200); // Or whatever the actual successful response should be
  }

  #[Test]
  public function revoked_token_cannot_access_protected_routes()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();
    $user = User::factory()->create(['user_level_id' => $userLevel->id]);
    $token = $user->createToken('auth-token')->plainTextToken;

    // Revoke the token
    $user->tokens()->delete();

    // Act
    $response = $this->getJson('/api/users', [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(401);
  }
}
