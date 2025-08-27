<?php

namespace App\Modules\Companies\Models\Factories;

use App\Modules\Companies\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Companies\Models\Company>
 */
class CompanyFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Company::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'name' => fake()->company() . ' LTDA',
      'cnpj' => $this->generateCnpj(),
      'email' => fake()->optional()->companyEmail(),
      'phone' => fake()->optional()->phoneNumber(),
    ];
  }

  /**
   * Generate a fake CNPJ.
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

  /**
   * Indicate that the company has no optional fields.
   */
  public function minimal(): static
  {
    return $this->state(fn(array $attributes) => [
      'email' => null,
      'phone' => null,
    ]);
  }

  /**
   * Indicate that the company has a specific name.
   */
  public function withName(string $name): static
  {
    return $this->state(fn(array $attributes) => [
      'name' => $name,
    ]);
  }

  /**
   * Indicate that the company has a specific CNPJ.
   */
  public function withCnpj(string $cnpj): static
  {
    return $this->state(fn(array $attributes) => [
      'cnpj' => $cnpj,
    ]);
  }
}

