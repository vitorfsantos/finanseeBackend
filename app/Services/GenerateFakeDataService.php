<?php

namespace App\Services;

use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLevel;
use App\Modules\Companies\Models\Company;
use App\Modules\Transactions\Models\Transaction;
use App\Modules\Addresses\Models\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GenerateFakeDataService
{
  /**
   * Generate fake data based on type
   */
  public function generate(string $type): array
  {
    return match ($type) {
      'user' => $this->generateUserData(),
      'companyAdmin' => $this->generateCompanyAdminData(),
      default => throw new \InvalidArgumentException('Tipo inválido. Use "user" ou "companyAdmin".')
    };
  }

  /**
   * Generate individual user data with personal transactions
   */
  private function generateUserData(): array
  {
    return DB::transaction(function () {
      // Get user level
      $userLevel = UserLevel::where('slug', 'user')->first();

      // Generate password
      $plainPassword = 'password123';

      // Create user
      $user = User::create([
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make($plainPassword),
        'phone' => fake()->optional()->phoneNumber(),
        'user_level_id' => $userLevel->id,
        'email_verified_at' => now(),
      ]);

      // Generate personal transactions (10-20 transactions)
      $transactionCount = fake()->numberBetween(10, 20);
      $this->generatePersonalTransactions($user, $transactionCount);

      return [
        'email' => $user->email,
        'password' => $plainPassword,
        'user_type' => 'user',
        'generated_data' => [
          'user_id' => $user->id,
          'transactions_count' => $transactionCount,
        ]
      ];
    });
  }

  /**
   * Generate company admin data with company, users and transactions
   */
  private function generateCompanyAdminData(): array
  {
    return DB::transaction(function () {
      // Get user levels
      $companyAdminLevel = UserLevel::where('slug', 'companyAdmin')->first();
      $companyUserLevel = UserLevel::where('slug', 'companyUser')->first();

      // Generate password for admin
      $plainPassword = 'password123';

      // Create company
      $company = Company::create([
        'name' => fake()->company() . ' LTDA',
        'cnpj' => $this->generateCnpj(),
        'email' => fake()->companyEmail(),
        'phone' => fake()->phoneNumber(),
      ]);

      // Create company address
      $this->createCompanyAddress($company);

      // Create company admin user
      $adminUser = User::create([
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make($plainPassword),
        'phone' => fake()->optional()->phoneNumber(),
        'user_level_id' => $companyAdminLevel->id,
        'email_verified_at' => now(),
      ]);

      // Link admin to company
      $adminUser->companies()->attach($company->id, [
        'id' => Str::uuid(),
        'role' => 'owner',
        'position' => 'CEO',
        'created_at' => now(),
        'updated_at' => now(),
      ]);

      // Create company users (3-7 users)
      $userCount = fake()->numberBetween(3, 7);
      $companyUsers = [];

      for ($i = 0; $i < $userCount; $i++) {
        $companyUser = User::create([
          'name' => fake()->name(),
          'email' => fake()->unique()->safeEmail(),
          'password' => Hash::make('password123'),
          'phone' => fake()->optional()->phoneNumber(),
          'user_level_id' => $companyUserLevel->id,
          'email_verified_at' => now(),
        ]);

        // Link user to company
        $companyUser->companies()->attach($company->id, [
          'id' => Str::uuid(),
          'role' => fake()->randomElement(['manager', 'employee']),
          'position' => fake()->randomElement(['Gerente', 'Analista', 'Assistente', 'Coordenador']),
          'created_at' => now(),
          'updated_at' => now(),
        ]);

        $companyUsers[] = $companyUser;
      }

      // Generate company transactions (20-50 transactions)
      $transactionCount = fake()->numberBetween(20, 50);
      $this->generateCompanyTransactions($company, array_merge([$adminUser], $companyUsers), $transactionCount);

      return [
        'email' => $adminUser->email,
        'password' => $plainPassword,
        'user_type' => 'companyAdmin',
        'generated_data' => [
          'company_id' => $company->id,
          'company_name' => $company->name,
          'admin_user_id' => $adminUser->id,
          'company_users_count' => $userCount,
          'transactions_count' => $transactionCount,
        ]
      ];
    });
  }

  /**
   * Generate personal transactions for a user
   */
  private function generatePersonalTransactions(User $user, int $count): void
  {
    $categories = ['Alimentação', 'Transporte', 'Lazer', 'Saúde', 'Educação', 'Moradia', 'Vestuário', 'Outros'];
    $incomeCategories = ['Salário', 'Freelance', 'Investimentos', 'Vendas', 'Outros'];

    for ($i = 0; $i < $count; $i++) {
      $isIncome = fake()->boolean(30); // 30% chance of income

      Transaction::create([
        'user_id' => $user->id,
        'company_id' => null, // Personal transaction
        'type' => $isIncome ? 'income' : 'expense',
        'category' => $isIncome
          ? fake()->randomElement($incomeCategories)
          : fake()->randomElement($categories),
        'description' => $isIncome
          ? fake()->randomElement(['Salário mensal', 'Freelance projeto', 'Dividendos', 'Venda de item'])
          : fake()->randomElement(['Supermercado', 'Combustível', 'Cinema', 'Consulta médica', 'Aluguel']),
        'amount' => $isIncome
          ? fake()->randomFloat(2, 500, 5000)
          : fake()->randomFloat(2, 10, 500),
        'date' => fake()->dateTimeBetween('-6 months', 'now'),
      ]);
    }
  }

  /**
   * Generate company transactions for multiple users
   */
  private function generateCompanyTransactions(Company $company, array $users, int $count): void
  {
    $categories = ['Vendas', 'Compras', 'Marketing', 'Operacional', 'RH', 'TI', 'Financeiro', 'Outros'];
    $incomeCategories = ['Vendas', 'Serviços', 'Consultoria', 'Licenciamento', 'Outros'];

    for ($i = 0; $i < $count; $i++) {
      $isIncome = fake()->boolean(40); // 40% chance of income
      $user = fake()->randomElement($users);

      Transaction::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'type' => $isIncome ? 'income' : 'expense',
        'category' => $isIncome
          ? fake()->randomElement($incomeCategories)
          : fake()->randomElement($categories),
        'description' => $isIncome
          ? fake()->randomElement(['Venda de produto', 'Serviço prestado', 'Consultoria', 'Licenciamento'])
          : fake()->randomElement(['Compra de material', 'Campanha publicitária', 'Salários', 'Aluguel', 'Equipamentos']),
        'amount' => $isIncome
          ? fake()->randomFloat(2, 1000, 10000)
          : fake()->randomFloat(2, 100, 2000),
        'date' => fake()->dateTimeBetween('-6 months', 'now'),
      ]);
    }
  }

  /**
   * Create address for company
   */
  private function createCompanyAddress(Company $company): void
  {
    Address::create([
      'addressable_type' => Company::class,
      'addressable_id' => $company->id,
      'street' => fake()->streetAddress(),
      'number' => fake()->buildingNumber(),
      'complement' => fake()->optional()->secondaryAddress(),
      'neighborhood' => fake()->citySuffix(),
      'city' => fake()->city(),
      'state' => fake()->stateAbbr(),
      'zipcode' => fake()->postcode(),
      'country' => 'Brasil',
    ]);
  }

  /**
   * Generate a fake CNPJ
   */
  private function generateCnpj(): string
  {
    $cnpj = '';
    for ($i = 0; $i < 8; $i++) {
      $cnpj .= fake()->numberBetween(0, 9);
    }
    $cnpj .= '0001'; // Branch
    for ($i = 0; $i < 2; $i++) {
      $cnpj .= fake()->numberBetween(0, 9);
    }

    // Format CNPJ
    return substr($cnpj, 0, 2) . '.' .
      substr($cnpj, 2, 3) . '.' .
      substr($cnpj, 5, 3) . '/' .
      substr($cnpj, 8, 4) . '-' .
      substr($cnpj, 12, 2);
  }
}
