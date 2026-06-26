<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add deleted_at column to e_media_folders for soft delete
        Schema::table('e_media_folders', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->after('updated_at')->comment('Soft delete timestamp');
            $table->index('deleted_at', 'idx_deleted');
        });

        // Add deleted_at column to e_media_files for soft delete
        Schema::table('e_media_files', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->after('updated_at')->comment('Soft delete timestamp');
            $table->index('deleted_at', 'idx_deleted');
        });
    }

    public function down(): void
    {
        Schema::table('e_media_folders', function (Blueprint $table) {
            $table->dropIndex('idx_deleted');
            $table->dropColumn('deleted_at');
        });

        Schema::table('e_media_files', function (Blueprint $table) {
            $table->dropIndex('idx_deleted');
            $table->dropColumn('deleted_at');
        });
    }
};
