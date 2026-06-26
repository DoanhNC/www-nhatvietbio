<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add translations_hash column
        Schema::table('e_languages', function (Blueprint $table) {
            $table->string('translations_hash', 64)->nullable()->after('translations')->comment('MD5 hash of translations for caching');
        });

        // Update existing records with hash
        $languages = DB::table('e_languages')->get();
        foreach ($languages as $language) {
            $translations = json_decode($language->translations, true) ?? [];
            $hash = md5(json_encode($translations, JSON_UNESCAPED_UNICODE));
            DB::table('e_languages')->where('id', $language->id)->update(['translations_hash' => $hash]);
        }
    }

    public function down(): void
    {
        Schema::table('e_languages', function (Blueprint $table) {
            $table->dropColumn('translations_hash');
        });
    }
};
