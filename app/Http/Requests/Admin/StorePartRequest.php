<?php

namespace App\Http\Requests\Admin;

use App\Enums\PartType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', new Enum(PartType::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Введите название.',
            'type.required' => 'Укажите тип.',
        ];
    }
}
