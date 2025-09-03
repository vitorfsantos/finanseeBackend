<?php

namespace App\Modules\Transactions\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\Services\ShowTransactionService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Get(
 *     path="/api/transactions/{transaction}",
 *     operationId="showTransaction",
 *     tags={"Transações"},
 *     summary="Exibir transação",
 *     description="Exibe uma transação financeira específica. As permissões variam conforme o nível do usuário.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="transaction",
 *         in="path",
 *         description="ID da transação",
 *         required=true,
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Transação encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="string", format="uuid"),
 *                 @OA\Property(property="user_id", type="string", format="uuid"),
 *                 @OA\Property(property="company_id", type="string", format="uuid", nullable=true),
 *                 @OA\Property(property="type", type="string"),
 *                 @OA\Property(property="category", type="string"),
 *                 @OA\Property(property="description", type="string"),
 *                 @OA\Property(property="amount", type="number", format="float"),
 *                 @OA\Property(property="date", type="string", format="date"),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time"),
 *                 @OA\Property(property="user", type="object"),
 *                 @OA\Property(property="company", type="object", nullable=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Não autorizado"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Acesso negado"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Transação não encontrada"
 *     )
 * )
 */
class ShowTransactionController extends Controller
{
  protected ShowTransactionService $showTransactionService;

  public function __construct(ShowTransactionService $showTransactionService)
  {
    $this->showTransactionService = $showTransactionService;
  }

  /**
   * Display the specified transaction
   */
  public function __invoke(Transaction $transaction): JsonResponse
  {
    $transaction = $this->showTransactionService->show($transaction, null);

    return response()->json([
      'data' => $transaction
    ]);
  }
}
