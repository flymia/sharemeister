<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Tag;

class Screenshot extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = ['uploader_id', 'image', 'filename', 'file_size_kb', 'created_at', 'is_permanent', 'file_hash'];
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

    /**
     * Actual on-disk size in KB, read from the filesystem. The stored `file_size_kb`
     * column (written at upload time) is the source of truth for quota/UI; use this only
     * when you explicitly need to reconcile against disk.
     */
    public function actualFileSizeKb(): ?int
    {
        $filePath = storage_path('app/public/' . $this->image);

        return file_exists($filePath) ? (int) round(filesize($filePath) / 1024) : null;
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

    public function syncTags(string $tagString): void
    {
        $tagNames = collect(explode(',', $tagString))
            ->map(fn($t) => trim($t))
            ->filter()
            ->unique();
        
        $tagIds = [];
        foreach ($tagNames as $name) {
            $tag = Tag::firstOrCreate(['name' => $name]);
            $tagIds[] = $tag->id;
        }
        $this->tags()->sync($tagIds);
    }

}
