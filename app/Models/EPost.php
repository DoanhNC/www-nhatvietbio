<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EPost extends Model
{
    protected $table = 'e_posts';

    protected $fillable = [
        // Tab 1: Thông tin cơ bản
        'titles',
        'categories',
        'main_category_id',
        'author_id',
        'position',
        'view_count',
        'is_featured',
        'show_toc',
        'status',
        'created_by',

        // Tab 2: Mô tả bài viết
        'main_image',
        'album_images',
        'short_descriptions',
        'contents',
        'tags',
        'attachments',
        'video_urls',

        // Tab 4: SEO (global fields)
        'slug',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'related_posts',

        // Tab 3: Thuộc tính mở rộng
        'json_data',
    ];

    protected $casts = [
        // Multi-language JSON fields
        'titles' => 'array',
        'short_descriptions' => 'array',
        'contents' => 'array',

        // SEO fields (global - simple strings)
        // 'slug', 'seo_title', 'seo_description', 'seo_keywords' are simple strings, no cast needed

        // Array fields
        'categories' => 'array',
        'tags' => 'array',
        'album_images' => 'array',
        'attachments' => 'array',
        'video_urls' => 'array',
        'related_posts' => 'array',
        'json_data' => 'array',

        // Boolean/Integer fields
        'position' => 'integer',
        'view_count' => 'integer',
        'is_featured' => 'boolean',
        'show_toc' => 'boolean',
        'status' => 'integer',
    ];

    // ========================================
    // Relationships
    // ========================================

    /**
     * Get the main category (for URL/breadcrumb)
     */
    public function mainCategory()
    {
        return $this->belongsTo(EPostCategory::class, 'main_category_id');
    }

    /**
     * Get the author
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all categories this post belongs to
     */
    public function getCategoriesAttribute($value)
    {
        $ids = is_array($value) ? $value : json_decode($value, true) ?? [];
        return EPostCategory::whereIn('id', $ids)->get();
    }

    /**
     * Get related posts
     */
    public function getRelatedPostsModels()
    {
        $ids = $this->related_posts ?? [];
        return self::whereIn('id', $ids)->where('status', 1)->get();
    }

    // ========================================
    // Multi-language Helpers (with fallback)
    // ========================================

    /**
     * Get title for a specific language with fallback
     */
    public function getTitle(string $lang): string
    {
        return $this->getLocalizedField('titles', $lang);
    }

    /**
     * Get slug for a specific language with fallback
     * @deprecated Use $this->slug directly
     */
    public function getSlug(string $lang = null): string
    {
        return $this->slug ?? '';
    }

    /**
     * Get short description for a specific language with fallback
     */
    public function getShortDescription(string $lang): string
    {
        return $this->getLocalizedField('short_descriptions', $lang);
    }

    /**
     * Get content for a specific language with fallback
     */
    public function getContent(string $lang): string
    {
        return $this->getLocalizedField('contents', $lang);
    }

    /**
     * Get SEO title (global, not language-specific)
     */
    public function getSeoTitle(): string
    {
        return $this->seo_title ?? '';
    }

    /**
     * Get SEO description (global, not language-specific)
     */
    public function getSeoDescription(): string
    {
        return $this->seo_description ?? '';
    }

    /**
     * Get SEO keywords as array (global, not language-specific)
     */
    public function getSeoKeywordsArray(): array
    {
        if (empty($this->seo_keywords)) return [];
        return array_map('trim', explode(',', $this->seo_keywords));
    }

    /**
     * Generic method to get localized field with fallback
     */
    protected function getLocalizedField(string $field, string $lang): string
    {
        $data = $this->$field ?? [];

        // Try requested language first
        if (!empty($data[$lang])) {
            return $data[$lang];
        }

        // Fallback to default language
        $default = ELanguage::getDefault();
        if ($default && !empty($data[$default->code])) {
            return $data[$default->code];
        }

        // Return first available value
        return array_values($data)[0] ?? '';
    }

    // ========================================
    // Setters for multi-language fields
    // ========================================

    /**
     * Set title for a specific language
     */
    public function setTitle(string $lang, string $value): void
    {
        $titles = $this->titles ?? [];
        $titles[$lang] = $value;
        $this->titles = $titles;
    }

    /**
     * Set content for a specific language
     */
    public function setContent(string $lang, string $value): void
    {
        $contents = $this->contents ?? [];
        $contents[$lang] = $value;
        $this->contents = $contents;
    }

    // ========================================
    // Scopes
    // ========================================

    /**
     * Scope for published posts only
     */
    public function scopePublished($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for featured posts only
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for posts in a specific category
     */
    public function scopeInCategory($query, int $categoryId)
    {
        return $query->whereJsonContains('categories', $categoryId);
    }

    // ========================================
    // Utilities
    // ========================================

    /**
     * Increment view count
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Check if post has content for all active languages
     */
    public function hasAllLanguages(): bool
    {
        $activeLanguages = ELanguage::where('is_active', true)->pluck('code');

        foreach ($activeLanguages as $lang) {
            if (empty($this->titles[$lang]) || empty($this->contents[$lang])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get missing languages for this post
     */
    public function getMissingLanguages(): array
    {
        $activeLanguages = ELanguage::where('is_active', true)->pluck('code')->toArray();
        $missing = [];

        foreach ($activeLanguages as $lang) {
            if (empty($this->titles[$lang]) || empty($this->contents[$lang])) {
                $missing[] = $lang;
            }
        }

        return $missing;
    }

    // ========================================
    // Table of Contents (TOC)
    // ========================================

    /**
     * Generate Table of Contents from content headings
     * Returns array of TOC items with id, text, level
     * 
     * @param string $lang Language code
     * @return array
     */
    public function getToc(string $lang): array
    {
        if (!$this->show_toc) {
            return [];
        }

        $content = $this->getContent($lang);
        if (empty($content)) {
            return [];
        }

        $toc = [];

        // Match h2, h3, h4 headings
        preg_match_all('/<h([2-4])[^>]*>(.*?)<\/h\1>/is', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $level = (int) $match[1];
            $text = strip_tags($match[2]);
            $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
            $text = trim($text);

            if (empty($text)) continue;

            // Generate slug from text
            $id = $this->generateTocSlug($text);

            $toc[] = [
                'id' => $id,
                'text' => $text,
                'level' => $level,
            ];
        }

        return $toc;
    }

    /**
     * Get content with TOC anchor IDs added to headings
     * Use this when rendering post content
     * 
     * @param string $lang Language code
     * @return string HTML content with anchor IDs
     */
    public function getContentWithTocAnchors(string $lang): string
    {
        $content = $this->getContent($lang);
        if (empty($content) || !$this->show_toc) {
            return $content;
        }

        // Add ID to each heading
        $content = preg_replace_callback(
            '/<h([2-4])([^>]*)>(.*?)<\/h\1>/is',
            function ($match) {
                $level = $match[1];
                $attrs = $match[2];
                $text = strip_tags($match[3]);
                $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
                $id = $this->generateTocSlug(trim($text));

                // If already has id, don't add
                if (preg_match('/\bid\s*=/i', $attrs)) {
                    return $match[0];
                }

                return "<h{$level} id=\"{$id}\"{$attrs}>{$match[3]}</h{$level}>";
            },
            $content
        );

        return $content;
    }

    /**
     * Generate URL-friendly slug from Vietnamese text
     */
    protected function generateTocSlug(string $text): string
    {
        // Vietnamese character map
        $vietnamese = [
            'à',
            'á',
            'ạ',
            'ả',
            'ã',
            'â',
            'ầ',
            'ấ',
            'ậ',
            'ẩ',
            'ẫ',
            'ă',
            'ằ',
            'ắ',
            'ặ',
            'ẳ',
            'ẵ',
            'è',
            'é',
            'ẹ',
            'ẻ',
            'ẽ',
            'ê',
            'ề',
            'ế',
            'ệ',
            'ể',
            'ễ',
            'ì',
            'í',
            'ị',
            'ỉ',
            'ĩ',
            'ò',
            'ó',
            'ọ',
            'ỏ',
            'õ',
            'ô',
            'ồ',
            'ố',
            'ộ',
            'ổ',
            'ỗ',
            'ơ',
            'ờ',
            'ớ',
            'ợ',
            'ở',
            'ỡ',
            'ù',
            'ú',
            'ụ',
            'ủ',
            'ũ',
            'ư',
            'ừ',
            'ứ',
            'ự',
            'ử',
            'ữ',
            'ỳ',
            'ý',
            'ỵ',
            'ỷ',
            'ỹ',
            'đ',
            'À',
            'Á',
            'Ạ',
            'Ả',
            'Ã',
            'Â',
            'Ầ',
            'Ấ',
            'Ậ',
            'Ẩ',
            'Ẫ',
            'Ă',
            'Ằ',
            'Ắ',
            'Ặ',
            'Ẳ',
            'Ẵ',
            'È',
            'É',
            'Ẹ',
            'Ẻ',
            'Ẽ',
            'Ê',
            'Ề',
            'Ế',
            'Ệ',
            'Ể',
            'Ễ',
            'Ì',
            'Í',
            'Ị',
            'Ỉ',
            'Ĩ',
            'Ò',
            'Ó',
            'Ọ',
            'Ỏ',
            'Õ',
            'Ô',
            'Ồ',
            'Ố',
            'Ộ',
            'Ổ',
            'Ỗ',
            'Ơ',
            'Ờ',
            'Ớ',
            'Ợ',
            'Ở',
            'Ỡ',
            'Ù',
            'Ú',
            'Ụ',
            'Ủ',
            'Ũ',
            'Ư',
            'Ừ',
            'Ứ',
            'Ự',
            'Ử',
            'Ữ',
            'Ỳ',
            'Ý',
            'Ỵ',
            'Ỷ',
            'Ỹ',
            'Đ'
        ];

        $ascii = [
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'i',
            'i',
            'i',
            'i',
            'i',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'y',
            'y',
            'y',
            'y',
            'y',
            'd',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'i',
            'i',
            'i',
            'i',
            'i',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'y',
            'y',
            'y',
            'y',
            'y',
            'd'
        ];

        $slug = str_replace($vietnamese, $ascii, $text);
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug ?: 'heading';
    }
}
