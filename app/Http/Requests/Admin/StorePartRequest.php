<?php

namespace App\Http\Requests\Admin;

use App\Enums\PartType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StorePartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isNusqa = $this->input('type') === PartType::Nusqa->value;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::when($isNusqa, ['numeric', 'integer', 'min:1', 'max:9999']),
            ],
            'type' => ['required', new Enum(PartType::class)],
            'is_trial' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Введите название.',
            'title.numeric' => 'Нұсқа должна быть числом.',
            'title.integer' => 'Нұсқа должна быть целым числом.',
            'title.min' => 'Нұсқа должна быть не менее :min.',
            'type.required' => 'Укажите тип.',
        ];
    }
}
