<?php

namespace App\Modules\Companies\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends BaseRequest
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
    $company = request()->route('company');
    return [
      'name' => 'sometimes|string|max:255',
      'cnpj' => 'sometimes|string|max:18|unique:companies,cnpj,' . $company->id,
      'email' => 'nullable|email|max:255',
      'phone' => 'nullable|string|max:20',
      'address' => 'nullable|array',
      'address.street' => 'required_with:address|string|max:255',
      'address.number' => 'nullable|string|max:20',
      'address.complement' => 'nullable|string|max:255',
      'address.neighborhood' => 'nullable|string|max:255',
      'address.city' => 'required_with:address|string|max:255',
      'address.state' => 'required_with:address|string|size:2',
      'address.zipcode' => 'required_with:address|string|max:10',
      'address.country' => 'nullable|string|max:255',
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
      'address.street.required_with' => 'A rua é obrigatória quando o endereço é fornecido.',
      'address.street.max' => 'A rua não pode ter mais de 255 caracteres.',
      'address.number.max' => 'O número não pode ter mais de 20 caracteres.',
      'address.complement.max' => 'O complemento não pode ter mais de 255 caracteres.',
      'address.neighborhood.max' => 'O bairro não pode ter mais de 255 caracteres.',
      'address.city.required_with' => 'A cidade é obrigatória quando o endereço é fornecido.',
      'address.city.max' => 'A cidade não pode ter mais de 255 caracteres.',
      'address.state.required_with' => 'O estado é obrigatório quando o endereço é fornecido.',
      'address.state.size' => 'O estado deve ter exatamente 2 caracteres.',
      'address.zipcode.required_with' => 'O CEP é obrigatório quando o endereço é fornecido.',
      'address.zipcode.max' => 'O CEP não pode ter mais de 255 caracteres.',
      'address.country.max' => 'O país não pode ter mais de 255 caracteres.',
    ];
  }
}
