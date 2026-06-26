<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('e_posts', function (Blueprint $table) {
            // === TAB 1: Thông tin cơ bản ===
            // Titles (JSON multi-lang) - thêm trước khi xóa title
            $table->json('titles')->after('id')
                ->comment('{"vi":"...", "en":"...", "ja":"..."}');

            // Categories (JSON array) - nhiều danh mục
            $table->json('categories')->after('titles')
                ->comment('Array of category IDs: [1, 2, 3]');

            // Main category (FK) - danh mục chính cho URL/breadcrumb
            $table->foreignId('main_category_id')->nullable()->after('categories')
                ->constrained('e_post_categories')->nullOnDelete();

            // Author (có thể khác created_by)
            $table->foreignId('author_id')->nullable()->after('main_category_id')
                ->constrained('users')->nullOnDelete();

            // Lượt xem
            $table->unsignedInteger('view_count')->default(0)->after('position');

            // Hiển thị mục lục
            $table->boolean('show_toc')->default(false)->after('is_featured')
                ->comment('Hiển thị table of contents');

            // === TAB 2: Mô tả bài viết ===
            // Ảnh chính (single image)
            $table->string('main_image')->nullable()->after('show_toc');

            // Short descriptions (multi-lang)
            $table->json('short_descriptions')->nullable()->after('main_image')
                ->comment('Mô tả ngắn đa ngôn ngữ');

            // Contents (multi-lang)
            $table->json('contents')->nullable()->after('short_descriptions')
                ->comment('Nội dung đa ngôn ngữ');

            // === TAB 4: SEO ===
            // Slugs (multi-lang)
            $table->json('slugs')->after('contents')
                ->comment('Đường dẫn SEO đa ngôn ngữ');

            // SEO Titles (multi-lang)
            $table->json('seo_titles')->nullable()->after('slugs');

            // SEO Descriptions (multi-lang)
            $table->json('seo_descriptions')->nullable()->after('seo_titles');

            // SEO Keywords (multi-lang)
            $table->json('seo_keywords')->nullable()->after('seo_descriptions');

            // Bài viết liên quan
            $table->json('related_posts')->nullable()->after('seo_keywords')
                ->comment('Array of post IDs');
        });

        // Migrate existing data: copy title/content to JSON format
        // This should be run manually or in a seeder for existing data

        // Drop old columns in separate Schema call to avoid issues
        Schema::table('e_posts', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['category_id']);

            // Drop old single-lang columns
            $table->dropColumn(['title', 'slug', 'short_description', 'content', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::table('e_posts', function (Blueprint $table) {
            // Re-add old columns
            $table->string('title')->after('id');
            $table->string('slug')->unique()->after('title');
            $table->foreignId('category_id')->after('slug')
                ->constrained('e_post_categories')->restrictOnDelete();
            $table->text('short_description')->nullable();
            $table->longText('content')->nullable();
        });

        Schema::table('e_posts', function (Blueprint $table) {
            // Drop new columns
            $table->dropForeign(['main_category_id']);
            $table->dropForeign(['author_id']);
            $table->dropColumn([
                'titles',
                'categories',
                'main_category_id',
                'author_id',
                'view_count',
                'show_toc',
                'main_image',
                'short_descriptions',
                'contents',
                'slugs',
                'seo_titles',
                'seo_descriptions',
                'seo_keywords',
                'related_posts',
            ]);
        });
    }
};
