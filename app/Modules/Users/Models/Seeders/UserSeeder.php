<?php

namespace App\Modules\Users\Models\Seeders;

use App\Modules\Users\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    User::create([
      'name' => 'Admin Master',
      'email' => 'admin@finansee.com',
      'password' => Hash::make('admin123'),
      'email_verified_at' => now(),
      'user_level_id' => 1, // Admin Master
    ]);

    User::create([
      'name' => 'Company Admin',
      'email' => 'company@finansee.com',
      'password' => Hash::make('company123'),
      'email_verified_at' => now(),
      'user_level_id' => 2, // Company Admin
    ]);

    User::create([
      'name' => 'Company User',
      'email' => 'user@finansee.com',
      'password' => Hash::make('user123'),
      'email_verified_at' => now(),
      'user_level_id' => 3, // Company User
    ]);

    User::create([
      'name' => 'Usuário Teste',
      'email' => 'usuario@exemplo.com',
      'password' => Hash::make('123456'),
      'email_verified_at' => now(),
      'user_level_id' => 4, // Usuário básico
    ]);
  }
}
