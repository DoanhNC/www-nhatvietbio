<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('Mã ngôn ngữ: vi, ja, en...');
            $table->string('name')->comment('Tên hiển thị: Tiếng Việt, 日本語...');
            $table->string('flag', 50)->nullable()->comment('Flag emoji hoặc URL ảnh');
            $table->boolean('is_default')->default(0)->comment('1=Ngôn ngữ mặc định');
            $table->boolean('is_active')->default(1)->comment('1=Hoạt động, 0=Không hoạt động');
            $table->unsignedInteger('position')->default(0)->comment('Thứ tự hiển thị');
            $table->longText('translations')->nullable()->comment('JSON chứa các bản dịch');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_languages');
    }
};
