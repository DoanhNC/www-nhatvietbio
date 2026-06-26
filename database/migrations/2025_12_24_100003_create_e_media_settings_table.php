<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_media_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100)->unique();
            $table->text('setting_value');
            $table->timestamps();
        });

        // Insert default settings
        DB::table('e_media_settings')->insert([
            [
                'setting_key' => 'max_storage_bytes',
                'setting_value' => '2147483648', // 2GB
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'allowed_extensions',
                'setting_value' => '["jpg","jpeg","png","gif","webp","svg","pdf","doc","docx","xls","xlsx","ppt","pptx","mp4","mp3","zip","rar"]',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'max_file_size_bytes',
                'setting_value' => '52428800', // 50MB per file
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('e_media_settings');
    }
};
