<?php

namespace App\Modules\Companies\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Services\DeleteCompanyService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Delete(
 *     path="/api/companies/{company}",
 *     operationId="deleteCompany",
 *     tags={"Empresas"},
 *     summary="Excluir empresa",
 *     description="Exclui uma empresa. Requer permissão de admin master.",
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
 *         description="Empresa excluída com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Empresa excluída com sucesso")
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
 *     )
 * )
 */

class DeleteCompanyController extends Controller
{
  protected DeleteCompanyService $deleteCompanyService;

  public function __construct(DeleteCompanyService $deleteCompanyService)
  {
    $this->deleteCompanyService = $deleteCompanyService;
  }

  /**
   * Remove the specified company
   */
  public function __invoke(Company $company): JsonResponse
  {
    $this->deleteCompanyService->delete($company);

    return response()->json([
      'message' => 'Empresa excluída com sucesso'
    ]);
  }
}
