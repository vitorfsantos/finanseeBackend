<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Services\ListUsersService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Get(
 *     path="/api/users",
 *     operationId="listUsers",
 *     tags={"Usuários"},
 *     summary="Listar usuários",
 *     description="Retorna uma lista paginada de usuários. Requer permissão de admin master.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Número da página",
 *         required=false,
 *         @OA\Schema(type="integer", default=1)
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="Quantidade de itens por página",
 *         required=false,
 *         @OA\Schema(type="integer", default=10)
 *     ),
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         description="Termo de busca para filtrar usuários",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista de usuários retornada com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items(
 *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
 *                 @OA\Property(property="name", type="string", example="João Silva"),
 *                 @OA\Property(property="email", type="string", example="joao@exemplo.com"),
 *                 @OA\Property(property="phone", type="string", example="(11) 99999-9999"),
 *                 @OA\Property(property="user_level_id", type="integer", example=1),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
 *             )),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="total", type="integer", example=100),
 *                 @OA\Property(property="per_page", type="integer", example=15),
 *                 @OA\Property(property="current_page", type="integer", example=1),
 *                 @OA\Property(property="last_page", type="integer", example=7)
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
 *         description="Acesso negado - Requer permissão de admin master",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Access denied.")
 *         )
 *     )
 * )
 */

/**
 * @OA\Get(
 *     path="/api/users/levels",
 *     operationId="getLevels",
 *     tags={"Usuários"},
 *     summary="Obter níveis de usuário",
 *     description="Retorna os níveis de usuário abaixo do nível do usuário logado. Apenas usuários com nível 1 (Admin Master) ou 2 (Company Admin) podem acessar.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Níveis retornados com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items(
 *                 @OA\Property(property="id", type="integer", example=2),
 *                 @OA\Property(property="slug", type="string", example="companyAdmin"),
 *                 @OA\Property(property="name", type="string", example="Administrador da Empresa"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
 *             ))
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Não autorizado",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Não autorizado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Acesso negado - Usuários com nível 3 ou 4 não têm permissão",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Usuários com nível 3 ou 4 não têm permissão para acessar esta funcionalidade.")
 *         )
 *     )
 * )
 */

class ListUsersController extends Controller
{
  protected ListUsersService $listUsersService;

  public function __construct(ListUsersService $listUsersService)
  {
    $this->listUsersService = $listUsersService;
  }

  /**
   * Display a listing of users
   */
  public function __invoke(Request $request)
  {
    $users = $this->listUsersService->getAllUsers($request->all());

    return response()->json([
      'data' => $users->items(),
      'meta' => [
        'total' => $users->total(),
        'per_page' => $users->perPage(),
        'current_page' => $users->currentPage(),
        'last_page' => $users->lastPage(),
      ]
    ], 200);
  }

  /**
   * Get levels below the logged user's level
   */
  public function getLevels(Request $request): JsonResponse
  {
    try {
      $levels = $this->listUsersService->getLevels($request->user());

      return response()->json([
        'data' => $levels
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'message' => $e->getMessage()
      ], 403);
    }
  }
}
