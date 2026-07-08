<?php

namespace App\Services;

use App\Models\Screenshot;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScreenshotService
{
    public function handleUpload($file, User $user)
    {
        // Check if $file is a path (string) or an UploadedFile (object)
        $isPath = is_string($file);
        
        // 1. Duplicate Finder Check
        // Get the actual system path to calculate the SHA-256 hash of the original file
        $realPath = $isPath ? $file : $file->getRealPath();
        $fileHash = hash_file('sha256', $realPath);

        // Check if this specific user already uploaded the exact same file
        $existingScreenshot = Screenshot::where('uploader_id', $user->id)
            ->where('file_hash', $fileHash)
            ->first();

        if ($existingScreenshot) {
            // Duplicate found! Return the existing record immediately to prevent double processing and save storage
            // This prevents user confusion without wasting storage space
            $existingScreenshot->touch();
            return $existingScreenshot;
        }

        $fileSizeKbOriginal = $isPath 
            ? round(filesize($file) / 1024) 
            : round($file->getSize() / 1024);
        
        // Physical File Size Limit Check
        $maxSize = config('app.max_upload_size');
        if ($fileSizeKbOriginal > $maxSize) {
            // Ensure we don't process files larger than configured limit
            throw new \Exception("File too large. Max allowed: {$maxSize} KB.");
        }

        // Quota Check
        $currentUsageKb = Screenshot::where('uploader_id', $user->id)->sum('file_size_kb');
        if ($user->storage_limit_mb != -1 && ($currentUsageKb / 1024 + $fileSizeKbOriginal / 1024) > $user->storage_limit_mb) {
            throw new \Exception('Storage limit reached.');
        }

        $userId = $user->id;
        $datePath = date('Y/m/d');
        $folderPath = "screenshots/{$userId}/{$datePath}";
        
        // Get MimeType correctly for both types
        $mime = $isPath ? File::mimeType($file) : $file->getMimeType();
        
        // 2. Collision Check Loop
        do {
            $randomName = Str::random(8);
            $extension = ($mime === 'image/gif') ? 'gif' : 'webp';
            $imageName = $randomName . '.' . $extension;
            $relativeStoragePath = $folderPath . '/' . $imageName;

            // The basename must be globally unique: it is the only identifier used to
            // serve the raw image, and folders differ by user/date, so a full-path check
            // alone would allow the same basename in two different folders.
            $exists = Screenshot::where('image', $relativeStoragePath)->exists()
                    || Screenshot::where('image', 'like', '%/' . $imageName)->exists()
                    || Storage::disk('public')->exists($relativeStoragePath);
        } while ($exists);

        $fullPath = storage_path("app/public/{$folderPath}/{$imageName}");

        if (!File::isDirectory(dirname($fullPath))) {
            File::makeDirectory(dirname($fullPath), 0755, true);
        }

        // 3. Process & Convert
        if ($mime === 'image/gif') {
            // Use File::copy for strings (CLI) and storeAs for UploadedFiles (Web)
            $isPath 
                ? File::copy($file, $fullPath) 
                : $file->storeAs($folderPath, $imageName, 'public');
        } else {
            $image = $this->createImageResource($file);
            if ($image) {
                // Fix Palette/Indexed images. imagepalettetotruecolor() keeps the alpha
                // channel, unlike a manual imagecreatetruecolor()/imagecopy() which would
                // flatten transparency onto an opaque black canvas.
                if (!imageistruecolor($image)) {
                    imagepalettetotruecolor($image);
                }

                // Preserve transparency in the WebP output; without these calls imagewebp()
                // flattens transparent pixels to black.
                imagealphablending($image, false);
                imagesavealpha($image, true);

                imagewebp($image, $fullPath, 80);

                imagedestroy($image);
                // Explicitly unset to help the Garbage Collector
                unset($image);
            } else {
                // Fallback for unsupported formats
                $isPath 
                    ? File::copy($file, $fullPath) 
                    : $file->storeAs($folderPath, $imageName, 'public');
            }
        }

        // 4. Create Database Entry (Including the original file hash)
        return Screenshot::create([
            'image' => $relativeStoragePath,
            'uploader_id' => $user->id,
            'file_size_kb' => round(filesize($fullPath) / 1024),
            'file_hash' => $fileHash,
        ]);
    }

    /**
     * Create an image resource from either a path string or an UploadedFile object.
     */
    private function createImageResource($file)
    {
        // Get the actual system path regardless of input type
        $path = is_string($file) ? $file : $file->getRealPath();
        
        // Get the mime type correctly
        $mime = is_string($file) ? File::mimeType($file) : $file->getMimeType();

        return match($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png'  => imagecreatefrompng($path),
            'image/gif'  => imagecreatefromgif($path),
            'image/webp' => imagecreatefromwebp($path),
            default      => null,
        };
    }
}
