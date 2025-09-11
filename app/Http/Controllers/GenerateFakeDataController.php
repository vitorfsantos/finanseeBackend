<?php

namespace App\Http\Controllers;

use App\Services\GenerateFakeDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GenerateFakeDataController extends Controller
{
  public function __construct(
    private GenerateFakeDataService $generateFakeDataService
  ) {}

  /**
   * Generate fake data for testing purposes
   *
   * @OA\Post(
   *     path="/api/generate-fake-data",
   *     operationId="generateFakeData",
   *     tags={"Desenvolvimento"},
   *     summary="Gerar dados fake para testes",
   *     description="Gera dados fake para testes do sistema. Retorna credenciais de login para acessar o sistema.",
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"type"},
   *             @OA\Property(
   *                 property="type",
   *                 type="string",
   *                 enum={"user", "companyAdmin"},
   *                 example="user",
   *                 description="Tipo de dados a serem gerados: 'user' para usuário individual ou 'companyAdmin' para empresa com múltiplos usuários"
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Dados fake gerados com sucesso",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string", example="Dados fake gerados com sucesso"),
   *             @OA\Property(
   *                 property="data",
   *                 type="object",
   *                 @OA\Property(property="email", type="string", example="user@example.com"),
   *                 @OA\Property(property="password", type="string", example="password123"),
   *                 @OA\Property(property="user_type", type="string", example="user"),
   *                 @OA\Property(property="generated_data", type="object")
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response=422,
   *         description="Dados de entrada inválidos",
   *         @OA\JsonContent(
   *             @OA\Property(property="message", type="string", example="The given data was invalid."),
   *             @OA\Property(property="errors", type="object")
   *         )
   *     )
   * )
   */
  public function generate(Request $request): JsonResponse
  {
    $request->validate([
      'type' => ['required', 'string', Rule::in(['user', 'companyAdmin'])]
    ]);

    try {
      $result = $this->generateFakeDataService->generate($request->input('type'));

      return response()->json([
        'success' => true,
        'message' => 'Dados fake gerados com sucesso',
        'data' => $result
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Erro ao gerar dados fake: ' . $e->getMessage()
      ], 500);
    }
  }
}
