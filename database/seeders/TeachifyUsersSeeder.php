<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeachifyUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'teacher@teachify.test'],
            [
                'name' => 'Teachify Teacher',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'email_verified_at' => now(),
            ],
        );

        collect([
            ['name' => 'Mona Hassan', 'email' => 'mona.parent@teachify.test'],
            ['name' => 'Karim Saleh', 'email' => 'karim.parent@teachify.test'],
            ['name' => 'Sara Nabil', 'email' => 'sara.parent@teachify.test'],
        ])->each(fn (array $parent): User => User::updateOrCreate(
            ['email' => $parent['email']],
            [
                'name' => $parent['name'],
                'password' => Hash::make('password'),
                'role' => 'parent',
                'email_verified_at' => now(),
            ],
        ));
    }
}
