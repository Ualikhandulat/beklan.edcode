<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'login'    => ['required', 'string', 'regex:/^87\d{9}$/', 'unique:users,login'],
            'iin'      => ['required', 'string', 'digits:12', 'unique:users,iin'],
            'password' => ['required', 'string', 'min:6'],
            'role'     => ['required', new Enum(Role::class)],
            'group_id' => ['nullable', 'exists:groups,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Введите имя пользователя.',
            'login.required'    => 'Введите номер телефона.',
            'login.regex'       => 'Номер должен начинаться с 87 и содержать 11 цифр.',
            'login.unique'      => 'Этот номер уже зарегистрирован.',
            'iin.required'      => 'Введите ИИН.',
            'iin.digits'        => 'ИИН должен содержать ровно 12 цифр.',
            'iin.unique'        => 'Этот ИИН уже зарегистрирован.',
            'password.required' => 'Введите пароль.',
            'password.min'      => 'Пароль должен содержать не менее 6 символов.',
            'role.required'     => 'Выберите роль.',
            'group_id.exists'   => 'Выбранная группа не найдена.',
        ];
    }
}
