<?php

namespace App\Modules\Companies\Tests;

use PHPUnit\Framework\Attributes\Test;

use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Services\CreateCompanyService;
use App\Modules\Addresses\Services\AddressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateCompanyServiceTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  protected CreateCompanyService $service;

  protected function setUp(): void
  {
    parent::setUp();

    $this->service = new CreateCompanyService(app(AddressService::class));
  }

  #[Test]
  public function it_can_create_a_company_with_all_fields()
  {
    // Arrange
    $companyData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90',
      'email' => 'contact@testcompany.com',
      'phone' => '(11) 3333-4444'
    ];

    // Act
    $company = $this->service->create($companyData);

    // Assert
    $this->assertInstanceOf(Company::class, $company);
    $this->assertEquals($companyData['name'], $company->name);
    $this->assertEquals($companyData['cnpj'], $company->cnpj);
    $this->assertEquals($companyData['email'], $company->email);
    $this->assertEquals($companyData['phone'], $company->phone);
    $this->assertNotNull($company->id);
    $this->assertNotNull($company->created_at);
    $this->assertNotNull($company->updated_at);
  }

  #[Test]
  public function it_can_create_a_company_with_only_required_fields()
  {
    // Arrange
    $companyData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90'
    ];

    // Act
    $company = $this->service->create($companyData);

    // Assert
    $this->assertInstanceOf(Company::class, $company);
    $this->assertEquals($companyData['name'], $company->name);
    $this->assertEquals($companyData['cnpj'], $company->cnpj);
    $this->assertNull($company->email);
    $this->assertNull($company->phone);
  }

  #[Test]
  public function it_persists_company_to_database()
  {
    // Arrange
    $companyData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90',
      'email' => 'contact@testcompany.com',
      'phone' => '(11) 3333-4444'
    ];

    // Act
    $company = $this->service->create($companyData);

    // Assert
    $this->assertDatabaseHas('companies', [
      'id' => $company->id,
      'name' => $companyData['name'],
      'cnpj' => $companyData['cnpj'],
      'email' => $companyData['email'],
      'phone' => $companyData['phone']
    ]);
  }

  #[Test]
  public function it_generates_uuid_for_new_company()
  {
    // Arrange
    $companyData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90'
    ];

    // Act
    $company = $this->service->create($companyData);

    // Assert
    $this->assertIsString($company->id);
    $this->assertEquals(36, strlen($company->id)); // UUID length
    $this->assertMatchesRegularExpression(
      '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
      $company->id
    );
  }

  #[Test]
  public function it_sets_timestamps_on_creation()
  {
    // Arrange
    $companyData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90'
    ];

    // Act
    $company = $this->service->create($companyData);

    // Assert
    $this->assertNotNull($company->created_at);
    $this->assertNotNull($company->updated_at);
    $this->assertInstanceOf(\Carbon\Carbon::class, $company->created_at);
    $this->assertInstanceOf(\Carbon\Carbon::class, $company->updated_at);
  }

  #[Test]
  public function it_returns_fresh_company_instance()
  {
    // Arrange
    $companyData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90',
      'email' => 'contact@testcompany.com'
    ];

    // Act
    $company = $this->service->create($companyData);

    // Assert
    $this->assertTrue($company->exists);
    $this->assertFalse($company->wasRecentlyCreated === false); // Recently created should be true
    $this->assertEquals($companyData['name'], $company->name);
  }

  #[Test]
  public function it_handles_optional_email_field()
  {
    // Arrange
    $companyData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90',
      'phone' => '(11) 3333-4444'
    ];

    // Act
    $company = $this->service->create($companyData);

    // Assert
    $this->assertEquals($companyData['name'], $company->name);
    $this->assertEquals($companyData['cnpj'], $company->cnpj);
    $this->assertNull($company->email);
    $this->assertEquals($companyData['phone'], $company->phone);
  }

  #[Test]
  public function it_handles_optional_phone_field()
  {
    // Arrange
    $companyData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90',
      'email' => 'contact@testcompany.com'
    ];

    // Act
    $company = $this->service->create($companyData);

    // Assert
    $this->assertEquals($companyData['name'], $company->name);
    $this->assertEquals($companyData['cnpj'], $company->cnpj);
    $this->assertEquals($companyData['email'], $company->email);
    $this->assertNull($company->phone);
  }

  #[Test]
  public function it_creates_multiple_companies_with_unique_ids()
  {
    // Arrange
    $companyData1 = [
      'name' => 'First Company LTDA',
      'cnpj' => '11.111.111/0001-11'
    ];

    $companyData2 = [
      'name' => 'Second Company LTDA',
      'cnpj' => '22.222.222/0001-22'
    ];

    // Act
    $company1 = $this->service->create($companyData1);
    $company2 = $this->service->create($companyData2);

    // Assert
    $this->assertNotEquals($company1->id, $company2->id);
    $this->assertEquals('First Company LTDA', $company1->name);
    $this->assertEquals('Second Company LTDA', $company2->name);
  }
}
