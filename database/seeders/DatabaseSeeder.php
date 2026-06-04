<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if ( User::doesntExist() ) {
            User::create([
                'role' => Role::Admin,
                'name' => "Dulat",
                'login' => "87074432113",
                'iin' => "000000000000",
                'password' => Hash::make("123456"),
            ]);
        }
    }
}
