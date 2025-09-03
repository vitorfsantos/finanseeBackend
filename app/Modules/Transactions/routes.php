<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Transactions\Controllers\ListTransactionsController;
use App\Modules\Transactions\Controllers\ShowTransactionController;
use App\Modules\Transactions\Controllers\CreateTransactionController;
use App\Modules\Transactions\Controllers\UpdateTransactionController;
use App\Modules\Transactions\Controllers\DeleteTransactionController;

Route::prefix('transactions')->group(function () {
  Route::middleware(['role:adminMaster'])->group(function () {
    Route::get('/', ListTransactionsController::class)->name('transactions.index');
    Route::post('/', CreateTransactionController::class)->name('transactions.store');
    Route::put('/{transaction}', UpdateTransactionController::class)->name('transactions.update');
    Route::delete('/{transaction}', DeleteTransactionController::class)->name('transactions.destroy');
  });

  Route::middleware(['role:companyAdmin'])->group(function () {
    Route::get('/', ListTransactionsController::class)->name('transactions.index');
    Route::post('/', CreateTransactionController::class)->name('transactions.store');
    Route::put('/{transaction}', UpdateTransactionController::class)->name('transactions.update');
    Route::delete('/{transaction}', DeleteTransactionController::class)->name('transactions.destroy');
  });

  Route::middleware(['role:companyUser'])->group(function () {
    Route::get('/', ListTransactionsController::class)->name('transactions.index');
    Route::post('/', CreateTransactionController::class)->name('transactions.store');
    Route::put('/{transaction}', UpdateTransactionController::class)->name('transactions.update');
    Route::delete('/{transaction}', DeleteTransactionController::class)->name('transactions.destroy');
  });

  Route::middleware(['role:user'])->group(function () {
    Route::get('/', ListTransactionsController::class)->name('transactions.index');
    Route::post('/', CreateTransactionController::class)->name('transactions.store');
    Route::put('/{transaction}', UpdateTransactionController::class)->name('transactions.update');
    Route::delete('/{transaction}', DeleteTransactionController::class)->name('transactions.destroy');
  });

  Route::get('/{transaction}', ShowTransactionController::class)->name('transactions.show');
});
