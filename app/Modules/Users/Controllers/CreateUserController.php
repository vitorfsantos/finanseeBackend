<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Models\User;
use App\Modules\Users\Requests\CreateUserRequest;
use App\Modules\Users\Services\CreateUserService;
use Illuminate\Http\JsonResponse;

class CreateUserController extends Controller
{
  protected CreateUserService $createUserService;

  public function __construct(CreateUserService $createUserService)
  {
    $this->createUserService = $createUserService;
  }

  /**
   * Store a newly created user
   */
  public function __invoke(CreateUserRequest $request): JsonResponse
  {
    $user = $this->createUserService->execute($request->validated());

    return response()->json([
      'message' => 'Usuário criado com sucesso',
      'data' => $user
    ], 201);
  }
}
