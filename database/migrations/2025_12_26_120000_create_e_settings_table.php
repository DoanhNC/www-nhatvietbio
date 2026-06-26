<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('e_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_group', 50)->index(); // website, email_smtp
            $table->string('setting_key', 100);
            $table->text('setting_value')->nullable();
            $table->timestamps();

            $table->unique(['setting_group', 'setting_key']);
        });

        // Seed default website settings
        $defaultSettings = [
            ['setting_group' => 'website', 'setting_key' => 'name', 'setting_value' => 'Website Name'],
            ['setting_group' => 'website', 'setting_key' => 'company', 'setting_value' => ''],
            ['setting_group' => 'website', 'setting_key' => 'hotline', 'setting_value' => ''],
            ['setting_group' => 'website', 'setting_key' => 'phone', 'setting_value' => ''],
            ['setting_group' => 'website', 'setting_key' => 'email', 'setting_value' => ''],
            ['setting_group' => 'website', 'setting_key' => 'address', 'setting_value' => ''],
            ['setting_group' => 'email_smtp', 'setting_key' => 'is_active', 'setting_value' => '0'],
            ['setting_group' => 'email_smtp', 'setting_key' => 'host', 'setting_value' => 'smtp.gmail.com'],
            ['setting_group' => 'email_smtp', 'setting_key' => 'port', 'setting_value' => '587'],
            ['setting_group' => 'email_smtp', 'setting_key' => 'username', 'setting_value' => ''],
            ['setting_group' => 'email_smtp', 'setting_key' => 'password', 'setting_value' => ''],
            ['setting_group' => 'email_smtp', 'setting_key' => 'encryption', 'setting_value' => 'tls'],
            ['setting_group' => 'email_smtp', 'setting_key' => 'from_name', 'setting_value' => ''],
            ['setting_group' => 'email_smtp', 'setting_key' => 'from_email', 'setting_value' => ''],
        ];

        $now = now();
        foreach ($defaultSettings as &$setting) {
            $setting['created_at'] = $now;
            $setting['updated_at'] = $now;
        }

        \DB::table('e_settings')->insert($defaultSettings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_settings');
    }
};
