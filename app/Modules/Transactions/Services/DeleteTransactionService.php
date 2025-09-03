<?php

namespace App\Modules\Transactions\Services;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Users\Models\User;

class DeleteTransactionService
{
  /**
   * Delete a transaction based on user permissions
   */
  public function delete(Transaction $transaction, ?User $currentUser): bool
  {
    if ($currentUser) {
      $this->checkDeletePermissions($transaction, $currentUser);
    }

    return $transaction->delete();
  }

  /**
   * Check if user can delete this transaction
   */
  private function checkDeletePermissions(Transaction $transaction, User $currentUser): void
  {
    if (!$currentUser->level) {
      throw new \Exception('Usuário sem nível definido');
    }

    $userLevel = $currentUser->level->slug;

    switch ($userLevel) {
      case 'adminMaster':
        // Admin Master pode deletar qualquer transação
        return;

      case 'companyAdmin':
      case 'companyUser':
        // Company Admin e Company User só podem deletar transações da sua empresa
        $companyId = $this->getUserCompanyId($currentUser);
        if ($transaction->company_id !== $companyId) {
          throw new \Exception('Não autorizado a deletar esta transação');
        }
        break;

      case 'user':
        // User comum só pode deletar suas próprias transações
        if ($transaction->user_id !== $currentUser->id) {
          throw new \Exception('Não autorizado a deletar esta transação');
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
