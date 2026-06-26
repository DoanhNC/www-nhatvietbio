<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_media_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->nullable()->constrained('e_media_folders')->onDelete('set null')->comment('Thư mục chứa');
            $table->string('original_name')->comment('Tên file gốc: image.jpg');
            $table->string('stored_name')->comment('Tên lưu trữ: uuid.jpg');
            $table->string('path', 1000)->comment('Đường dẫn đầy đủ: /images/banner/image.jpg');
            $table->string('storage_path', 1000)->comment('Đường dẫn vật lý: media/2025/12/uuid.jpg');
            $table->string('mime_type', 100)->comment('image/jpeg, application/pdf');
            $table->enum('file_type', ['image', 'document', 'video', 'audio', 'other'])->default('other');
            $table->unsignedBigInteger('file_size')->comment('Kích thước (bytes)');
            $table->string('extension', 20)->comment('jpg, png, pdf');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('folder_id', 'idx_folder');
            $table->index('file_type', 'idx_file_type');
            $table->index('path', 'idx_path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_media_files');
    }
};
