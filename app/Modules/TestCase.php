<?php

namespace App\Modules;

use App\Modules\Users\Models\UserLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
  use RefreshDatabase, WithFaker;

  protected function setUp(): void
  {
    parent::setUp();

    // Seed user levels for all tests
    $this->seedUserLevels();
  }

  /**
   * Seed the user levels table with default levels
   */
  protected function seedUserLevels(): void
  {
    $levels = [
      [
        'id' => 1,
        'slug' => 'adminMaster',
        'name' => 'Administrador Master',
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'id' => 2,
        'slug' => 'companyAdmin',
        'name' => 'Administrador da Empresa',
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'id' => 3,
        'slug' => 'companyUser',
        'name' => 'Usuário da Empresa',
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'id' => 4,
        'slug' => 'user',
        'name' => 'Usuário',
        'created_at' => now(),
        'updated_at' => now(),
      ],
    ];

    foreach ($levels as $level) {
      UserLevel::updateOrCreate(['id' => $level['id']], $level);
    }
  }
}
