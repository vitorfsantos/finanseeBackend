<?php

namespace App\Modules\Auth\Requests;

use App\Http\Requests\BaseRequest;

class LoginRequest extends BaseRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   */
  public function rules(): array
  {
    return [
      'email' => 'required|email',
      'password' => 'required|string',
    ];
  }

  /**
   * Get custom messages for validator errors.
   */
  public function messages(): array
  {
    return [
      'email.required' => 'O email é obrigatório.',
      'email.email' => 'O email deve ser um endereço válido.',
      'password.required' => 'A senha é obrigatória.',
      'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
    ];
  }
}
