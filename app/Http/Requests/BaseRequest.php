<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseRequest extends FormRequest
{
  /**
   * Handle a failed validation attempt.
   */
  protected function failedValidation(Validator $validator)
  {
    throw new HttpResponseException(
      response()->json([
        'message' => 'Dados de validação inválidos.',
        'errors' => $validator->errors()
      ], 422)
    );
  }

  /**
   * Handle a failed authorization attempt.
   */
  protected function failedAuthorization()
  {
    throw new HttpResponseException(
      response()->json([
        'message' => 'Acesso não autorizado.'
      ], 403)
    );
  }
}
