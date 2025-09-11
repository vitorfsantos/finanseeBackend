<?php

namespace App\Modules\Transactions\Services;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Users\Models\User;

class ShowTransactionService
{
  /**
   * Show a transaction based on user permissions
   */
  public function show(Transaction $transaction, ?User $currentUser): Transaction
  {
    if ($currentUser) {
      $this->checkShowPermissions($transaction, $currentUser);
    }

    return $transaction->load(['user', 'company']);
  }

  /**
   * Check if user can view this transaction
   */
  private function checkShowPermissions(Transaction $transaction, User $currentUser): void
  {
    if (!$currentUser->level) {
      throw new \Exception('Usuário sem nível definido');
    }

    $userLevel = $currentUser->level->slug;

    switch ($userLevel) {
      case 'adminMaster':
        // Admin Master pode ver qualquer transação
        return;

      case 'companyAdmin':
      case 'companyUser':
        // Company Admin e Company User podem ver transações das suas empresas
        // e transações sem company_id apenas se user_id for o próprio usuário
        $companyIds = $this->getUserCompanyIds($currentUser);

        // Se a transação tem company_id, verificar se é das empresas do usuário
        if ($transaction->company_id !== null) {
          if (!in_array($transaction->company_id, $companyIds)) {
            throw new \Exception('Não autorizado a visualizar esta transação');
          }
        } else {
          // Se não tem company_id, só pode ver se for do próprio usuário
          if ($transaction->user_id !== $currentUser->id) {
            throw new \Exception('Não autorizado a visualizar esta transação');
          }
        }
        break;

      case 'user':
        // User comum só pode ver suas próprias transações
        if ($transaction->user_id !== $currentUser->id) {
          throw new \Exception('Não autorizado a visualizar esta transação');
        }
        break;

      default:
        throw new \Exception('Nível de usuário não reconhecido');
    }
  }

  /**
   * Get the company IDs for a user (companyAdmin or companyUser)
   */
  private function getUserCompanyIds(User $user): array
  {
    // Busca todas as empresas do usuário através da tabela pivot company_user
    $companyUsers = \Illuminate\Support\Facades\DB::table('company_user')
      ->where('user_id', $user->id)
      ->pluck('company_id')
      ->toArray();

    return $companyUsers;
  }
}
