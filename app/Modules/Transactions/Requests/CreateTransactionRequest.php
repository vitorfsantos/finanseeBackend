<?php

namespace App\Modules\Transactions\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class CreateTransactionRequest extends BaseRequest
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
      'type' => 'required|in:income,expense',
      'category' => 'nullable|string|max:255',
      'description' => 'nullable|string|max:1000',
      'amount' => 'required|numeric|min:0.01|max:999999.99',
      'date' => 'required|date|before_or_equal:today',
      'user_id' => 'nullable|exists:users,id',
      'company_id' => 'nullable|exists:companies,id',
    ];
  }

  /**
   * Get custom messages for validator errors.
   */
  public function messages(): array
  {
    return [
      'type.required' => 'O tipo de transação é obrigatório.',
      'type.in' => 'O tipo deve ser "income" ou "expense".',
      'category.max' => 'A categoria não pode ter mais de 255 caracteres.',
      'description.max' => 'A descrição não pode ter mais de 1000 caracteres.',
      'amount.required' => 'O valor é obrigatório.',
      'amount.numeric' => 'O valor deve ser um número.',
      'amount.min' => 'O valor deve ser maior que zero.',
      'amount.max' => 'O valor não pode ser maior que 999.999,99.',
      'date.required' => 'A data é obrigatória.',
      'date.date' => 'A data deve ser uma data válida.',
      'date.before_or_equal' => 'A data não pode ser futura.',
      'user_id.required' => 'O usuário é obrigatório.',
      'user_id.exists' => 'O usuário não existe.',
      'company_id.exists' => 'A empresa não existe.',
    ];
  }
}
