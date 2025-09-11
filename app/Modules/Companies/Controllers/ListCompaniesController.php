<?php

namespace App\Modules\Companies\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Companies\Services\ListCompaniesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Get(
 *     path="/api/companies",
 *     operationId="listCompanies",
 *     tags={"Empresas"},
 *     summary="Listar empresas",
 *     description="Retorna uma lista paginada de empresas. Requer permissão de admin master.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Número da página (opcional, padrão: 1)",
 *         required=false,
 *         @OA\Schema(type="integer", default=1)
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="Quantidade de itens por página (opcional, padrão: 15)",
 *         required=false,
 *         @OA\Schema(type="integer", default=15)
 *     ),
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         description="Termo de busca para filtrar empresas por nome ou CNPJ (opcional)",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista de empresas retornada com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items(
 *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
 *                 @OA\Property(property="name", type="string", example="Empresa Exemplo LTDA"),
 *                 @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90"),
 *                 @OA\Property(property="email", type="string", example="contato@empresa.com"),
 *                 @OA\Property(property="phone", type="string", example="(11) 3333-4444"),
 *                 @OA\Property(property="address", type="object", nullable=true,
 *                     @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
 *                     @OA\Property(property="street", type="string", example="Rua das Flores"),
 *                     @OA\Property(property="number", type="string", example="123"),
 *                     @OA\Property(property="complement", type="string", example="Sala 45"),
 *                     @OA\Property(property="neighborhood", type="string", example="Centro"),
 *                     @OA\Property(property="city", type="string", example="São Paulo"),
 *                     @OA\Property(property="state", type="string", example="SP"),
 *                     @OA\Property(property="zipcode", type="string", example="01234-567"),
 *                     @OA\Property(property="country", type="string", example="Brasil")
 *                 ),
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

class ListCompaniesController extends Controller
{
  protected ListCompaniesService $listCompaniesService;

  public function __construct(ListCompaniesService $listCompaniesService)
  {
    $this->listCompaniesService = $listCompaniesService;
  }

  /**
   * Display a listing of companies
   */
  public function __invoke(Request $request): JsonResponse
  {
    $perPage = $request->get('per_page', 15);
    $search = $request->get('search');
    $user = $request->user();

    if ($search) {
      $companies = $this->listCompaniesService->search($search, $perPage, $user);
    } else {
      $companies = $this->listCompaniesService->getPaginated($perPage, $user);
    }

    return response()->json([
      'data' => $companies->items(),
      'meta' => [
        'total' => $companies->total(),
        'per_page' => $companies->perPage(),
        'current_page' => $companies->currentPage(),
        'last_page' => $companies->lastPage(),
      ]
    ]);
  }
}
