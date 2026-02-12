<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Screenshot;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ImportScreenshot extends Command
{
    protected $signature = 'sharemeister:import {path : Source directory} {useremail : Target user email}';
    protected $description = 'Import screenshots from a local path into a user account';

    public function handle()
    {
        $sourcePath = $this->argument('path');
        $user = User::where('email', $this->argument('useremail'))->first();

        if (!$user) {
            $this->error("Error: User not found.");
            return 1;
        }

        if (!File::isDirectory($sourcePath)) {
            $this->error("Error: Path '{$sourcePath}' is not a directory.");
            return 1;
        }

        $files = File::files($sourcePath);
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        $this->info("Scanning " . count($files) . " files for user: " . $user->email);

        foreach ($files as $file) {
            $mimeType = File::mimeType($file);

            if (!in_array($mimeType, $allowedMimeTypes)) {
                $this->warn("Skipping: {$file->getFilename()} (Invalid Mime: {$mimeType})");
                continue;
            }

            // --- REFACTORED PATH LOGIC (Same as Controller) ---
            $userId = $user->id;
            $datePath = date('Y/m/d');
            $folderPath = "screenshots/{$userId}/{$datePath}"; // Structured path
            
            $extension = $file->getExtension();
            $fileSizeKb = round(File::size($file) / 1024);
            $fileSizeMb = $fileSizeKb / 1024;

            // Check Quota before importing
            $currentUsageMb = Screenshot::where('uploader_id', $user->id)->sum('file_size_kb') / 1024;
            if ($user->storage_limit_mb != -1 && ($currentUsageMb + $fileSizeMb) > $user->storage_limit_mb) {
                $this->error("Quota exceeded for user {$user->email}. Stopping import.");
                return 1;
            }

            $newFilename = str()->random(8) . '.' . $extension;
            $targetPath = $folderPath . '/' . $newFilename;

            // Copy to storage disk
            Storage::disk('public')->put($targetPath, File::get($file));

            // Create database record
            Screenshot::create([
                'uploader_id' => $user->id,
                'image' => $targetPath,
                'file_size_kb' => $fileSizeKb,
            ]);

            $this->line("Imported: {$file->getFilename()} to {$targetPath}");
        }

        $this->info("Import completed successfully.");
        return 0;
    }
}