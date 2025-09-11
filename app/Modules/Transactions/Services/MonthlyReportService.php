<?php

namespace App\Modules\Transactions\Services;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class MonthlyReportService
{
  /**
   * Generate monthly report based on user permissions
   */
  public function generateReport(User $currentUser, ?int $year = null, ?int $month = null): array
  {
    // Se não especificado, usa o mês atual
    $year = $year ?? now()->year;
    $month = $month ?? now()->month;

    $startDate = Carbon::create($year, $month, 1)->startOfMonth();
    $endDate = Carbon::create($year, $month, 1)->endOfMonth();

    // Aplica filtros baseados nas permissões do usuário
    $query = $this->buildPermissionQuery($currentUser, $startDate, $endDate);

    // Calcula totais
    $totals = $this->calculateTotals($query);

    // Busca últimas 5 transações
    $latestTransactions = $this->getLatestTransactions($currentUser, 5);

    // Se for admin, agrupa por empresa
    $companyBreakdown = [];
    if ($currentUser->isAdmin()) {
      $companyBreakdown = $this->getCompanyBreakdown($currentUser, $startDate, $endDate);
    }

    return [
      'period' => [
        'year' => $year,
        'month' => $month,
        'start_date' => $startDate->format('Y-m-d'),
        'end_date' => $endDate->format('Y-m-d'),
        'month_name' => $startDate->format('F'),
      ],
      'summary' => $totals,
      'latest_transactions' => $latestTransactions,
      'company_breakdown' => $companyBreakdown,
    ];
  }

  /**
   * Build query with permission filters
   */
  private function buildPermissionQuery(User $currentUser, Carbon $startDate, Carbon $endDate): Builder
  {
    $query = Transaction::query()
      ->whereBetween('date', [$startDate, $endDate]);

    if (!$currentUser->level) {
      throw new \Exception('Usuário sem nível definido');
    }

    $userLevel = $currentUser->level->slug;

    switch ($userLevel) {
      case 'adminMaster':
        // Admin Master pode ver todas as transações
        break;

      case 'companyAdmin':
      case 'companyUser':
        // Company Admin e Company User podem ver transações das suas empresas
        // e transações sem company_id apenas se user_id for o próprio usuário
        $companyIds = $this->getUserCompanyIds($currentUser);
        if (!empty($companyIds)) {
          $query->where(function ($q) use ($companyIds, $currentUser) {
            $q->whereIn('company_id', $companyIds)
              ->orWhere(function ($subQ) use ($currentUser) {
                $subQ->whereNull('company_id')
                  ->where('user_id', $currentUser->id);
              });
          });
        } else {
          // Se não tem empresas, só pode ver suas próprias transações sem company_id
          $query->whereNull('company_id')
            ->where('user_id', $currentUser->id);
        }
        break;

      case 'user':
        // User comum só pode ver suas próprias transações
        $query->where('user_id', $currentUser->id);
        break;

      default:
        throw new \Exception('Nível de usuário não reconhecido');
    }

    return $query;
  }

  /**
   * Calculate totals from query
   */
  private function calculateTotals(Builder $query): array
  {
    $incomeTotal = (clone $query)->where('type', 'income')->sum('amount');
    $expenseTotal = (clone $query)->where('type', 'expense')->sum('amount');
    $transactionCount = (clone $query)->count();
    $balance = $incomeTotal - $expenseTotal;

    return [
      'total_income' => round($incomeTotal, 2),
      'total_expenses' => round($expenseTotal, 2),
      'balance' => round($balance, 2),
      'transaction_count' => $transactionCount,
    ];
  }

  /**
   * Get latest transactions based on user permissions
   */
  private function getLatestTransactions(User $currentUser, int $limit = 5): array
  {
    $query = Transaction::with(['user', 'company'])
      ->orderBy('date', 'desc')
      ->orderBy('created_at', 'desc')
      ->limit($limit);

    // Aplica os mesmos filtros de permissão, mas sem filtro de data
    $this->applyPermissionFiltersToQuery($query, $currentUser);

    return $query->get()->map(function ($transaction) {
      return [
        'id' => $transaction->id,
        'type' => $transaction->type,
        'category' => $transaction->category,
        'description' => $transaction->description,
        'amount' => $transaction->amount,
        'date' => $transaction->date->format('Y-m-d'),
        'user' => $transaction->user ? [
          'id' => $transaction->user->id,
          'name' => $transaction->user->name,
        ] : null,
        'company' => $transaction->company ? [
          'id' => $transaction->company->id,
          'name' => $transaction->company->name,
        ] : null,
      ];
    })->toArray();
  }

  /**
   * Get company breakdown for admin users
   */
  private function getCompanyBreakdown(User $currentUser, Carbon $startDate, Carbon $endDate): array
  {
    $query = $this->buildPermissionQuery($currentUser, $startDate, $endDate);

    $companyData = $query->with('company')
      ->get()
      ->groupBy('company_id')
      ->map(function ($transactions, $companyId) {
        $company = $transactions->first()->company;
        $income = $transactions->where('type', 'income')->sum('amount');
        $expenses = $transactions->where('type', 'expense')->sum('amount');
        $count = $transactions->count();

        return [
          'company' => $company ? [
            'id' => $company->id,
            'name' => $company->name,
          ] : null,
          'total_income' => round($income, 2),
          'total_expenses' => round($expenses, 2),
          'balance' => round($income - $expenses, 2),
          'transaction_count' => $count,
        ];
      })
      ->values()
      ->toArray();

    return $companyData;
  }

  /**
   * Apply permission filters to a query (reused logic)
   */
  private function applyPermissionFiltersToQuery(Builder $query, User $currentUser): void
  {
    if (!$currentUser->level) {
      throw new \Exception('Usuário sem nível definido');
    }

    $userLevel = $currentUser->level->slug;

    switch ($userLevel) {
      case 'adminMaster':
        // Admin Master pode ver todas as transações
        break;

      case 'companyAdmin':
      case 'companyUser':
        // Company Admin e Company User podem ver transações das suas empresas
        // e transações sem company_id apenas se user_id for o próprio usuário
        $companyIds = $this->getUserCompanyIds($currentUser);
        if (!empty($companyIds)) {
          $query->where(function ($q) use ($companyIds, $currentUser) {
            $q->whereIn('company_id', $companyIds)
              ->orWhere(function ($subQ) use ($currentUser) {
                $subQ->whereNull('company_id')
                  ->where('user_id', $currentUser->id);
              });
          });
        } else {
          // Se não tem empresas, só pode ver suas próprias transações sem company_id
          $query->whereNull('company_id')
            ->where('user_id', $currentUser->id);
        }
        break;

      case 'user':
        // User comum só pode ver suas próprias transações
        $query->where('user_id', $currentUser->id);
        break;

      default:
        throw new \Exception('Nível de usuário não reconhecido');
    }
  }

  /**
   * Get the company IDs for a user (companyAdmin or companyUser)
   */
  private function getUserCompanyIds(User $user): array
  {
    // Busca todas as empresas do usuário através da tabela pivot company_user
    $companyUsers = \Illuminate\Support\Facades\DB::table('company_user')
      ->where('user_id', $user->id)
      ->pluck('company_id')
      ->toArray();

    return $companyUsers;
  }
}
