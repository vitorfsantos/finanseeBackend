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
        // Company Admin e Company User só podem ver transações da sua empresa
        $companyId = $this->getUserCompanyId($currentUser);
        if ($transaction->company_id !== $companyId) {
          throw new \Exception('Não autorizado a visualizar esta transação');
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
