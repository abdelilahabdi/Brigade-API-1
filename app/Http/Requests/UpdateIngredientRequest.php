<?php

namespace App\Http\Requests;

use App\Models\Ingredient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIngredientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');

        if (is_string($name)) {
            $this->merge([
                'name' => trim($name),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'tags' => ['sometimes', 'required', 'array'],
            'tags.*' => ['string', 'distinct', Rule::in(Ingredient::allowedTags())],
        ];
    }
}
