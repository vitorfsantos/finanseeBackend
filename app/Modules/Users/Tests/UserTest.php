<?php

namespace App\Modules\Users\Tests;

use PHPUnit\Framework\Attributes\Test;

use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  #[Test]
  public function it_can_be_created_with_required_fields()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();
    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => Hash::make('password123'),
      'user_level_id' => $userLevel->id
    ];

    // Act
    $user = User::create($userData);

    // Assert
    $this->assertInstanceOf(User::class, $user);
    $this->assertEquals($userData['name'], $user->name);
    $this->assertEquals($userData['email'], $user->email);
    $this->assertEquals($userData['user_level_id'], $user->user_level_id);
    $this->assertNotNull($user->id);
    $this->assertNotNull($user->created_at);
    $this->assertNotNull($user->updated_at);
  }

  #[Test]
  public function it_can_be_created_with_phone()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();
    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => Hash::make('password123'),
      'phone' => '(11) 99999-9999',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $user = User::create($userData);

    // Assert
    $this->assertEquals($userData['phone'], $user->phone);
  }

  #[Test]
  public function it_uses_uuid_as_primary_key()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    // Act
    $user = User::factory()->create(['user_level_id' => $userLevel->id]);

    // Assert
    $this->assertIsString($user->id);
    $this->assertEquals(36, strlen($user->id)); // UUID length
    $this->assertMatchesRegularExpression(
      '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
      $user->id
    );
  }

  #[Test]
  public function it_hashes_password_automatically()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();
    $password = 'password123';

    // Act
    $user = User::factory()->create([
      'password' => $password,
      'user_level_id' => $userLevel->id
    ]);

    // Assert
    $this->assertNotEquals($password, $user->password);
    $this->assertTrue(Hash::check($password, $user->password));
  }

  #[Test]
  public function it_hides_password_and_remember_token_in_serialization()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();
    $user = User::factory()->create(['user_level_id' => $userLevel->id]);

    // Act
    $userArray = $user->toArray();

    // Assert
    $this->assertArrayNotHasKey('password', $userArray);
    $this->assertArrayNotHasKey('remember_token', $userArray);
  }

  #[Test]
  public function it_casts_email_verified_at_to_datetime()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();
    $user = User::factory()->create([
      'email_verified_at' => now(),
      'user_level_id' => $userLevel->id
    ]);

    // Assert
    $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
  }

  #[Test]
  public function it_has_level_relationship()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create(['name' => 'Admin']);
    $user = User::factory()->create(['user_level_id' => $userLevel->id]);

    // Act & Assert
    $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $user->level());
    $this->assertEquals('Admin', $user->level->name);
  }

  #[Test]
  public function it_can_check_if_user_is_admin()
  {
    // Arrange
    $adminLevel = UserLevel::factory()->create(['id' => 1]); // Admin Master
    $regularLevel = UserLevel::factory()->create(['id' => 3]); // Regular User

    $adminUser = User::factory()->create(['user_level_id' => $adminLevel->id]);
    $regularUser = User::factory()->create(['user_level_id' => $regularLevel->id]);

    // Act & Assert
    $this->assertTrue($adminUser->isAdmin());
    $this->assertFalse($regularUser->isAdmin());
  }

  #[Test]
  public function it_can_check_if_user_is_master_admin()
  {
    // Arrange
    $masterAdminLevel = UserLevel::factory()->create(['id' => 1]); // Admin Master
    $companyAdminLevel = UserLevel::factory()->create(['id' => 2]); // Company Admin
    $regularLevel = UserLevel::factory()->create(['id' => 3]); // Regular User

    $masterAdmin = User::factory()->create(['user_level_id' => $masterAdminLevel->id]);
    $companyAdmin = User::factory()->create(['user_level_id' => $companyAdminLevel->id]);
    $regularUser = User::factory()->create(['user_level_id' => $regularLevel->id]);

    // Act & Assert
    $this->assertTrue($masterAdmin->isMasterAdmin());
    $this->assertFalse($companyAdmin->isMasterAdmin());
    $this->assertFalse($regularUser->isMasterAdmin());
  }

  #[Test]
  public function it_can_check_if_user_can_manage_users()
  {
    // Arrange
    $masterAdminLevel = UserLevel::factory()->create(['id' => 1]); // Admin Master
    $companyAdminLevel = UserLevel::factory()->create(['id' => 2]); // Company Admin
    $regularLevel = UserLevel::factory()->create(['id' => 3]); // Regular User

    $masterAdmin = User::factory()->create(['user_level_id' => $masterAdminLevel->id]);
    $companyAdmin = User::factory()->create(['user_level_id' => $companyAdminLevel->id]);
    $regularUser = User::factory()->create(['user_level_id' => $regularLevel->id]);

    // Act & Assert
    $this->assertTrue($masterAdmin->canManageUsers());
    $this->assertTrue($companyAdmin->canManageUsers());
    $this->assertFalse($regularUser->canManageUsers());
  }

  #[Test]
  public function it_returns_false_for_admin_methods_when_no_level()
  {
    // Arrange
    $user = User::factory()->make(['user_level_id' => null]);

    // Act & Assert
    $this->assertFalse($user->isAdmin());
    $this->assertFalse($user->isMasterAdmin());
    $this->assertFalse($user->canManageUsers());
  }

  #[Test]
  public function it_has_correct_fillable_fields()
  {
    // Arrange
    $user = new User();

    // Act
    $fillable = $user->getFillable();

    // Assert
    $expectedFillable = ['name', 'email', 'password', 'phone', 'user_level_id'];
    $this->assertEquals($expectedFillable, $fillable);
  }

  #[Test]
  public function it_can_be_updated()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();
    $user = User::factory()->create([
      'name' => 'Original Name',
      'email' => 'original@example.com',
      'user_level_id' => $userLevel->id
    ]);

    $updateData = [
      'name' => 'Updated Name',
      'email' => 'updated@example.com'
    ];

    // Act
    $user->update($updateData);

    // Assert
    $this->assertEquals('Updated Name', $user->fresh()->name);
    $this->assertEquals('updated@example.com', $user->fresh()->email);
  }

  #[Test]
  public function it_can_create_api_tokens()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();
    $user = User::factory()->create(['user_level_id' => $userLevel->id]);

    // Act
    $token = $user->createToken('test-token');

    // Assert
    $this->assertNotNull($token);
    $this->assertNotEmpty($token->plainTextToken);
    $this->assertDatabaseHas('personal_access_tokens', [
      'tokenable_id' => $user->id,
      'name' => 'test-token'
    ]);
  }

  #[Test]
  public function it_can_revoke_all_tokens()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();
    $user = User::factory()->create(['user_level_id' => $userLevel->id]);

    $user->createToken('token1');
    $user->createToken('token2');

    // Act
    $user->tokens()->delete();

    // Assert
    $this->assertDatabaseMissing('personal_access_tokens', [
      'tokenable_id' => $user->id
    ]);
  }

  #[Test]
  public function it_loads_level_relationship_correctly()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create(['name' => 'Test Level', 'slug' => 'test-level']);
    $user = User::factory()->create(['user_level_id' => $userLevel->id]);

    // Act
    $user->load('level');

    // Assert
    $this->assertTrue($user->relationLoaded('level'));
    $this->assertEquals('Test Level', $user->level->name);
    $this->assertEquals('test-level', $user->level->slug);
  }
}
