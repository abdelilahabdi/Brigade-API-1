<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'dietary_tags' => ['required', 'array'],
            'dietary_tags.*' => ['string', 'distinct', Rule::in(User::allowedDietaryTags())],
        ];
    }
}
