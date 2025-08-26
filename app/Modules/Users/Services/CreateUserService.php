<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserService
{
  /**
   * Create a new user
   */
  public function create(array $data): User
  {
    // Hash the password if provided
    if (isset($data['password'])) {
      $data['password'] = Hash::make($data['password']);
    }

    return User::create($data);
  }
}
