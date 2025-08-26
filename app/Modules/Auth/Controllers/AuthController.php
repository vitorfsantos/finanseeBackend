<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Post(
 *     path="/api/auth/login",
 *     operationId="login",
 *     tags={"Autenticação"},
 *     summary="Autenticar usuário",
 *     description="Autentica um usuário e retorna um token de acesso",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email","password"},
 *             @OA\Property(property="email", type="string", format="email", example="usuario@exemplo.com", description="Email do usuário"),
 *             @OA\Property(property="password", type="string", format="password", example="123456", description="Senha do usuário")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login realizado com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Login successful"),
 *             @OA\Property(property="user", type="object",
 *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
 *                 @OA\Property(property="name", type="string", example="João Silva"),
 *                 @OA\Property(property="email", type="string", example="joao@exemplo.com"),
 *                 @OA\Property(property="phone", type="string", example="(11) 99999-9999"),
 *                 @OA\Property(property="user_level_id", type="integer", example=1)
 *             ),
 *             @OA\Property(property="token", type="string", example="1|abc123def456...")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Credenciais inválidas",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Credenciais inválidas")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Dados de entrada inválidos",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(property="errors", type="object",
 *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="O email é obrigatório.")),
 *                 @OA\Property(property="password", type="array", @OA\Items(type="string", example="A senha é obrigatória."))
 *             )
 *         )
 *     )
 * )
 * 
 * @OA\Post(
 *     path="/api/auth/logout",
 *     operationId="logout",
 *     tags={"Autenticação"},
 *     summary="Fazer logout",
 *     description="Faz logout do usuário e revoga o token de acesso",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Logout realizado com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Logout successful")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Não autorizado",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */

class AuthController extends Controller
{
  protected AuthService $authService;

  public function __construct(AuthService $authService)
  {
    $this->authService = $authService;
  }

  /**
   * Authenticate user and return token
   */
  public function login(LoginRequest $request): JsonResponse
  {
    $credentials = $request->validated();
    $result = $this->authService->login($credentials);

    if (!$result['success']) {
      return response()->json([
        'message' => $result['message']
      ], 401);
    }

    return response()->json([
      'message' => 'Login successful',
      'user' => $result['user'],
      'token' => $result['token']
    ], 200);
  }

  /**
   * Logout user and revoke token
   */
  public function logout(Request $request): JsonResponse
  {
    $result = $this->authService->logout($request->user());

    return response()->json([
      'message' => 'Logout successful'
    ], 200);
  }
}
