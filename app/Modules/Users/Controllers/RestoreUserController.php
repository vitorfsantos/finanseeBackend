<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Models\User;
use App\Modules\Users\Services\RestoreUserService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Patch(
 *     path="/api/users/{user}/restore",
 *     operationId="restoreUser",
 *     tags={"Usuários"},
 *     summary="Restaurar usuário",
 *     description="Restaura um usuário que foi excluído (soft delete). Requer permissão de admin da empresa.",
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
 *         description="Usuário restaurado com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Usuário restaurado com sucesso")
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
 *         description="Usuário não foi excluído ou já foi restaurado",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User is not deleted or already restored.")
 *         )
 *     )
 * )
 */

class RestoreUserController extends Controller
{
  protected RestoreUserService $restoreUserService;

  public function __construct(RestoreUserService $restoreUserService)
  {
    $this->restoreUserService = $restoreUserService;
  }

  /**
   * Restore the specified user
   */
  public function __invoke(User $user): JsonResponse
  {
    // Check if user is actually deleted
    if (!$user->trashed()) {
      return response()->json([
        'message' => 'Usuário não foi excluído ou já foi restaurado'
      ], 422);
    }

    $this->restoreUserService->restore($user);

    return response()->json([
      'message' => 'Usuário restaurado com sucesso'
    ], 200);
  }
}
