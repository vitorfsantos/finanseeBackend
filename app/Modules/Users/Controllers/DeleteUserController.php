<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Models\User;
use App\Modules\Users\Services\DeleteUserService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Delete(
 *     path="/api/users/{user}",
 *     operationId="deleteUser",
 *     tags={"Usuários"},
 *     summary="Excluir usuário",
 *     description="Exclui um usuário específico. Requer permissão de admin da empresa.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="user",
 *         in="path",
 *         description="ID do usuário",
 *         required=true,
 *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Usuário excluído com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Usuário excluído com sucesso")
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
 *     )
 * )
 */

class DeleteUserController extends Controller
{
  protected DeleteUserService $deleteUserService;

  public function __construct(DeleteUserService $deleteUserService)
  {
    $this->deleteUserService = $deleteUserService;
  }

  /**
   * Remove the specified user
   */
  public function __invoke(User $user): JsonResponse
  {
    $this->deleteUserService->delete($user);

    return response()->json([
      'message' => 'Usuário excluído com sucesso'
    ], 200);
  }
}
