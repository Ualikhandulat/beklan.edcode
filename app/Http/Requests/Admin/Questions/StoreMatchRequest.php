<?php

namespace App\Http\Requests\Admin\Questions;

use Illuminate\Foundation\Http\FormRequest;

class StoreMatchRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'question' => ['nullable', 'string'],
            'var1'     => ['required', 'string'],   // левый 1
            'var2'     => ['required', 'string'],   // левый 2
            'var5'     => ['required', 'string'],   // правильный для var1
            'var6'     => ['required', 'string'],   // правильный для var2
            'var7'     => ['required', 'string'],   // доп. вариант (дистрактор)
            'var8'     => ['nullable', 'string'],   // доп. вариант (необязательный)
        ];
    }

    public function messages(): array
    {
        return [
            'var1.required' => 'Введите 1-й элемент левого столбца.',
            'var2.required' => 'Введите 2-й элемент левого столбца.',
            'var5.required' => 'Введите правильное соответствие для 1-го элемента.',
            'var6.required' => 'Введите правильное соответствие для 2-го элемента.',
            'var7.required' => 'Введите хотя бы один дистрактор.',
        ];
    }
}
