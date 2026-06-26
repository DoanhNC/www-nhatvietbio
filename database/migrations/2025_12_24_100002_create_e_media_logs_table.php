<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_media_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->comment('Người thực hiện');
            $table->enum('action_type', ['upload', 'create_folder', 'rename', 'move', 'delete'])->comment('Loại hành động');
            $table->enum('target_type', ['file', 'folder'])->comment('Đối tượng');
            $table->string('target_path', 1000)->comment('Đường dẫn mục tiêu');
            $table->string('target_name')->nullable()->comment('Tên file/folder');
            $table->string('old_path', 1000)->nullable()->comment('Đường dẫn cũ (cho rename/move)');
            $table->json('details')->nullable()->comment('Thông tin bổ sung');
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id', 'idx_user');
            $table->index('action_type', 'idx_action');
            $table->index('created_at', 'idx_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_media_logs');
    }
};
