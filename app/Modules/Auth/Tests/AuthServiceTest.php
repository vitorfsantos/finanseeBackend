<?php

namespace App\Modules\Auth\Tests;

use App\Modules\Auth\Services\AuthService;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class AuthServiceTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  protected AuthService $authService;

  protected function setUp(): void
  {
    parent::setUp();

    $this->authService = new AuthService();
  }

  #[Test]
  public function it_can_login_with_valid_credentials()
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
    $result = $this->authService->login($credentials);

    // Assert
    $this->assertTrue($result['success']);
    $this->assertEquals($user->id, $result['user']->id);
    $this->assertEquals($user->email, $result['user']->email);
    $this->assertNotEmpty($result['token']);
    $this->assertIsString($result['token']);
  }

  #[Test]
  public function it_fails_login_with_invalid_email()
  {
    // Arrange
    $credentials = [
      'email' => 'nonexistent@example.com',
      'password' => 'password123'
    ];

    // Act
    $result = $this->authService->login($credentials);

    // Assert
    $this->assertFalse($result['success']);
    $this->assertEquals('Credenciais inválidas', $result['message']);
    $this->assertArrayNotHasKey('user', $result);
    $this->assertArrayNotHasKey('token', $result);
  }

  #[Test]
  public function it_fails_login_with_invalid_password()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
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
    $result = $this->authService->login($credentials);

    // Assert
    $this->assertFalse($result['success']);
    $this->assertEquals('Credenciais inválidas', $result['message']);
    $this->assertArrayNotHasKey('user', $result);
    $this->assertArrayNotHasKey('token', $result);
  }

  #[Test]
  public function it_can_logout_user()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $user = User::factory()->create(['user_level_id' => $userLevel->id]);

    // Create a token for the user
    $token = $user->createToken('auth-token');
    $user->withAccessToken($token->accessToken);

    // Act
    $result = $this->authService->logout($user);

    // Assert
    $this->assertTrue($result['success']);
    $this->assertEquals('Logout realizado com sucesso', $result['message']);

    // Verify token was deleted
    $this->assertDatabaseMissing('personal_access_tokens', [
      'id' => $token->accessToken->id
    ]);
  }

  #[Test]
  public function it_generates_unique_tokens_for_multiple_logins()
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
    $result1 = $this->authService->login($credentials);
    $result2 = $this->authService->login($credentials);

    // Assert
    $this->assertTrue($result1['success']);
    $this->assertTrue($result2['success']);
    $this->assertNotEquals($result1['token'], $result2['token']);
  }

  #[Test]
  public function it_preserves_user_relationships_in_login_response()
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
    $result = $this->authService->login($credentials);

    // Assert
    $this->assertTrue($result['success']);
    $this->assertEquals($user->name, $result['user']->name);
    $this->assertEquals($user->email, $result['user']->email);
    $this->assertEquals($user->user_level_id, $result['user']->user_level_id);
  }
}
