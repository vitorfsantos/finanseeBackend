<?php

namespace App\Modules\Users\Tests;

use App\Modules\Users\Controllers\CreateUserController;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLevel;
use App\Modules\Users\Requests\CreateUserRequest;
use App\Modules\Users\Services\CreateUserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class CreateUserControllerTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  protected CreateUserController $controller;
  protected CreateUserService $createUserService;

  protected function setUp(): void
  {
    parent::setUp();

    $this->createUserService = Mockery::mock(CreateUserService::class);
    $this->controller = new CreateUserController($this->createUserService);
  }

  protected function tearDown(): void
  {
    Mockery::close();
    parent::tearDown();
  }

  #[Test]
  public function it_can_create_a_user_successfully()
  {
    // Arrange
    $userLevel = UserLevel::factory()->make(['id' => 2, 'name' => 'Company Admin']);

    $requestData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'phone' => '(11) 99999-9999',
      'user_level_id' => 2
    ];

    $createdUser = User::factory()->make($requestData);
    $createdUser->id = '550e8400-e29b-41d4-a716-446655440000';
    $createdUser->setRelation('level', $userLevel);

    $request = Mockery::mock(CreateUserRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createUserService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdUser);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(201, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals('Usuário criado com sucesso', $responseData['message']);
    $this->assertEquals($createdUser->toArray(), $responseData['data']);
  }

  #[Test]
  public function it_can_create_a_user_with_only_required_fields()
  {
    // Arrange
    $userLevel = UserLevel::factory()->make(['id' => 2, 'name' => 'Company Admin']);

    $requestData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => 2
    ];

    $createdUser = User::factory()->make($requestData);
    $createdUser->id = '550e8400-e29b-41d4-a716-446655440000';
    $createdUser->setRelation('level', $userLevel);

    $request = Mockery::mock(CreateUserRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createUserService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdUser);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(201, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals('Usuário criado com sucesso', $responseData['message']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertEquals($requestData['name'], $responseData['data']['name']);
    $this->assertEquals($requestData['email'], $responseData['data']['email']);
    $this->assertEquals($requestData['user_level_id'], $responseData['data']['user_level_id']);
  }

  #[Test]
  public function it_passes_validated_data_to_service()
  {
    // Arrange
    $requestData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'phone' => '(11) 99999-9999',
      'user_level_id' => 2
    ];

    $createdUser = User::factory()->make($requestData);

    $request = Mockery::mock(CreateUserRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createUserService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdUser)
      ->once();

    // Act
    $this->controller->__invoke($request);

    // Assert
    // Assertions are handled by Mockery expectations
    $this->assertTrue(true);
  }

  #[Test]
  public function it_returns_created_user_data_in_response()
  {
    // Arrange
    $userLevel = UserLevel::factory()->make(['id' => 2, 'name' => 'Company Admin']);

    $requestData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'phone' => '(11) 99999-9999',
      'user_level_id' => 2
    ];

    $createdUser = User::factory()->make([
      'id' => '550e8400-e29b-41d4-a716-446655440000',
      'name' => $requestData['name'],
      'email' => $requestData['email'],
      'phone' => $requestData['phone'],
      'user_level_id' => $requestData['user_level_id'],
      'created_at' => now(),
      'updated_at' => now()
    ]);
    $createdUser->setRelation('level', $userLevel);

    $request = Mockery::mock(CreateUserRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createUserService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdUser);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $responseData = json_decode($response->getContent(), true);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertEquals($createdUser->toArray(), $responseData['data']);
    $this->assertEquals($requestData['name'], $responseData['data']['name']);
    $this->assertEquals($requestData['email'], $responseData['data']['email']);
    $this->assertEquals($requestData['phone'], $responseData['data']['phone']);
    $this->assertEquals($requestData['user_level_id'], $responseData['data']['user_level_id']);
  }

  #[Test]
  public function it_has_correct_success_message()
  {
    // Arrange
    $requestData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => 2
    ];

    $createdUser = User::factory()->make($requestData);

    $request = Mockery::mock(CreateUserRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createUserService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdUser);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals('Usuário criado com sucesso', $responseData['message']);
  }

  #[Test]
  public function it_excludes_password_from_response_data()
  {
    // Arrange
    $requestData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => 2
    ];

    $createdUser = User::factory()->make($requestData);
    $createdUser->id = '550e8400-e29b-41d4-a716-446655440000';

    $request = Mockery::mock(CreateUserRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createUserService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdUser);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $responseData = json_decode($response->getContent(), true);
    $this->assertArrayNotHasKey('password', $responseData['data']);
    $this->assertArrayNotHasKey('remember_token', $responseData['data']);
  }

  #[Test]
  public function it_can_be_instantiated_with_create_user_service()
  {
    // Arrange & Act
    $controller = new CreateUserController($this->createUserService);

    // Assert
    $this->assertInstanceOf(CreateUserController::class, $controller);
  }

  #[Test]
  public function it_handles_user_with_phone_field()
  {
    // Arrange
    $requestData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'phone' => '(11) 99999-9999',
      'user_level_id' => 2
    ];

    $createdUser = User::factory()->make($requestData);
    $createdUser->phone = $requestData['phone'];

    $request = Mockery::mock(CreateUserRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createUserService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdUser);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals($requestData['phone'], $responseData['data']['phone']);
  }

  #[Test]
  public function it_handles_user_without_phone_field()
  {
    // Arrange
    $requestData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => 2
    ];

    $createdUser = User::factory()->make($requestData);
    $createdUser->phone = null;

    $request = Mockery::mock(CreateUserRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createUserService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdUser);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $responseData = json_decode($response->getContent(), true);
    $this->assertNull($responseData['data']['phone']);
  }
}
