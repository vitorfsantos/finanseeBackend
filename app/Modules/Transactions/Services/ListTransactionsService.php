<?php

namespace App\Modules\Transactions\Services;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ListTransactionsService
{
  /**
   * List transactions based on user permissions
   */
  public function list(?User $currentUser, array $filters = []): LengthAwarePaginator
  {
    $query = Transaction::with(['user', 'company']);

    // Aplica filtros baseados nas permissões do usuário
    if ($currentUser) {
      $this->applyPermissionFilters($query, $currentUser);
    }

    // Aplica filtros adicionais
    $this->applyFilters($query, $filters);

    // Ordena por data mais recente
    $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');

    return $query->paginate($filters['per_page'] ?? 15);
  }

  /**
   * Apply permission-based filters
   */
  private function applyPermissionFilters(Builder $query, User $currentUser): void
  {
    if (!$currentUser->level) {
      throw new \Exception('Usuário sem nível definido');
    }

    $userLevel = $currentUser->level->slug;

    switch ($userLevel) {
      case 'adminMaster':
        // Admin Master pode ver todas as transações
        break;

      case 'companyAdmin':
      case 'companyUser':
        // Company Admin e Company User só podem ver transações da sua empresa
        $companyId = $this->getUserCompanyId($currentUser);
        if ($companyId) {
          $query->where('company_id', $companyId);
        }
        break;

      case 'user':
        // User comum só pode ver suas próprias transações
        $query->where('user_id', $currentUser->id);
        break;

      default:
        throw new \Exception('Nível de usuário não reconhecido');
    }
  }

  /**
   * Apply additional filters
   */
  private function applyFilters(Builder $query, array $filters): void
  {
    // Filtro por tipo
    if (isset($filters['type']) && in_array($filters['type'], ['income', 'expense'])) {
      $query->ofType($filters['type']);
    }

    // Filtro por usuário (apenas para adminMaster)
    if (isset($filters['user_id'])) {
      $query->forUser($filters['user_id']);
    }

    // Filtro por empresa (apenas para adminMaster)
    if (isset($filters['company_id'])) {
      $query->forCompany($filters['company_id']);
    }

    // Filtro por categoria
    if (isset($filters['category'])) {
      $query->where('category', 'like', '%' . $filters['category'] . '%');
    }

    // Filtro por data
    if (isset($filters['start_date']) && isset($filters['end_date'])) {
      $query->inDateRange($filters['start_date'], $filters['end_date']);
    } elseif (isset($filters['start_date'])) {
      $query->where('date', '>=', $filters['start_date']);
    } elseif (isset($filters['end_date'])) {
      $query->where('date', '<=', $filters['end_date']);
    }

    // Filtro por valor mínimo
    if (isset($filters['min_amount'])) {
      $query->where('amount', '>=', $filters['min_amount']);
    }

    // Filtro por valor máximo
    if (isset($filters['max_amount'])) {
      $query->where('amount', '<=', $filters['max_amount']);
    }
  }

  /**
   * Get the company ID for a user (companyAdmin or companyUser)
   */
  private function getUserCompanyId(User $user): ?string
  {
    // Busca a empresa do usuário através da tabela pivot company_user
    $companyUser = \Illuminate\Support\Facades\DB::table('company_user')
      ->where('user_id', $user->id)
      ->first();

    return $companyUser ? $companyUser->company_id : null;
  }
}
