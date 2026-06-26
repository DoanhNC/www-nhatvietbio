<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add parent_id for hierarchical structure
        Schema::table('e_post_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id')->comment('ID danh mục cha');
            $table->unsignedInteger('position')->default(0)->after('created_by')->comment('Thứ tự hiển thị');
            $table->boolean('is_active')->default(1)->after('position')->comment('Trạng thái hoạt động');
            $table->string('slug')->nullable()->after('is_active')->comment('Đường dẫn SEO');
            $table->longText('names')->nullable()->after('slug')->comment('JSON tên theo ngôn ngữ: {"vi": "Tên VN", "en": "Name EN"}');

            $table->foreign('parent_id')->references('id')->on('e_post_categories')->onDelete('set null');
        });

        // Step 2: Migrate existing data from name_vi, name_ja, name_en to JSON names
        $categories = DB::table('e_post_categories')->get();
        foreach ($categories as $category) {
            $names = [];
            if (!empty($category->name_vi)) $names['vi'] = $category->name_vi;
            if (!empty($category->name_ja)) $names['ja'] = $category->name_ja;
            if (!empty($category->name_en)) $names['en'] = $category->name_en;

            DB::table('e_post_categories')
                ->where('id', $category->id)
                ->update(['names' => json_encode($names, JSON_UNESCAPED_UNICODE)]);
        }

        // Step 3: Drop old columns
        Schema::table('e_post_categories', function (Blueprint $table) {
            $table->dropColumn(['name_vi', 'name_ja', 'name_en']);
        });
    }

    public function down(): void
    {
        // Step 1: Re-add old columns
        Schema::table('e_post_categories', function (Blueprint $table) {
            $table->string('name_vi')->after('parent_id');
            $table->string('name_ja')->after('name_vi');
            $table->string('name_en')->after('name_ja');
        });

        // Step 2: Migrate data back from JSON to individual columns
        $categories = DB::table('e_post_categories')->get();
        foreach ($categories as $category) {
            $names = json_decode($category->names ?? '{}', true) ?: [];
            DB::table('e_post_categories')
                ->where('id', $category->id)
                ->update([
                    'name_vi' => $names['vi'] ?? '',
                    'name_ja' => $names['ja'] ?? '',
                    'name_en' => $names['en'] ?? '',
                ]);
        }

        // Step 3: Drop new columns
        Schema::table('e_post_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'position', 'is_active', 'slug', 'names']);
        });
    }
};
