<?php

namespace App\Modules\Companies\Services;

use App\Modules\Companies\Models\Company;
use App\Modules\Addresses\Services\AddressService;

class CreateCompanyService
{
  protected AddressService $addressService;

  public function __construct(AddressService $addressService)
  {
    $this->addressService = $addressService;
  }

  /**
   * Create a new company
   */
  public function create(array $data): Company
  {
    $addressData = $data['address'] ?? null;
    unset($data['address']);

    $company = Company::create($data);

    if ($addressData) {
      $this->addressService->createOrUpdateAddress($company, $addressData);
    }

    return $company->load('address');
  }
}
