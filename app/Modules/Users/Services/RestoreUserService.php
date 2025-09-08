<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;

class RestoreUserService
{
  /**
   * Restore a soft deleted user
   */
  public function restore(User $user): bool
  {
    return $user->restore();
  }
}
