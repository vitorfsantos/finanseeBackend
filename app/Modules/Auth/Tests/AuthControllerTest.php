<?php

namespace App\Modules\Auth\Tests;

use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class AuthControllerTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  protected AuthController $controller;
  protected AuthService $authService;

  protected function setUp(): void
  {
    parent::setUp();

    $this->authService = Mockery::mock(AuthService::class);
    $this->controller = new AuthController($this->authService);
  }

  protected function tearDown(): void
  {
    Mockery::close();
    parent::tearDown();
  }

  #[Test]
  public function it_can_login_with_valid_credentials()
  {
    // Arrange
    $credentials = [
      'email' => 'test@example.com',
      'password' => 'password123'
    ];

    $user = User::factory()->make([
      'id' => '550e8400-e29b-41d4-a716-446655440000',
      'email' => 'test@example.com',
      'name' => 'Test User'
    ]);

    $expectedResult = [
      'success' => true,
      'user' => $user,
      'token' => 'fake-token-123'
    ];

    $request = Mockery::mock(LoginRequest::class);
    $request->shouldReceive('validated')->andReturn($credentials);

    $this->authService
      ->shouldReceive('login')
      ->with($credentials)
      ->andReturn($expectedResult);

    // Act
    $response = $this->controller->login($request);

    // Assert
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals('Login successful', $responseData['message']);
    $this->assertEquals($user->toArray(), $responseData['user']);
    $this->assertEquals('fake-token-123', $responseData['token']);
  }

  #[Test]
  public function it_returns_401_for_invalid_credentials()
  {
    // Arrange
    $credentials = [
      'email' => 'test@example.com',
      'password' => 'wrongpassword'
    ];

    $expectedResult = [
      'success' => false,
      'message' => 'Credenciais inválidas'
    ];

    $request = Mockery::mock(LoginRequest::class);
    $request->shouldReceive('validated')->andReturn($credentials);

    $this->authService
      ->shouldReceive('login')
      ->with($credentials)
      ->andReturn($expectedResult);

    // Act
    $response = $this->controller->login($request);

    // Assert
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(401, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals('Credenciais inválidas', $responseData['message']);
  }

  #[Test]
  public function it_can_logout_authenticated_user()
  {
    // Arrange
    $user = User::factory()->make(['id' => '550e8400-e29b-41d4-a716-446655440000']);

    $request = Mockery::mock(\Illuminate\Http\Request::class);
    $request->shouldReceive('user')->andReturn($user);

    $expectedResult = [
      'success' => true,
      'message' => 'Logout realizado com sucesso'
    ];

    $this->authService
      ->shouldReceive('logout')
      ->with($user)
      ->andReturn($expectedResult);

    // Act
    $response = $this->controller->logout($request);

    // Assert
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals('Logout successful', $responseData['message']);
  }

  #[Test]
  public function it_can_be_instantiated_with_auth_service()
  {
    // Arrange & Act
    $controller = new AuthController($this->authService);

    // Assert
    $this->assertInstanceOf(AuthController::class, $controller);
  }
}
