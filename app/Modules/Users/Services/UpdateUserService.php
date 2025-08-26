<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserService
{
  /**
   * Update an existing user
   */
  public function execute(User $user, array $data): User
  {
    // Hash the password if provided
    if (isset($data['password'])) {
      $data['password'] = Hash::make($data['password']);
    }

    $user->update($data);

    return $user->fresh();
  }
}
