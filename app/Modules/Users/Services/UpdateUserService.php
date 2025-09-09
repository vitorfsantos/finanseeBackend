<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UpdateUserService
{
  /**
   * Update an existing user
   */
  public function update(User $user, array $data): User
  {
    return DB::transaction(function () use ($user, $data) {
      // Hash the password if provided
      if (isset($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      }

      // Extrair dados das companies
      $companies = $data['companies'] ?? null;

      // Remover dados que não pertencem ao usuário
      unset($data['companies']);

      // Atualizar dados do usuário
      $user->update($data);

      // Se companies foi fornecido, atualizar vínculos
      if ($companies !== null) {
        $this->updateUserCompanyLinks($user, $companies);
      }

      return $user->fresh(['companies']);
    });
  }

  /**
   * Update user-company relationships
   * This method will sync the companies array, removing old relationships and creating new ones
   */
  private function updateUserCompanyLinks(User $user, array $companies): void
  {
    // Primeiro, remover todos os vínculos existentes
    $user->companies()->detach();

    // Se o array companies está vazio, apenas removemos os vínculos
    if (empty($companies)) {
      return;
    }

    // Criar novos vínculos
    foreach ($companies as $companyData) {
      $companyId = $companyData['company_id'];
      $role = $companyData['role'];
      $position = $companyData['position'] ?? null;

      $user->companies()->attach($companyId, [
        'id' => \Illuminate\Support\Str::uuid(),
        'role' => $role,
        'position' => $position,
        'created_at' => now(),
        'updated_at' => now(),
      ]);
    }
  }
}
