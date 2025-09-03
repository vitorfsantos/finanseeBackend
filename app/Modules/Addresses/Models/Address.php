<?php

namespace App\Modules\Addresses\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
  use HasFactory, HasUuids, SoftDeletes;

  /**
   * Create a new factory instance for the model.
   */
  protected static function newFactory()
  {
    return \App\Modules\Addresses\Models\Factories\AddressFactory::new();
  }

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = [
    'addressable_id',
    'addressable_type',
    'street',
    'number',
    'complement',
    'neighborhood',
    'city',
    'state',
    'zipcode',
    'country',
  ];

  /**
   * The attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'created_at' => 'datetime',
      'updated_at' => 'datetime',
      'deleted_at' => 'datetime',
    ];
  }

  /**
   * Get the parent addressable model (user or company).
   */
  public function addressable(): MorphTo
  {
    return $this->morphTo();
  }
}

