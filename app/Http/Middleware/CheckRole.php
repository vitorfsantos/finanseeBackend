<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next, string $role): Response
  {
    $user = $request->user();

    if (!$user) {
      return response()->json(['message' => 'Não autorizado'], 401);
    }

    // Verifica se o usuário tem o role necessário
    // Aqui você pode implementar sua lógica de verificação de role
    // Por exemplo, verificar na tabela user_levels ou roles
    if (!$this->userHasRole($user, $role)) {
      return response()->json(['message' => 'Permissão insuficiente'], 403);
    }

    return $next($request);
  }

  /**
   * Verifica se o usuário tem o role especificado
   */
  private function userHasRole($user, string $role): bool
  {
    // Verifica se o usuário tem um nível definido
    if (!$user->level) {
      return false;
    }

    // Verifica baseado no ID do nível (quanto menor o ID, maior a permissão)
    switch ($role) {
      case 'adminMaster':
        // Admin Master: user_level_id = 1 (Admin Master)
        return $user->user_level_id == 1;
      case 'companyAdmin':
        // Company Admin: user_level_id = 2 (Company Admin)
        return $user->user_level_id <= 2;
      case 'companyUser':
        // Company User: user_level_id >= 3 (Company User)
        return $user->user_level_id <= 3;
      case 'user':
        // User: user_level_id >= 4 (User)
        return $user->user_level_id <= 4;

      default:
        return false;
    }
  }
}
