<?php

namespace App\Modules\Transactions\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\Requests\UpdateTransactionRequest;
use App\Modules\Transactions\Services\UpdateTransactionService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Put(
 *     path="/api/transactions/{transaction}",
 *     operationId="updateTransaction",
 *     tags={"Transações"},
 *     summary="Atualizar transação",
 *     description="Atualiza uma transação financeira existente. As permissões variam conforme o nível do usuário.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="transaction",
 *         in="path",
 *         description="ID da transação",
 *         required=true,
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="type", type="string", enum={"income","expense"}, example="expense", description="Tipo da transação (opcional: receita ou despesa)"),
 *             @OA\Property(property="amount", type="number", format="float", example=25.50, description="Valor da transação (opcional, mínimo 0.01)"),
 *             @OA\Property(property="category", type="string", maxLength=255, example="Alimentação", description="Categoria da transação (opcional)"),
 *             @OA\Property(property="description", type="string", maxLength=1000, example="Almoço no restaurante", description="Descrição da transação (opcional)"),
 *             @OA\Property(property="date", type="string", format="date", example="2024-01-15", description="Data da transação (opcional, não pode ser futura)"),
 *             @OA\Property(property="user_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000", description="ID do usuário (opcional, apenas adminMaster)"),
 *             @OA\Property(property="company_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001", description="ID da empresa (opcional, apenas adminMaster)")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Transação atualizada com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Transação atualizada com sucesso"),
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
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
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
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Dados de entrada inválidos"
 *     )
 * )
 */
class UpdateTransactionController extends Controller
{
  protected UpdateTransactionService $updateTransactionService;

  public function __construct(UpdateTransactionService $updateTransactionService)
  {
    $this->updateTransactionService = $updateTransactionService;
  }

  /**
   * Update the specified transaction
   */
  public function __invoke(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
  {
    $transaction = $this->updateTransactionService->update($transaction, $request->validated(), null);

    return response()->json([
      'message' => 'Transação atualizada com sucesso',
      'data' => $transaction
    ]);
  }
}
