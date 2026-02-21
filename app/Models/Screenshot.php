<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Ramsey\Uuid\Type\Integer;
use App\Models\Tag;

class Screenshot extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = ['uploader_id', 'image', 'file_size_kb', 'created_at', 'is_permanent'];
    protected $appends = ['public_url'];

    protected $casts = [
        'is_permanent' => 'boolean',
        'created_at' => 'datetime',
    ];

    protected function publicUrl(): Attribute
    {
        return Attribute::make(
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

    public function uploader(): BelongsTo
    {
            return $this->belongsTo(User::class, 'uploader_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Protect the screenshot from deletion.
     */
    public function protect(): bool
    {
        return $this->update(['is_permanent' => true]);
    }

    /**
     * Unprotect the screenshot, allowing deletion.
     */
    public function unprotect(): bool
    {
        return $this->update(['is_permanent' => false]);
    }

    /**
     * Toggle the protection status.
     */
    public function toggleProtection(): bool
    {
        return $this->update(['is_permanent' => !$this->is_permanent]);
    }

}
