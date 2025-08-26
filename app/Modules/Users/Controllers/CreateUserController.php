<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Models\User;
use App\Modules\Users\Requests\CreateUserRequest;
use App\Modules\Users\Services\CreateUserService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Post(
 *     path="/api/users",
 *     operationId="createUser",
 *     tags={"Usuários"},
 *     summary="Criar usuário",
 *     description="Cria um novo usuário. Requer permissão de admin da empresa.",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","email","password"},
 *             @OA\Property(property="name", type="string", maxLength=255, example="João Silva", description="Nome completo do usuário"),
 *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="joao@exemplo.com", description="Email único do usuário"),
 *             @OA\Property(property="password", type="string", minLength=6, maxLength=255, example="123456", description="Senha do usuário (mínimo 6 caracteres)"),
 *             @OA\Property(property="phone", type="string", example="(11) 99999-9999", description="Telefone do usuário (opcional)"),
 *             @OA\Property(property="user_level_id", type="integer", example=2, description="ID do nível do usuário (opcional)")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Usuário criado com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Usuário criado com sucesso"),
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
 *         response=422,
 *         description="Dados de entrada inválidos",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(property="errors", type="object",
 *                 @OA\Property(property="name", type="array", @OA\Items(type="string", example="O nome é obrigatório.")),
 *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="Este email já está em uso.")),
 *                 @OA\Property(property="password", type="array", @OA\Items(type="string", example="A senha deve ter pelo menos 6 caracteres."))
 *             )
 *         )
 *     )
 * )
 */

class CreateUserController extends Controller
{
  protected CreateUserService $createUserService;

  public function __construct(CreateUserService $createUserService)
  {
    $this->createUserService = $createUserService;
  }

  /**
   * Store a newly created user
   */
  public function __invoke(CreateUserRequest $request): JsonResponse
  {
    $user = $this->createUserService->create($request->validated());

    return response()->json([
      'message' => 'Usuário criado com sucesso',
      'data' => $user
    ], 201);
  }
}
