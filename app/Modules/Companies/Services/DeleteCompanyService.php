<?php

namespace App\Modules\Companies\Services;

use App\Modules\Companies\Models\Company;

class DeleteCompanyService
{
  /**
   * Delete a company
   */
  public function delete(Company $company): bool
  {
    return $company->delete();
  }
}
