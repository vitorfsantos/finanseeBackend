<?php

namespace App\Modules\Companies\Services;

use App\Modules\Companies\Models\Company;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ListCompaniesService
{
  /**
   * Get all companies
   */
  public function getAll(): Collection
  {
    return Company::all();
  }

  /**
   * Get paginated companies
   */
  public function getPaginated(int $perPage = 15): LengthAwarePaginator
  {
    return Company::with('address')->paginate($perPage);
  }

  /**
   * Search companies by name or CNPJ
   */
  public function search(string $search, int $perPage = 15): LengthAwarePaginator
  {
    return Company::with('address')
      ->where('name', 'like', "%{$search}%")
      ->orWhere('cnpj', 'like', "%{$search}%")
      ->paginate($perPage);
  }
}
