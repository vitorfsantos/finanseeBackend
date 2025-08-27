<?php

namespace App\Modules\Companies\Tests;

use PHPUnit\Framework\Attributes\Test;

use App\Modules\Companies\Controllers\CreateCompanyController;
use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Requests\CreateCompanyRequest;
use App\Modules\Companies\Services\CreateCompanyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;
use Mockery;

class CreateCompanyControllerTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  protected CreateCompanyController $controller;
  protected CreateCompanyService $createCompanyService;

  protected function setUp(): void
  {
    parent::setUp();

    $this->createCompanyService = Mockery::mock(CreateCompanyService::class);
    $this->controller = new CreateCompanyController($this->createCompanyService);
  }

  protected function tearDown(): void
  {
    Mockery::close();
    parent::tearDown();
  }

  #[Test]
  public function it_can_create_a_company_successfully()
  {
    // Arrange
    $requestData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90',
      'email' => 'contact@testcompany.com',
      'phone' => '(11) 3333-4444'
    ];

    $createdCompany = Company::factory()->make($requestData);
    $createdCompany->id = '550e8400-e29b-41d4-a716-446655440000';

    $request = Mockery::mock(CreateCompanyRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createCompanyService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdCompany);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(201, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals('Empresa criada com sucesso', $responseData['message']);
    $this->assertEquals($createdCompany->toArray(), $responseData['data']);
  }

  #[Test]
  public function it_can_create_a_company_with_only_required_fields()
  {
    // Arrange
    $requestData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90'
    ];

    $createdCompany = Company::factory()->make($requestData);
    $createdCompany->id = '550e8400-e29b-41d4-a716-446655440000';

    $request = Mockery::mock(CreateCompanyRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createCompanyService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdCompany);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(201, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals('Empresa criada com sucesso', $responseData['message']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertEquals($requestData['name'], $responseData['data']['name']);
    $this->assertEquals($requestData['cnpj'], $responseData['data']['cnpj']);
  }

  #[Test]
  public function it_passes_validated_data_to_service()
  {
    // Arrange
    $requestData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90',
      'email' => 'contact@testcompany.com',
      'phone' => '(11) 3333-4444'
    ];

    $createdCompany = Company::factory()->make($requestData);

    $request = Mockery::mock(CreateCompanyRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createCompanyService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdCompany)
      ->once();

    // Act
    $this->controller->__invoke($request);

    // Assert
    // Assertions are handled by Mockery expectations
    $this->assertTrue(true);
  }

  #[Test]
  public function it_returns_created_company_data_in_response()
  {
    // Arrange
    $requestData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90',
      'email' => 'contact@testcompany.com',
      'phone' => '(11) 3333-4444'
    ];

    $createdCompany = Company::factory()->make([
      'id' => '550e8400-e29b-41d4-a716-446655440000',
      'name' => $requestData['name'],
      'cnpj' => $requestData['cnpj'],
      'email' => $requestData['email'],
      'phone' => $requestData['phone'],
      'created_at' => now(),
      'updated_at' => now()
    ]);

    $request = Mockery::mock(CreateCompanyRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createCompanyService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdCompany);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $responseData = json_decode($response->getContent(), true);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertEquals($createdCompany->toArray(), $responseData['data']);
    $this->assertEquals($requestData['name'], $responseData['data']['name']);
    $this->assertEquals($requestData['cnpj'], $responseData['data']['cnpj']);
    $this->assertEquals($requestData['email'], $responseData['data']['email']);
    $this->assertEquals($requestData['phone'], $responseData['data']['phone']);
  }

  #[Test]
  public function it_has_correct_success_message()
  {
    // Arrange
    $requestData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90'
    ];

    $createdCompany = Company::factory()->make($requestData);

    $request = Mockery::mock(CreateCompanyRequest::class);
    $request->shouldReceive('validated')->andReturn($requestData);

    $this->createCompanyService
      ->shouldReceive('create')
      ->with($requestData)
      ->andReturn($createdCompany);

    // Act
    $response = $this->controller->__invoke($request);

    // Assert
    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals('Empresa criada com sucesso', $responseData['message']);
  }

  #[Test]
  public function it_can_be_instantiated_with_create_company_service()
  {
    // Arrange & Act
    $controller = new CreateCompanyController($this->createCompanyService);

    // Assert
    $this->assertInstanceOf(CreateCompanyController::class, $controller);
  }
}
