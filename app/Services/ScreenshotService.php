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
        $fileSizeKbOriginal = $isPath 
            ? round(filesize($file) / 1024) 
            : round($file->getSize() / 1024);
        
        // 1. Quota Check
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

            $exists = Screenshot::where('image', $relativeStoragePath)->exists() 
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
                // Fix Palette/Indexed images
                if (!imageistruecolor($image)) {
                    $trueColorImage = imagecreatetruecolor(imagesx($image), imagesy($image));
                    imagecopy($trueColorImage, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
                    imagedestroy($image);
                    $image = $trueColorImage;
                }
                imagewebp($image, $fullPath, 80);
                
                imagedestroy($image);
                // Explicitly unset to help the Garbage Collector
                unset($image);

                // If you created a trueColorImage, make sure it's destroyed too
                if (isset($trueColorImage)) {
                    imagedestroy($trueColorImage);
                    unset($trueColorImage);
                }
            } else {
                // Fallback for unsupported formats
                $isPath 
                    ? File::copy($file, $fullPath) 
                    : $file->storeAs($folderPath, $imageName, 'public');
            }
        }

        // 4. Create Database Entry
        return Screenshot::create([
            'image' => $relativeStoragePath,
            'uploader_id' => $user->id,
            'file_size_kb' => round(filesize($fullPath) / 1024),
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
            default      => null,
        };
    }
}