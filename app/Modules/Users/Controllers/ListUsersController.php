<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Services\ListUsersService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListUsersController extends Controller
{
  protected ListUsersService $listUsersService;

  public function __construct(ListUsersService $listUsersService)
  {
    $this->listUsersService = $listUsersService;
  }

  /**
   * Display a listing of users
   */
  public function __invoke(Request $request)
  {
    $users = $this->listUsersService->execute($request->all());

    return response()->json([
      'data' => $users->items(),
      'meta' => [
        'total' => $users->total(),
        'per_page' => $users->perPage(),
        'current_page' => $users->currentPage(),
        'last_page' => $users->lastPage(),
      ]
    ], 200);
  }
}
