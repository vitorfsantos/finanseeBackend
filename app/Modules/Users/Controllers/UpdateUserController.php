<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Models\User;
use App\Modules\Users\Requests\UpdateUserRequest;
use App\Modules\Users\Services\UpdateUserService;
use Illuminate\Http\JsonResponse;

class UpdateUserController extends Controller
{
  protected UpdateUserService $updateUserService;

  public function __construct(UpdateUserService $updateUserService)
  {
    $this->updateUserService = $updateUserService;
  }

  /**
   * Update the specified user
   */
  public function __invoke(UpdateUserRequest $request, User $user): JsonResponse
  {
    $updatedUser = $this->updateUserService->execute($user, $request->validated());

    return response()->json([
      'message' => 'Usuário atualizado com sucesso',
      'data' => $updatedUser
    ], 200);
  }
}
