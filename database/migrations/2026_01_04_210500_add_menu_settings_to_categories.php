<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('e_post_categories', function (Blueprint $table) {
            $table->boolean('show_related_posts')->default(false)->after('is_active')
                ->comment('Hiển thị bài viết liên quan trong trang chi tiết');
            $table->boolean('show_in_menu')->default(true)->after('show_related_posts')
                ->comment('Hiển thị trong menu website');
        });
    }

    public function down(): void
    {
        Schema::table('e_post_categories', function (Blueprint $table) {
            $table->dropColumn(['show_related_posts', 'show_in_menu']);
        });
    }
};
