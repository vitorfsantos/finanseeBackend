<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;

class RestoreUserService
{
  /**
   * Restore a soft deleted user
   */
  public function restore($userId): bool
  {
    // Find user including soft deleted ones
    $userModel = User::withTrashed()->find($userId);

    if (!$userModel) {
      return false;
    }

    // Check if user is actually deleted
    if (!$userModel->trashed()) {
    }
    return $userModel->restore();
  }
}
