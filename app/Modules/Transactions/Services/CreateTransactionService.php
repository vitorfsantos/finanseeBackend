<?php

namespace App\Modules\Transactions\Services;

use App\Modules\Transactions\Models\Transaction;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTransactionService
{
  /**
   * Create a new transaction based on user permissions
   */
  public function create(array $data): Transaction
  {
    $data['user_id'] = $data['user_id'] ?? auth()->user()->id;

    return DB::transaction(function () use ($data) {
      return Transaction::create($data);
    });
  }

}
