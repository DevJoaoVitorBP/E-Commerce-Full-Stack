<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => 'required|string|min:1|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'q.required' => 'O termo de busca é obrigatório.',
            'q.max' => 'O termo de busca não pode exceder 255 caracteres.',
            'per_page.max' => 'O limite de resultados não pode exceder 100 itens por página.',
        ];
    }
}
