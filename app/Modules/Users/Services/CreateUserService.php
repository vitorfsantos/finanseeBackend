<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;
use App\Modules\Companies\Models\Company;
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

      // Extrair dados da empresa e vínculo
      $companyData = $data['company'] ?? null;
      $companyId = $data['company_id'] ?? null;
      $role = $data['role'] ?? 'employee';
      $position = $data['position'] ?? null;

      // Remover dados que não pertencem ao usuário
      unset($data['company'], $data['company_id'], $data['role'], $data['position']);

      // Criar o usuário
      $user = User::create($data);

      // Se há dados de empresa ou company_id, criar empresa e/ou vínculo
      if ($companyData || $companyId) {
        $company = $this->handleCompany($companyData, $companyId);
        $this->createUserCompanyLink($user, $company, $role, $position);
      }

      return $user;
    });
  }

  /**
   * Handle company creation or retrieval
   */
  private function handleCompany(?array $companyData, ?string $companyId): Company
  {
    if ($companyData) {
      // Criar nova empresa
      return Company::create($companyData);
    }

    if ($companyId) {
      // Buscar empresa existente
      return Company::findOrFail($companyId);
    }

    throw new \InvalidArgumentException('Either company data or company_id must be provided');
  }

  /**
   * Create user-company relationship
   */
  private function createUserCompanyLink(User $user, Company $company, string $role, ?string $position): void
  {
    $user->companies()->attach($company->id, [
      'role' => $role,
      'position' => $position,
      'created_at' => now(),
      'updated_at' => now(),
    ]);
  }
}
