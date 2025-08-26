<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Models\User;
use App\Modules\Users\Requests\UpdateUserRequest;
use App\Modules\Users\Services\UpdateUserService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Put(
 *     path="/api/users/{user}",
 *     operationId="updateUser",
 *     tags={"Usuários"},
 *     summary="Atualizar usuário",
 *     description="Atualiza os dados de um usuário específico. Requer permissão de admin da empresa.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="user",
 *         in="path",
 *         description="ID do usuário",
 *         required=true,
 *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", maxLength=255, example="João Silva Santos", description="Nome completo do usuário"),
 *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="joao.silva@exemplo.com", description="Email do usuário"),
 *             @OA\Property(property="password", type="string", minLength=6, maxLength=255, example="123456", description="Nova senha do usuário (mínimo 6 caracteres)"),
 *             @OA\Property(property="phone", type="string", example="(11) 88888-8888", description="Telefone do usuário"),
 *             @OA\Property(property="user_level_id", type="integer", example=3, description="ID do nível do usuário")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Usuário atualizado com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Usuário atualizado com sucesso"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
 *                 @OA\Property(property="name", type="string", example="João Silva Santos"),
 *                 @OA\Property(property="email", type="string", example="joao.silva@exemplo.com"),
 *                 @OA\Property(property="phone", type="string", example="(11) 88888-8888"),
 *                 @OA\Property(property="user_level_id", type="integer", example=3),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00.000000Z")
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
 *         description="Acesso negado - Requer permissão de admin da empresa",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Access denied.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Usuário não encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User not found.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Dados de entrada inválidos",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(property="errors", type="object",
 *                 @OA\Property(property="name", type="array", @OA\Items(type="string", example="O nome deve ser uma string.")),
 *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="O email deve ser um endereço válido.")),
 *                 @OA\Property(property="password", type="array", @OA\Items(type="string", example="A senha deve ter pelo menos 6 caracteres."))
 *             )
 *         )
 *     )
 * )
 */

class UpdateUserController extends Controller
{
  protected UpdateUserService $updateUserService;

  public function __construct(UpdateUserService $updateUserService)
  {
    $this->updateUserService = $updateUserService;
  }

  /**
   * Update the specified user
   */
  public function __invoke(UpdateUserRequest $request, User $user): JsonResponse
  {
    $updatedUser = $this->updateUserService->execute($user, $request->validated());

    return response()->json([
      'message' => 'Usuário atualizado com sucesso',
      'data' => $updatedUser
    ], 200);
  }
}
