<?php

namespace App\Http\Requests\Admin\Questions;

use Illuminate\Foundation\Http\FormRequest;

class StoreMultiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string'],
            'var1' => ['required', 'string'],
            'var2' => ['required', 'string'],
            'var3' => ['required', 'string'],
            'var4' => ['required', 'string'],
            'var5' => ['required', 'string'],
            'var6' => ['required', 'string'],
            'var7' => ['nullable', 'string'],
            'var8' => ['nullable', 'string'],
            'answers' => ['required', 'array', 'min:1', 'max:3'],
            'answers.*' => ['integer', 'min:1', 'max:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'Введите текст вопроса.',
            'answers.required' => 'Отметьте правильные ответы.',
            'answers.min' => 'Должен быть минимум 1 правильный ответ.',
            'answers.max' => 'Максимум 3 правильных ответа.',
        ];
    }
}
