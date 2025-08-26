<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserLevelSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $levels = [
      [
        'id' => 1,
        'slug' => 'adminMaster',
        'name' => 'Administrador Master',
      ],
      [
        'id' => 2,
        'slug' => 'companyAdmin',
        'name' => 'Administrador da Empresa',
      ],
      [
        'id' => 3,
        'slug' => 'companyUser',
        'name' => 'Usuário da Empresa',
      ],
      [
        'id' => 4,
        'slug' => 'user',
        'name' => 'Usuário',
      ],
    ];

    foreach ($levels as $level) {
      DB::table('user_levels')->insert($level);
    }
  }
}
