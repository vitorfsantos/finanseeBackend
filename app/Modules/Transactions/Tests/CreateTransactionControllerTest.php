<?php

namespace App\Modules\Transactions\Tests;

use App\Modules\Transactions\Controllers\CreateTransactionController;
use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\Requests\CreateTransactionRequest;
use App\Modules\Transactions\Services\CreateTransactionService;
use App\Modules\Users\Models\User;
use App\Modules\Companies\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class CreateTransactionControllerTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  protected CreateTransactionController $controller;
  protected CreateTransactionService $createTransactionService;

  protected function setUp(): void
  {
    parent::setUp();

    $this->createTransactionService = Mockery::mock(CreateTransactionService::class);
    $this->controller = new CreateTransactionController($this->createTransactionService);
  }

  protected function tearDown(): void
  {
    Mockery::close();
    parent::tearDown();
  }

  #[Test]
  public function it_can_create_a_transaction_successfully()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 4]); // user level
    $company = Company::factory()->create();

    $requestData = [
      'type' => 'expense',
      'category' => 'Alimentação',
      'description' => 'Almoço no restaurante',
      'amount' => 25.50,
      'date' => '2024-01-15',
      'user_id' => $user->id,
      'company_id' => $company->id,
    ];

    $createdTransaction = Transaction::factory()->make($requestData);
    $createdTransaction->id = '550e8400-e29b-41d4-a716-446655440000';

    $request = Mockery::mock(CreateTransactionRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createTransactionService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdTransaction);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(201, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals('Transação criada com sucesso', $responseData['message']);
    $this->assertEquals($createdTransaction->toArray(), $responseData['data']);
  }

  #[Test]
  public function it_can_create_a_transaction_with_only_required_fields()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 4]); // user level

    $requestData = [
      'type' => 'income',
      'amount' => 1000.00,
      'date' => '2024-01-15',
      'user_id' => $user->id,
    ];

    $createdTransaction = Transaction::factory()->make($requestData);
    $createdTransaction->id = '550e8400-e29b-41d4-a716-446655440000';

    $request = Mockery::mock(CreateTransactionRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createTransactionService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdTransaction);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(201, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals('Transação criada com sucesso', $responseData['message']);
    $this->assertEquals($createdTransaction->toArray(), $responseData['data']);
  }

  #[Test]
  public function it_creates_income_transaction()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 4]); // user level

    $requestData = [
      'type' => 'income',
      'category' => 'Salário',
      'description' => 'Salário mensal',
      'amount' => 3500.00,
      'date' => '2024-01-05',
      'user_id' => $user->id,
    ];

    $createdTransaction = Transaction::factory()->make($requestData);
    $createdTransaction->id = '550e8400-e29b-41d4-a716-446655440000';

    $request = Mockery::mock(CreateTransactionRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createTransactionService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdTransaction);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $this->assertEquals(201, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals('income', $responseData['data']['type']);
    $this->assertEquals('Salário', $responseData['data']['category']);
  }

  #[Test]
  public function it_creates_expense_transaction()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 4]);

    $requestData = [
      'type' => 'expense',
      'category' => 'Transporte',
      'description' => 'Combustível para o carro',
      'amount' => 120.00,
      'date' => '2024-01-10',
      'user_id' => $user->id,
    ];

    $createdTransaction = Transaction::factory()->make($requestData);
    $createdTransaction->id = '550e8400-e29b-41d4-a716-446655440000';

    $request = Mockery::mock(CreateTransactionRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createTransactionService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdTransaction);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $this->assertEquals(201, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals('expense', $responseData['data']['type']);
    $this->assertEquals('Transporte', $responseData['data']['category']);
  }

  #[Test]
  public function it_creates_transaction_with_company()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 4]);
    $company = Company::factory()->create();

    $requestData = [
      'type' => 'income',
      'category' => 'Vendas',
      'description' => 'Venda de produtos',
      'amount' => 1500.00,
      'date' => '2024-01-15',
      'user_id' => $user->id,
      'company_id' => $company->id,
    ];

    $createdTransaction = Transaction::factory()->make($requestData);
    $createdTransaction->id = '550e8400-e29b-41d4-a716-446655440000';

    $request = Mockery::mock(CreateTransactionRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createTransactionService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdTransaction);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $this->assertEquals(201, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals($company->id, $responseData['data']['company_id']);
  }

  #[Test]
  public function it_creates_personal_transaction()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 4]);

    $requestData = [
      'type' => 'expense',
      'category' => 'Lazer',
      'description' => 'Cinema com amigos',
      'amount' => 75.50,
      'date' => '2024-01-15',
      'user_id' => $user->id,
      // company_id not provided
    ];

    $createdTransaction = Transaction::factory()->make($requestData);
    $createdTransaction->id = '550e8400-e29b-41d4-a716-446655440000';

    $request = Mockery::mock(CreateTransactionRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createTransactionService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdTransaction);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $this->assertEquals(201, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertNull($responseData['data']['company_id']);
  }

  #[Test]
  public function it_returns_correct_response_structure()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 4]);

    $requestData = [
      'type' => 'expense',
      'amount' => 50.00,
      'date' => '2024-01-15',
      'user_id' => $user->id,
    ];

    $createdTransaction = Transaction::factory()->make($requestData);
    $createdTransaction->id = '550e8400-e29b-41d4-a716-446655440000';

    $request = Mockery::mock(CreateTransactionRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createTransactionService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdTransaction);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $responseData = json_decode($response->getContent(), true);

    $this->assertArrayHasKey('message', $responseData);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertEquals('Transação criada com sucesso', $responseData['message']);
    $this->assertIsArray($responseData['data']);
  }

  #[Test]
  public function it_returns_201_status_code()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 4]);

    $requestData = [
      'type' => 'income',
      'amount' => 100.00,
      'date' => '2024-01-15',
      'user_id' => $user->id,
    ];

    $createdTransaction = Transaction::factory()->make($requestData);
    $createdTransaction->id = '550e8400-e29b-41d4-a716-446655440000';

    $request = Mockery::mock(CreateTransactionRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createTransactionService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdTransaction);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $this->assertEquals(201, $response->getStatusCode());
  }

  #[Test]
  public function it_calls_service_with_validated_data()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 4]);

    $requestData = [
      'type' => 'expense',
      'amount' => 25.50,
      'date' => '2024-01-15',
      'user_id' => $user->id,
    ];

    $createdTransaction = Transaction::factory()->make($requestData);
    $createdTransaction->id = '550e8400-e29b-41d4-a716-446655440000';

    $request = Mockery::mock(CreateTransactionRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createTransactionService
      ->shouldReceive('create')
      ->once()
      ->with($requestData)
      ->andReturn($createdTransaction);

    // Act
    $this->controller->__invoke($request);

    // Assert - The mock expectation above will verify the service was called correctly
  }

  #[Test]
  public function it_handles_large_amounts()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 4]);

    $requestData = [
      'type' => 'income',
      'amount' => 999999.99,
      'date' => '2024-01-15',
      'user_id' => $user->id,
    ];

    $createdTransaction = Transaction::factory()->make($requestData);
    $createdTransaction->id = '550e8400-e29b-41d4-a716-446655440000';

    $request = Mockery::mock(CreateTransactionRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createTransactionService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdTransaction);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $this->assertEquals(201, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals(999999.99, $responseData['data']['amount']);
  }

  #[Test]
  public function it_handles_small_amounts()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 4]);

    $requestData = [
      'type' => 'expense',
      'amount' => 0.01,
      'date' => '2024-01-15',
      'user_id' => $user->id,
    ];

    $createdTransaction = Transaction::factory()->make($requestData);
    $createdTransaction->id = '550e8400-e29b-41d4-a716-446655440000';

    $request = Mockery::mock(CreateTransactionRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createTransactionService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdTransaction);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $this->assertEquals(201, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals(0.01, $responseData['data']['amount']);
  }
}
