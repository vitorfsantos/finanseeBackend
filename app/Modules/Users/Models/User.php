<?php

namespace App\Modules\Users\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
  /** @use HasFactory<\App\Modules\Users\Models\Factories\UserFactory> */
  use HasFactory, Notifiable, HasApiTokens, HasUuids, SoftDeletes;

  /**
   * Create a new factory instance for the model.
   */
  protected static function newFactory()
  {
    return \App\Modules\Users\Models\Factories\UserFactory::new();
  }

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
    'phone',
    'user_level_id',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var list<string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  /**
   * Get the user's level
   */
  public function level(): BelongsTo
  {
    return $this->belongsTo(UserLevel::class, 'user_level_id');
  }

  /**
   * Check if user has admin privileges
   */
  public function isAdmin(): bool
  {
    return $this->level && $this->level->isAdmin();
  }

  /**
   * Check if user has master admin privileges
   */
  public function isMasterAdmin(): bool
  {
    return $this->level && $this->level->isMasterAdmin();
  }

  /**
   * Check if user can manage users
   */
  public function canManageUsers(): bool
  {
    return $this->level && $this->level->canManageUsers();
  }

  /**
   * Get the companies associated with this user
   */
  public function companies(): BelongsToMany
  {
    return $this->belongsToMany(
      \App\Modules\Companies\Models\Company::class,
      'company_user',
      'user_id',
      'company_id'
    )->withPivot(['role', 'position', 'created_at', 'updated_at'])
      ->withTimestamps();
  }
}
