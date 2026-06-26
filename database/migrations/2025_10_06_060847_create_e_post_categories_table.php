<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->comment('đường dẫn thân thiện SEO');
            $table->foreignId('created_by')->constrained('users');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('status')->default(1)->comment('1=hoạt động, 0=không');
            $table->longText('json_data')->nullable();
            $table->timestamps();

            $table->index(['status', 'position'], 'idx_status_position');
            $table->index(['created_by'], 'idx_created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_post_categories');
    }
};
