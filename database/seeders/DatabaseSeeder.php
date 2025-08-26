<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Users\Models\Seeders\UserSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // User::factory(10)->create();

    // Seed user levels first
    $this->call([
      UserLevelSeeder::class,
      UserSeeder::class,
    ]);
  }
}
