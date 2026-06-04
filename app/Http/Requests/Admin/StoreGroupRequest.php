<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255', 'unique:groups,title'],
            'description' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Введите название группы.',
            'title.unique'         => 'Группа с таким названием уже существует.',
            'description.required' => 'Введите описание группы.',
        ];
    }
}
