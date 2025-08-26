<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Companies\Controllers\ListCompaniesController;
use App\Modules\Companies\Controllers\ShowCompanyController;
use App\Modules\Companies\Controllers\CreateCompanyController;
use App\Modules\Companies\Controllers\UpdateCompanyController;
use App\Modules\Companies\Controllers\DeleteCompanyController;

Route::prefix('companies')->group(function () {
  Route::middleware(['role:adminMaster'])->group(function () {
    Route::get('/', ListCompaniesController::class)->name('companies.index');
    Route::post('/', CreateCompanyController::class)->name('companies.store');
    Route::put('/{company}', UpdateCompanyController::class)->name('companies.update');
    Route::delete('/{company}', DeleteCompanyController::class)->name('companies.destroy');
  });

  Route::get('/{company}', ShowCompanyController::class)->name('companies.show');
});
