<?php

namespace App\Http\Requests\Admin\Questions;

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
            'context_mode' => ['required', 'in:new,existing'],
            'context_text' => ['required_if:context_mode,new', 'nullable', 'string'],
            'context_id' => ['required_if:context_mode,existing', 'nullable', 'exists:questions,id'],
            'question' => ['required', 'string'],
            'var1' => ['required', 'string'],
            'var2' => ['required', 'string'],
            'var3' => ['required', 'string'],
            'var4' => ['nullable', 'string'],
            'var5' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'context_text.required_if' => 'Введите текст контекста.',
            'context_id.required_if' => 'Выберите существующий контекст.',
            'question.required' => 'Введите текст вопроса.',
            'var1.required' => 'Введите правильный ответ.',
            'var2.required' => 'Введите 2-й вариант.',
            'var3.required' => 'Введите 3-й вариант.',
        ];
    }
}
