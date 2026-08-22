<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'regex:/^87\d{9}$/', 'unique:users,login'],
            'iin' => ['required', 'string', 'digits:12', 'unique:users,iin'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => __('Введите ваше имя.'),
            'login.required' => __('Введите номер телефона.'),
            'login.regex' => __('Номер должен начинаться с 87 и содержать 11 цифр.'),
            'login.unique' => __('Этот номер уже зарегистрирован.'),
            'iin.required' => __('Введите ИИН.'),
            'iin.digits' => __('ИИН должен содержать ровно 12 цифр.'),
            'iin.unique' => __('Этот ИИН уже зарегистрирован.'),
            'password.required' => __('Введите пароль.'),
            'password.min' => __('Пароль должен содержать не менее 6 символов.'),
            'password.confirmed' => __('Пароли не совпадают.'),
        ];
    }
}
