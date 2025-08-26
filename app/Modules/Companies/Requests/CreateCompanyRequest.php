<?php

namespace App\Modules\Companies\Requests;

use App\Http\Requests\BaseRequest;

class CreateCompanyRequest extends BaseRequest
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
      'cnpj' => 'required|string|unique:companies,cnpj|max:18',
      'email' => 'nullable|email|max:255',
      'phone' => 'nullable|string|max:20',
    ];
  }

  /**
   * Get custom messages for validator errors.
   */
  public function messages(): array
  {
    return [
      'name.required' => 'O nome da empresa é obrigatório.',
      'name.max' => 'O nome da empresa não pode ter mais de 255 caracteres.',
      'cnpj.required' => 'O CNPJ é obrigatório.',
      'cnpj.unique' => 'Este CNPJ já está em uso.',
      'cnpj.max' => 'O CNPJ não pode ter mais de 18 caracteres.',
      'email.email' => 'O email deve ser um endereço válido.',
      'email.max' => 'O email não pode ter mais de 255 caracteres.',
      'phone.max' => 'O telefone não pode ter mais de 20 caracteres.',
    ];
  }
}
