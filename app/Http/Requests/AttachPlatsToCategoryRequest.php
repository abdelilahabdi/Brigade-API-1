<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachPlatsToCategoryRequest extends FormRequest
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
            'plat_ids' => ['required', 'array', 'min:1'],
            'plat_ids.*' => ['required', 'integer', 'distinct', 'exists:plats,id'],
        ];
    }
}
