<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Users\Controllers\ListUsersController;
use App\Modules\Users\Controllers\ShowUserController;
use App\Modules\Users\Controllers\CreateUserController;
use App\Modules\Users\Controllers\UpdateUserController;
use App\Modules\Users\Controllers\DeleteUserController;

Route::prefix('users')->group(function () {
  // Rotas para todos os usuários autenticados
  Route::middleware(['role:adminMaster'])->group(function () {  
    Route::get('/', ListUsersController::class)->name('users.index');
  });
  

  // Rotas que precisam de permissão de admin (user_level_id <= 2)
  Route::middleware(['role:companyAdmin'])->group(function () {
    Route::post('/', CreateUserController::class)->name('users.store');
    Route::put('/{user}', UpdateUserController::class)->name('users.update');
    Route::delete('/{user}', DeleteUserController::class)->name('users.destroy');
  });

  Route::get('/{user}', ShowUserController::class)->name('users.show');
});
