<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Tên thư mục');
            $table->foreignId('parent_id')->nullable()->constrained('e_media_folders')->onDelete('cascade')->comment('ID thư mục cha');
            $table->string('path', 1000)->comment('Đường dẫn đầy đủ: /images/banner');
            $table->foreignId('created_by')->constrained('users')->comment('Người tạo');
            $table->timestamps();

            $table->index('parent_id', 'idx_parent');
            $table->index('path', 'idx_path');
            $table->unique('path', 'unique_path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_media_folders');
    }
};
