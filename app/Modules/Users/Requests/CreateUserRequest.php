<?php

namespace App\Modules\Users\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends BaseRequest
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
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:users,email|max:255',
      'password' => 'required|string|min:6|max:255',
    ];
  }

  /**
   * Get custom messages for validator errors.
   */
  public function messages(): array
  {
    return [
      'name.required' => 'O nome é obrigatório.',
      'name.max' => 'O nome não pode ter mais de 255 caracteres.',
      'email.required' => 'O email é obrigatório.',
      'email.email' => 'O email deve ser um endereço válido.',
      'email.unique' => 'Este email já está em uso.',
      'email.max' => 'O email não pode ter mais de 255 caracteres.',
      'password.required' => 'A senha é obrigatória.',
      'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
      'password.max' => 'A senha não pode ter mais de 255 caracteres.',
    ];
  }
}
