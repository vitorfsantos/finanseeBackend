<?php

namespace App\Modules\Companies\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Requests\CreateCompanyRequest;
use App\Modules\Companies\Services\CreateCompanyService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Post(
 *     path="/api/companies",
 *     operationId="createCompany",
 *     tags={"Empresas"},
 *     summary="Criar empresa",
 *     description="Cria uma nova empresa. Requer permissão de admin master.",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","cnpj"},
 *             @OA\Property(property="name", type="string", maxLength=255, example="Empresa Exemplo LTDA", description="Nome da empresa"),
 *             @OA\Property(property="cnpj", type="string", maxLength=18, example="12.345.678/0001-90", description="CNPJ único da empresa"),
 *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="contato@empresa.com", description="Email da empresa (opcional)"),
 *             @OA\Property(property="phone", type="string", maxLength=20, example="(11) 3333-4444", description="Telefone da empresa (opcional)")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Empresa criada com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Empresa criada com sucesso"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
 *                 @OA\Property(property="name", type="string", example="Empresa Exemplo LTDA"),
 *                 @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90"),
 *                 @OA\Property(property="email", type="string", example="contato@empresa.com"),
 *                 @OA\Property(property="phone", type="string", example="(11) 3333-4444"),
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
 *         description="Acesso negado - Requer permissão de admin master",
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
 *                 @OA\Property(property="name", type="array", @OA\Items(type="string", example="O nome da empresa é obrigatório.")),
 *                 @OA\Property(property="cnpj", type="array", @OA\Items(type="string", example="Este CNPJ já está em uso."))
 *             )
 *         )
 *     )
 * )
 */

class CreateCompanyController extends Controller
{
  protected CreateCompanyService $createCompanyService;

  public function __construct(CreateCompanyService $createCompanyService)
  {
    $this->createCompanyService = $createCompanyService;
  }

  /**
   * Store a newly created company
   */
  public function __invoke(CreateCompanyRequest $request): JsonResponse
  {
    $company = $this->createCompanyService->create($request->validated());

    return response()->json([
      'message' => 'Empresa criada com sucesso',
      'data' => $company
    ], 201);
  }
}
