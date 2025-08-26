<?php

namespace App\Modules\Auth\Models;

use App\Modules\Users\Models\User;

class AuthUser extends User
{

  /**
   * Get the user's full name
   */
  public function getFullNameAttribute(): string
  {
    return $this->name;
  }

  /**
   * Check if user is active
   */
  public function isActive(): bool
  {
    return !is_null($this->email_verified_at);
  }

  /**
   * Get user's basic info for API responses
   */
  public function toApiArray(): array
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'email' => $this->email,
      'email_verified_at' => $this->email_verified_at,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}
