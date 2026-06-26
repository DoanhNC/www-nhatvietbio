<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('e_languages', function (Blueprint $table) {
            $table->string('flag_icon', 500)->nullable()->after('flag')->comment('URL to flag icon image');
        });
    }

    public function down(): void
    {
        Schema::table('e_languages', function (Blueprint $table) {
            $table->dropColumn('flag_icon');
        });
    }
};
