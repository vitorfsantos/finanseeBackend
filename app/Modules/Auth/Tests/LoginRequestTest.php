<?php

namespace App\Modules\Auth\Tests;

use App\Modules\Auth\Requests\LoginRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LoginRequestTest extends TestCase
{
  use RefreshDatabase;

  protected LoginRequest $request;

  protected function setUp(): void
  {
    parent::setUp();

    $this->request = new LoginRequest();
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
    $this->assertArrayHasKey('email', $rules);
    $this->assertArrayHasKey('password', $rules);
    $this->assertStringContainsString('required', $rules['email']);
    $this->assertStringContainsString('email', $rules['email']);
    $this->assertStringContainsString('required', $rules['password']);
    $this->assertStringContainsString('string', $rules['password']);
  }

  #[Test]
  public function it_validates_required_email()
  {
    // Arrange
    $data = [
      'password' => 'password123'
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
    $data = [
      'email' => 'invalid-email',
      'password' => 'password123'
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('email', $validator->errors()->toArray());
    $this->assertContains('O email deve ser um endereço válido.', $validator->errors()->get('email'));
  }

  #[Test]
  public function it_validates_required_password()
  {
    // Arrange
    $data = [
      'email' => 'test@example.com'
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('password', $validator->errors()->toArray());
    $this->assertContains('A senha é obrigatória.', $validator->errors()->get('password'));
  }

  #[Test]
  public function it_passes_validation_with_valid_data()
  {
    // Arrange
    $data = [
      'email' => 'test@example.com',
      'password' => 'password123'
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
    $this->assertArrayHasKey('email.required', $messages);
    $this->assertArrayHasKey('email.email', $messages);
    $this->assertArrayHasKey('password.required', $messages);
    $this->assertArrayHasKey('password.min', $messages);

    $this->assertEquals('O email é obrigatório.', $messages['email.required']);
    $this->assertEquals('O email deve ser um endereço válido.', $messages['email.email']);
    $this->assertEquals('A senha é obrigatória.', $messages['password.required']);
    $this->assertEquals('A senha deve ter pelo menos 6 caracteres.', $messages['password.min']);
  }

  #[Test]
  public function it_accepts_various_valid_email_formats()
  {
    // Arrange
    $validEmails = [
      'test@example.com',
      'user.name@domain.co.uk',
      'test+tag@gmail.com',
      'admin@localhost'
    ];

    foreach ($validEmails as $email) {
      $data = [
        'email' => $email,
        'password' => 'password123'
      ];

      // Act
      $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

      // Assert
      $this->assertFalse($validator->fails(), "Email $email should be valid");
    }
  }
}
