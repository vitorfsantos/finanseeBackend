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
      'user_level_id' => 'required|exists:user_levels,id',
      'phone' => 'nullable|string|max:255',

      // Opção 1: Enviar company_id existente
      'company_id' => 'nullable|exists:companies,id',

      // Opção 2: Enviar dados da empresa para criar
      'company' => 'nullable|array',
      'company.name' => 'required_with:company|string|max:255',
      'company.cnpj' => 'required_with:company|string|unique:companies,cnpj|max:255',
      'company.email' => 'nullable|email|max:255',
      'company.phone' => 'nullable|string|max:255',

      // Dados do vínculo empresa-usuário
      'role' => 'nullable|in:owner,manager,employee',
      'position' => 'nullable|string|max:255',
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
      'user_level_id.required' => 'O nível de usuário é obrigatório.',
      'user_level_id.exists' => 'O nível de usuário não existe.',

      // Mensagens para empresa
      'company_id.exists' => 'A empresa especificada não existe.',
      'company.name.required_with' => 'O nome da empresa é obrigatório quando dados da empresa são fornecidos.',
      'company.name.max' => 'O nome da empresa não pode ter mais de 255 caracteres.',
      'company.cnpj.required_with' => 'O CNPJ da empresa é obrigatório quando dados da empresa são fornecidos.',
      'company.cnpj.unique' => 'Este CNPJ já está em uso.',
      'company.cnpj.max' => 'O CNPJ não pode ter mais de 255 caracteres.',
      'company.email.email' => 'O email da empresa deve ser um endereço válido.',
      'company.email.max' => 'O email da empresa não pode ter mais de 255 caracteres.',
      'company.phone.max' => 'O telefone da empresa não pode ter mais de 255 caracteres.',

      // Mensagens para vínculo
      'role.in' => 'O cargo deve ser: owner, manager ou employee.',
      'position.max' => 'O cargo não pode ter mais de 255 caracteres.',
    ];
  }
}
