<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Users\Controllers\ListUsersController;
use App\Modules\Users\Controllers\ShowUserController;
use App\Modules\Users\Controllers\CreateUserController;
use App\Modules\Users\Controllers\UpdateUserController;
use App\Modules\Users\Controllers\DeleteUserController;
use App\Modules\Users\Controllers\RestoreUserController;

Route::prefix('users')->group(function () {
  Route::middleware(['role:companyAdmin'])->group(function () {
    Route::get('/', ListUsersController::class)->name('users.index');
    Route::post('/', CreateUserController::class)->name('users.store');
    Route::put('/{user}', UpdateUserController::class)->name('users.update');
    Route::delete('/{user}', DeleteUserController::class)->name('users.destroy');
    Route::patch('/{user}/restore', RestoreUserController::class)->name('users.restore');
    Route::get('/levels', [ListUsersController::class, 'getLevels'])->name('users.levels');
  });

  Route::get('/{user}', ShowUserController::class)->name('users.show');
});
