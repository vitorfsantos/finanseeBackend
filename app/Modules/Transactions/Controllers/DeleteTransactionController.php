<?php

namespace App\Modules\Transactions\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\Services\DeleteTransactionService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Delete(
 *     path="/api/transactions/{transaction}",
 *     operationId="deleteTransaction",
 *     tags={"Transações"},
 *     summary="Excluir transação",
 *     description="Exclui uma transação financeira. As permissões variam conforme o nível do usuário.",
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
 *         description="Transação excluída com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Transação excluída com sucesso")
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
class DeleteTransactionController extends Controller
{
  protected DeleteTransactionService $deleteTransactionService;

  public function __construct(DeleteTransactionService $deleteTransactionService)
  {
    $this->deleteTransactionService = $deleteTransactionService;
  }

  /**
   * Remove the specified transaction
   */
  public function __invoke(Transaction $transaction): JsonResponse
  {
    $this->deleteTransactionService->delete($transaction, null);

    return response()->json([
      'message' => 'Transação excluída com sucesso'
    ]);
  }
}
