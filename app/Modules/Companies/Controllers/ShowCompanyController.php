<?php

namespace App\Modules\Companies\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Companies\Models\Company;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Get(
 *     path="/api/companies/{company}",
 *     operationId="showCompany",
 *     tags={"Empresas"},
 *     summary="Exibir empresa",
 *     description="Retorna os dados de uma empresa específica.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="company",
 *         in="path",
 *         description="ID da empresa",
 *         required=true,
 *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Empresa retornada com sucesso",
 *         @OA\JsonContent(
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
 *         response=404,
 *         description="Empresa não encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Empresa não encontrada.")
 *         )
 *     )
 * )
 */

class ShowCompanyController extends Controller
{
  /**
   * Display the specified company
   */
  public function __invoke(Company $company): JsonResponse
  {
    return response()->json([
      'data' => $company
    ]);
  }
}
