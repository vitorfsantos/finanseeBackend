<?php

namespace App\Modules\Transactions\Services;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateTransactionService
{
  /**
   * Create a new transaction based on user permissions
   */
  public function create(array $data): Transaction
  {
    $currentUser = Auth::user();

    // Define user_id se não fornecido
    $data['user_id'] = $data['user_id'] ?? $currentUser?->id;

    // Define company_id baseado nas permissões do usuário
    $data['company_id'] = $this->determineCompanyId($data, $currentUser);

    // Se date não for fornecido, será definido pelo mutator no modelo
    if (!isset($data['date'])) {
      $data['date'] = Carbon::now();
    }

    return DB::transaction(function () use ($data) {
      return Transaction::create($data);
    });
  }

  /**
   * Determine the company_id based on user permissions
   */
  private function determineCompanyId(array $data, ?User $currentUser): ?string
  {
    // Se company_id foi fornecido explicitamente, usar ele
    if (isset($data['company_id'])) {
      return $data['company_id'];
    }

    // Se não há usuário logado, não definir company_id
    if (!$currentUser || !$currentUser->level) {
      return null;
    }

    $userLevel = $currentUser->level->slug;

    switch ($userLevel) {
      case 'adminMaster':
        // Admin Master pode criar transações sem company_id ou com qualquer company_id
        return null;

      case 'companyAdmin':
      case 'companyUser':
        // Company Admin e Company User devem ter company_id definido
        // Se o usuário tem apenas uma empresa, usar ela
        $companyIds = $this->getUserCompanyIds($currentUser);
        if (count($companyIds) === 1) {
          return $companyIds[0];
        }
        // Se tem múltiplas empresas, não definir automaticamente (deve ser fornecido)
        return null;

      case 'user':
        // User comum não tem empresa associada
        return null;

      default:
        return null;
    }
  }

  /**
   * Get the company IDs for a user (companyAdmin or companyUser)
   */
  private function getUserCompanyIds(User $user): array
  {
    // Busca todas as empresas do usuário através da tabela pivot company_user
    $companyUsers = DB::table('company_user')
      ->where('user_id', $user->id)
      ->pluck('company_id')
      ->toArray();

    return $companyUsers;
  }
}
