<?php

namespace App\Modules\Users\Tests;

use PHPUnit\Framework\Attributes\Test;

use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLevel;
use App\Modules\Users\Requests\CreateUserRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use App\Modules\TestCase;

class CreateUserRequestTest extends TestCase
{

  protected CreateUserRequest $request;

  protected function setUp(): void
  {
    parent::setUp();

    $this->request = new CreateUserRequest();
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
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:users,email|max:255',
      'password' => 'required|string|min:6|max:255',
      'user_level_id' => 'required|exists:user_levels,id',
      'phone' => 'nullable|string|max:255',
      'companies' => 'nullable|array',
      'companies.*.company_id' => 'required_with:companies|exists:companies,id',
      'companies.*.role' => 'required_with:companies|in:owner,manager,employee',
      'companies.*.position' => 'nullable|string|max:255',
    ];

    $this->assertEquals($expectedRules, $rules);
  }

  #[Test]
  public function it_validates_required_name()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $data = [
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('name', $validator->errors()->toArray());
    $this->assertContains('O nome é obrigatório.', $validator->errors()->get('name'));
  }

  #[Test]
  public function it_validates_name_max_length()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $data = [
      'name' => str_repeat('a', 256), // 256 characters
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('name', $validator->errors()->toArray());
    $this->assertContains('O nome não pode ter mais de 255 caracteres.', $validator->errors()->get('name'));
  }

  #[Test]
  public function it_validates_required_email()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $data = [
      'name' => 'John Doe',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('email', $validator->errors()->toArray());
    $this->assertContains('O email é obrigatório.', $validator->errors()->get('email'));
  }

  #[Test]
  public function it_validates_email_format()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $data = [
      'name' => 'John Doe',
      'email' => 'invalid-email',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('email', $validator->errors()->toArray());
    $this->assertContains('O email deve ser um endereço válido.', $validator->errors()->get('email'));
  }

  #[Test]
  public function it_validates_email_uniqueness()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();
    User::factory()->create(['email' => 'john@example.com', 'user_level_id' => $userLevel->id]);

    $data = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('email', $validator->errors()->toArray());
    $this->assertContains('Este email já está em uso.', $validator->errors()->get('email'));
  }

  #[Test]
  public function it_validates_email_max_length()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $data = [
      'name' => 'John Doe',
      'email' => str_repeat('a', 250) . '@test.com', // More than 255 characters
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('email', $validator->errors()->toArray());
    $this->assertContains('O email não pode ter mais de 255 caracteres.', $validator->errors()->get('email'));
  }

  #[Test]
  public function it_validates_required_password()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $data = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('password', $validator->errors()->toArray());
    $this->assertContains('A senha é obrigatória.', $validator->errors()->get('password'));
  }

  #[Test]
  public function it_validates_password_min_length()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $data = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => '12345', // 5 characters (less than 6)
      'user_level_id' => $userLevel->id
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('password', $validator->errors()->toArray());
    $this->assertContains('A senha deve ter pelo menos 6 caracteres.', $validator->errors()->get('password'));
  }

  #[Test]
  public function it_validates_password_max_length()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $data = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => str_repeat('a', 256), // 256 characters
      'user_level_id' => $userLevel->id
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('password', $validator->errors()->toArray());
    $this->assertContains('A senha não pode ter mais de 255 caracteres.', $validator->errors()->get('password'));
  }

  #[Test]
  public function it_validates_required_user_level_id()
  {
    // Arrange
    $data = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123'
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('user_level_id', $validator->errors()->toArray());
    $this->assertContains('O nível de usuário é obrigatório.', $validator->errors()->get('user_level_id'));
  }

  #[Test]
  public function it_validates_user_level_id_exists()
  {
    // Arrange
    $data = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => 999 // Non-existent ID
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('user_level_id', $validator->errors()->toArray());
    $this->assertContains('O nível de usuário não existe.', $validator->errors()->get('user_level_id'));
  }

  #[Test]
  public function it_passes_validation_with_all_valid_fields()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $data = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertFalse($validator->fails());
    $this->assertEmpty($validator->errors()->toArray());
  }

  #[Test]
  public function it_has_custom_error_messages()
  {
    // Act
    $messages = $this->request->messages();

    // Assert
    $expectedMessages = [
      'name.required' => 'O nome é obrigatório.',
      'name.max' => 'O nome não pode ter mais de 255 caracteres.',
      'email.required' => 'O email é obrigatório.',
      'email.email' => 'O email deve ser um endereço válido.',
      'email.unique' => 'Este email já está em uso.',
      'email.max' => 'O email não pode ter mais de 255 caracteres.',
      'password.required' => 'A senha é obrigatória.',
      'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
      'password.max' => 'A senha não pode ter mais de 255 caracteres.',
      'user_level_id.required' => 'O nível de usuário é obrigatório.',
      'user_level_id.exists' => 'O nível de usuário não existe.',
      'companies.array' => 'Companies deve ser um array.',
      'companies.*.company_id.required_with' => 'O ID da empresa é obrigatório.',
      'companies.*.company_id.exists' => 'A empresa especificada não existe.',
      'companies.*.role.required_with' => 'O cargo é obrigatório.',
      'companies.*.role.in' => 'O cargo deve ser: owner, manager ou employee.',
      'companies.*.position.max' => 'A posição não pode ter mais de 255 caracteres.',
    ];

    $this->assertEquals($expectedMessages, $messages);
  }

  #[Test]
  public function it_accepts_various_valid_email_formats()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $validEmails = [
      'test@example.com',
      'user.name@domain.co.uk',
      'test+tag@gmail.com',
      'admin@localhost'
    ];

    foreach ($validEmails as $email) {
      $data = [
        'name' => 'John Doe',
        'email' => $email,
        'password' => 'password123',
        'user_level_id' => $userLevel->id
      ];

      // Act
      $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

      // Assert
      $this->assertFalse($validator->fails(), "Email $email should be valid");
    }
  }

  #[Test]
  public function it_accepts_minimum_valid_password()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $data = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => '123456', // Exactly 6 characters
      'user_level_id' => $userLevel->id
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertFalse($validator->fails());
  }

  #[Test]
  public function it_accepts_maximum_valid_password()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $data = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => str_repeat('a', 255), // Exactly 255 characters
      'user_level_id' => $userLevel->id
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertFalse($validator->fails());
  }

  #[Test]
  public function it_accepts_valid_user_level_ids()
  {
    // Arrange (UserLevels are already created by TestCase base)
    $validLevelIds = [1, 2, 3, 4];

    foreach ($validLevelIds as $levelId) {
      $data = [
        'name' => 'John Doe',
        'email' => "john$levelId@example.com",
        'password' => 'password123',
        'user_level_id' => $levelId
      ];

      // Act
      $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

      // Assert
      $this->assertFalse($validator->fails(), "User level ID $levelId should be valid");
    }
  }
}
