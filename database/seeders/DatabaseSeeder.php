<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'id' => Str::uuid(),
            'name' => 'Max Mustermann',
            'email' => 'test@test.com',
            'password' => Hash::make('start')
        ]);

        $generatedToken = $user->createToken('testtoken')->plainTextToken;
        $this->command->info("Token for User " . $user->name . " is " . $generatedToken);

        $this->call([
            ScreenshotSeeder::class
        ]);
    }
}
