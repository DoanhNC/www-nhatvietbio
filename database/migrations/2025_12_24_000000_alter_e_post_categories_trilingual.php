<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('e_post_categories', function (Blueprint $table) {
            // Rename name to name_vi
            $table->renameColumn('name', 'name_vi');
        });

        Schema::table('e_post_categories', function (Blueprint $table) {
            // Add name_ja and name_en columns
            $table->string('name_ja')->after('name_vi')->comment('Tên tiếng Nhật');
            $table->string('name_en')->after('name_ja')->comment('Tên tiếng Anh');

            // Drop unnecessary columns
            $table->dropIndex('idx_status_position');
            $table->dropColumn(['slug', 'position', 'status', 'json_data']);
        });
    }

    public function down(): void
    {
        Schema::table('e_post_categories', function (Blueprint $table) {
            // Re-add dropped columns
            $table->string('slug')->unique()->after('name_vi')->comment('đường dẫn thân thiện SEO');
            $table->unsignedInteger('position')->default(0)->after('created_by');
            $table->boolean('status')->default(1)->comment('1=hoạt động, 0=không')->after('position');
            $table->longText('json_data')->nullable()->after('status');

            // Drop added columns
            $table->dropColumn(['name_ja', 'name_en']);

            // Re-add index
            $table->index(['status', 'position'], 'idx_status_position');
        });

        Schema::table('e_post_categories', function (Blueprint $table) {
            // Rename back to name
            $table->renameColumn('name_vi', 'name');
        });
    }
};
