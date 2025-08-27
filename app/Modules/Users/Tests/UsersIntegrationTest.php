<?php

namespace App\Modules\Users\Tests;

use PHPUnit\Framework\Attributes\Test;

use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsersIntegrationTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  protected function setUp(): void
  {
    parent::setUp();
  }

  #[Test]
  public function admin_master_can_list_users()
  {
    // Arrange
    $adminLevel = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $userLevel = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);

    $admin = User::factory()->create(['user_level_id' => $adminLevel->id]);
    $users = User::factory()->count(3)->create(['user_level_id' => $userLevel->id]);

    $token = $admin->createToken('auth-token')->plainTextToken;

    // Act
    $response = $this->getJson('/api/users', [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(200)
      ->assertJsonStructure([
        'data' => [
          '*' => [
            'id',
            'name',
            'email',
            'user_level_id',
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

    $this->assertEquals(4, $response->json('meta.total')); // 3 users + 1 admin
  }

  #[Test]
  public function non_admin_master_cannot_list_users()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);
    $user = User::factory()->create(['user_level_id' => $userLevel->id]);
    $token = $user->createToken('auth-token')->plainTextToken;

    // Act
    $response = $this->getJson('/api/users', [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(403);
  }

  #[Test]
  public function company_admin_can_create_user()
  {
    // Arrange
    $companyAdminLevel = UserLevel::factory()->create(['id' => 2, 'name' => 'Company Admin']);
    $regularUserLevel = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);

    $admin = User::factory()->create(['user_level_id' => $companyAdminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'phone' => '(11) 99999-9999',
      'user_level_id' => $regularUserLevel->id
    ];

    // Act
    $response = $this->postJson('/api/users', $userData, [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(201)
      ->assertJsonStructure([
        'message',
        'data' => [
          'id',
          'name',
          'email',
          'user_level_id',
          'created_at',
          'updated_at'
        ]
      ])
      ->assertJson([
        'message' => 'Usuário criado com sucesso',
        'data' => [
          'name' => $userData['name'],
          'email' => $userData['email'],
          'user_level_id' => $userData['user_level_id']
        ]
      ]);

    $this->assertDatabaseHas('users', [
      'name' => $userData['name'],
      'email' => $userData['email']
    ]);
  }

  #[Test]
  public function regular_user_cannot_create_user()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);
    $user = User::factory()->create(['user_level_id' => $userLevel->id]);
    $token = $user->createToken('auth-token')->plainTextToken;

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $response = $this->postJson('/api/users', $userData, [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(403);
  }

  #[Test]
  public function anyone_can_view_specific_user()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);
    $user = User::factory()->create(['user_level_id' => $userLevel->id]);
    $targetUser = User::factory()->create(['user_level_id' => $userLevel->id]);
    $token = $user->createToken('auth-token')->plainTextToken;

    // Act
    $response = $this->getJson("/api/users/{$targetUser->id}", [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(200)
      ->assertJsonStructure([
        'data' => [
          'id',
          'name',
          'email',
          'user_level_id',
          'created_at',
          'updated_at'
        ]
      ])
      ->assertJson([
        'data' => [
          'id' => $targetUser->id,
          'name' => $targetUser->name,
          'email' => $targetUser->email,
          'user_level_id' => $targetUser->user_level_id
        ]
      ]);
  }

  #[Test]
  public function create_user_validates_required_fields()
  {
    // Arrange
    $companyAdminLevel = UserLevel::factory()->create(['id' => 2, 'name' => 'Company Admin']);
    $admin = User::factory()->create(['user_level_id' => $companyAdminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    // Act
    $response = $this->postJson('/api/users', [], [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(422)
      ->assertJsonValidationErrors(['name', 'email', 'password', 'user_level_id']);
  }

  #[Test]
  public function create_user_validates_unique_email()
  {
    // Arrange
    $companyAdminLevel = UserLevel::factory()->create(['id' => 2, 'name' => 'Company Admin']);
    $userLevel = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);

    $admin = User::factory()->create(['user_level_id' => $companyAdminLevel->id]);
    $existingUser = User::factory()->create([
      'email' => 'existing@example.com',
      'user_level_id' => $userLevel->id
    ]);

    $token = $admin->createToken('auth-token')->plainTextToken;

    $userData = [
      'name' => 'New User',
      'email' => 'existing@example.com', // Same email
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $response = $this->postJson('/api/users', $userData, [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(422)
      ->assertJsonValidationErrors(['email']);
  }

  #[Test]
  public function create_user_validates_email_format()
  {
    // Arrange
    $companyAdminLevel = UserLevel::factory()->create(['id' => 2, 'name' => 'Company Admin']);
    $userLevel = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);

    $admin = User::factory()->create(['user_level_id' => $companyAdminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    $userData = [
      'name' => 'John Doe',
      'email' => 'invalid-email',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $response = $this->postJson('/api/users', $userData, [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(422)
      ->assertJsonValidationErrors(['email']);
  }

  #[Test]
  public function create_user_validates_password_minimum_length()
  {
    // Arrange
    $companyAdminLevel = UserLevel::factory()->create(['id' => 2, 'name' => 'Company Admin']);
    $userLevel = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);

    $admin = User::factory()->create(['user_level_id' => $companyAdminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => '12345', // 5 characters (less than 6)
      'user_level_id' => $userLevel->id
    ];

    // Act
    $response = $this->postJson('/api/users', $userData, [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(422)
      ->assertJsonValidationErrors(['password']);
  }

  #[Test]
  public function create_user_validates_user_level_exists()
  {
    // Arrange
    $companyAdminLevel = UserLevel::factory()->create(['id' => 2, 'name' => 'Company Admin']);
    $admin = User::factory()->create(['user_level_id' => $companyAdminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => 999 // Non-existent level
    ];

    // Act
    $response = $this->postJson('/api/users', $userData, [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(422)
      ->assertJsonValidationErrors(['user_level_id']);
  }

  #[Test]
  public function created_user_password_is_hashed()
  {
    // Arrange
    $companyAdminLevel = UserLevel::factory()->create(['id' => 2, 'name' => 'Company Admin']);
    $userLevel = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);

    $admin = User::factory()->create(['user_level_id' => $companyAdminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    $plainPassword = 'password123';
    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => $plainPassword,
      'user_level_id' => $userLevel->id
    ];

    // Act
    $response = $this->postJson('/api/users', $userData, [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(201);

    $createdUser = User::where('email', 'john@example.com')->first();
    $this->assertNotEquals($plainPassword, $createdUser->password);
    $this->assertTrue(Hash::check($plainPassword, $createdUser->password));
  }

  #[Test]
  public function created_user_has_uuid_id()
  {
    // Arrange
    $companyAdminLevel = UserLevel::factory()->create(['id' => 2, 'name' => 'Company Admin']);
    $userLevel = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);

    $admin = User::factory()->create(['user_level_id' => $companyAdminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $response = $this->postJson('/api/users', $userData, [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(201);
    $userId = $response->json('data.id');

    $this->assertIsString($userId);
    $this->assertEquals(36, strlen($userId));
    $this->assertMatchesRegularExpression(
      '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
      $userId
    );
  }

  #[Test]
  public function created_user_response_excludes_sensitive_data()
  {
    // Arrange
    $companyAdminLevel = UserLevel::factory()->create(['id' => 2, 'name' => 'Company Admin']);
    $userLevel = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);

    $admin = User::factory()->create(['user_level_id' => $companyAdminLevel->id]);
    $token = $admin->createToken('auth-token')->plainTextToken;

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $response = $this->postJson('/api/users', $userData, [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(201);
    $responseData = $response->json('data');

    $this->assertArrayNotHasKey('password', $responseData);
    $this->assertArrayNotHasKey('remember_token', $responseData);
  }

  #[Test]
  public function unauthenticated_user_cannot_access_user_endpoints()
  {
    // Act & Assert
    $this->getJson('/api/users')->assertStatus(401);
    $this->postJson('/api/users', [])->assertStatus(401);
  }

  #[Test]
  public function users_list_supports_pagination()
  {
    // Arrange
    $adminLevel = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $userLevel = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);

    $admin = User::factory()->create(['user_level_id' => $adminLevel->id]);
    User::factory()->count(25)->create(['user_level_id' => $userLevel->id]);

    $token = $admin->createToken('auth-token')->plainTextToken;

    // Act
    $response = $this->getJson('/api/users?per_page=10', [
      'Authorization' => "Bearer $token"
    ]);

    // Assert
    $response->assertStatus(200);
    $this->assertEquals(10, $response->json('meta.per_page'));
    $this->assertEquals(26, $response->json('meta.total')); // 25 users + 1 admin
  }
}
