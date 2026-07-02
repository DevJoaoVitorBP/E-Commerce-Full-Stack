<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('active')) {
            $this->merge([
                'active' => filter_var($this->active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }

    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'name' => "required|string|max:255|unique:products,name,{$productId}",
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0.01',
            'cost_price' => 'nullable|numeric|min:0|lt:price',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ];
    }
}
