<?php

namespace App\Modules\Companies\Tests;

use PHPUnit\Framework\Attributes\Test;

use App\Modules\Companies\Models\Company;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  #[Test]
  public function it_can_be_created_with_required_fields()
  {
    // Arrange
    $companyData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90',
      'email' => 'contact@testcompany.com',
      'phone' => '(11) 3333-4444'
    ];

    // Act
    $company = Company::create($companyData);

    // Assert
    $this->assertInstanceOf(Company::class, $company);
    $this->assertEquals($companyData['name'], $company->name);
    $this->assertEquals($companyData['cnpj'], $company->cnpj);
    $this->assertEquals($companyData['email'], $company->email);
    $this->assertEquals($companyData['phone'], $company->phone);
    $this->assertNotNull($company->id);
    $this->assertNotNull($company->created_at);
    $this->assertNotNull($company->updated_at);
  }

  #[Test]
  public function it_can_be_created_with_only_required_fields()
  {
    // Arrange
    $companyData = [
      'name' => 'Test Company LTDA',
      'cnpj' => '12.345.678/0001-90'
    ];

    // Act
    $company = Company::create($companyData);

    // Assert
    $this->assertInstanceOf(Company::class, $company);
    $this->assertEquals($companyData['name'], $company->name);
    $this->assertEquals($companyData['cnpj'], $company->cnpj);
    $this->assertNull($company->email);
    $this->assertNull($company->phone);
  }

  #[Test]
  public function it_uses_uuid_as_primary_key()
  {
    // Arrange & Act
    $company = Company::factory()->create();

    // Assert
    $this->assertIsString($company->id);
    $this->assertEquals(36, strlen($company->id)); // UUID length
    $this->assertMatchesRegularExpression(
      '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
      $company->id
    );
  }

  #[Test]
  public function it_soft_deletes()
  {
    // Arrange
    $company = Company::factory()->create();

    // Act
    $company->delete();

    // Assert
    $this->assertSoftDeleted($company);
    $this->assertNotNull($company->fresh()->deleted_at);
  }

  #[Test]
  public function it_can_be_restored_after_soft_delete()
  {
    // Arrange
    $company = Company::factory()->create();
    $company->delete();

    // Act
    $company->restore();

    // Assert
    $this->assertNull($company->fresh()->deleted_at);
    $this->assertDatabaseHas('companies', [
      'id' => $company->id,
      'deleted_at' => null
    ]);
  }

  #[Test]
  public function it_has_users_relationship()
  {
    // Arrange
    $company = Company::factory()->create();
    $userLevel = UserLevel::factory()->create();
    $user1 = User::factory()->create(['user_level_id' => $userLevel->id]);
    $user2 = User::factory()->create(['user_level_id' => $userLevel->id]);

    // Act & Assert
    $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $company->users());
  }

  #[Test]
  public function it_casts_timestamps_to_datetime()
  {
    // Arrange & Act
    $company = Company::factory()->create();

    // Assert
    $this->assertInstanceOf(\Carbon\Carbon::class, $company->created_at);
    $this->assertInstanceOf(\Carbon\Carbon::class, $company->updated_at);
  }

  #[Test]
  public function it_casts_deleted_at_to_datetime_when_soft_deleted()
  {
    // Arrange
    $company = Company::factory()->create();

    // Act
    $company->delete();

    // Assert
    $this->assertInstanceOf(\Carbon\Carbon::class, $company->fresh()->deleted_at);
  }

  #[Test]
  public function it_has_correct_fillable_fields()
  {
    // Arrange
    $company = new Company();

    // Act
    $fillable = $company->getFillable();

    // Assert
    $expectedFillable = ['name', 'cnpj', 'email', 'phone'];
    $this->assertEquals($expectedFillable, $fillable);
  }

  #[Test]
  public function it_can_be_updated()
  {
    // Arrange
    $company = Company::factory()->create([
      'name' => 'Original Name',
      'email' => 'original@example.com'
    ]);

    $updateData = [
      'name' => 'Updated Name',
      'email' => 'updated@example.com'
    ];

    // Act
    $company->update($updateData);

    // Assert
    $this->assertEquals('Updated Name', $company->fresh()->name);
    $this->assertEquals('updated@example.com', $company->fresh()->email);
  }

  #[Test]
  public function it_excludes_soft_deleted_companies_from_default_queries()
  {
    // Arrange
    $activeCompany = Company::factory()->create(['name' => 'Active Company']);
    $deletedCompany = Company::factory()->create(['name' => 'Deleted Company']);
    $deletedCompany->delete();

    // Act
    $companies = Company::all();

    // Assert
    $this->assertEquals(1, $companies->count());
    $this->assertEquals('Active Company', $companies->first()->name);
  }

  #[Test]
  public function it_includes_soft_deleted_companies_when_using_with_trashed()
  {
    // Arrange
    $activeCompany = Company::factory()->create(['name' => 'Active Company']);
    $deletedCompany = Company::factory()->create(['name' => 'Deleted Company']);
    $deletedCompany->delete();

    // Act
    $companies = Company::withTrashed()->get();

    // Assert
    $this->assertEquals(2, $companies->count());
    $this->assertTrue($companies->contains('name', 'Active Company'));
    $this->assertTrue($companies->contains('name', 'Deleted Company'));
  }
}
