<?php

namespace App\Modules\Transactions\Models\Seeders;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Users\Models\User;
use App\Modules\Companies\Models\Company;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TransactionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Busca usuários e empresas existentes
    $users = User::all();
    $companies = Company::all();

    if ($users->isEmpty() || $companies->isEmpty()) {
      $this->command->warn('Usuários ou empresas não encontrados. Execute os seeders de Users e Companies primeiro.');
      return;
    }

    // Cria transações de exemplo
    $this->createSampleTransactions($users, $companies);
  }

  /**
   * Create sample transactions
   */
  private function createSampleTransactions($users, $companies): void
  {
    $categories = [
      'income' => ['Salário', 'Freelance', 'Investimentos', 'Vendas', 'Outros'],
      'expense' => ['Alimentação', 'Transporte', 'Lazer', 'Saúde', 'Educação', 'Moradia', 'Vestuário', 'Outros']
    ];

    foreach ($users as $user) {
      // Cria algumas transações pessoais para cada usuário
      $this->createPersonalTransactions($user, $categories);

      // Se o usuário estiver associado a uma empresa, cria transações da empresa
      $userCompany = $this->getUserCompany($user);
      if ($userCompany) {
        $this->createCompanyTransactions($user, $userCompany, $categories);
      }
    }

    $this->command->info('Transações de exemplo criadas com sucesso!');
  }

  /**
   * Create personal transactions for a user
   */
  private function createPersonalTransactions($user, $categories): void
  {
    $faker = Faker::create();

    // Transações de receita pessoal
    for ($i = 0; $i < rand(2, 5); $i++) {
      Transaction::factory()->create([
        'user_id' => $user->id,
        'company_id' => null,
        'type' => 'income',
        'category' => $faker->randomElement($categories['income']),
        'amount' => $faker->randomFloat(2, 100.00, 5000.00),
        'date' => $faker->dateTimeBetween('-6 months', 'now'),
      ]);
    }

    // Transações de despesa pessoal
    for ($i = 0; $i < rand(5, 15); $i++) {
      Transaction::factory()->create([
        'user_id' => $user->id,
        'company_id' => null,
        'type' => 'expense',
        'category' => $faker->randomElement($categories['expense']),
        'amount' => $faker->randomFloat(2, 10.00, 500.00),
        'date' => $faker->dateTimeBetween('-6 months', 'now'),
      ]);
    }
  }

  /**
   * Create company transactions
   */
  private function createCompanyTransactions($user, $company, $categories): void
  {
    $faker = Faker::create();

    // Transações de receita da empresa
    for ($i = 0; $i < rand(3, 8); $i++) {
      Transaction::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'type' => 'income',
        'category' => $faker->randomElement($categories['income']),
        'amount' => $faker->randomFloat(2, 500.00, 10000.00),
        'date' => $faker->dateTimeBetween('-6 months', 'now'),
      ]);
    }

    // Transações de despesa da empresa
    for ($i = 0; $i < rand(8, 20); $i++) {
      Transaction::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'type' => 'expense',
        'category' => $faker->randomElement($categories['expense']),
        'amount' => $faker->randomFloat(2, 50.00, 2000.00),
        'date' => $faker->dateTimeBetween('-6 months', 'now'),
      ]);
    }
  }

  /**
   * Get the company associated with a user
   */
  private function getUserCompany($user): ?Company
  {
    $companyUser = \Illuminate\Support\Facades\DB::table('company_user')
      ->where('user_id', $user->id)
      ->first();

    return $companyUser ? Company::find($companyUser->company_id) : null;
  }
}
