<?php

namespace App\Modules\Companies\Services;

use App\Modules\Companies\Models\Company;

class CreateCompanyService
{
  /**
   * Create a new company
   */
  public function create(array $data): Company
  {
    return Company::create($data);
  }
}
