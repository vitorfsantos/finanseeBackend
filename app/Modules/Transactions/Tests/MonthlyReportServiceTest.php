<?php

namespace App\Modules\Transactions\Tests;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\Services\MonthlyReportService;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLevel;
use App\Modules\Companies\Models\Company;
use App\Modules\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MonthlyReportServiceTest extends TestCase
{

  private MonthlyReportService $service;

  protected function setUp(): void
  {
    parent::setUp();
    $this->service = new MonthlyReportService();
  }

  public function test_generates_monthly_report_for_regular_user()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 4]);

    // Criar transações do mês atual
    Transaction::factory()->create([
      'user_id' => $user->id,
      'type' => 'income',
      'amount' => 1000.00,
      'date' => now()->startOfMonth()->addDays(5),
    ]);

    Transaction::factory()->create([
      'user_id' => $user->id,
      'type' => 'expense',
      'amount' => 300.00,
      'date' => now()->startOfMonth()->addDays(10),
    ]);

    // Act
    $report = $this->service->generateReport($user);

    // Assert
    $this->assertArrayHasKey('period', $report);
    $this->assertArrayHasKey('summary', $report);
    $this->assertArrayHasKey('latest_transactions', $report);
    $this->assertArrayHasKey('company_breakdown', $report);

    $this->assertEquals(1000.00, $report['summary']['total_income']);
    $this->assertEquals(300.00, $report['summary']['total_expenses']);
    $this->assertEquals(700.00, $report['summary']['balance']);
    $this->assertEquals(2, $report['summary']['transaction_count']);

    $this->assertCount(2, $report['latest_transactions']);
    $this->assertEmpty($report['company_breakdown']); // User comum não tem breakdown por empresa
  }

  public function test_generates_monthly_report_for_company_admin()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 2]);
    $company = Company::factory()->create();

    // Associar usuário à empresa usando DB direto para evitar problema com UUID
    \Illuminate\Support\Facades\DB::table('company_user')->insert([
      'id' => \Illuminate\Support\Str::uuid(),
      'user_id' => $user->id,
      'company_id' => $company->id,
      'role' => 'manager',
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    // Criar transações da empresa
    Transaction::factory()->create([
      'user_id' => $user->id,
      'company_id' => $company->id,
      'type' => 'income',
      'amount' => 2000.00,
      'date' => now()->startOfMonth()->addDays(3),
    ]);

    Transaction::factory()->create([
      'user_id' => $user->id,
      'company_id' => $company->id,
      'type' => 'expense',
      'amount' => 500.00,
      'date' => now()->startOfMonth()->addDays(7),
    ]);

    // Act
    $report = $this->service->generateReport($user);

    // Assert
    $this->assertEquals(2000.00, $report['summary']['total_income']);
    $this->assertEquals(500.00, $report['summary']['total_expenses']);
    $this->assertEquals(1500.00, $report['summary']['balance']);
    $this->assertEquals(2, $report['summary']['transaction_count']);

    $this->assertCount(1, $report['company_breakdown']);
    $this->assertEquals($company->id, $report['company_breakdown'][0]['company']['id']);
  }

  public function test_generates_monthly_report_for_specific_month()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 4]);

    $specificMonth = now()->subMonth(); // Mês passado

    // Criar transação no mês específico
    Transaction::factory()->create([
      'user_id' => $user->id,
      'type' => 'income',
      'amount' => 500.00,
      'date' => $specificMonth->startOfMonth()->addDays(10),
    ]);

    // Criar transação no mês atual (não deve aparecer)
    Transaction::factory()->create([
      'user_id' => $user->id,
      'type' => 'income',
      'amount' => 1000.00,
      'date' => now()->startOfMonth()->addDays(5),
    ]);

    // Act
    $report = $this->service->generateReport($user, $specificMonth->year, $specificMonth->month);

    // Assert
    $this->assertEquals($specificMonth->year, $report['period']['year']);
    $this->assertEquals($specificMonth->month, $report['period']['month']);
    $this->assertEquals(500.00, $report['summary']['total_income']);
    $this->assertEquals(0.00, $report['summary']['total_expenses']);
    $this->assertEquals(500.00, $report['summary']['balance']);
    $this->assertEquals(1, $report['summary']['transaction_count']);
  }

  public function test_returns_latest_transactions()
  {
    // Arrange
    $user = User::factory()->create(['user_level_id' => 4]);

    // Criar 7 transações (mais que o limite de 5)
    for ($i = 0; $i < 7; $i++) {
      Transaction::factory()->create([
        'user_id' => $user->id,
        'type' => 'income',
        'amount' => 100.00,
        'date' => now()->subDays($i),
      ]);
    }

    // Act
    $report = $this->service->generateReport($user);

    // Assert
    $this->assertCount(5, $report['latest_transactions']);

    // Verificar se estão ordenadas por data (mais recente primeiro)
    $dates = array_column($report['latest_transactions'], 'date');
    $this->assertEquals(now()->format('Y-m-d'), $dates[0]);
  }
}
