<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SharemeisterInstall extends Command
{
    protected $signature = 'sharemeister:install';
    protected $description = 'Initialize the Sharemeister instance (First-time setup)';

    public function handle()
    {
        $this->info("--------------------------------------------------");
        $this->info("   Sharemeister Installation    ");
        $this->info("--------------------------------------------------");

        // 1. Check if already installed
        if ($this->isAlreadyInstalled()) {
            $this->error("Aborting: Sharemeister is already installed!");
            $this->line("At least one administrator account was found in the database.");
            if (!$this->confirm('Do you want to run the installer anyway? (This might overwrite settings)')) {
                return 1;
            }
        }

        // 2. Instance Configuration
        $this->warn("\n[1/4] Instance Settings");
        
        $instanceName = $this->askValidated(
            'What is the name of this Sharemeister Instance?',
            ['required', 'string', 'max:25', 'regex:/^[a-zA-Z0-9\s]+$/'],
            config('app.name'),
            "Only letters, numbers and spaces allowed (Max 25 chars)."
        );
        
        $this->setEnv('APP_NAME', "\"$instanceName\"");
        config(['app.name' => $instanceName]);

        // 3. Admin Account
        $this->warn("\n[2/4] Administrative Account");
        $name = $this->ask('Admin Name', 'Admin');
        
        $email = $this->askValidated(
            'Admin Email',
            ['required', 'email', 'unique:users,email']
        );

        $password = $this->askValidated(
            'Admin Password (min. 8 chars)',
            ['required', 'string', 'min:8'],
            null,
            null,
            true // Secret input
        );

        // 4. Storage Settings
        $this->warn("\n[3/4] Storage Configuration");
        $defaultLimit = $this->askValidated(
            'Default storage limit for new users (in MB)?',
            ['required', 'integer', 'min:-1'],
            150
        );

        // Summary
        $this->info("\nSummary:");
        $this->table(['Setting', 'Value'], [
            ['Instance Name', $instanceName],
            ['Admin Email', $email],
            ['Default Limit', $defaultLimit . ' MB']
        ]);

        if ($this->confirm('Proceed with installation?', true)) {
            try {
                User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'is_admin' => true,
                    'storage_limit_mb' => -1, // Admin is always unlimited
                ]);

                $this->info("\nSharemeister instance '{$instanceName}' is now ready!");
            } catch (\Exception $e) {
                $this->error("Critical error during installation: " . $e->getMessage());
                return 1;
            }
        }

        return 0;
    }

    /**
     * Check if an admin user already exists to prevent accidental re-install
     */
    protected function isAlreadyInstalled()
    {
        try {
            // Check if the users table even exists first
            if (!Schema::hasTable('users')) {
                return false;
            }
            return User::where('is_admin', true)->exists();
        } catch (\Exception $e) {
            // If the DB connection fails or table doesn't exist, we assume it's not installed
            return false;
        }
    }

    /**
     * Helper to ask and validate input in a loop until it's correct
     */
    protected function askValidated($question, $rules, $default = null, $customError = null, $secret = false)
    {
        $value = $secret ? $this->secret($question) : $this->ask($question, $default);

        $validator = Validator::make(['input' => $value], [
            'input' => $rules
        ]);

        if ($validator->fails()) {
            $this->error($customError ?? $validator->errors()->first('input'));
            return $this->askValidated($question, $rules, $default, $customError, $secret);
        }

        return $value;
    }

    /**
     * Update the .env file with the new configuration
     */
    protected function setEnv($key, $value)
    {
        $path = base_path('.env');
        if (File::exists($path)) {
            $currentContent = File::get($path);
            if (str_contains($currentContent, "{$key}=")) {
                $newContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $currentContent);
            } else {
                $newContent = $currentContent . "\n{$key}={$value}";
            }
            File::put($path, $newContent);
        }
    }
}