<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlateRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'image' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'is_available' => ['sometimes', 'boolean'],
            'category_id' => ['sometimes', 'required', 'integer', Rule::exists('categories', 'id')],
            'ingredient_ids' => ['sometimes', 'array'],
            'ingredient_ids.*' => ['integer', 'distinct', Rule::exists('ingredients', 'id')],
        ];
    }
}
