<?php

namespace App\Modules\Addresses\Tests;

use App\Modules\Addresses\Models\Address;
use App\Modules\Addresses\Services\AddressService;
use App\Modules\Companies\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressServiceTest extends TestCase
{
  use RefreshDatabase;

  protected AddressService $addressService;

  protected function setUp(): void
  {
    parent::setUp();
    $this->addressService = new AddressService();
  }

  public function test_can_create_address_for_company()
  {
    $company = Company::factory()->create();
    $addressData = [
      'street' => 'Rua das Flores',
      'number' => '123',
      'complement' => 'Sala 45',
      'neighborhood' => 'Centro',
      'city' => 'São Paulo',
      'state' => 'SP',
      'zipcode' => '01234-567',
      'country' => 'Brasil'
    ];

    $address = $this->addressService->createOrUpdateAddress($company, $addressData);

    $this->assertInstanceOf(Address::class, $address);
    $this->assertEquals($company->id, $address->addressable_id);
    $this->assertEquals(Company::class, $address->addressable_type);
    $this->assertEquals('Rua das Flores', $address->street);
    $this->assertEquals('São Paulo', $address->city);
    $this->assertEquals('SP', $address->state);
  }

  public function test_can_update_existing_address()
  {
    $company = Company::factory()->create();
    $address = Address::factory()->create([
      'addressable_id' => $company->id,
      'addressable_type' => Company::class,
      'street' => 'Rua Antiga',
      'city' => 'Rio de Janeiro'
    ]);

    $addressData = [
      'street' => 'Rua Nova',
      'city' => 'São Paulo',
      'state' => 'SP',
      'zipcode' => '01234-567'
    ];

    $updatedAddress = $this->addressService->updateAddress($company, $addressData);

    $this->assertEquals('Rua Nova', $updatedAddress->street);
    $this->assertEquals('São Paulo', $updatedAddress->city);
    $this->assertEquals('SP', $updatedAddress->state);
  }

  public function test_returns_null_when_no_address_data()
  {
    $company = Company::factory()->create();

    $address = $this->addressService->createOrUpdateAddress($company, []);

    $this->assertNull($address);
  }

  public function test_can_delete_address()
  {
    $company = Company::factory()->create();
    $address = Address::factory()->create([
      'addressable_id' => $company->id,
      'addressable_type' => Company::class
    ]);

    $result = $this->addressService->deleteAddress($company);

    $this->assertTrue($result);
    $this->assertSoftDeleted('addresses', ['id' => $address->id]);
  }

  public function test_delete_returns_false_when_no_address_exists()
  {
    $company = Company::factory()->create();

    $result = $this->addressService->deleteAddress($company);

    $this->assertFalse($result);
  }
}

