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

        if (!is_dir($sourcePath)) {
            $this->error("Error: Path '{$sourcePath}' is not a directory.");
            return 1;
        }

        $files = File::files($sourcePath);
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        $this->info("Scanning " . count($files) . " files...");

        foreach ($files as $file) {
            $mimeType = File::mimeType($file);

            if (!in_array($mimeType, $allowedMimeTypes)) {
                $this->warn("Skipping: {$file->getFilename()} (Invalid Mime: {$mimeType})");
                continue;
            }

            $folderPath = 'screenshots/' . date('Y/m/d') . '/';
            $extension = $file->getExtension();
            $fileSizeKb = round(File::size($file) / 1024);
            
            do {
                $newFilename = str()->random(8) . '.' . $extension;
            } while (Screenshot::where('image', 'like', "%$newFilename")->exists());

            $targetPath = $folderPath . $newFilename;

            // Copy to storage
            Storage::disk('public')->put($targetPath, File::get($file));

            // Create record with file_size_kb for the quota system
            Screenshot::create([
                'uploader_id' => $user->id,
                'image' => $targetPath,
                'file_size_kb' => $fileSizeKb,
            ]);

            $this->line("Imported: {$file->getFilename()} (" . $fileSizeKb . " KB)");
        }

        $this->info("Import completed successfully.");
        return 0;
    }
}