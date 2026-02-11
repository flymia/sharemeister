<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Screenshot;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ImportScreenshot extends Command
{
    protected $signature = 'app:import-screenshot {path} {useremail}';
    protected $description = 'Import screenshots from a path to a user account and move them to storage.';

    public function handle()
    {
        $sourcePath = $this->argument('path');
        $user = User::where('email', $this->argument('useremail'))->first();

        if (!$user) {
            $this->error("Error: User not found.");
            return 1;
        }

        if (!is_dir($sourcePath)) {
            $this->error("Error: Path is not a directory.");
            return 1;
        }

        $files = File::files($sourcePath);
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

        foreach ($files as $file) {
            $mimeType = File::mimeType($file);

            if (!in_array($mimeType, $allowedMimeTypes)) {
                $this->warn("Skipping: {$file->getFilename()} (Invalid Mime)");
                continue;
            }

            // 1. Logic from Controller: generate path and name
            $folderPath = 'screenshots/' . date('Y/m/d') . '/';
            $extension = $file->getExtension();
            
            do {
                $newFilename = str()->random(8) . '.' . $extension;
            } while (Screenshot::where('image', 'like', "%$newFilename")->exists());

            $targetPath = $folderPath . $newFilename;

            // 2. Physically copy file to Laravel Storage (storage/app/public/...)
            // We use 'put' to write the content directly
            Storage::disk('public')->put($targetPath, File::get($file));

            // 3. Database entry
            Screenshot::create([
                'uploader_id' => $user->id,
                'image' => $targetPath,
            ]);

            $this->info("Imported: {$file->getFilename()} -> {$targetPath}");
        }

        $this->info("Import completed.");
        return 0;
    }
}