<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="FinanSee API",
 *     description="API para gerenciamento financeiro e de usuários",
 *     @OA\Contact(
 *         email="contato@finansee.com",
 *         name="Suporte FinanSee"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor de Desenvolvimento"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Tag(
 *     name="Autenticação",
 *     description="Endpoints para autenticação de usuários"
 * )
 * 
 * @OA\Tag(
 *     name="Usuários",
 *     description="Endpoints para gerenciamento de usuários"
 * )
 */
class SwaggerController extends Controller
{
  //
}
