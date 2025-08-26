<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Models\User;
use App\Modules\Users\Services\DeleteUserService;
use Illuminate\Http\JsonResponse;

class DeleteUserController extends Controller
{
  protected DeleteUserService $deleteUserService;

  public function __construct(DeleteUserService $deleteUserService)
  {
    $this->deleteUserService = $deleteUserService;
  }

  /**
   * Remove the specified user
   */
  public function __invoke(User $user): JsonResponse
  {
    $this->deleteUserService->execute($user);

    return response()->json([
      'message' => 'Usuário excluído com sucesso'
    ], 200);
  }
}
