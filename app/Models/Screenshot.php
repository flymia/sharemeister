<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Type\Integer;

class Screenshot extends Model
{
    use HasUuids, HasFactory;

    /**
     * Get the public URL for the screenshot.
     *
     * @return string
     */
    public function getPublicURLAttribute()
    {
        // Generate URL based on the file path.
        return url('share/' . basename($this->image));
    }

    public function getFileSizeKbAttribute()
    {
        $filePath = storage_path('app/public/' . $this->image);

        if (file_exists($filePath)) {
            // Show it in Kilobyte
            return round(filesize($filePath) / 1024);
        }

        return null; // Null if the file does not exist for some reason.
    }

}
