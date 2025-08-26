<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="Modelo de usuário",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="name", type="string", example="João Silva"),
 *     @OA\Property(property="email", type="string", format="email", example="joao@exemplo.com"),
 *     @OA\Property(property="phone", type="string", example="(11) 99999-9999"),
 *     @OA\Property(property="user_level_id", type="integer", example=2),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
 * )
 * 
 * @OA\Schema(
 *     schema="UserCreate",
 *     title="User Create",
 *     description="Dados para criação de usuário",
 *     required={"name", "email", "password"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="João Silva", description="Nome completo do usuário"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, example="joao@exemplo.com", description="Email único do usuário"),
 *     @OA\Property(property="password", type="string", minLength=6, maxLength=255, example="123456", description="Senha do usuário (mínimo 6 caracteres)"),
 *     @OA\Property(property="phone", type="string", example="(11) 99999-9999", description="Telefone do usuário (opcional)"),
 *     @OA\Property(property="user_level_id", type="integer", example=2, description="ID do nível do usuário (opcional)")
 * )
 * 
 * @OA\Schema(
 *     schema="UserUpdate",
 *     title="User Update",
 *     description="Dados para atualização de usuário",
 *     @OA\Property(property="name", type="string", maxLength=255, example="João Silva Santos", description="Nome completo do usuário"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, example="joao.silva@exemplo.com", description="Email do usuário"),
 *     @OA\Property(property="password", type="string", minLength=6, maxLength=255, example="123456", description="Nova senha do usuário (mínimo 6 caracteres)"),
 *     @OA\Property(property="phone", type="string", example="(11) 88888-8888", description="Telefone do usuário"),
 *     @OA\Property(property="user_level_id", type="integer", example=3, description="ID do nível do usuário")
 * )
 * 
 * @OA\Schema(
 *     schema="LoginRequest",
 *     title="Login Request",
 *     description="Dados para autenticação",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email", example="usuario@exemplo.com", description="Email do usuário"),
 *     @OA\Property(property="password", type="string", format="password", example="123456", description="Senha do usuário")
 * )
 * 
 * @OA\Schema(
 *     schema="LoginResponse",
 *     title="Login Response",
 *     description="Resposta do login",
 *     @OA\Property(property="message", type="string", example="Login successful"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="token", type="string", example="1|abc123def456...")
 * )
 * 
 * @OA\Schema(
 *     schema="PaginatedResponse",
 *     title="Paginated Response",
 *     description="Resposta paginada",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
 *     @OA\Property(property="meta", type="object",
 *         @OA\Property(property="total", type="integer", example=100),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=7)
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     title="Error Response",
 *     description="Resposta de erro",
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(property="errors", type="object",
 *         @OA\Property(property="field_name", type="array", @OA\Items(type="string", example="Error message"))
 *     )
 *     )
 * )
 */
class SwaggerSchemas
{
  // Esta classe serve apenas para definir schemas reutilizáveis
  // Não contém lógica de negócio
}
