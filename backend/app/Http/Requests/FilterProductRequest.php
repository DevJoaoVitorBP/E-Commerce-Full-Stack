<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'nullable|integer|exists:categories,id',
            'search' => 'nullable|string|max:255',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'sort' => 'nullable|in:id,name,price,created_at,updated_at,quantity,min_quantity,category_id',
            'sort_direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'sort.in' => 'O campo de ordenação é inválido.',
            'sort_direction.in' => 'A direção de ordenação deve ser "asc" ou "desc".',
            'per_page.max' => 'O limite de resultados não pode exceder 100 itens por página.',
            'category_id.exists' => 'A categoria selecionada não existe.',
        ];
    }
}
