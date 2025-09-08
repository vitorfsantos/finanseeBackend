<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLevel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ListUsersService
{
  /**
   * Get all users with optional filtering
   */
  public function getAllUsers(array $filters = []): LengthAwarePaginator
  {
    $query = User::query();

    // Apply filters
    if (isset($filters['search'])) {
      $query->where(function ($q) use ($filters) {
        $q->where('name', 'like', '%' . $filters['search'] . '%')
          ->orWhere('email', 'like', '%' . $filters['search'] . '%');
      });
    }

    if (isset($filters['email_verified'])) {
      if ($filters['email_verified']) {
        $query->whereNotNull('email_verified_at');
      } else {
        $query->whereNull('email_verified_at');
      }
    }

    // Apply ordering
    $orderBy = $filters['order_by'] ?? 'name';
    $orderDirection = $filters['order_direction'] ?? 'asc';
    $query->orderBy($orderBy, $orderDirection);
    $query->with('level');
    $query->withTrashed();

    return $query->paginate(10);
  }

  /**
   * Get levels below the logged user's level
   * 
   * @param User $loggedUser The currently logged user
   * @return Collection Collection of UserLevel models
   * @throws \Exception If user level is 3 or 4 (not authorized)
   */
  public function getLevels(User $loggedUser): Collection
  {
    // Verifica se o usuário tem nível 3 ou 4 (não autorizado)
    if ($loggedUser->user_level_id >= 3) {
      throw new \Exception('Usuários com nível 3 ou 4 não têm permissão para acessar esta funcionalidade.');
    }

    // Obtém o nível do usuário logado
    $userLevel = $loggedUser->user_level_id;

    // Retorna os níveis baseado no nível do usuário logado
    // Se for nível 1 (Admin Master), retorna até o 4 (níveis 2, 3 e 4)
    // Se for nível 2 (Company Admin), retorna apenas até o 3 (apenas nível 3)
    if ($userLevel === 1) {
      return UserLevel::where('id', '>=', $userLevel)
        ->where('id', '<=', 4)
        ->orderBy('id', 'asc')
        ->get();
    } elseif ($userLevel === 2) {
      return UserLevel::where('id', '>=', $userLevel)
        ->where('id', '<=', 3)
        ->orderBy('id', 'asc')
        ->get();
    }

    return collect();
  }
}
