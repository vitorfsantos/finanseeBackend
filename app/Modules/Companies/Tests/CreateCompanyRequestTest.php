<?php

namespace App\Modules\Companies\Tests;

use PHPUnit\Framework\Attributes\Test;

use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Requests\CreateCompanyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateCompanyRequestTest extends TestCase
{
  use RefreshDatabase;

  protected CreateCompanyRequest $request;

  protected function setUp(): void
  {
    parent::setUp();

    $this->request = new CreateCompanyRequest();
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
      'cnpj' => 'required|string|unique:companies,cnpj|max:18',
      'email' => 'nullable|email|max:255',
      'phone' => 'nullable|string|max:20',
      'address' => 'nullable|array',
      'address.street' => 'required_with:address|string|max:255',
      'address.number' => 'nullable|string|max:20',
      'address.complement' => 'nullable|string|max:255',
      'address.neighborhood' => 'nullable|string|max:255',
      'address.city' => 'required_with:address|string|max:255',
      'address.state' => 'required_with:address|string|size:2',
      'address.zipcode' => 'required_with:address|string|max:10',
      'address.country' => 'nullable|string|max:255',
    ];

    $this->assertEquals($expectedRules, $rules);
  }

  #[Test]
  public function it_validates_required_name()
  {
    // Arrange
    $data = [
      'cnpj' => '12.345.678/0001-90'
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('name', $validator->errors()->toArray());
    $this->assertContains('O nome da empresa é obrigatório.', $validator->errors()->get('name'));
  }

  #[Test]
  public function it_validates_name_max_length()
  {
    // Arrange
    $data = [
      'name' => str_repeat('a', 256), // 256 characters
      'cnpj' => '12.345.678/0001-90'
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('name', $validator->errors()->toArray());
    $this->assertContains('O nome da empresa não pode ter mais de 255 caracteres.', $validator->errors()->get('name'));
  }

  #[Test]
  public function it_validates_required_cnpj()
  {
    // Arrange
    $data = [
      'name' => 'Test Company'
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('cnpj', $validator->errors()->toArray());
    $this->assertContains('O CNPJ é obrigatório.', $validator->errors()->get('cnpj'));
  }

  #[Test]
  public function it_validates_cnpj_uniqueness()
  {
    // Arrange
    Company::factory()->create(['cnpj' => '12.345.678/0001-90']);

    $data = [
      'name' => 'Test Company',
      'cnpj' => '12.345.678/0001-90'
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('cnpj', $validator->errors()->toArray());
    $this->assertContains('Este CNPJ já está em uso.', $validator->errors()->get('cnpj'));
  }

  #[Test]
  public function it_validates_cnpj_max_length()
  {
    // Arrange
    $data = [
      'name' => 'Test Company',
      'cnpj' => str_repeat('1', 19) // 19 characters
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('cnpj', $validator->errors()->toArray());
    $this->assertContains('O CNPJ não pode ter mais de 18 caracteres.', $validator->errors()->get('cnpj'));
  }

  #[Test]
  public function it_validates_email_format()
  {
    // Arrange
    $data = [
      'name' => 'Test Company',
      'cnpj' => '12.345.678/0001-90',
      'email' => 'invalid-email'
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('email', $validator->errors()->toArray());
    $this->assertContains('O email deve ser um endereço válido.', $validator->errors()->get('email'));
  }

  #[Test]
  public function it_validates_email_max_length()
  {
    // Arrange
    $data = [
      'name' => 'Test Company',
      'cnpj' => '12.345.678/0001-90',
      'email' => str_repeat('a', 250) . '@test.com' // More than 255 characters
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('email', $validator->errors()->toArray());
    $this->assertContains('O email não pode ter mais de 255 caracteres.', $validator->errors()->get('email'));
  }

  #[Test]
  public function it_validates_phone_max_length()
  {
    // Arrange
    $data = [
      'name' => 'Test Company',
      'cnpj' => '12.345.678/0001-90',
      'phone' => str_repeat('1', 21) // 21 characters
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertTrue($validator->fails());
    $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    $this->assertContains('O telefone não pode ter mais de 20 caracteres.', $validator->errors()->get('phone'));
  }

  #[Test]
  public function it_passes_validation_with_all_valid_fields()
  {
    // Arrange
    $data = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90',
      'email' => 'contact@testcompany.com',
      'phone' => '(11) 3333-4444'
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertFalse($validator->fails());
    $this->assertEmpty($validator->errors()->toArray());
  }

  #[Test]
  public function it_passes_validation_with_only_required_fields()
  {
    // Arrange
    $data = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90'
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertFalse($validator->fails());
    $this->assertEmpty($validator->errors()->toArray());
  }

  #[Test]
  public function it_allows_null_email()
  {
    // Arrange
    $data = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90',
      'email' => null
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertFalse($validator->fails());
  }

  #[Test]
  public function it_allows_null_phone()
  {
    // Arrange
    $data = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90',
      'phone' => null
    ];

    // Act
    $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

    // Assert
    $this->assertFalse($validator->fails());
  }

  #[Test]
  public function it_has_custom_error_messages()
  {
    // Act
    $messages = $this->request->messages();

    // Assert
    $expectedMessages = [
      'name.required' => 'O nome da empresa é obrigatório.',
      'name.max' => 'O nome da empresa não pode ter mais de 255 caracteres.',
      'cnpj.required' => 'O CNPJ é obrigatório.',
      'cnpj.unique' => 'Este CNPJ já está em uso.',
      'cnpj.max' => 'O CNPJ não pode ter mais de 18 caracteres.',
      'email.email' => 'O email deve ser um endereço válido.',
      'email.max' => 'O email não pode ter mais de 255 caracteres.',
      'phone.max' => 'O telefone não pode ter mais de 20 caracteres.',
      'address.street.required_with' => 'A rua é obrigatória quando o endereço é fornecido.',
      'address.street.max' => 'A rua não pode ter mais de 255 caracteres.',
      'address.number.max' => 'O número não pode ter mais de 20 caracteres.',
      'address.complement.max' => 'O complemento não pode ter mais de 255 caracteres.',
      'address.neighborhood.max' => 'O bairro não pode ter mais de 255 caracteres.',
      'address.city.required_with' => 'A cidade é obrigatória quando o endereço é fornecido.',
      'address.city.max' => 'A cidade não pode ter mais de 255 caracteres.',
      'address.state.required_with' => 'O estado é obrigatório quando o endereço é fornecido.',
      'address.state.size' => 'O estado deve ter exatamente 2 caracteres.',
      'address.zipcode.required_with' => 'O CEP é obrigatório quando o endereço é fornecido.',
      'address.zipcode.max' => 'O CEP não pode ter mais de 10 caracteres.',
      'address.country.max' => 'O país não pode ter mais de 255 caracteres.',
    ];

    $this->assertEquals($expectedMessages, $messages);
  }

  #[Test]
  public function it_accepts_various_valid_email_formats()
  {
    // Arrange
    $validEmails = [
      'test@example.com',
      'company.contact@domain.co.uk',
      'info+sales@company.com',
      'admin@localhost'
    ];

    foreach ($validEmails as $email) {
      $data = [
        'name' => 'Test Company',
        'cnpj' => '12.345.678/0001-90',
        'email' => $email
      ];

      // Act
      $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

      // Assert
      $this->assertFalse($validator->fails(), "Email $email should be valid");
    }
  }

  #[Test]
  public function it_accepts_various_cnpj_formats()
  {
    // Arrange
    $validCnpjs = [
      '12.345.678/0001-90',
      '12345678000190',
      '12.345.678/0001-91',
      '98.765.432/0001-10'
    ];

    foreach ($validCnpjs as $cnpj) {
      $data = [
        'name' => 'Test Company',
        'cnpj' => $cnpj
      ];

      // Act
      $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

      // Assert
      $this->assertFalse($validator->fails(), "CNPJ $cnpj should be valid");
    }
  }
}
