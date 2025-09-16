<?php

namespace App\Modules\Transactions\Tests;

use PHPUnit\Framework\Attributes\Test;

use App\Modules\Transactions\Requests\CreateTransactionRequest;
use App\Modules\Users\Models\User;
use App\Modules\Companies\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use App\Modules\TestCase;

class CreateTransactionRequestTest extends TestCase
{

  protected CreateTransactionRequest $request;

  protected function setUp(): void
  {
    parent::setUp();

    $this->request = new CreateTransactionRequest();
  }

  #[Test]
  public function it_authorizes_all_requests()
  {
    // Act & Assert
    $this->assertTrue($this->request->authorize());
  }

  #[Test]
  public function it_has_correct_validation_rules()
  {
    // Act
    $rules = $this->request->rules();

    // Assert
    $expectedRules = [
      'type' => 'required|in:income,expense',
      'category' => 'nullable|string|max:255',
      'description' => 'nullable|string|max:1000',
      'amount' => 'required|numeric|min:0.01|max:999999.99',
      'date' => 'nullable|date',
      'user_id' => 'nullable|exists:users,id',
      'company_id' => 'nullable|exists:companies,id',
    ];

    $this->assertEquals($expectedRules, $rules);
  }

  #[Test]
  public function it_validates_required_type()
  {
    // Arrange
    $data = [
      'amount' => 25.50,
      'date' => '2024-01-15',
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('type', $validator->errors()->toArray());
    $this->assertContains('O tipo de transação é obrigatório.', $validator->errors()->get('type'));
  }

  #[Test]
  public function it_validates_type_enum_values()
  {
    // Arrange
    $data = [
      'type' => 'invalid_type',
      'amount' => 25.50,
      'date' => '2024-01-15',
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('type', $validator->errors()->toArray());
    $this->assertContains('O tipo deve ser "income" ou "expense".', $validator->errors()->get('type'));
  }

  #[Test]
  public function it_validates_required_amount()
  {
    // Arrange
    $data = [
      'type' => 'expense',
      'date' => '2024-01-15',
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    $this->assertContains('O valor é obrigatório.', $validator->errors()->get('amount'));
  }

  #[Test]
  public function it_validates_amount_is_numeric()
  {
    // Arrange
    $data = [
      'type' => 'expense',
      'amount' => 'not_a_number',
      'date' => '2024-01-15',
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    $this->assertContains('O valor deve ser um número.', $validator->errors()->get('amount'));
  }

  #[Test]
  public function it_validates_amount_minimum_value()
  {
    // Arrange
    $data = [
      'type' => 'expense',
      'amount' => 0.00,
      'date' => '2024-01-15',
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    $this->assertContains('O valor deve ser maior que zero.', $validator->errors()->get('amount'));
  }

  #[Test]
  public function it_validates_amount_maximum_value()
  {
    // Arrange
    $data = [
      'type' => 'expense',
      'amount' => 1000000.00,
      'date' => '2024-01-15',
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    $this->assertContains('O valor não pode ser maior que 999.999,99.', $validator->errors()->get('amount'));
  }


  #[Test]
  public function it_validates_date_format()
  {
    // Arrange
    $data = [
      'type' => 'expense',
      'amount' => 25.50,
      'date' => 'invalid_date',
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('date', $validator->errors()->toArray());
    $this->assertContains('A data deve ser uma data válida.', $validator->errors()->get('date'));
  }


  #[Test]
  public function it_validates_category_max_length()
  {
    // Arrange
    $data = [
      'type' => 'expense',
      'amount' => 25.50,
      'date' => '2024-01-15',
      'category' => str_repeat('a', 256), // 256 characters
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('category', $validator->errors()->toArray());
    $this->assertContains('A categoria não pode ter mais de 255 caracteres.', $validator->errors()->get('category'));
  }

  #[Test]
  public function it_validates_description_max_length()
  {
    // Arrange
    $data = [
      'type' => 'expense',
      'amount' => 25.50,
      'date' => '2024-01-15',
      'description' => str_repeat('a', 1001), // 1001 characters
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('description', $validator->errors()->toArray());
    $this->assertContains('A descrição não pode ter mais de 1000 caracteres.', $validator->errors()->get('description'));
  }

  #[Test]
  public function it_validates_user_id_exists()
  {
    // Arrange
    $data = [
      'type' => 'expense',
      'amount' => 25.50,
      'date' => '2024-01-15',
      'user_id' => '550e8400-e29b-41d4-a716-446655440000', // Non-existent UUID
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('user_id', $validator->errors()->toArray());
    $this->assertContains('O usuário não existe.', $validator->errors()->get('user_id'));
  }

  #[Test]
  public function it_validates_company_id_exists()
  {
    // Arrange
    $data = [
      'type' => 'expense',
      'amount' => 25.50,
      'date' => '2024-01-15',
      'company_id' => '550e8400-e29b-41d4-a716-446655440000', // Non-existent UUID
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('company_id', $validator->errors()->toArray());
    $this->assertContains('A empresa não existe.', $validator->errors()->get('company_id'));
  }

  #[Test]
  public function it_passes_validation_with_valid_data()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 4]); // user level
    $company = Company::factory()->create();

    $data = [
      'type' => 'expense',
      'category' => 'Alimentação',
      'description' => 'Almoço no restaurante',
      'amount' => 25.50,
      'date' => '2024-01-15',
      'user_id' => $user->id,
      'company_id' => $company->id,
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertFalse($validator->fails());
  }

  #[Test]
  public function it_passes_validation_with_minimal_data()
  {
    // Arrange
    $data = [
      'type' => 'income',
      'amount' => 100.00,
      'date' => '2024-01-15',
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertFalse($validator->fails());
  }

  #[Test]
  public function it_passes_validation_with_today_date()
  {
    // Arrange
    $data = [
      'type' => 'expense',
      'amount' => 25.50,
      'date' => now()->format('Y-m-d'),
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertFalse($validator->fails());
  }

  #[Test]
  public function it_validates_income_type()
  {
    // Arrange
    $data = [
      'type' => 'income',
      'amount' => 1000.00,
      'date' => '2024-01-15',
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertFalse($validator->fails());
  }

  #[Test]
  public function it_validates_expense_type()
  {
    // Arrange
    $data = [
      'type' => 'expense',
      'amount' => 50.00,
      'date' => '2024-01-15',
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertFalse($validator->fails());
  }
}
