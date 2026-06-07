<?php

namespace App\Http\Requests\Admin\Questions;

use Illuminate\Foundation\Http\FormRequest;

class StoreMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string'],
            'var1' => ['required', 'string'],   // левый 1
            'var2' => ['required', 'string'],   // левый 2
            'var5' => ['required', 'string'],   // правильный для var1
            'var6' => ['required', 'string'],   // правильный для var2
            'var7' => ['required', 'string'],   // дистрактор 1
            'var8' => ['required', 'string'],   // дистрактор 2
        ];
    }

    public function messages(): array
    {
        return [
            'var1.required' => 'Введите 1-й элемент левого столбца.',
            'var2.required' => 'Введите 2-й элемент левого столбца.',
            'var5.required' => 'Введите правильное соответствие для 1-го элемента.',
            'var6.required' => 'Введите правильное соответствие для 2-го элемента.',
            'var7.required' => 'Введите 1-й дистрактор.',
            'var8.required' => 'Введите 2-й дистрактор. Для вопроса на соответствие нужно ровно 4 варианта ответа.',
        ];
    }
}
