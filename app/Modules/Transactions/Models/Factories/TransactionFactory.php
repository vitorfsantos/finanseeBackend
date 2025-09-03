<?php

namespace App\Modules\Transactions\Models\Factories;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Users\Models\User;
use App\Modules\Companies\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Transactions\Models\Transaction>
 */
class TransactionFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Transaction::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'user_id' => User::factory(),
      'company_id' => Company::factory(),
      'type' => $this->faker->randomElement(['income', 'expense']),
      'category' => $this->faker->randomElement(['Alimentação', 'Transporte', 'Lazer', 'Saúde', 'Educação', 'Moradia', 'Vestuário', 'Outros']),
      'description' => $this->faker->sentence(),
      'amount' => $this->faker->randomFloat(2, 0.01, 1000.00),
      'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
    ];
  }

  /**
   * Indicate that the transaction is income.
   */
  public function income(): static
  {
    return $this->state(fn(array $attributes) => [
      'type' => 'income',
    ]);
  }

  /**
   * Indicate that the transaction is expense.
   */
  public function expense(): static
  {
    return $this->state(fn(array $attributes) => [
      'type' => 'expense',
    ]);
  }

  /**
   * Indicate that the transaction has no company.
   */
  public function personal(): static
  {
    return $this->state(fn(array $attributes) => [
      'company_id' => null,
    ]);
  }

  /**
   * Indicate that the transaction is for a specific user.
   */
  public function forUser(User $user): static
  {
    return $this->state(fn(array $attributes) => [
      'user_id' => $user->id,
    ]);
  }

  /**
   * Indicate that the transaction is for a specific company.
   */
  public function forCompany(Company $company): static
  {
    return $this->state(fn(array $attributes) => [
      'company_id' => $company->id,
    ]);
  }
}
