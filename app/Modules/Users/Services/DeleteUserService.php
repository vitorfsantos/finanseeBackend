<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;

class DeleteUserService
{
  /**
   * Delete an existing user
   */
  public function delete(User $user): bool
  {
    // Revoke all tokens before deleting
    $user->tokens()->delete();

    return $user->delete();
  }
}
