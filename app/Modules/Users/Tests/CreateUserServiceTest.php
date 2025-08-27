<?php

namespace App\Modules\Users\Tests;

use PHPUnit\Framework\Attributes\Test;

use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLevel;
use App\Modules\Users\Services\CreateUserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateUserServiceTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  protected CreateUserService $service;

  protected function setUp(): void
  {
    parent::setUp();

    $this->service = new CreateUserService();
  }

  #[Test]
  public function it_can_create_a_user_with_all_fields()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create(['id' => 2, 'name' => 'Company Admin']);

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'phone' => '(11) 99999-9999',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $user = $this->service->create($userData);

    // Assert
    $this->assertInstanceOf(User::class, $user);
    $this->assertEquals($userData['name'], $user->name);
    $this->assertEquals($userData['email'], $user->email);
    $this->assertEquals($userData['phone'], $user->phone);
    $this->assertEquals($userData['user_level_id'], $user->user_level_id);
    $this->assertNotNull($user->id);
    $this->assertNotNull($user->created_at);
    $this->assertNotNull($user->updated_at);
  }

  #[Test]
  public function it_can_create_a_user_with_only_required_fields()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create(['id' => 2, 'name' => 'Company Admin']);

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $user = $this->service->create($userData);

    // Assert
    $this->assertInstanceOf(User::class, $user);
    $this->assertEquals($userData['name'], $user->name);
    $this->assertEquals($userData['email'], $user->email);
    $this->assertEquals($userData['user_level_id'], $user->user_level_id);
    $this->assertNull($user->phone);
  }

  #[Test]
  public function it_hashes_password_before_storing()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();
    $plainPassword = 'password123';

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => $plainPassword,
      'user_level_id' => $userLevel->id
    ];

    // Act
    $user = $this->service->create($userData);

    // Assert
    $this->assertNotEquals($plainPassword, $user->password);
    $this->assertTrue(Hash::check($plainPassword, $user->password));
  }

  #[Test]
  public function it_requires_password_field()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'user_level_id' => $userLevel->id
      // Note: no password provided
    ];

    // Act & Assert
    $this->expectException(\Illuminate\Database\QueryException::class);
    $this->service->create($userData);
  }

  #[Test]
  public function it_persists_user_to_database()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'phone' => '(11) 99999-9999',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $user = $this->service->create($userData);

    // Assert
    $this->assertDatabaseHas('users', [
      'id' => $user->id,
      'name' => $userData['name'],
      'email' => $userData['email'],
      'phone' => $userData['phone'],
      'user_level_id' => $userData['user_level_id']
    ]);
  }

  #[Test]
  public function it_generates_uuid_for_new_user()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $user = $this->service->create($userData);

    // Assert
    $this->assertIsString($user->id);
    $this->assertEquals(36, strlen($user->id)); // UUID length
    $this->assertMatchesRegularExpression(
      '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
      $user->id
    );
  }

  #[Test]
  public function it_sets_timestamps_on_creation()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $user = $this->service->create($userData);

    // Assert
    $this->assertNotNull($user->created_at);
    $this->assertNotNull($user->updated_at);
    $this->assertInstanceOf(\Carbon\Carbon::class, $user->created_at);
    $this->assertInstanceOf(\Carbon\Carbon::class, $user->updated_at);
  }

  #[Test]
  public function it_returns_fresh_user_instance()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $user = $this->service->create($userData);

    // Assert
    $this->assertTrue($user->exists);
    $this->assertFalse($user->wasRecentlyCreated === false); // Recently created should be true
    $this->assertEquals($userData['name'], $user->name);
  }

  #[Test]
  public function it_handles_optional_phone_field()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $user = $this->service->create($userData);

    // Assert
    $this->assertEquals($userData['name'], $user->name);
    $this->assertEquals($userData['email'], $user->email);
    $this->assertEquals($userData['user_level_id'], $user->user_level_id);
    $this->assertNull($user->phone);
  }

  #[Test]
  public function it_creates_multiple_users_with_unique_ids()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $userData1 = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    $userData2 = [
      'name' => 'Jane Smith',
      'email' => 'jane@example.com',
      'password' => 'password456',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $user1 = $this->service->create($userData1);
    $user2 = $this->service->create($userData2);

    // Assert
    $this->assertNotEquals($user1->id, $user2->id);
    $this->assertEquals('John Doe', $user1->name);
    $this->assertEquals('Jane Smith', $user2->name);
  }

  #[Test]
  public function it_preserves_user_level_relationship()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create(['name' => 'Test Level']);

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $user = $this->service->create($userData);

    // Assert
    $this->assertEquals($userLevel->id, $user->user_level_id);
    $this->assertEquals('Test Level', $user->level->name);
  }

  #[Test]
  public function it_handles_different_password_lengths()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $shortPassword = '123456'; // Minimum length
    $longPassword = str_repeat('a', 200); // Long password

    $userData1 = [
      'name' => 'User 1',
      'email' => 'user1@example.com',
      'password' => $shortPassword,
      'user_level_id' => $userLevel->id
    ];

    $userData2 = [
      'name' => 'User 2',
      'email' => 'user2@example.com',
      'password' => $longPassword,
      'user_level_id' => $userLevel->id
    ];

    // Act
    $user1 = $this->service->create($userData1);
    $user2 = $this->service->create($userData2);

    // Assert
    $this->assertTrue(Hash::check($shortPassword, $user1->password));
    $this->assertTrue(Hash::check($longPassword, $user2->password));
  }

  #[Test]
  public function it_handles_empty_password_string()
  {
    // Arrange
    $userLevel = UserLevel::factory()->create();

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => '',
      'user_level_id' => $userLevel->id
    ];

    // Act
    $user = $this->service->create($userData);

    // Assert
    $this->assertTrue(Hash::check('', $user->password));
  }
}
