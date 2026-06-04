<?php

namespace App\Http\Requests\Admin\Questions;

use Illuminate\Foundation\Http\FormRequest;

class StoreOneRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string'],
            'var1'     => ['required', 'string'],  // правильный
            'var2'     => ['required', 'string'],
            'var3'     => ['required', 'string'],
            'var4'     => ['nullable', 'string'],
            'var5'     => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'Введите текст вопроса.',
            'var1.required'     => 'Введите правильный ответ.',
            'var2.required'     => 'Введите 2-й вариант.',
            'var3.required'     => 'Введите 3-й вариант.',
        ];
    }
}
