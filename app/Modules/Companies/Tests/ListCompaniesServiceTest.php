<?php

namespace App\Modules\Companies\Tests;

use PHPUnit\Framework\Attributes\Test;

use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Services\ListCompaniesService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ListCompaniesServiceTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  protected ListCompaniesService $service;

  protected function setUp(): void
  {
    parent::setUp();

    $this->service = new ListCompaniesService();
  }

  #[Test]
  public function it_can_get_all_companies()
  {
    // Arrange
    Company::factory()->count(3)->create();

    // Act
    $companies = $this->service->getAll();

    // Assert
    $this->assertInstanceOf(Collection::class, $companies);
    $this->assertEquals(3, $companies->count());
  }

  #[Test]
  public function it_returns_empty_collection_when_no_companies_exist()
  {
    // Act
    $companies = $this->service->getAll();

    // Assert
    $this->assertInstanceOf(Collection::class, $companies);
    $this->assertEquals(0, $companies->count());
    $this->assertTrue($companies->isEmpty());
  }

  #[Test]
  public function it_excludes_soft_deleted_companies_from_get_all()
  {
    // Arrange
    $activeCompany = Company::factory()->create(['name' => 'Active Company']);
    $deletedCompany = Company::factory()->create(['name' => 'Deleted Company']);
    $deletedCompany->delete();

    // Act
    $companies = $this->service->getAll();

    // Assert
    $this->assertEquals(1, $companies->count());
    $this->assertEquals('Active Company', $companies->first()->name);
  }

  #[Test]
  public function it_can_get_paginated_companies_with_default_per_page()
  {
    // Arrange
    Company::factory()->count(20)->create();

    // Act
    $companies = $this->service->getPaginated();

    // Assert
    $this->assertInstanceOf(LengthAwarePaginator::class, $companies);
    $this->assertEquals(15, $companies->perPage()); // Default per page
    $this->assertEquals(20, $companies->total());
    $this->assertEquals(2, $companies->lastPage());
  }

  #[Test]
  public function it_can_get_paginated_companies_with_custom_per_page()
  {
    // Arrange
    Company::factory()->count(25)->create();

    // Act
    $companies = $this->service->getPaginated(10);

    // Assert
    $this->assertInstanceOf(LengthAwarePaginator::class, $companies);
    $this->assertEquals(10, $companies->perPage());
    $this->assertEquals(25, $companies->total());
    $this->assertEquals(3, $companies->lastPage());
  }

  #[Test]
  public function it_excludes_soft_deleted_companies_from_pagination()
  {
    // Arrange
    Company::factory()->count(10)->create();
    $deletedCompanies = Company::factory()->count(5)->create();

    // Soft delete some companies
    $deletedCompanies->each(function ($company) {
      $company->delete();
    });

    // Act
    $companies = $this->service->getPaginated();

    // Assert
    $this->assertEquals(10, $companies->total());
    $this->assertEquals(10, count($companies->items()));
  }

  #[Test]
  public function it_can_search_companies_by_name()
  {
    // Arrange
    Company::factory()->create(['name' => 'Tech Company LTDA', 'cnpj' => '11.111.111/0001-11']);
    Company::factory()->create(['name' => 'Marketing Agency', 'cnpj' => '22.222.222/0001-22']);
    Company::factory()->create(['name' => 'Tech Solutions Inc', 'cnpj' => '33.333.333/0001-33']);

    // Act
    $companies = $this->service->search('Tech');

    // Assert
    $this->assertInstanceOf(LengthAwarePaginator::class, $companies);
    $this->assertEquals(2, $companies->total());
    $items = collect($companies->items());
    $this->assertTrue($items->contains('name', 'Tech Company LTDA'));
    $this->assertTrue($items->contains('name', 'Tech Solutions Inc'));
    $this->assertFalse($items->contains('name', 'Marketing Agency'));
  }

  #[Test]
  public function it_can_search_companies_by_cnpj()
  {
    // Arrange
    Company::factory()->create(['name' => 'Company A', 'cnpj' => '11.111.111/0001-11']);
    Company::factory()->create(['name' => 'Company B', 'cnpj' => '22.222.222/0001-22']);
    Company::factory()->create(['name' => 'Company C', 'cnpj' => '11.333.333/0001-33']);

    // Act
    $companies = $this->service->search('11.111');

    // Assert
    $this->assertInstanceOf(LengthAwarePaginator::class, $companies);
    $this->assertEquals(1, $companies->total());
    $items = collect($companies->items());
    $this->assertEquals('Company A', $items->first()->name);
  }

  #[Test]
  public function it_can_search_companies_by_partial_cnpj()
  {
    // Arrange
    Company::factory()->create(['name' => 'Company A', 'cnpj' => '11.111.111/0001-11']);
    Company::factory()->create(['name' => 'Company B', 'cnpj' => '22.222.222/0001-22']);
    Company::factory()->create(['name' => 'Company C', 'cnpj' => '11.333.333/0001-33']);

    // Act
    $companies = $this->service->search('11.');

    // Assert
    $this->assertEquals(2, $companies->total());
    $items = collect($companies->items());
    $this->assertTrue($items->contains('name', 'Company A'));
    $this->assertTrue($items->contains('name', 'Company C'));
  }

  #[Test]
  public function it_returns_empty_paginator_when_search_finds_no_results()
  {
    // Arrange
    Company::factory()->create(['name' => 'Tech Company', 'cnpj' => '11.111.111/0001-11']);

    // Act
    $companies = $this->service->search('NonExistent');

    // Assert
    $this->assertInstanceOf(LengthAwarePaginator::class, $companies);
    $this->assertEquals(0, $companies->total());
    $this->assertTrue(empty($companies->items()));
  }

  #[Test]
  public function it_search_is_case_insensitive()
  {
    // Arrange
    Company::factory()->create(['name' => 'Tech Company LTDA', 'cnpj' => '11.111.111/0001-11']);

    // Act
    $companiesLower = $this->service->search('tech');
    $companiesUpper = $this->service->search('TECH');
    $companiesMixed = $this->service->search('TeCh');

    // Assert
    $this->assertEquals(1, $companiesLower->total());
    $this->assertEquals(1, $companiesUpper->total());
    $this->assertEquals(1, $companiesMixed->total());
  }

  #[Test]
  public function it_can_search_with_custom_per_page()
  {
    // Arrange
    Company::factory()->count(25)->create([
      'name' => 'Tech Company'
    ]);

    // Act
    $companies = $this->service->search('Tech', 5);

    // Assert
    $this->assertEquals(5, $companies->perPage());
    $this->assertEquals(25, $companies->total());
    $this->assertEquals(5, $companies->lastPage());
  }

  #[Test]
  public function it_excludes_soft_deleted_companies_from_search()
  {
    // Arrange
    $activeCompany = Company::factory()->create(['name' => 'Tech Company Active']);
    $deletedCompany = Company::factory()->create(['name' => 'Tech Company Deleted']);
    $deletedCompany->delete();

    // Act
    $companies = $this->service->search('Tech Company');

    // Assert
    $this->assertEquals(1, $companies->total());
    $items = collect($companies->items());
    $this->assertEquals('Tech Company Active', $items->first()->name);
  }

  #[Test]
  public function it_searches_in_both_name_and_cnpj_fields()
  {
    // Arrange
    Company::factory()->create(['name' => 'ABC Company', 'cnpj' => '11.111.111/0001-11']);
    Company::factory()->create(['name' => 'XYZ Corporation', 'cnpj' => '22.ABC.222/0001-22']);
    Company::factory()->create(['name' => 'DEF Ltd', 'cnpj' => '33.333.333/0001-33']);

    // Act
    $companies = $this->service->search('ABC');

    // Assert
    $this->assertEquals(2, $companies->total());
    $items = collect($companies->items());
    $this->assertTrue($items->contains('name', 'ABC Company'));
    $this->assertTrue($items->contains('name', 'XYZ Corporation'));
  }
}
