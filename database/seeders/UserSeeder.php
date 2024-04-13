<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Max Mustermann',
            'email' => 'test@test.com',
            'password' => Hash::make('start')
        ]);

        $generatedToken = $user->createToken('testtoken')->plainTextToken;
        $this->command->info("Token for User " . $user->name . " is " . $generatedToken);
    }
}
