<?php

namespace App\Modules\Transactions\Services;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateTransactionService
{
  /**
   * Create a new transaction based on user permissions
   */
  public function create(array $data): Transaction
  {
    $data['user_id'] = $data['user_id'] ?? Auth::user()?->id;
    // Se date não for fornecido, será definido pelo mutator no modelo
    if (!isset($data['date'])) {
      $data['date'] = Carbon::now();
    }

    return DB::transaction(function () use ($data) {
      return Transaction::create($data);
    });
  }
}
