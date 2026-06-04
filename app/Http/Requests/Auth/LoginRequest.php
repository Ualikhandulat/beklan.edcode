<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login'    => ['required', 'string', 'regex:/^87\d{9}$/'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'Введите номер телефона.',
            'login.regex'    => 'Номер должен начинаться с 87 и содержать 11 цифр.',
            'password.required' => 'Введите пароль.',
            'password.min'   => 'Пароль должен содержать не менее 6 символов.',
        ];
    }
}
