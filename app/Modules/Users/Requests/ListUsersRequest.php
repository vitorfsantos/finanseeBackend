<?php

namespace App\Modules\Users\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ListUsersRequest extends BaseRequest
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
      'search' => 'nullable|string|max:255',
      'user_level_id' => 'nullable|integer|exists:user_levels,id',
      'page' => 'nullable|integer|min:1',
      'per_page' => 'nullable|integer|min:1|max:100',
      'order_by' => 'nullable|string|in:name,email,created_at,updated_at',
      'order_direction' => 'nullable|string|in:asc,desc',
      'email_verified' => 'nullable|boolean',
    ];
  }

  /**
   * Get custom messages for validator errors.
   */
  public function messages(): array
  {
    return [
      'search.max' => 'O termo de busca não pode ter mais de 255 caracteres.',
      'user_level_id.integer' => 'O nível de usuário deve ser um número inteiro.',
      'user_level_id.exists' => 'O nível de usuário não existe.',
      'page.integer' => 'A página deve ser um número inteiro.',
      'page.min' => 'A página deve ser maior que zero.',
      'per_page.integer' => 'A quantidade por página deve ser um número inteiro.',
      'per_page.min' => 'A quantidade por página deve ser maior que zero.',
      'per_page.max' => 'A quantidade por página não pode ser maior que 100.',
      'order_by.in' => 'O campo de ordenação deve ser: name, email, created_at ou updated_at.',
      'order_direction.in' => 'A direção da ordenação deve ser: asc ou desc.',
      'email_verified.boolean' => 'O filtro de email verificado deve ser verdadeiro ou falso.',
    ];
  }

  /**
   * Get custom attributes for validator errors.
   */
  public function attributes(): array
  {
    return [
      'search' => 'termo de busca',
      'user_level_id' => 'nível de usuário',
      'page' => 'página',
      'per_page' => 'quantidade por página',
      'order_by' => 'campo de ordenação',
      'order_direction' => 'direção da ordenação',
      'email_verified' => 'email verificado',
    ];
  }
}

