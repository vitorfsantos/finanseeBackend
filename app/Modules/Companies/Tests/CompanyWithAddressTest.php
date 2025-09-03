<?php

namespace App\Modules\Companies\Tests;

use App\Modules\Addresses\Models\Address;
use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Services\CreateCompanyService;
use App\Modules\Companies\Services\UpdateCompanyService;
use App\Modules\Addresses\Services\AddressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyWithAddressTest extends TestCase
{
  use RefreshDatabase;

  public function test_can_create_company_with_address()
  {
    $addressService = new AddressService();
    $createCompanyService = new CreateCompanyService($addressService);

    $companyData = [
      'name' => 'Empresa com Endereço LTDA',
      'cnpj' => '12.345.678/0001-90',
      'email' => 'contato@empresa.com',
      'phone' => '(11) 3333-4444',
      'address' => [
        'street' => 'Rua das Flores',
        'number' => '123',
        'complement' => 'Sala 45',
        'neighborhood' => 'Centro',
        'city' => 'São Paulo',
        'state' => 'SP',
        'zipcode' => '01234-567',
        'country' => 'Brasil'
      ]
    ];

    $company = $createCompanyService->create($companyData);

    $this->assertInstanceOf(Company::class, $company);
    $this->assertEquals('Empresa com Endereço LTDA', $company->name);
    $this->assertNotNull($company->address);
    $this->assertEquals('Rua das Flores', $company->address->street);
    $this->assertEquals('São Paulo', $company->address->city);
    $this->assertEquals('SP', $company->address->state);
  }

  public function test_can_create_company_without_address()
  {
    $addressService = new AddressService();
    $createCompanyService = new CreateCompanyService($addressService);

    $companyData = [
      'name' => 'Empresa sem Endereço LTDA',
      'cnpj' => '98.765.432/0001-10',
      'email' => 'contato@empresa2.com'
    ];

    $company = $createCompanyService->create($companyData);

    $this->assertInstanceOf(Company::class, $company);
    $this->assertEquals('Empresa sem Endereço LTDA', $company->name);
    $this->assertNull($company->address);
  }

  public function test_can_update_company_with_address()
  {
    $addressService = new AddressService();
    $createCompanyService = new CreateCompanyService($addressService);
    $updateCompanyService = new UpdateCompanyService($addressService);

    // Criar empresa sem endereço
    $companyData = [
      'name' => 'Empresa Original LTDA',
      'cnpj' => '11.222.333/0001-44',
      'email' => 'original@empresa.com'
    ];

    $company = $createCompanyService->create($companyData);

    // Atualizar com endereço
    $updateData = [
      'name' => 'Empresa Atualizada LTDA',
      'address' => [
        'street' => 'Nova Rua',
        'city' => 'Rio de Janeiro',
        'state' => 'RJ',
        'zipcode' => '20000-000'
      ]
    ];

    $updatedCompany = $updateCompanyService->update($company, $updateData);

    $this->assertEquals('Empresa Atualizada LTDA', $updatedCompany->name);
    $this->assertNotNull($updatedCompany->address);
    $this->assertEquals('Nova Rua', $updatedCompany->address->street);
    $this->assertEquals('Rio de Janeiro', $updatedCompany->address->city);
    $this->assertEquals('RJ', $updatedCompany->address->state);
  }

  public function test_can_update_company_address()
  {
    $addressService = new AddressService();
    $createCompanyService = new CreateCompanyService($addressService);
    $updateCompanyService = new UpdateCompanyService($addressService);

    // Criar empresa com endereço
    $companyData = [
      'name' => 'Empresa com Endereço LTDA',
      'cnpj' => '55.666.777/0001-88',
      'address' => [
        'street' => 'Rua Antiga',
        'city' => 'São Paulo',
        'state' => 'SP',
        'zipcode' => '01234-567'
      ]
    ];

    $company = $createCompanyService->create($companyData);

    // Atualizar endereço
    $updateData = [
      'address' => [
        'street' => 'Rua Nova',
        'city' => 'Belo Horizonte',
        'state' => 'MG',
        'zipcode' => '30000-000'
      ]
    ];

    $updatedCompany = $updateCompanyService->update($company, $updateData);

    $this->assertEquals('Rua Nova', $updatedCompany->address->street);
    $this->assertEquals('Belo Horizonte', $updatedCompany->address->city);
    $this->assertEquals('MG', $updatedCompany->address->state);
  }

  public function test_address_relationship_works_correctly()
  {
    $company = Company::factory()->create();

    $address = Address::factory()->create([
      'addressable_id' => $company->id,
      'addressable_type' => Company::class,
      'street' => 'Rua Teste',
      'city' => 'Cidade Teste',
      'state' => 'TS',
      'zipcode' => '00000-000'
    ]);

    $company->load('address');

    $this->assertNotNull($company->address);
    $this->assertEquals($address->id, $company->address->id);
    $this->assertEquals('Rua Teste', $company->address->street);
  }
}

