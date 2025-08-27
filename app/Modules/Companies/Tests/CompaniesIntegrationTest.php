<?php

namespace App\Modules\Companies\Tests;

use PHPUnit\Framework\Attributes\Test;

use App\Modules\Companies\Models\Company;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompaniesIntegrationTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  protected function setUp(): void
  {
    parent::setUp();
  }

  #[Test]
  public function admin_master_can_create_company()
  {
    // Arrange
    $adminLevel = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $admin = User::factory()->create(['user_level_id' => $adminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    $companyData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90',
      'email' => 'contact@testcompany.com',
      'phone' => '(11) 3333-4444'
    ];

    // Act
    $response = $this->postJson('/api/companies', $companyData, [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(201)
      ->assertJsonStructure([
        'message',
        'data' => [
          'id',
          'name',
          'cnpj',
          'email',
          'phone',
          'created_at',
          'updated_at'
        ]
      ])
      ->assertJson([
        'message' => 'Empresa criada com sucesso',
        'data' => [
          'name' => $companyData['name'],
          'cnpj' => $companyData['cnpj'],
          'email' => $companyData['email'],
          'phone' => $companyData['phone']
        ]
      ]);

    $this->assertDatabaseHas('companies', [
      'name' => $companyData['name'],
      'cnpj' => $companyData['cnpj']
    ]);
  }

  #[Test]
  public function non_admin_master_cannot_create_company()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);
    $user = User::factory()->create(['user_level_id' => $userLevel->id]);
    $token = $user->createToken('auth-token')->plainTextToken;

    $companyData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90'
    ];

    // Act
    $response = $this->postJson('/api/companies', $companyData, [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(403);
  }

  #[Test]
  public function admin_master_can_list_companies()
  {
    // Arrange
    $adminLevel = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $admin = User::factory()->create(['user_level_id' => $adminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    $companies = Company::factory()->count(3)->create();

    // Act
    $response = $this->getJson('/api/companies', [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(200)
      ->assertJsonStructure([
        'data' => [
          '*' => [
            'id',
            'name',
            'cnpj',
            'email',
            'phone',
            'created_at',
            'updated_at'
          ]
        ],
        'meta' => [
          'total',
          'per_page',
          'current_page',
          'last_page'
        ]
      ]);

    $this->assertEquals(3, $response->json('meta.total'));
  }

  #[Test]
  public function admin_master_can_search_companies()
  {
    // Arrange
    $adminLevel = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $admin = User::factory()->create(['user_level_id' => $adminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    Company::factory()->create(['name' => 'Tech Company LTDA']);
    Company::factory()->create(['name' => 'Marketing Agency']);
    Company::factory()->create(['name' => 'Tech Solutions Inc']);

    // Act
    $response = $this->getJson('/api/companies?search=Tech', [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(200);
    $this->assertEquals(2, $response->json('meta.total'));
  }

  #[Test]
  public function anyone_can_view_specific_company()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);
    $user = User::factory()->create(['user_level_id' => $userLevel->id]);
    $token = $user->createToken('auth-token')->plainTextToken;

    $company = Company::factory()->create();

    // Act
    $response = $this->getJson("/api/companies/{$company->id}", [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(200)
      ->assertJsonStructure([
        'data' => [
          'id',
          'name',
          'cnpj',
          'created_at',
          'updated_at'
        ]
      ])
      ->assertJson([
        'data' => [
          'id' => $company->id,
          'name' => $company->name,
          'cnpj' => $company->cnpj
        ]
      ]);
  }

  #[Test]
  public function create_company_validates_required_fields()
  {
    // Arrange
    $adminLevel = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $admin = User::factory()->create(['user_level_id' => $adminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    // Act
    $response = $this->postJson('/api/companies', [], [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(422)
      ->assertJsonValidationErrors(['name', 'cnpj']);
  }

  #[Test]
  public function create_company_validates_unique_cnpj()
  {
    // Arrange
    $adminLevel = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $admin = User::factory()->create(['user_level_id' => $adminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    $existingCompany = Company::factory()->create(['cnpj' => '12.345.678/0001-90']);

    $companyData = [
      'name' => 'New Company',
      'cnpj' => '12.345.678/0001-90' // Same CNPJ
    ];

    // Act
    $response = $this->postJson('/api/companies', $companyData, [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(422)
      ->assertJsonValidationErrors(['cnpj']);
  }

  #[Test]
  public function create_company_validates_email_format()
  {
    // Arrange
    $adminLevel = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $admin = User::factory()->create(['user_level_id' => $adminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    $companyData = [
      'name' => 'Test Company',
      'cnpj' => '12.345.678/0001-90',
      'email' => 'invalid-email'
    ];

    // Act
    $response = $this->postJson('/api/companies', $companyData, [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(422)
      ->assertJsonValidationErrors(['email']);
  }

  #[Test]
  public function companies_list_supports_pagination()
  {
    // Arrange
    $adminLevel = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $admin = User::factory()->create(['user_level_id' => $adminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    Company::factory()->count(25)->create();

    // Act
    $response = $this->getJson('/api/companies?per_page=10', [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(200);
    $this->assertEquals(10, $response->json('meta.per_page'));
    $this->assertEquals(25, $response->json('meta.total'));
    $this->assertEquals(3, $response->json('meta.last_page'));
  }

  #[Test]
  public function soft_deleted_companies_are_excluded_from_list()
  {
    // Arrange
    $adminLevel = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $admin = User::factory()->create(['user_level_id' => $adminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    $activeCompany = Company::factory()->create(['name' => 'Active Company']);
    $deletedCompany = Company::factory()->create(['name' => 'Deleted Company']);
    $deletedCompany->delete();

    // Act
    $response = $this->getJson('/api/companies', [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(200);
    $this->assertEquals(1, $response->json('meta.total'));
    $this->assertEquals('Active Company', $response->json('data.0.name'));
  }

  #[Test]
  public function unauthenticated_user_cannot_access_companies_endpoints()
  {
    // Act & Assert
    $this->getJson('/api/companies')->assertStatus(401);
    $this->postJson('/api/companies', [])->assertStatus(401);
  }

  #[Test]
  public function company_creation_generates_uuid()
  {
    // Arrange
    $adminLevel = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $admin = User::factory()->create(['user_level_id' => $adminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    $companyData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90'
    ];

    // Act
    $response = $this->postJson('/api/companies', $companyData, [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(201);
    $companyId = $response->json('data.id');

    $this->assertIsString($companyId);
    $this->assertEquals(36, strlen($companyId));
    $this->assertMatchesRegularExpression(
      '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
      $companyId
    );
  }
}
