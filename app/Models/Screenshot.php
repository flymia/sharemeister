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
        // Generiere die URL basierend auf dem Bildpfad
        return url('share/' . basename($this->image));
    }

    public function getFileSizeKbAttribute()
    {
        $filePath = storage_path('app/public/' . $this->image);

        // Check if the file exists. This should not happen, as the model would not be found, but u know...
        if (file_exists($filePath)) {
            // Show it in Kilobyte
            return round(filesize($filePath) / 1024);
        }

        return null; // Gibt null zurück, wenn die Datei nicht existiert
    }

}
