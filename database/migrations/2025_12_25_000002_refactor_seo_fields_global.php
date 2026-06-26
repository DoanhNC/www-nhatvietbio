<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add new columns
        Schema::table('e_posts', function (Blueprint $table) {
            $table->string('slug', 500)->nullable()->after('titles');
            $table->string('seo_title', 255)->nullable()->after('seo_keywords');
            $table->text('seo_description')->nullable()->after('seo_title');
        });

        // Step 2: Migrate data from JSON to single fields
        DB::table('e_posts')->orderBy('id')->chunk(100, function ($posts) {
            foreach ($posts as $post) {
                $updates = [];

                // Migrate slugs -> slug (prefer 'vi', then first available)
                if ($post->slugs) {
                    $slugs = json_decode($post->slugs, true);
                    if (is_array($slugs)) {
                        $updates['slug'] = $slugs['vi'] ?? reset($slugs) ?: null;
                    }
                }

                // Migrate seo_titles -> seo_title
                if ($post->seo_titles) {
                    $seoTitles = json_decode($post->seo_titles, true);
                    if (is_array($seoTitles)) {
                        $updates['seo_title'] = $seoTitles['vi'] ?? reset($seoTitles) ?: null;
                    }
                }

                // Migrate seo_descriptions -> seo_description
                if ($post->seo_descriptions) {
                    $seoDescs = json_decode($post->seo_descriptions, true);
                    if (is_array($seoDescs)) {
                        $updates['seo_description'] = $seoDescs['vi'] ?? reset($seoDescs) ?: null;
                    }
                }

                // Migrate seo_keywords -> seo_keywords (convert array to comma-separated)
                if ($post->seo_keywords) {
                    $keywords = json_decode($post->seo_keywords, true);
                    if (is_array($keywords)) {
                        // Get 'vi' keywords or first available
                        $kwArray = $keywords['vi'] ?? reset($keywords) ?: [];
                        if (is_array($kwArray)) {
                            $updates['seo_keywords'] = implode(', ', $kwArray);
                        }
                    }
                }

                if (!empty($updates)) {
                    DB::table('e_posts')->where('id', $post->id)->update($updates);
                }
            }
        });

        // Step 3: Drop old JSON columns
        Schema::table('e_posts', function (Blueprint $table) {
            $table->dropColumn(['slugs', 'seo_titles', 'seo_descriptions']);
        });

        // Step 4: Rename seo_keywords column (now contains comma-separated string)
        // Already updated in-place, just need to change column type
        Schema::table('e_posts', function (Blueprint $table) {
            $table->string('seo_keywords', 500)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Recreate old columns
        Schema::table('e_posts', function (Blueprint $table) {
            $table->json('slugs')->nullable()->after('titles');
            $table->json('seo_titles')->nullable();
            $table->json('seo_descriptions')->nullable();
        });

        // Migrate data back (simplified - just uses 'vi' key)
        DB::table('e_posts')->orderBy('id')->chunk(100, function ($posts) {
            foreach ($posts as $post) {
                $updates = [];

                if ($post->slug) {
                    $updates['slugs'] = json_encode(['vi' => $post->slug]);
                }
                if ($post->seo_title) {
                    $updates['seo_titles'] = json_encode(['vi' => $post->seo_title]);
                }
                if ($post->seo_description) {
                    $updates['seo_descriptions'] = json_encode(['vi' => $post->seo_description]);
                }
                if ($post->seo_keywords) {
                    $keywords = array_map('trim', explode(',', $post->seo_keywords));
                    $updates['seo_keywords'] = json_encode(['vi' => $keywords]);
                }

                if (!empty($updates)) {
                    DB::table('e_posts')->where('id', $post->id)->update($updates);
                }
            }
        });

        // Drop new columns
        Schema::table('e_posts', function (Blueprint $table) {
            $table->dropColumn(['slug', 'seo_title', 'seo_description']);
        });

        // Change seo_keywords back to JSON
        Schema::table('e_posts', function (Blueprint $table) {
            $table->json('seo_keywords')->nullable()->change();
        });
    }
};
