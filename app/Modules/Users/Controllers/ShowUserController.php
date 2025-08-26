<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Models\User;
use Illuminate\Http\JsonResponse;

class ShowUserController extends Controller
{
  /**
   * Display the specified user
   */
  public function __invoke(User $user): JsonResponse
  {
    return response()->json([
      'data' => $user
    ], 200);
  }
}
