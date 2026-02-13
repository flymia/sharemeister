<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Screenshot;
use Illuminate\Support\Facades\File;

class ClearUserScreenshots extends Command
{
    protected $signature = 'sharemeister:clear-user-storage {email : The email of the user} {--force : Skip confirmation}';

    protected $description = 'Deletes all screenshots of a specific user from database and filesystem';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Error: User with email '{$email}' not found.");
            return 1;
        }

        $screenshots = Screenshot::where('uploader_id', $user->id)->get();
        $count = $screenshots->count();

        if ($count === 0) {
            $this->info("User '{$user->name}' ({$email}) has no screenshots to delete.");
            return 0;
        }

        $this->warn("User '{$user->name}' has {$count} screenshots.");

        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to permanently delete all files for this user?")) {
                $this->info("Operation aborted.");
                return 0;
            }
        }

        $this->info("Starting cleanup for {$email}...");

        $this->withProgressBar($screenshots, function ($screenshot) {
            $path = storage_path('app/public/' . $screenshot->image);
            if (File::exists($path)) {
                File::delete($path);
            }
            $screenshot->delete();
        });

        $this->newLine();
        $this->info("Cleanup finished. Successfully deleted {$count} screenshots for '{$user->name}'.");
        
        return 0;
    }
}