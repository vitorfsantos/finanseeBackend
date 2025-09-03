<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ========================================
// ROTAS PÚBLICAS (sem autenticação)
// ========================================

// Login público
Route::post('/auth/login', [\App\Modules\Auth\Controllers\AuthController::class, 'login'])->name('auth.login');

// ========================================
// ROTAS AUTENTICADAS (com auth:sanctum)
// ========================================

Route::middleware(['auth:sanctum'])->group(function () {

  // Rota para obter usuário atual
  Route::get('/user', function (Request $request) {
    return $request->user();
  });

  // Auth module - rotas autenticadas (logout)
  Route::group([], base_path('app/Modules/Auth/routes.php'));

  // Users module - rotas autenticadas
  Route::group([], base_path('app/Modules/Users/routes.php'));

  // ========================================
  // OUTROS MÓDULOS AUTENTICADOS
  // ========================================

  // Companies module
  Route::group([], base_path('app/Modules/Companies/routes.php'));

  // Transactions module
  Route::group([], base_path('app/Modules/Transactions/routes.php'));

  // Reports module
  // Route::group([], base_path('app/Modules/Reports/routes.php'));
});
