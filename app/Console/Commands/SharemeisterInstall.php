<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class SharemeisterInstall extends Command
{
    protected $signature = 'sharemeister:install';
    protected $description = 'Initialize the Sharemeister instance (First-time setup)';

    public function handle()
    {
        $this->info("--------------------------------------------------");
        $this->info("   Sharemeister Installation - Virtual Cockpit    ");
        $this->info("--------------------------------------------------");

        if (User::where('is_admin', true)->exists()) {
            if (!$this->confirm("An admin already exists. Do you want to run the setup again?")) {
                return 0;
            }
        }

        // 1. Admin Account
        $this->warn("\n[1/3] Administrative Account");
        $name = $this->ask('Admin Name', 'Admin');
        $email = $this->ask('Admin Email');
        $password = $this->secret('Admin Password');

        // 2. Storage Settings
        $this->warn("\n[2/3] Storage Configuration");
        $defaultLimit = $this->ask('Default storage limit for new users (in MB)?', 150);

        // 3. Confirming Setup
        $this->info("\nSummary:");
        $this->line("Admin: {$email}");
        $this->line("Default Limit: {$defaultLimit} MB");

        if ($this->confirm('Proceed with installation?')) {
            // Create Admin
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
                'storage_limit_mb' => -1, // Admin is always infinite
            ]);

            $this->info("\nSuccessfully created Admin account.");
            $this->info("Sharemeister instance is now ready to use!");
        }

        return 0;
    }
}