<?php

namespace App\Modules\Transactions\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\Requests\CreateTransactionRequest;
use App\Modules\Transactions\Services\CreateTransactionService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Post(
 *     path="/api/transactions",
 *     operationId="createTransaction",
 *     tags={"Transações"},
 *     summary="Criar transação",
 *     description="Cria uma nova transação financeira. As permissões variam conforme o nível do usuário: adminMaster pode criar para qualquer usuário/empresa, companyAdmin/companyUser para sua empresa, user para si mesmo.",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"type","amount","date"},
 *             @OA\Property(property="type", type="string", enum={"income","expense"}, example="expense", description="Tipo da transação (receita ou despesa)"),
 *             @OA\Property(property="category", type="string", maxLength=255, example="Alimentação", description="Categoria da transação (opcional)"),
 *             @OA\Property(property="description", type="string", maxLength=1000, example="Almoço no restaurante", description="Descrição da transação (opcional)"),
 *             @OA\Property(property="amount", type="number", format="float", example=25.50, description="Valor da transação (mínimo 0.01)"),
 *             @OA\Property(property="date", type="string", format="date", example="2024-01-15", description="Data da transação (não pode ser futura)"),
 *             @OA\Property(property="user_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000", description="ID do usuário (opcional, definido automaticamente conforme permissões)"),
 *             @OA\Property(property="company_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001", description="ID da empresa (opcional, definido automaticamente conforme permissões)")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Transação criada com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Transação criada com sucesso"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440002"),
 *                 @OA\Property(property="user_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *                 @OA\Property(property="company_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440001"),
 *                 @OA\Property(property="type", type="string", example="expense"),
 *                 @OA\Property(property="category", type="string", example="Alimentação"),
 *                 @OA\Property(property="description", type="string", example="Almoço no restaurante"),
 *                 @OA\Property(property="amount", type="number", format="float", example=25.50),
 *                 @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T12:00:00.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T12:00:00.000000Z")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Não autorizado",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Acesso negado",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Access denied.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Dados de entrada inválidos",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Dados de validação inválidos."),
 *             @OA\Property(property="errors", type="object",
 *                 @OA\Property(property="type", type="array", @OA\Items(type="string", example="O tipo de transação é obrigatório.")),
 *                 @OA\Property(property="amount", type="array", @OA\Items(type="string", example="O valor deve ser maior que zero.")),
 *                 @OA\Property(property="date", type="array", @OA\Items(type="string", example="A data não pode ser futura."))
 *             )
 *         )
 *     )
 * )
 */
class CreateTransactionController extends Controller
{
  protected CreateTransactionService $createTransactionService;

  public function __construct(CreateTransactionService $createTransactionService)
  {
    $this->createTransactionService = $createTransactionService;
  }

  /**
   * Store a newly created transaction
   */
  public function __invoke(CreateTransactionRequest $request): JsonResponse
  {
    $transaction = $this->createTransactionService->create($request->validated());

    return response()->json([
      'message' => 'Transação criada com sucesso',
      'data' => $transaction
    ], 201);
  }
}
