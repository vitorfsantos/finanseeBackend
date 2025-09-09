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
    $user = request()->route('user');

    return [
      'name' => 'sometimes|string|max:255',
      'email' => [
        'sometimes',
        'email',
        'max:255',
        Rule::unique('users', 'email')->ignore($user)
      ],
      'password' => 'sometimes|string|min:6|max:255',
      'phone' => 'nullable|string|max:255',
      'user_level_id' => 'sometimes|exists:user_levels,id',

      // Array de companies com company_id, role e position
      'companies' => 'sometimes|array',
      'companies.*.company_id' => 'required_with:companies|exists:companies,id',
      'companies.*.role' => 'required_with:companies|in:owner,manager,employee',
      'companies.*.position' => 'nullable|string|max:255',
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
      'user_level_id.exists' => 'O nível de usuário não existe.',

      // Mensagens para companies
      'companies.array' => 'Companies deve ser um array.',
      'companies.*.company_id.required_with' => 'O ID da empresa é obrigatório.',
      'companies.*.company_id.exists' => 'A empresa especificada não existe.',
      'companies.*.role.required_with' => 'O cargo é obrigatório.',
      'companies.*.role.in' => 'O cargo deve ser: owner, manager ou employee.',
      'companies.*.position.max' => 'A posição não pode ter mais de 255 caracteres.',
    ];
  }
}
