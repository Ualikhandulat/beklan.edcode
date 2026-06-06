<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $kzFirstNames = ['Айдана', 'Асель', 'Дина', 'Жанар', 'Гульнара', 'Алина', 'Камила', 'Зарина',
            'Айгерим', 'Мадина', 'Аружан', 'Сабина', 'Лаура', 'Меруерт', 'Назгуль',
            'Ерлан', 'Арман', 'Дулат', 'Нурлан', 'Серик', 'Бауыржан', 'Азамат', 'Даурен',
            'Руслан', 'Алибек', 'Нурсултан', 'Жандос', 'Бекзат', 'Тимур', 'Максат'];

        $kzLastNames = ['Ахметов', 'Сейткали', 'Нурмагамбетов', 'Байжанов', 'Касымов',
            'Жумабеков', 'Сарсенов', 'Алиев', 'Ибрагимов', 'Хасанов',
            'Ахметова', 'Сейткалиева', 'Нурмагамбетова', 'Байжанова', 'Касымова',
            'Жумабекова', 'Сарсенова', 'Алиева', 'Ибрагимова', 'Хасанова'];

        $firstName = fake()->randomElement($kzFirstNames);
        $lastName = fake()->randomElement($kzLastNames);

        return [
            'role' => Role::Student,
            'name' => "{$firstName} {$lastName}",
            'login' => '87'.fake()->numerify('#########'),
            'iin' => fake()->numerify('############'),
            'password' => static::$password ??= Hash::make('password'),
        ];
    }

    public function student(): static
    {
        return $this->state(fn () => ['role' => Role::Student]);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => Role::Admin]);
    }
}
