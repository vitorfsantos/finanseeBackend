<?php

namespace App\Modules\Transactions\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transactions\Services\ListTransactionsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Get(
 *     path="/api/transactions",
 *     operationId="listTransactions",
 *     tags={"Transações"},
 *     summary="Listar transações",
 *     description="Lista transações financeiras com filtros. As permissões variam conforme o nível do usuário.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="type",
 *         in="query",
 *         description="Filtrar por tipo (opcional: income ou expense)",
 *         required=false,
 *         @OA\Schema(type="string", enum={"income", "expense"})
 *     ),
 *     @OA\Parameter(
 *         name="user_id",
 *         in="query",
 *         description="Filtrar por usuário (opcional, apenas adminMaster)",
 *         required=false,
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\Parameter(
 *         name="company_id",
 *         in="query",
 *         description="Filtrar por empresa (opcional, apenas adminMaster)",
 *         required=false,
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\Parameter(
 *         name="category",
 *         in="query",
 *         description="Filtrar por categoria (opcional)",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="start_date",
 *         in="query",
 *         description="Data inicial (opcional, formato: YYYY-MM-DD)",
 *         required=false,
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="end_date",
 *         in="query",
 *         description="Data final (opcional, formato: YYYY-MM-DD)",
 *         required=false,
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="min_amount",
 *         in="query",
 *         description="Valor mínimo (opcional)",
 *         required=false,
 *         @OA\Schema(type="number", format="float")
 *     ),
 *     @OA\Parameter(
 *         name="max_amount",
 *         in="query",
 *         description="Valor máximo (opcional)",
 *         required=false,
 *         @OA\Schema(type="number", format="float")
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="Itens por página (opcional, padrão: 15)",
 *         required=false,
 *         @OA\Schema(type="integer", default=15)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista de transações",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items(
 *                 @OA\Property(property="id", type="string", format="uuid"),
 *                 @OA\Property(property="user_id", type="string", format="uuid"),
 *                 @OA\Property(property="company_id", type="string", format="uuid", nullable=true),
 *                 @OA\Property(property="type", type="string"),
 *                 @OA\Property(property="category", type="string"),
 *                 @OA\Property(property="description", type="string"),
 *                 @OA\Property(property="amount", type="number", format="float"),
 *                 @OA\Property(property="date", type="string", format="date"),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )),
 *             @OA\Property(property="current_page", type="integer"),
 *             @OA\Property(property="last_page", type="integer"),
 *             @OA\Property(property="per_page", type="integer"),
 *             @OA\Property(property="total", type="integer")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Não autorizado"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Acesso negado"
 *     )
 * )
 */
class ListTransactionsController extends Controller
{
  protected ListTransactionsService $listTransactionsService;

  public function __construct(ListTransactionsService $listTransactionsService)
  {
    $this->listTransactionsService = $listTransactionsService;
  }

  /**
   * Display a listing of transactions
   */
  public function __invoke(Request $request): JsonResponse
  {
    $filters = $request->all();
    $transactions = $this->listTransactionsService->list(null, $filters);

    return response()->json($transactions);
  }
}
