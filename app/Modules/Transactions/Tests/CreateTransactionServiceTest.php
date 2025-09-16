<?php

namespace App\Modules\Transactions\Tests;

use PHPUnit\Framework\Attributes\Test;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\Services\CreateTransactionService;
use App\Modules\Users\Models\User;
use App\Modules\Companies\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Modules\TestCase;

class CreateTransactionServiceTest extends TestCase
{

  protected CreateTransactionService $service;
  protected User $user;
  protected Company $company;

  protected function setUp(): void
  {
    parent::setUp();

    $this->service = new CreateTransactionService();
    $this->user = User::factory()->create(['user_level_id' => 4]); // user level
    $this->company = Company::factory()->create();
  }

  #[Test]
  public function it_can_create_a_transaction_with_all_fields()
  {
    // Arrange
    $transactionData = [
      'type' => 'expense',
      'category' => 'Alimentação',
      'description' => 'Almoço no restaurante',
      'amount' => 25.50,
      'date' => '2024-01-15',
      'user_id' => $this->user->id,
      'company_id' => $this->company->id,
    ];

    // Act
    $transaction = $this->service->create($transactionData);

    // Assert
    $this->assertInstanceOf(Transaction::class, $transaction);
    $this->assertEquals($transactionData['type'], $transaction->type);
    $this->assertEquals($transactionData['category'], $transaction->category);
    $this->assertEquals($transactionData['description'], $transaction->description);
    $this->assertEquals($transactionData['amount'], $transaction->amount);
    $this->assertEquals($transactionData['date'], $transaction->date->format('Y-m-d'));
    $this->assertEquals($transactionData['user_id'], $transaction->user_id);
    $this->assertEquals($transactionData['company_id'], $transaction->company_id);
  }

  #[Test]
  public function it_can_create_a_transaction_with_only_required_fields()
  {
    // Arrange
    $transactionData = [
      'type' => 'income',
      'amount' => 100.00,
      'date' => '2024-01-15',
      'user_id' => $this->user->id,
    ];

    // Act
    $transaction = $this->service->create($transactionData);

    // Assert
    $this->assertInstanceOf(Transaction::class, $transaction);
    $this->assertEquals($transactionData['type'], $transaction->type);
    $this->assertEquals($transactionData['amount'], $transaction->amount);
    $this->assertEquals($transactionData['date'], $transaction->date->format('Y-m-d'));
    $this->assertEquals($transactionData['user_id'], $transaction->user_id);
    $this->assertNull($transaction->company_id);
    $this->assertNull($transaction->category);
    $this->assertNull($transaction->description);
  }

  #[Test]
  public function it_sets_user_id_from_auth_when_not_provided()
  {
    // Arrange
    $this->actingAs($this->user);

    $transactionData = [
      'type' => 'expense',
      'amount' => 25.50,
      'date' => '2024-01-15',
    ];

    // Act
    $transaction = $this->service->create($transactionData);

    // Assert
    $this->assertEquals($this->user->id, $transaction->user_id);
  }

  #[Test]
  public function it_creates_transaction_in_database()
  {
    // Arrange
    $transactionData = [
      'type' => 'expense',
      'category' => 'Transporte',
      'description' => 'Combustível',
      'amount' => 120.00,
      'date' => '2024-01-15',
      'user_id' => $this->user->id,
      'company_id' => $this->company->id,
    ];

    // Act
    $transaction = $this->service->create($transactionData);

    // Assert
    $this->assertDatabaseHas('transactions', [
      'id' => $transaction->id,
      'type' => 'expense',
      'category' => 'Transporte',
      'description' => 'Combustível',
      'amount' => 120.00,
      'user_id' => $this->user->id,
      'company_id' => $this->company->id,
    ]);
  }

  #[Test]
  public function it_creates_transaction_with_correct_type()
  {
    // Arrange
    $incomeData = [
      'type' => 'income',
      'amount' => 1000.00,
      'date' => '2024-01-15',
      'user_id' => $this->user->id,
    ];

    $expenseData = [
      'type' => 'expense',
      'amount' => 50.00,
      'date' => '2024-01-15',
      'user_id' => $this->user->id,
    ];

    // Act
    $incomeTransaction = $this->service->create($incomeData);
    $expenseTransaction = $this->service->create($expenseData);

    // Assert
    $this->assertEquals('income', $incomeTransaction->type);
    $this->assertEquals('expense', $expenseTransaction->type);
  }

  #[Test]
  public function it_creates_transaction_with_correct_amount()
  {
    // Arrange
    $transactionData = [
      'type' => 'expense',
      'amount' => 99.99,
      'date' => '2024-01-15',
      'user_id' => $this->user->id,
    ];

    // Act
    $transaction = $this->service->create($transactionData);

    // Assert
    $this->assertEquals(99.99, $transaction->amount);
  }

  #[Test]
  public function it_creates_transaction_with_correct_date()
  {
    // Arrange
    $transactionData = [
      'type' => 'income',
      'amount' => 500.00,
      'date' => '2024-01-15',
      'user_id' => $this->user->id,
    ];

    // Act
    $transaction = $this->service->create($transactionData);

    // Assert
    $this->assertEquals('2024-01-15', $transaction->date->format('Y-m-d'));
    $this->assertInstanceOf(\Carbon\Carbon::class, $transaction->date);
  }

  #[Test]
  public function it_creates_transaction_with_optional_fields()
  {
    // Arrange
    $transactionData = [
      'type' => 'expense',
      'amount' => 75.50,
      'date' => '2024-01-15',
      'user_id' => $this->user->id,
      'category' => 'Lazer',
      'description' => 'Cinema com amigos',
    ];

    // Act
    $transaction = $this->service->create($transactionData);

    // Assert
    $this->assertEquals('Lazer', $transaction->category);
    $this->assertEquals('Cinema com amigos', $transaction->description);
  }

  #[Test]
  public function it_creates_transaction_without_company()
  {
    // Arrange
    $transactionData = [
      'type' => 'expense',
      'amount' => 30.00,
      'date' => '2024-01-15',
      'user_id' => $this->user->id,
      // company_id not provided
    ];

    // Act
    $transaction = $this->service->create($transactionData);

    // Assert
    $this->assertNull($transaction->company_id);
  }

  #[Test]
  public function it_creates_transaction_with_company()
  {
    // Arrange
    $transactionData = [
      'type' => 'income',
      'amount' => 2000.00,
      'date' => '2024-01-15',
      'user_id' => $this->user->id,
      'company_id' => $this->company->id,
    ];

    // Act
    $transaction = $this->service->create($transactionData);

    // Assert
    $this->assertEquals($this->company->id, $transaction->company_id);
  }

  #[Test]
  public function it_creates_multiple_transactions()
  {
    // Arrange
    $transactionsData = [
      [
        'type' => 'income',
        'amount' => 1000.00,
        'date' => '2024-01-15',
        'user_id' => $this->user->id,
      ],
      [
        'type' => 'expense',
        'amount' => 50.00,
        'date' => '2024-01-16',
        'user_id' => $this->user->id,
      ],
    ];

    // Act
    $transactions = [];
    foreach ($transactionsData as $data) {
      $transactions[] = $this->service->create($data);
    }

    // Assert
    $this->assertCount(2, $transactions);
    $this->assertEquals('income', $transactions[0]->type);
    $this->assertEquals('expense', $transactions[1]->type);
  }

  #[Test]
  public function it_uses_current_datetime_when_date_not_provided()
  {
    // Arrange
    $this->actingAs($this->user);

    $transactionData = [
      'type' => 'expense',
      'amount' => 25.50,
      // date not provided
    ];

    $beforeCreation = now();

    // Act
    $transaction = $this->service->create($transactionData);

    $afterCreation = now();

    // Assert
    $this->assertInstanceOf(Transaction::class, $transaction);
    $this->assertEquals($this->user->id, $transaction->user_id);
    $this->assertInstanceOf(\Carbon\Carbon::class, $transaction->date);
    $this->assertTrue($transaction->date->between($beforeCreation->subSecond(), $afterCreation->addSecond()));
  }

  #[Test]
  public function it_can_create_transaction_with_minimal_required_fields()
  {
    // Arrange
    $this->actingAs($this->user);

    $transactionData = [
      'type' => 'income',
      'amount' => 100.00,
      // date and user_id not provided - should use defaults
    ];

    // Act
    $transaction = $this->service->create($transactionData);

    // Assert
    $this->assertInstanceOf(Transaction::class, $transaction);
    $this->assertEquals('income', $transaction->type);
    $this->assertEquals(100.00, $transaction->amount);
    $this->assertEquals($this->user->id, $transaction->user_id);
    $this->assertInstanceOf(\Carbon\Carbon::class, $transaction->date);
    $this->assertNull($transaction->company_id);
    $this->assertNull($transaction->category);
    $this->assertNull($transaction->description);
  }
}
