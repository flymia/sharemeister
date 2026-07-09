<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('screenshots', function (Blueprint $table) {
            // Basename of the stored image (globally unique 8-char name + ext). Serving and
            // the collision check query this by equality instead of an unindexable LIKE.
            $table->string('filename')->nullable()->after('image')->index();

            // The dedup lookup filters on (uploader_id, file_hash); a composite index serves
            // it directly. The previous single-column file_hash index is redundant (the
            // composite's leftmost prefix covers no file_hash-only query the app makes).
            $table->index(['uploader_id', 'file_hash']);
            $table->dropIndex(['file_hash']);
        });

        // Backfill filename for existing rows from the stored image path.
        foreach (DB::table('screenshots')->select('id', 'image')->cursor() as $row) {
            DB::table('screenshots')
                ->where('id', $row->id)
                ->update(['filename' => basename($row->image)]);
        }
    }

    public function down(): void
    {
        Schema::table('screenshots', function (Blueprint $table) {
            $table->index('file_hash');
            $table->dropIndex(['uploader_id', 'file_hash']);
            $table->dropIndex(['filename']);
            $table->dropColumn('filename');
        });
    }
};
