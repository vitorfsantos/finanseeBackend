<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateUserService
{
  /**
   * Create a new user
   */
  public function create(array $data): User
  {
    return DB::transaction(function () use ($data) {
      // Hash the password if provided
      if (isset($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      }

      // Extrair dados das companies
      $companies = $data['companies'] ?? [];

      // Remover dados que não pertencem ao usuário
      unset($data['companies']);

      // Criar o usuário
      $user = User::create($data);

      // Se há companies, criar vínculos
      if (!empty($companies)) {
        $this->createUserCompanyLinks($user, $companies);
      }

      return $user;
    });
  }

  /**
   * Create user-company relationships for multiple companies
   */
  private function createUserCompanyLinks(User $user, array $companies): void
  {
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
