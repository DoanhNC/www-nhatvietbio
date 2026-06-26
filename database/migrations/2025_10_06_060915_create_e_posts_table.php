<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique()->comment('đường dẫn thân thiện SEO');
            $table->foreignId('category_id')->constrained('e_post_categories')->restrictOnDelete();
            $table->text('tags')->nullable()->comment('mảng JSON của thẻ bài viết');
            $table->text('album_images')->nullable()->comment('mảng đường dẫn ảnh');
            $table->text('attachments')->nullable()->comment('mảng đường dẫn tệp');
            $table->text('video_urls')->nullable()->comment('mảng URL video');
            $table->unsignedInteger('position')->default(0)->comment('vị trí ưu tiên hiển thị');
            $table->boolean('is_featured')->default(0)->comment('1=bài nổi bật, 0=bình thường');
            $table->boolean('status')->default(1)->comment('1=hoạt động, 0=không hoạt động');
            $table->foreignId('created_by')->constrained('users');
            $table->text('short_description')->nullable();
            $table->longText('content')->nullable();
            $table->longText('json_data')->nullable()->comment('dữ liệu bổ sung (vd lịch sử cập nhật)');
            $table->timestamps();

            $table->index(['status', 'is_featured', 'position'], 'idx_status_featured_position');
            $table->index(['category_id'], 'idx_category_id');
            $table->index(['created_by'], 'idx_created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_posts');
    }
};
