<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserService
{
  /**
   * Create a new user
   */
  public function execute(array $data): User
  {
    // Hash the password if provided
    if (isset($data['password'])) {
      $data['password'] = Hash::make($data['password']);
    }

    // Set email_verified_at if not provided
    if (!isset($data['email_verified_at'])) {
      $data['email_verified_at'] = now();
    }

    return User::create($data);
  }
}
