<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'admin',
            'lastname' => 'prime',
            'username' => 'admin.prime',
            'email' => 'admin.prime@example.com',
            'password' => bcrypt('admin123'),
        ]);

        User::factory()->create([
            'name' => 'Ana',
            'lastname' => 'García',
            'username' => 'anag',
            'email' => 'ana@example.com',
            'password' => bcrypt('password123'),
        ]);

        User::factory()->create([
            'name' => 'Juan',
            'lastname' => 'Pérez',
            'username' => 'juanp',
            'email' => 'juan@example.com',
            'password' => bcrypt('password123'),
        ]);
    }
}
