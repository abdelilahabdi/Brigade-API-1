<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'gt:0'],
            'image' => ['nullable', 'string', 'max:2048'],
            'is_available' => ['sometimes', 'boolean'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'ingredient_ids' => ['sometimes', 'array'],
            'ingredient_ids.*' => ['integer', 'distinct', Rule::exists('ingredients', 'id')],
        ];
    }
}
