<?php

namespace App\Modules\Companies\Services;

use App\Modules\Companies\Models\Company;

class UpdateCompanyService
{
  /**
   * Update a company
   */
  public function update(Company $company, array $data): Company
  {
    $company->update($data);
    return $company->fresh();
  }
}
