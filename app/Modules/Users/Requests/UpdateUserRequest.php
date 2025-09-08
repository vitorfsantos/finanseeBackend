<?php

namespace App\Modules\Users\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseRequest
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
      'name' => 'sometimes|string|max:255',
      'email' => 'sometimes|email|max:255',
      'password' => 'sometimes|string|min:6|max:255',
      'phone' => 'sometimes|string|max:255',
    ];
  }

  /**
   * Get custom messages for validator errors.
   */
  public function messages(): array
  {
    return [
      'name.string' => 'O nome deve ser uma string.',
      'name.max' => 'O nome não pode ter mais de 255 caracteres.',
      'email.email' => 'O email deve ser um endereço válido.',
      'email.unique' => 'Este email já está em uso.',
      'email.max' => 'O email não pode ter mais de 255 caracteres.',
      'password.string' => 'A senha deve ser uma string.',
      'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
      'password.max' => 'A senha não pode ter mais de 255 caracteres.',
    ];
  }
}
