<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ManageUser extends Command
{
    /**
     * The name and signature of the console command.
     * Actions: create, delete, list
     */
    protected $signature = 'sharemeister:user 
                            {action : Action to perform (create, delete, list)} 
                            {email? : The email of the user}';

    protected $description = 'Manage Sharemeister users and their physical storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $email = $this->argument('email');

        switch ($action) {
            case 'create':
                $this->createUser($email);
                break;
            case 'delete':
                $this->deleteUser($email);
                break;
            case 'list':
                $this->listUsers();
                break;
            default:
                $this->error("Unknown action: {$action}. Use create, delete, or list.");
        }
    }

    protected function createUser($email)
    {
        // 1. Initial Email Input
        if (!$email) {
            $email = $this->ask('Enter the user email');
        }

        // 2. Comprehensive Validation
        // We use Laravel's validator for the email first
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());
            return;
        }

        // 3. Password with minimum security check
        $password = $this->secret('Enter password for the new user');
        if (empty($password) || strlen($password) < 8) {
            $this->error("Password must be at least 8 characters long!");
            return;
        }

        // 4. Storage Limit Validation
        $limit = $this->ask('Storage limit in MB (-1 for unlimited)', 500);
        
        // English comment: Ensure limit is either -1 or a positive integer
        if (!is_numeric($limit) || ($limit < 1 && $limit != -1)) {
            $this->error("Invalid storage limit! Please enter a number (e.g., 500) or -1.");
            return;
        }

        $isAdmin = $this->confirm('Assign Administrator privileges?', false);

        // 5. Final Creation
        try {
            // English comment: Safe explode since email validation passed
            $name = explode('@', $email)[0];

            User::create([
                'name' => ucfirst($name), // Make it look nicer (e.g. 'Peter' instead of 'peter')
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => $isAdmin,
                'storage_limit_mb' => (int)$limit,
            ]);

            $this->info("Success: User {$email} created with " . ($limit == -1 ? 'unlimited' : $limit . 'MB') . " storage.");
        } catch (\Exception $e) {
            $this->error("Database error: " . $e->getMessage());
        }
    }

    protected function deleteUser($email)
    {
        if (!$email) {
            $this->error("Please provide an email for the delete action.");
            return;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found.");
            return;
        }

        $this->warn("!!! WARNING !!!");
        $this->warn("You are about to delete user: {$user->email}");
        $this->warn("This will permanently REMOVE all screenshots from the filesystem.");

        if ($this->confirm('Do you really want to proceed? This cannot be undone!')) {
            // Path: screenshots/{user_id}/
            $userDirectory = "screenshots/{$user->id}";

            // 1. Physical Cleanup (The SysAdmin part)
            if (Storage::disk('public')->exists($userDirectory)) {
                Storage::disk('public')->deleteDirectory($userDirectory);
                $this->info("Deleted directory: storage/app/public/{$userDirectory}");
            } else {
                $this->comment("No physical directory found for this user. Skipping FS cleanup.");
            }

            // 2. Database Cleanup (Cascading deletes if set up, or manual)
            $user->delete();

            $this->info("User and all data removed successfully.");
        }
    }

protected function listUsers()
    {
        // Fetch all users with their statistics
        $users = User::all();
        
        $data = $users->map(function ($user) {
            // Calculate stats for each user
            $count = \App\Models\Screenshot::where('uploader_id', $user->id)->count();
            $totalSizeKb = \App\Models\Screenshot::where('uploader_id', $user->id)->sum('file_size_kb');
            $totalSizeMb = round($totalSizeKb / 1024, 2);
            
            // Format quota string (e.g., "45.5 MB / 500 MB")
            $limit = $user->storage_limit_mb == -1 ? 'Unlimited' : $user->storage_limit_mb . ' MB';
            $usage = "{$totalSizeMb} MB / {$limit}";

            return [
                'ID' => $user->id,
                'Name' => $user->name,
                'Email' => $user->email,
                'Admin' => $user->is_admin ? 'Yes' : 'No',
                'Screenshots' => $count,
                'Quota Usage' => $usage,
            ];
        });

        if ($data->isEmpty()) {
            $this->warn("No users found in the system.");
            return;
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Admin', 'Screenshots', 'Quota Usage'],
            $data
        );
    }
}