<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ListUsersService
{
  /**
   * Get all users with optional filtering
   */
  public function execute(array $filters = []): LengthAwarePaginator
  {
    $query = User::query();

    // Apply filters
    if (isset($filters['search'])) {
      $query->where(function ($q) use ($filters) {
        $q->where('name', 'like', '%' . $filters['search'] . '%')
          ->orWhere('email', 'like', '%' . $filters['search'] . '%');
      });
    }

    if (isset($filters['email_verified'])) {
      if ($filters['email_verified']) {
        $query->whereNotNull('email_verified_at');
      } else {
        $query->whereNull('email_verified_at');
      }
    }

    // Apply ordering
    $orderBy = $filters['order_by'] ?? 'name';
    $orderDirection = $filters['order_direction'] ?? 'asc';
    $query->orderBy($orderBy, $orderDirection);

    return $query->paginate(10);
  }
}
