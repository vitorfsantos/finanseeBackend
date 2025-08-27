<?php

namespace App\Modules\Users\Models\Factories;

use App\Modules\Users\Models\UserLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Users\Models\UserLevel>
 */
class UserLevelFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = UserLevel::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'slug' => fake()->unique()->slug(),
      'name' => fake()->jobTitle(),
    ];
  }

  /**
   * Indicate that the user level is Admin Master.
   */
  public function adminMaster(): static
  {
    return $this->state(fn(array $attributes) => [
      'id' => 1,
      'slug' => 'admin-master',
      'name' => 'Admin Master',
    ]);
  }

  /**
   * Indicate that the user level is Company Admin.
   */
  public function companyAdmin(): static
  {
    return $this->state(fn(array $attributes) => [
      'id' => 2,
      'slug' => 'company-admin',
      'name' => 'Company Admin',
    ]);
  }

  /**
   * Indicate that the user level is Regular User.
   */
  public function regularUser(): static
  {
    return $this->state(fn(array $attributes) => [
      'id' => 3,
      'slug' => 'regular-user',
      'name' => 'Regular User',
    ]);
  }
}

