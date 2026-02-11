<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Ramsey\Uuid\Type\Integer;

class Screenshot extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = ['uploader_id', 'image'];
    protected $appends = ['publicURL'];

    /**
     * Accessor für die Public URL.
     * Ermöglicht den Aufruf via $screenshot->publicURL
     */
    protected function publicURL(): Attribute
    {
        return Attribute::make(
            // Wir generieren den Link direkt zu deiner 'rawShow' Route
            get: fn () => url('screenshots/' . basename($this->image)),
        );
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
