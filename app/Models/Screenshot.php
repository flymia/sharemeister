<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

}
