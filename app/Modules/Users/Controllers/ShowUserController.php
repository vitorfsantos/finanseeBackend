<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Get(
 *     path="/api/users/{user}",
 *     operationId="showUser",
 *     tags={"Usuários"},
 *     summary="Exibir usuário",
 *     description="Retorna os dados de um usuário específico",
 *     @OA\Parameter(
 *         name="user",
 *         in="path",
 *         description="ID do usuário",
 *         required=true,
 *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Dados do usuário retornados com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
 *                 @OA\Property(property="name", type="string", example="João Silva"),
 *                 @OA\Property(property="email", type="string", example="joao@exemplo.com"),
 *                 @OA\Property(property="phone", type="string", example="(11) 99999-9999"),
 *                 @OA\Property(property="user_level_id", type="integer", example=2),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
 *             )
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

class ShowUserController extends Controller
{
  /**
   * Display the specified user
   */
  public function __invoke(User $user): JsonResponse
  {
    return response()->json([
      'data' => $user
    ], 200);
  }
}
