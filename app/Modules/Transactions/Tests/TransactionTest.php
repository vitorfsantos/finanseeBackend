<?php

namespace App\Modules\Transactions\Tests;

use PHPUnit\Framework\Attributes\Test;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Users\Models\User;
use App\Modules\Companies\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  protected Transaction $transaction;
  protected User $user;
  protected Company $company;

  protected function setUp(): void
  {
    parent::setUp();

    $this->user = User::factory()->create(['user_level_id' => 4]); // user level
    $this->company = Company::factory()->create();
    $this->transaction = Transaction::factory()->create([
      'user_id' => $this->user->id,
      'company_id' => $this->company->id,
    ]);
  }

  #[Test]
  public function it_can_create_a_transaction_with_all_fields()
  {
    // Arrange
    $transactionData = [
      'user_id' => $this->user->id,
      'company_id' => $this->company->id,
      'type' => 'expense',
      'category' => 'Alimentação',
      'description' => 'Almoço no restaurante',
      'amount' => 25.50,
      'date' => '2024-01-15',
    ];

    // Act
    $transaction = Transaction::create($transactionData);

    // Assert
    $this->assertInstanceOf(Transaction::class, $transaction);
    $this->assertEquals($transactionData['user_id'], $transaction->user_id);
    $this->assertEquals($transactionData['company_id'], $transaction->company_id);
    $this->assertEquals($transactionData['type'], $transaction->type);
    $this->assertEquals($transactionData['category'], $transaction->category);
    $this->assertEquals($transactionData['description'], $transaction->description);
    $this->assertEquals($transactionData['amount'], $transaction->amount);
    $this->assertEquals($transactionData['date'], $transaction->date);
    $this->assertNotNull($transaction->id);
    $this->assertNotNull($transaction->created_at);
    $this->assertNotNull($transaction->updated_at);
  }

  #[Test]
  public function it_can_create_a_transaction_with_only_required_fields()
  {
    // Arrange
    $transactionData = [
      'user_id' => $this->user->id,
      'type' => 'income',
      'amount' => 100.00,
      'date' => '2024-01-15',
    ];

    // Act
    $transaction = Transaction::create($transactionData);

    // Assert
    $this->assertInstanceOf(Transaction::class, $transaction);
    $this->assertEquals($transactionData['user_id'], $transaction->user_id);
    $this->assertEquals($transactionData['type'], $transaction->type);
    $this->assertEquals($transactionData['amount'], $transaction->amount);
    $this->assertEquals($transactionData['date'], $transaction->date);
    $this->assertNull($transaction->company_id);
    $this->assertNull($transaction->category);
    $this->assertNull($transaction->description);
  }

  #[Test]
  public function it_casts_amount_to_decimal()
  {
    // Arrange
    $transaction = Transaction::factory()->create([
      'amount' => '25.50',
    ]);

    // Act & Assert
    $this->assertIsFloat($transaction->amount);
    $this->assertEquals(25.50, $transaction->amount);
  }

  #[Test]
  public function it_casts_date_to_date_object()
  {
    // Arrange
    $transaction = Transaction::factory()->create([
      'date' => '2024-01-15',
    ]);

    // Act & Assert
    $this->assertInstanceOf(\Carbon\Carbon::class, $transaction->date);
    $this->assertEquals('2024-01-15', $transaction->date->format('Y-m-d'));
  }

  #[Test]
  public function it_belongs_to_a_user()
  {
    // Act & Assert
    $this->assertInstanceOf(User::class, $this->transaction->user);
    $this->assertEquals($this->user->id, $this->transaction->user->id);
  }

  #[Test]
  public function it_belongs_to_a_company()
  {
    // Act & Assert
    $this->assertInstanceOf(Company::class, $this->transaction->company);
    $this->assertEquals($this->company->id, $this->transaction->company->id);
  }

  #[Test]
  public function it_can_have_null_company()
  {
    // Arrange
    $transaction = Transaction::factory()->create([
      'company_id' => null,
    ]);

    // Act & Assert
    $this->assertNull($transaction->company_id);
    $this->assertNull($transaction->company);
  }

  #[Test]
  public function it_uses_soft_deletes()
  {
    // Act
    $this->transaction->delete();

    // Assert
    $this->assertSoftDeleted($this->transaction);
    $this->assertDatabaseHas('transactions', [
      'id' => $this->transaction->id,
    ]);
  }

  #[Test]
  public function it_uses_uuids()
  {
    // Act & Assert
    $this->assertIsString($this->transaction->id);
    $this->assertEquals(36, strlen($this->transaction->id));
  }

  #[Test]
  public function it_has_fillable_fields()
  {
    // Act
    $fillable = $this->transaction->getFillable();

    // Assert
    $expectedFillable = [
      'user_id',
      'company_id',
      'type',
      'category',
      'description',
      'amount',
      'date',
    ];

    $this->assertEquals($expectedFillable, $fillable);
  }

  #[Test]
  public function it_scope_for_user()
  {
    // Arrange
    $otherUser = User::factory()->create();
    Transaction::factory()->create(['user_id' => $otherUser->id]);

    // Act
    $userTransactions = Transaction::forUser($this->user->id)->get();

    // Assert
    $this->assertCount(1, $userTransactions);
    $this->assertEquals($this->user->id, $userTransactions->first()->user_id);
  }

  #[Test]
  public function it_scope_for_company()
  {
    // Arrange
    $otherCompany = Company::factory()->create();
    Transaction::factory()->create(['company_id' => $otherCompany->id]);

    // Act
    $companyTransactions = Transaction::forCompany($this->company->id)->get();

    // Assert
    $this->assertCount(1, $companyTransactions);
    $this->assertEquals($this->company->id, $companyTransactions->first()->company_id);
  }

  #[Test]
  public function it_scope_of_type()
  {
    // Arrange
    Transaction::factory()->create(['type' => 'income']);

    // Act
    $expenseTransactions = Transaction::ofType('expense')->get();

    // Assert
    $this->assertCount(1, $expenseTransactions);
    $this->assertEquals('expense', $expenseTransactions->first()->type);
  }

  #[Test]
  public function it_scope_in_date_range()
  {
    // Arrange
    $startDate = '2024-01-01';
    $endDate = '2024-01-31';
    Transaction::factory()->create(['date' => '2024-02-01']); // Outside range

    // Act
    $rangeTransactions = Transaction::inDateRange($startDate, $endDate)->get();

    // Assert
    $this->assertCount(1, $rangeTransactions);
    $this->assertTrue($rangeTransactions->first()->date->between($startDate, $endDate));
  }
}
