<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EPostCategory extends Model
{
    protected $table = 'e_post_categories';

    protected $fillable = [
        'parent_id',
        'names',
        'slug',
        'position',
        'is_active',
        'show_related_posts',
        'show_in_menu',
        'created_by',
    ];

    protected $casts = [
        'names' => 'array',
        'is_active' => 'boolean',
        'show_related_posts' => 'boolean',
        'show_in_menu' => 'boolean',
        'position' => 'integer',
    ];

    /**
     * Get the parent category
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get child categories
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->withCount(['posts' => function ($q) {
                $q->where('status', 1);
            }])
            ->orderBy('position');
    }

    /**
     * Get all descendants (recursive)
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get posts in this category
     */
    public function posts()
    {
        return $this->hasMany(EPost::class, 'main_category_id');
    }

    /**
     * Get name for a specific language code
     */
    public function getName(string $langCode, ?string $fallback = null): string
    {
        $names = $this->names ?? [];

        // Try requested language first
        if (!empty($names[$langCode])) {
            return $names[$langCode];
        }

        // Try fallback
        if ($fallback && !empty($names[$fallback])) {
            return $names[$fallback];
        }

        // Try default language
        $defaultLang = ELanguage::getDefault();
        if ($defaultLang && !empty($names[$defaultLang->code])) {
            return $names[$defaultLang->code];
        }

        // Return first available name
        return array_values($names)[0] ?? '';
    }

    /**
     * Set name for a specific language code
     */
    public function setName(string $langCode, string $value): void
    {
        $names = $this->names ?? [];
        $names[$langCode] = $value;
        $this->names = $names;
    }

    /**
     * Get root categories (no parent)
     */
    public static function getRootCategories()
    {
        return self::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('position')
            ->get();
    }

    /**
     * Get categories as tree structure for menu
     */
    public static function getTreeForMenu(string $langCode = 'vi'): array
    {
        $rootCategories = self::with('descendants')
            ->withCount(['posts' => function ($q) {
                $q->where('status', 1);
            }])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('show_in_menu', true)
                    ->orWhereNull('show_in_menu');
            })
            ->orderBy('position')
            ->get();

        return self::buildMenuTree($rootCategories, $langCode);
    }

    /**
     * Build menu tree recursively
     */
    private static function buildMenuTree($categories, string $langCode): array
    {
        $tree = [];
        foreach ($categories as $category) {
            // Đệ quy lấy cây danh mục con trước
            $childTree = [];
            if ($category->children->count() > 0) {
                $childTree = self::buildMenuTree($category->children, $langCode);
            }

            // Một danh mục chỉ được hiện nếu:
            // 1. Bản thân nó có ít nhất 1 bài viết đã xuất bản (status = 1)
            // 2. HOẶC nó có danh mục con thỏa mãn điều kiện trên (childTree không rỗng)
            if ($category->posts_count > 0 || count($childTree) > 0) {
                $item = [
                    'id' => $category->id,
                    'name' => $category->getName($langCode),
                    'slug' => $category->slug,
                    'children' => $childTree,
                ];
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * Get flat list with indentation for admin dropdowns
     */
    public static function getFlatListForDropdown(string $langCode = 'vi', ?int $excludeId = null): array
    {
        $result = [];
        $rootCategories = self::with('descendants')
            ->whereNull('parent_id')
            ->orderBy('position')
            ->get();

        self::flattenForDropdown($rootCategories, $langCode, $result, 0, $excludeId);
        return $result;
    }

    private static function flattenForDropdown($categories, string $langCode, array &$result, int $level, ?int $excludeId): void
    {
        foreach ($categories as $category) {
            if ($excludeId && $category->id === $excludeId) continue;

            $prefix = str_repeat('— ', $level);
            $result[] = [
                'id' => $category->id,
                'name' => $prefix . $category->getName($langCode),
                'level' => $level,
            ];

            if ($category->children->count() > 0) {
                self::flattenForDropdown($category->children, $langCode, $result, $level + 1, $excludeId);
            }
        }
    }
}
