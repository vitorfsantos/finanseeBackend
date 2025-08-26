<?php

namespace App\Modules\Companies\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Requests\UpdateCompanyRequest;
use App\Modules\Companies\Services\UpdateCompanyService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Put(
 *     path="/api/companies/{company}",
 *     operationId="updateCompany",
 *     tags={"Empresas"},
 *     summary="Atualizar empresa",
 *     description="Atualiza os dados de uma empresa. Requer permissão de admin master.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="company",
 *         in="path",
 *         description="ID da empresa",
 *         required=true,
 *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", maxLength=255, example="Empresa Atualizada LTDA", description="Nome da empresa"),
 *             @OA\Property(property="cnpj", type="string", maxLength=18, example="12.345.678/0001-90", description="CNPJ da empresa"),
 *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="novo@empresa.com", description="Email da empresa"),
 *             @OA\Property(property="phone", type="string", maxLength=20, example="(11) 4444-5555", description="Telefone da empresa")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Empresa atualizada com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Empresa atualizada com sucesso"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
 *                 @OA\Property(property="name", type="string", example="Empresa Atualizada LTDA"),
 *                 @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90"),
 *                 @OA\Property(property="email", type="string", example="novo@empresa.com"),
 *                 @OA\Property(property="phone", type="string", example="(11) 4444-5555"),
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
 *         response=404,
 *         description="Empresa não encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Empresa não encontrada.")
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

class UpdateCompanyController extends Controller
{
  protected UpdateCompanyService $updateCompanyService;

  public function __construct(UpdateCompanyService $updateCompanyService)
  {
    $this->updateCompanyService = $updateCompanyService;
  }

  /**
   * Update the specified company
   */
  public function __invoke(UpdateCompanyRequest $request, Company $company): JsonResponse
  {
    $updatedCompany = $this->updateCompanyService->update($company, $request->validated());

    return response()->json([
      'message' => 'Empresa atualizada com sucesso',
      'data' => $updatedCompany
    ]);
  }
}
