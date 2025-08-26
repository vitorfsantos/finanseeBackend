<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\AuthController;

Route::prefix('auth')->group(function () {
  Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});
