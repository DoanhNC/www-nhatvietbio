<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify action_type ENUM to include new trash-related actions
        DB::statement("ALTER TABLE `e_media_logs` MODIFY COLUMN `action_type` 
            ENUM('upload', 'create_folder', 'rename', 'move', 'delete', 'restore', 'force_delete', 'empty_trash') 
            NOT NULL COMMENT 'Loại hành động'");
    }

    public function down(): void
    {
        // Revert to original ENUM values
        DB::statement("ALTER TABLE `e_media_logs` MODIFY COLUMN `action_type` 
            ENUM('upload', 'create_folder', 'rename', 'move', 'delete') 
            NOT NULL COMMENT 'Loại hành động'");
    }
};
