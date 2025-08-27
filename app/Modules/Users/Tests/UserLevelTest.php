<?php

namespace App\Modules\Users\Tests;

use PHPUnit\Framework\Attributes\Test;

use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserLevelTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  #[Test]
  public function it_can_be_created_with_required_fields()
  {
    // Arrange
    $levelData = [
      'slug' => 'admin-master',
      'name' => 'Admin Master'
    ];

    // Act
    $level = UserLevel::create($levelData);

    // Assert
    $this->assertInstanceOf(UserLevel::class, $level);
    $this->assertEquals($levelData['slug'], $level->slug);
    $this->assertEquals($levelData['name'], $level->name);
    $this->assertNotNull($level->id);
    $this->assertNotNull($level->created_at);
    $this->assertNotNull($level->updated_at);
  }

  #[Test]
  public function it_soft_deletes()
  {
    // Arrange
    $level = UserLevel::factory()->create();

    // Act
    $level->delete();

    // Assert
    $this->assertSoftDeleted($level);
    $this->assertNotNull($level->fresh()->deleted_at);
  }

  #[Test]
  public function it_can_be_restored_after_soft_delete()
  {
    // Arrange
    $level = UserLevel::factory()->create();
    $level->delete();

    // Act
    $level->restore();

    // Assert
    $this->assertNull($level->fresh()->deleted_at);
    $this->assertDatabaseHas('user_levels', [
      'id' => $level->id,
      'deleted_at' => null
    ]);
  }

  #[Test]
  public function it_has_users_relationship()
  {
    // Arrange
    $level = UserLevel::factory()->create();
    $user1 = User::factory()->create(['user_level_id' => $level->id]);
    $user2 = User::factory()->create(['user_level_id' => $level->id]);

    // Act & Assert
    $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $level->users());
    $this->assertEquals(2, $level->users()->count());
    $this->assertTrue($level->users->contains($user1));
    $this->assertTrue($level->users->contains($user2));
  }

  #[Test]
  public function it_identifies_admin_levels_correctly()
  {
    // Arrange
    $adminMaster = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $companyAdmin = UserLevel::factory()->create(['id' => 2, 'name' => 'Company Admin']);
    $regularUser = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);

    // Act & Assert
    $this->assertTrue($adminMaster->isAdmin());
    $this->assertTrue($companyAdmin->isAdmin());
    $this->assertFalse($regularUser->isAdmin());
  }

  #[Test]
  public function it_identifies_master_admin_correctly()
  {
    // Arrange
    $adminMaster = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $companyAdmin = UserLevel::factory()->create(['id' => 2, 'name' => 'Company Admin']);
    $regularUser = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);

    // Act & Assert
    $this->assertTrue($adminMaster->isMasterAdmin());
    $this->assertFalse($companyAdmin->isMasterAdmin());
    $this->assertFalse($regularUser->isMasterAdmin());
  }

  #[Test]
  public function it_identifies_user_management_permissions_correctly()
  {
    // Arrange
    $adminMaster = UserLevel::factory()->create(['id' => 1, 'name' => 'Admin Master']);
    $companyAdmin = UserLevel::factory()->create(['id' => 2, 'name' => 'Company Admin']);
    $regularUser = UserLevel::factory()->create(['id' => 3, 'name' => 'Regular User']);

    // Act & Assert
    $this->assertTrue($adminMaster->canManageUsers());
    $this->assertTrue($companyAdmin->canManageUsers());
    $this->assertFalse($regularUser->canManageUsers());
  }

  #[Test]
  public function it_has_correct_fillable_fields()
  {
    // Arrange
    $level = new UserLevel();

    // Act
    $fillable = $level->getFillable();

    // Assert
    $expectedFillable = ['slug', 'name'];
    $this->assertEquals($expectedFillable, $fillable);
  }

  #[Test]
  public function it_can_be_updated()
  {
    // Arrange
    $level = UserLevel::factory()->create([
      'slug' => 'original-slug',
      'name' => 'Original Name'
    ]);

    $updateData = [
      'slug' => 'updated-slug',
      'name' => 'Updated Name'
    ];

    // Act
    $level->update($updateData);

    // Assert
    $this->assertEquals('updated-slug', $level->fresh()->slug);
    $this->assertEquals('Updated Name', $level->fresh()->name);
  }

  #[Test]
  public function it_excludes_soft_deleted_levels_from_default_queries()
  {
    // Arrange
    $activeLevel = UserLevel::factory()->create(['name' => 'Active Level']);
    $deletedLevel = UserLevel::factory()->create(['name' => 'Deleted Level']);
    $deletedLevel->delete();

    // Act
    $levels = UserLevel::all();

    // Assert
    $this->assertEquals(1, $levels->count());
    $this->assertEquals('Active Level', $levels->first()->name);
  }

  #[Test]
  public function it_includes_soft_deleted_levels_when_using_with_trashed()
  {
    // Arrange
    $activeLevel = UserLevel::factory()->create(['name' => 'Active Level']);
    $deletedLevel = UserLevel::factory()->create(['name' => 'Deleted Level']);
    $deletedLevel->delete();

    // Act
    $levels = UserLevel::withTrashed()->get();

    // Assert
    $this->assertEquals(2, $levels->count());
    $this->assertTrue($levels->contains('name', 'Active Level'));
    $this->assertTrue($levels->contains('name', 'Deleted Level'));
  }

  #[Test]
  public function it_correctly_handles_edge_case_admin_level_ids()
  {
    // Arrange
    $level0 = UserLevel::factory()->create(['id' => 0, 'name' => 'Level 0']);
    $level1 = UserLevel::factory()->create(['id' => 1, 'name' => 'Level 1']);
    $level2 = UserLevel::factory()->create(['id' => 2, 'name' => 'Level 2']);
    $level3 = UserLevel::factory()->create(['id' => 3, 'name' => 'Level 3']);

    // Act & Assert
    // For id 0 (if it exists)
    $this->assertTrue($level0->isAdmin());
    $this->assertFalse($level0->isMasterAdmin()); // Only id = 1 is master admin
    $this->assertTrue($level0->canManageUsers());

    // For id 1 (Admin Master)
    $this->assertTrue($level1->isAdmin());
    $this->assertTrue($level1->isMasterAdmin());
    $this->assertTrue($level1->canManageUsers());

    // For id 2 (Company Admin)
    $this->assertTrue($level2->isAdmin());
    $this->assertFalse($level2->isMasterAdmin());
    $this->assertTrue($level2->canManageUsers());

    // For id 3 (Regular User)
    $this->assertFalse($level3->isAdmin());
    $this->assertFalse($level3->isMasterAdmin());
    $this->assertFalse($level3->canManageUsers());
  }

  #[Test]
  public function it_maintains_user_relationship_when_level_is_soft_deleted()
  {
    // Arrange
    $level = UserLevel::factory()->create();
    $user = User::factory()->create(['user_level_id' => $level->id]);

    // Act
    $level->delete();

    // Assert
    // User should still exist and reference the level
    $this->assertNotNull($user->fresh());
    $this->assertEquals($level->id, $user->fresh()->user_level_id);

    // But the level should be soft deleted
    $this->assertSoftDeleted($level);
  }

  #[Test]
  public function it_can_find_users_with_specific_level()
  {
    // Arrange
    $adminLevel = UserLevel::factory()->create(['name' => 'Admin']);
    $userLevel = UserLevel::factory()->create(['name' => 'User']);

    $admin1 = User::factory()->create(['user_level_id' => $adminLevel->id]);
    $admin2 = User::factory()->create(['user_level_id' => $adminLevel->id]);
    $user1 = User::factory()->create(['user_level_id' => $userLevel->id]);

    // Act
    $adminUsers = $adminLevel->users;
    $regularUsers = $userLevel->users;

    // Assert
    $this->assertEquals(2, $adminUsers->count());
    $this->assertEquals(1, $regularUsers->count());
    $this->assertTrue($adminUsers->contains($admin1));
    $this->assertTrue($adminUsers->contains($admin2));
    $this->assertTrue($regularUsers->contains($user1));
  }
}
