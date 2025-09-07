<?php

namespace App\Modules\Transactions\Tests;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLevel;
use App\Modules\Companies\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TransactionsIntegrationTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  protected User $user;
  protected User $companyAdmin;
  protected User $adminMaster;
  protected Company $company;

  protected function setUp(): void
  {
    parent::setUp();

    // Create user levels
    $userLevel = UserLevel::factory()->create(['id' => 4, 'slug' => 'user', 'name' => 'Usuário']);
    $companyAdminLevel = UserLevel::factory()->create(['id' => 2, 'slug' => 'companyAdmin', 'name' => 'Administrador da Empresa']);
    $adminMasterLevel = UserLevel::factory()->create(['id' => 1, 'slug' => 'adminMaster', 'name' => 'Administrador Master']);

    // Create users
    $this->user = User::factory()->create(['user_level_id' => $userLevel->id]);
    $this->companyAdmin = User::factory()->create(['user_level_id' => $companyAdminLevel->id]);
    $this->adminMaster = User::factory()->create(['user_level_id' => $adminMasterLevel->id]);

    // Create company
    $this->company = Company::factory()->create();

    // Associate companyAdmin with company
    \Illuminate\Support\Facades\DB::table('company_user')->insert([
      'id' => \Illuminate\Support\Str::uuid(),
      'company_id' => $this->company->id,
      'user_id' => $this->companyAdmin->id,
      'role' => 'manager',
      'position' => 'Manager',
      'created_at' => now(),
      'updated_at' => now(),
    ]);
  }

  #[Test]
  public function it_can_create_transaction_via_api()
  {
    // Arrange
    $this->actingAs($this->user);

    $transactionData = [
      'type' => 'expense',
      'category' => 'Alimentação',
      'description' => 'Almoço no restaurante',
      'amount' => 25.50,
      'date' => '2024-01-15',
    ];

    // Act
    $response = $this->postJson('/api/transactions', $transactionData);

    // Assert
    $response->assertStatus(201);
    $response->assertJsonStructure([
      'message',
      'data' => [
        'id',
        'user_id',
        'company_id',
        'type',
        'category',
        'description',
        'amount',
        'date',
        'created_at',
        'updated_at',
      ],
    ]);

    $this->assertDatabaseHas('transactions', [
      'type' => 'expense',
      'category' => 'Alimentação',
      'description' => 'Almoço no restaurante',
      'amount' => 25.50,
      'user_id' => $this->user->id,
    ]);
  }

  #[Test]
  public function it_can_list_transactions_via_api()
  {
    // Arrange
    $this->actingAs($this->user);

    // Create some transactions
    Transaction::factory()->count(3)->create([
      'user_id' => $this->user->id,
      'company_id' => null,
    ]);

    // Act
    $response = $this->getJson('/api/transactions');

    // Assert
    $response->assertStatus(200);
    $response->assertJsonStructure([
      'data' => [
        '*' => [
          'id',
          'user_id',
          'company_id',
          'type',
          'category',
          'description',
          'amount',
          'date',
          'created_at',
          'updated_at',
        ],
      ],
      'current_page',
      'last_page',
      'per_page',
      'total',
    ]);

    $this->assertCount(3, $response->json('data'));
  }

  #[Test]
  public function it_can_show_transaction_via_api()
  {
    // Arrange
    $this->actingAs($this->user);

    $transaction = Transaction::factory()->create([
      'user_id' => $this->user->id,
      'company_id' => null,
    ]);

    // Act
    $response = $this->getJson("/api/transactions/{$transaction->id}");

    // Assert
    $response->assertStatus(200);
    $response->assertJsonStructure([
      'data' => [
        'id',
        'user_id',
        'company_id',
        'type',
        'category',
        'description',
        'amount',
        'date',
        'created_at',
        'updated_at',
      ],
    ]);

    $this->assertEquals($transaction->id, $response->json('data.id'));
  }

  #[Test]
  public function it_can_update_transaction_via_api()
  {
    // Arrange
    $this->actingAs($this->user);

    $transaction = Transaction::factory()->create([
      'user_id' => $this->user->id,
      'company_id' => null,
    ]);

    $updateData = [
      'category' => 'Transporte',
      'description' => 'Combustível atualizado',
      'amount' => 30.00,
    ];

    // Act
    $response = $this->putJson("/api/transactions/{$transaction->id}", $updateData);

    // Assert
    $response->assertStatus(200);
    $response->assertJsonStructure([
      'message',
      'data' => [
        'id',
        'user_id',
        'company_id',
        'type',
        'category',
        'description',
        'amount',
        'date',
        'created_at',
        'updated_at',
      ],
    ]);

    $this->assertDatabaseHas('transactions', [
      'id' => $transaction->id,
      'category' => 'Transporte',
      'description' => 'Combustível atualizado',
      'amount' => 30.00,
    ]);
  }

  #[Test]
  public function it_can_delete_transaction_via_api()
  {
    // Arrange
    $this->actingAs($this->user);

    $transaction = Transaction::factory()->create([
      'user_id' => $this->user->id,
      'company_id' => null,
    ]);

    // Act
    $response = $this->deleteJson("/api/transactions/{$transaction->id}");

    // Assert
    $response->assertStatus(200);
    $response->assertJson([
      'message' => 'Transação excluída com sucesso',
    ]);

    $this->assertSoftDeleted($transaction);
  }

  #[Test]
  public function it_validates_required_fields()
  {
    // Arrange
    $this->actingAs($this->user);

    $invalidData = [
      'category' => 'Alimentação',
      'description' => 'Almoço no restaurante',
      'amount' => 25.50,
      // Missing type and date
    ];

    // Act
    $response = $this->postJson('/api/transactions', $invalidData);

    // Assert
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['type', 'date']);
  }

  #[Test]
  public function it_validates_amount_minimum()
  {
    // Arrange
    $this->actingAs($this->user);

    $invalidData = [
      'type' => 'expense',
      'amount' => 0.00, // Below minimum
      'date' => '2024-01-15',
    ];

    // Act
    $response = $this->postJson('/api/transactions', $invalidData);

    // Assert
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['amount']);
  }

  #[Test]
  public function it_validates_date_not_in_future()
  {
    // Arrange
    $this->actingAs($this->user);

    $futureDate = now()->addDays(1)->format('Y-m-d');
    $invalidData = [
      'type' => 'expense',
      'amount' => 25.50,
      'date' => $futureDate,
    ];

    // Act
    $response = $this->postJson('/api/transactions', $invalidData);

    // Assert
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['date']);
  }

  #[Test]
  public function it_filters_transactions_by_type()
  {
    // Arrange
    $this->actingAs($this->user);

    // Create income transactions
    Transaction::factory()->count(2)->create([
      'user_id' => $this->user->id,
      'type' => 'income',
    ]);

    // Create expense transactions
    Transaction::factory()->count(3)->create([
      'user_id' => $this->user->id,
      'type' => 'expense',
    ]);

    // Act
    $response = $this->getJson('/api/transactions?type=income');

    // Assert
    $response->assertStatus(200);
    $this->assertCount(2, $response->json('data'));

    foreach ($response->json('data') as $transaction) {
      $this->assertEquals('income', $transaction['type']);
    }
  }

  #[Test]
  public function it_filters_transactions_by_category()
  {
    // Arrange
    $this->actingAs($this->user);

    Transaction::factory()->create([
      'user_id' => $this->user->id,
      'category' => 'Alimentação',
    ]);

    Transaction::factory()->create([
      'user_id' => $this->user->id,
      'category' => 'Transporte',
    ]);

    // Act
    $response = $this->getJson('/api/transactions?category=Alimentação');

    // Assert
    $response->assertStatus(200);
    $this->assertCount(1, $response->json('data'));
    $this->assertEquals('Alimentação', $response->json('data.0.category'));
  }

  #[Test]
  public function it_paginates_transactions()
  {
    // Arrange
    $this->actingAs($this->user);

    // Create more than default per_page (15)
    Transaction::factory()->count(20)->create([
      'user_id' => $this->user->id,
    ]);

    // Act
    $response = $this->getJson('/api/transactions?per_page=10');

    // Assert
    $response->assertStatus(200);
    $this->assertCount(10, $response->json('data'));
    $this->assertEquals(10, $response->json('per_page'));
    $this->assertEquals(20, $response->json('total'));
    $this->assertEquals(2, $response->json('last_page'));
  }

  #[Test]
  public function it_creates_company_transaction_for_company_admin()
  {
    // Arrange
    $this->actingAs($this->companyAdmin);

    $transactionData = [
      'type' => 'income',
      'category' => 'Vendas',
      'description' => 'Venda de produtos',
      'amount' => 1500.00,
      'date' => '2024-01-15',
      'user_id' => $this->companyAdmin->id,
    ];

    // Act
    $response = $this->postJson('/api/transactions', $transactionData);

    // Assert
    $response->assertStatus(201);

    $this->assertDatabaseHas('transactions', [
      'type' => 'income',
      'category' => 'Vendas',
      'user_id' => $this->companyAdmin->id,
      'company_id' => $this->company->id, // Should be automatically set
    ]);
  }

  #[Test]
  public function it_creates_transaction_for_any_user_as_admin_master()
  {
    // Arrange
    $this->actingAs($this->adminMaster);

    $transactionData = [
      'type' => 'expense',
      'category' => 'Marketing',
      'description' => 'Campanha publicitária',
      'amount' => 5000.00,
      'date' => '2024-01-15',
      'user_id' => $this->user->id, // Different user
      'company_id' => $this->company->id, // Different company
    ];

    // Act
    $response = $this->postJson('/api/transactions', $transactionData);

    // Assert
    $response->assertStatus(201);

    $this->assertDatabaseHas('transactions', [
      'type' => 'expense',
      'category' => 'Marketing',
      'user_id' => $this->user->id,
      'company_id' => $this->company->id,
    ]);
  }
}

