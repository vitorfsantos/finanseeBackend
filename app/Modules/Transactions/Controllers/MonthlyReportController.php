<?php

namespace App\Modules\Transactions\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transactions\Services\MonthlyReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MonthlyReportController extends Controller
{
  public function __construct(
    private MonthlyReportService $monthlyReportService
  ) {}

  /**
   * Get monthly report for the authenticated user
   */
  public function index(Request $request): JsonResponse
  {
    try {
      $user = $request->user();

      if (!$user) {
        return response()->json([
          'message' => 'Usuário não autenticado'
        ], 401);
      }

      // Valida parâmetros opcionais
      $year = $request->query('year');
      $month = $request->query('month');

      if ($year && (!is_numeric($year) || $year < 2000 || $year > 2100)) {
        return response()->json([
          'message' => 'Ano inválido. Deve ser um número entre 2000 e 2100.'
        ], 400);
      }

      if ($month && (!is_numeric($month) || $month < 1 || $month > 12)) {
        return response()->json([
          'message' => 'Mês inválido. Deve ser um número entre 1 e 12.'
        ], 400);
      }

      $report = $this->monthlyReportService->generateReport(
        $user,
        $year ? (int) $year : null,
        $month ? (int) $month : null
      );

      return response()->json([
        'success' => true,
        'data' => $report
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Erro ao gerar relatório mensal: ' . $e->getMessage()
      ], 500);
    }
  }
}
