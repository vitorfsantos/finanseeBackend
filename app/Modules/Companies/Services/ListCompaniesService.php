<?php

namespace App\Modules\Companies\Services;

use App\Modules\Companies\Models\Company;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ListCompaniesService
{
  /**
   * Get all companies
   */
  public function getAll(?User $user = null): Collection
  {
    if ($user && $user->user_level_id > 1) {
      // Se não for admin master, filtrar apenas empresas que o usuário tem acesso
      return $user->companies()->with('address')->get();
    }

    return Company::all();
  }

  /**
   * Get paginated companies
   */
  public function getPaginated(int $perPage = 15, ?User $user = null): LengthAwarePaginator
  {
    if ($user && $user->user_level_id > 1) {
      // Se não for admin master, filtrar apenas empresas que o usuário tem acesso
      return $user->companies()->with('address')->paginate($perPage);
    }

    return Company::with('address')->paginate($perPage);
  }

  /**
   * Search companies by name or CNPJ
   */
  public function search(string $search, int $perPage = 15, ?User $user = null): LengthAwarePaginator
  {
    if ($user && $user->user_level_id > 1) {
      // Se não for admin master, filtrar apenas empresas que o usuário tem acesso
      return $user->companies()
        ->with('address')
        ->where('name', 'like', "%{$search}%")
        ->orWhere('cnpj', 'like', "%{$search}%")
        ->paginate($perPage);
    }

    return Company::with('address')
      ->where('name', 'like', "%{$search}%")
      ->orWhere('cnpj', 'like', "%{$search}%")
      ->paginate($perPage);
  }
}
