<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        $category = $this->route('category');
        $categoryId = $category instanceof Category ? $category->id : $category;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($categoryId)],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
