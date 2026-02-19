<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Screenshot;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;

class ImportScreenshot extends Command
{
    // Added optional --csv flag. Path and useremail remain for folder import.
    protected $signature = 'sharemeister:import 
                            {path? : Source directory (optional if --csv is used)} 
                            {useremail? : Target user email (optional if --csv is used)} 
                            {--csv= : Path to a CSV file for bulk import}';
                            
    protected $description = 'Import screenshots from a directory or a CSV file';

    public function handle()
    {
        $csvPath = $this->option('csv');

        if ($csvPath) {
            return $this->handleCsvImport($csvPath);
        }

        // Fallback to directory import
        return $this->handleDirectoryImport();
    }

    protected function handleCsvImport($csvPath)
    {
        if (!File::exists($csvPath)) {
            $this->error("CSV file not found: {$csvPath}");
            return 1;
        }

        $this->info("Starting CSV import from: {$csvPath}");
        
        if (($handle = fopen($csvPath, "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ","); // Skip Header: path,creationdate,uploader

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $sourceFile = $data[0];
                $creationDate = $data[1];
                $uploaderEmail = $data[2];

                if (!File::exists($sourceFile)) {
                    $this->warn("Skipping: File not found at {$sourceFile}");
                    continue;
                }

                $user = User::where('email', $uploaderEmail)->first();
                if (!$user) {
                    $this->warn("Skipping: User {$uploaderEmail} not found.");
                    continue;
                }

                $this->processFile($sourceFile, $user, $creationDate);
            }
            fclose($handle);
        }

        $this->info("CSV Import completed.");
        return 0;
    }

    protected function handleDirectoryImport()
    {
        $sourcePath = $this->argument('path');
        $userEmail = $this->argument('useremail');

        if (!$sourcePath || !$userEmail) {
            $this->error("Missing arguments. Use 'sharemeister:import {path} {email}' or '--csv={file}'");
            return 1;
        }

        $user = User::where('email', $userEmail)->first();
        if (!$user) {
            $this->error("User not found.");
            return 1;
        }

        if (!File::isDirectory($sourcePath)) {
            $this->error("Path '{$sourcePath}' is not a directory.");
            return 1;
        }

        $files = File::files($sourcePath);
        foreach ($files as $file) {
            $this->processFile($file->getRealPath(), $user, now());
        }

        return 0;
    }

    protected function processFile($absolutePath, $user, $date)
    {
        $filename = basename($absolutePath);
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = File::mimeType($absolutePath);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            $this->warn("Skipping {$filename}: Invalid MimeType {$mimeType}");
            return;
        }

        // --- Metadata Stripping & Image Processing ---
        // Load image to strip EXIF but keep orientation/quality
        $img = Image::make($absolutePath);

        // Strip all metadata (GPS, Camera info, etc.)
        $encoded = $img->encode(null, 90);

        // --- Path Logic (Keeping Original Filename) ---
        $targetPath = "screenshots/{$user->id}/" . $filename;

        // Quota Check
        $fileSizeKb = round(File::size($absolutePath) / 1024);
        $currentUsageMb = Screenshot::where('uploader_id', $user->id)->sum('file_size_kb') / 1024;
        
        if ($user->storage_limit_mb != -1 && ($currentUsageMb + ($fileSizeKb / 1024)) > $user->storage_limit_mb) {
            $this->error("Quota exceeded for {$user->email}. Skipping {$filename}");
            return;
        }

        // Save stripped image to storage
        Storage::disk('public')->put($targetPath, (string) $img->encode());

        // Create DB Record
        Screenshot::create([
            'uploader_id' => $user->id,
            'image' => $targetPath,
            'file_size_kb' => $fileSizeKb,
            'created_at' => Carbon::parse($date),
        ]);

        $this->line("Imported: {$filename} (User: {$user->email})");
    }
}
