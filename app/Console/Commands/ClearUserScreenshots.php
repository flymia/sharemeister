<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Screenshot;
use Illuminate\Support\Facades\File;

class ClearUserScreenshots extends Command
{
    /**
     * The name and signature of the console command.
     * Use {--force} to skip confirmation.
     */
    protected $signature = 'user:clear-storage {user_id : The ID of the user} {--force : Skip confirmation}';

    protected $description = 'Deletes all screenshots of a specific user from database and filesystem';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("Error: User with ID {$userId} not found.");
            return 1;
        }

        $screenshots = Screenshot::where('uploader_id', $userId)->get();
        $count = $screenshots->count();

        if ($count === 0) {
            $this->info("User '{$user->name}' has no screenshots to delete.");
            return 0;
        }

        $this->warn("User '{$user->name}' has {$count} screenshots.");

        // Confirmation Logic (skipped if --force is used)
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to permanently delete all files for this user?")) {
                $this->info("Operation aborted.");
                return 0;
            }
        }

 $this->info("Starting cleanup...");

        $this->withProgressBar($screenshots, function ($screenshot) {
            // 1. Delete physical file
            $path = storage_path('app/public/' . $screenshot->image);
            if (File::exists($path)) {
                File::delete($path);
            }

            // 2. Delete database record
            $screenshot->delete();
        });

        $this->newLine();
        // Use info() here - it renders in green in most terminals
        $this->info("Cleanup finished. Successfully deleted {$count} screenshots for '{$user->name}'.");
        
        return 0;
    }
}