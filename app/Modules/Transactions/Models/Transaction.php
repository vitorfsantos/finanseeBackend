<?php

namespace App\Modules\Transactions\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Users\Models\User;
use App\Modules\Companies\Models\Company;

class Transaction extends Model
{
  use HasFactory, HasUuids, SoftDeletes;

  /**
   * Create a new factory instance for the model.
   */
  protected static function newFactory()
  {
    return \App\Modules\Transactions\Models\Factories\TransactionFactory::new();
  }

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = [
    'user_id',
    'company_id',
    'type',
    'category',
    'description',
    'amount',
    'date',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'amount' => 'decimal:2',
      'date' => 'datetime',
    ];
  }


  /**
   * Get the user that owns the transaction
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the company that owns the transaction
   */
  public function company(): BelongsTo
  {
    return $this->belongsTo(Company::class);
  }

  /**
   * Scope to filter by user
   */
  public function scopeForUser($query, $userId)
  {
    return $query->where('user_id', $userId);
  }

  /**
   * Scope to filter by company
   */
  public function scopeForCompany($query, $companyId)
  {
    return $query->where('company_id', $companyId);
  }

  /**
   * Scope to filter by type
   */
  public function scopeOfType($query, $type)
  {
    return $query->where('type', $type);
  }

  /**
   * Scope to filter by date range
   */
  public function scopeInDateRange($query, $startDate, $endDate)
  {
    return $query->whereBetween('date', [$startDate, $endDate]);
  }
}
