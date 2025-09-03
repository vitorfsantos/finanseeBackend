<?php

namespace App\Modules\Companies\Services;

use App\Modules\Companies\Models\Company;
use App\Modules\Addresses\Services\AddressService;

class UpdateCompanyService
{
  protected AddressService $addressService;

  public function __construct(AddressService $addressService)
  {
    $this->addressService = $addressService;
  }

  /**
   * Update a company
   */
  public function update(Company $company, array $data): Company
  {
    $addressData = $data['address'] ?? null;
    unset($data['address']);

    $company->update($data);

    if ($addressData) {
      $this->addressService->updateAddress($company, $addressData);
    }

    return $company->fresh()->load('address');
  }
}
