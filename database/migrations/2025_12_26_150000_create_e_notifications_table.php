<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type', 50)->index(); // category, post, media, settings
            $table->string('action', 50); // created, updated, deleted
            $table->string('title');
            $table->text('message')->nullable();
            $table->json('data')->nullable();
            $table->string('permission', 100)->index(); // Required permission to view
            $table->boolean('is_read')->default(false)->index();
            $table->timestamps();

            $table->index(['permission', 'is_read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_notifications');
    }
};
