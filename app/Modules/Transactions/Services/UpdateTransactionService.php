<?php

namespace App\Modules\Transactions\Services;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateTransactionService
{
  /**
   * Update a transaction based on user permissions
   */
  public function update(Transaction $transaction, array $data, ?User $currentUser): Transaction
  {
    if ($currentUser) {
      $this->checkUpdatePermissions($transaction, $currentUser);
    }

    $updateData = $this->prepareUpdateData($data, $currentUser);

    return DB::transaction(function () use ($transaction, $updateData) {
      $transaction->update($updateData);
      return $transaction->fresh();
    });
  }

  /**
   * Check if user can update this transaction
   */
  private function checkUpdatePermissions(Transaction $transaction, User $currentUser): void
  {
    if (!$currentUser->level) {
      throw new \Exception('Usuário sem nível definido');
    }

    $userLevel = $currentUser->level->slug;

    switch ($userLevel) {
      case 'adminMaster':
        // Admin Master pode atualizar qualquer transação
        return;

      case 'companyAdmin':
      case 'companyUser':
        // Company Admin e Company User só podem atualizar transações da sua empresa
        $companyId = $this->getUserCompanyId($currentUser);
        if ($transaction->company_id !== $companyId) {
          throw new \Exception('Não autorizado a atualizar esta transação');
        }
        break;

      case 'user':
        // User comum só pode atualizar suas próprias transações
        if ($transaction->user_id !== $currentUser->id) {
          throw new \Exception('Não autorizado a atualizar esta transação');
        }
        break;

      default:
        throw new \Exception('Nível de usuário não reconhecido');
    }
  }

  /**
   * Prepare update data based on user permissions
   */
  private function prepareUpdateData(array $data, ?User $currentUser): array
  {
    $updateData = [];

    // Campos que podem ser atualizados
    if (isset($data['type'])) {
      $updateData['type'] = $data['type'];
    }
    if (isset($data['category'])) {
      $updateData['category'] = $data['category'];
    }
    if (isset($data['description'])) {
      $updateData['description'] = $data['description'];
    }
    if (isset($data['amount'])) {
      $updateData['amount'] = $data['amount'];
    }
    if (isset($data['date'])) {
      $updateData['date'] = $data['date'];
    }

    // Apenas adminMaster pode alterar user_id e company_id
    if ($currentUser && $currentUser->level && $currentUser->level->slug === 'adminMaster') {
      if (isset($data['user_id'])) {
        $updateData['user_id'] = $data['user_id'];
      }
      if (isset($data['company_id'])) {
        $updateData['company_id'] = $data['company_id'];
      }
    }

    return $updateData;
  }

  /**
   * Get the company ID for a user (companyAdmin or companyUser)
   */
  private function getUserCompanyId(User $user): ?string
  {
    // Busca a empresa do usuário através da tabela pivot company_user
    $companyUser = DB::table('company_user')
      ->where('user_id', $user->id)
      ->first();

    return $companyUser ? $companyUser->company_id : null;
  }
}
