<?php

namespace App\Modules\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserLevel extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'slug',
    'name',
  ];

  /**
   * Get users with this level
   */
  public function users(): HasMany
  {
    return $this->hasMany(User::class);
  }

  /**
   * Check if this level has admin privileges
   */
  public function isAdmin(): bool
  {
    return $this->id <= 2; // Admin Master (1) and Company Admin (2)
  }

  /**
   * Check if this level has master admin privileges
   */
  public function isMasterAdmin(): bool
  {
    return $this->id === 1; // Admin Master only
  }

  /**
   * Check if this level can manage users
   */
  public function canManageUsers(): bool
  {
    return $this->id <= 2; // Admin Master and Company Admin
  }
}
