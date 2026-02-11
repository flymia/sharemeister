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

        // 1. Instance Configuration
        $this->warn("\n[1/4] Instance Settings");
        $instanceName = $this->ask('What is the name of this Sharemeister Instance?', config('app.name'));
        
        // Update .env file logic
        $this->setEnv('APP_NAME', "\"$instanceName\"");
        // Refresh config so subsequent calls in this process use the new name
        config(['app.name' => $instanceName]);

        // 2. Admin Account
        $this->warn("\n[2/4] Administrative Account");
        $name = $this->ask('Admin Name', 'Admin');
        $email = $this->ask('Admin Email');
        $password = $this->secret('Admin Password');

        // 3. Storage Settings
        $this->warn("\n[3/4] Storage Configuration");
        $defaultLimit = $this->ask('Default storage limit for new users (in MB)?', 150);

        // Summary
        $this->info("\nSummary:");
        $this->line("Instance Name: {$instanceName}");
        $this->line("Admin: {$email}");
        $this->line("Default Limit: {$defaultLimit} MB");

        if ($this->confirm('Proceed with installation?')) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
                'storage_limit_mb' => -1,
            ]);

            $this->info("\nSharemeister instance '{$instanceName}' is now ready!");
        }

        return 0;
    }

    /**
     * Helper to write values to the .env file
     */
    protected function setEnv($key, $value)
    {
        $path = base_path('.env');

        if (File::exists($path)) {
            $currentContent = File::get($path);
            $newContent = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                $currentContent
            );
            File::put($path, $newContent);
        }
    }
}