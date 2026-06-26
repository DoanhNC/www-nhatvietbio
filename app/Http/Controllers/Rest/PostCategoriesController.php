<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Models\EPostCategory;
use App\Models\EPost;
use App\Models\ELanguage;
use App\Services\NotificationService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostCategoriesController extends Controller
{
    /**
     * Get all categories with optional tree structure
     */
    public function index(Request $request)
    {
        $filter = json_decode($request->query('filter', '[]'), true);
        $keyword = data_get($filter, 'keyword');
        $orderby = data_get($filter, 'orderby', 'position');
        $order = strtolower(data_get($filter, 'order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage = (int) data_get($filter, 'per_page', 50);
        $treeMode = $request->boolean('tree', false);

        $sortable = ['id', 'position', 'created_at', 'updated_at'];
        if (!in_array($orderby, $sortable, true)) $orderby = 'position';

        $q = EPostCategory::with(['parent', 'children', 'creator']);

        if ($keyword) {
            // Search in JSON names column - use JSON functions for each active language (case-insensitive)
            $activeLangs = ELanguage::where('is_active', true)->pluck('code')->toArray();
            $keywordLower = mb_strtolower($keyword);
            $q->where(function ($query) use ($keywordLower, $activeLangs) {
                foreach ($activeLangs as $langCode) {
                    $query->orWhereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(names, ?))) LIKE ?",
                        ['$.' . $langCode, '%' . $keywordLower . '%']
                    );
                }
            });
        }

        if ($treeMode) {
            // Return tree structure for menus
            $q->whereNull('parent_id')->orderBy($orderby, $order);

            // Load children with filter if keyword exists
            if ($keyword) {
                $keywordLower = mb_strtolower($keyword);
                $q->with(['children' => function ($query) use ($keywordLower, $activeLangs, $orderby, $order) {
                    $query->where(function ($q) use ($keywordLower, $activeLangs) {
                        foreach ($activeLangs as $langCode) {
                            $q->orWhereRaw(
                                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(names, ?))) LIKE ?",
                                ['$.' . $langCode, '%' . $keywordLower . '%']
                            );
                        }
                    })->orderBy($orderby, $order);
                }]);
            } else {
                $q->with(['children' => function ($query) use ($orderby, $order) {
                    $query->orderBy($orderby, $order);
                }]);
            }

            $rows = $q->get();

            // Get active languages for header
            $languages = ELanguage::where('is_active', true)->orderBy('position')->get(['id', 'code', 'name']);

            return response()->json([
                'data' => $rows,
                'languages' => $languages,
            ]);
        }

        $q->orderBy($orderby, $order);
        $rows = $q->paginate($perPage)->appends($request->all());

        // Add active languages for dynamic columns
        $languages = ELanguage::where('is_active', true)->orderBy('position')->get(['id', 'code', 'name']);

        return response()->json([
            'data' => $rows->items(),
            'total' => $rows->total(),
            'current_page' => $rows->currentPage(),
            'last_page' => $rows->lastPage(),
            'per_page' => $rows->perPage(),
            'languages' => $languages,
        ]);
    }

    /**
     * Create new category
     */
    public function store(Request $r)
    {
        // Get active languages
        $languages = ELanguage::where('is_active', true)->get();
        $defaultLang = ELanguage::getDefault();

        $data = $r->validate([
            'parent_id' => ['nullable', 'exists:e_post_categories,id'],
            'names' => ['required', 'array'],
            'slug' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'show_in_menu' => ['nullable', 'boolean'],
            'show_related_posts' => ['nullable', 'boolean'],
        ], [
            'names.required' => 'Vui lòng nhập tên danh mục',
        ]);

        // Validate at least default language has name
        if ($defaultLang && empty($data['names'][$defaultLang->code])) {
            return ApiResponse::error("Vui lòng nhập tên cho ngôn ngữ mặc định ({$defaultLang->name})", 422);
        }

        // Generate slug from default language name if not provided
        if (empty($data['slug'])) {
            $defaultName = $defaultLang ? ($data['names'][$defaultLang->code] ?? '') : array_values($data['names'])[0] ?? '';
            $data['slug'] = Str::slug($defaultName);
        }

        // Set position
        $maxPosition = EPostCategory::where('parent_id', $data['parent_id'] ?? null)->max('position') ?? 0;
        $data['position'] = $maxPosition + 1;
        $data['created_by'] = Auth::id();
        $data['is_active'] = $data['is_active'] ?? true;

        $row = EPostCategory::create($data);
        $row->load(['parent', 'children', 'creator']);

        // Create notification
        $categoryName = $defaultLang ? ($data['names'][$defaultLang->code] ?? '') : array_values($data['names'])[0] ?? '';
        $dateStr = now()->format('d/m/Y H:i');
        NotificationService::notify(
            'category',
            'created',
            'Danh mục mới: ' . $categoryName,
            'Danh mục "' . $categoryName . '" đã được tạo lúc ' . $dateStr,
            ['id' => $row->id, 'name' => $categoryName]
        );

        return ApiResponse::success($row, 201);
    }

    /**
     * Get single category
     */
    public function show($id)
    {
        $category = EPostCategory::with(['parent', 'children', 'creator'])->findOrFail($id);
        $languages = ELanguage::where('is_active', true)->orderBy('position')->get(['id', 'code', 'name']);

        return response()->json([
            'data' => $category,
            'languages' => $languages,
        ]);
    }

    /**
     * Update category
     */
    public function update(Request $r, $id)
    {
        $row = EPostCategory::findOrFail($id);
        $defaultLang = ELanguage::getDefault();

        $data = $r->validate([
            'parent_id' => ['nullable', 'exists:e_post_categories,id'],
            'names' => ['required', 'array'],
            'slug' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'show_in_menu' => ['nullable', 'boolean'],
            'show_related_posts' => ['nullable', 'boolean'],
            'position' => ['nullable', 'integer'],
        ], [
            'names.required' => 'Vui lòng nhập tên danh mục',
        ]);

        // Prevent setting self as parent
        if (isset($data['parent_id']) && $data['parent_id'] == $id) {
            return ApiResponse::error('Không thể đặt danh mục làm cha của chính nó', 422);
        }

        // Validate at least default language has name
        if ($defaultLang && empty($data['names'][$defaultLang->code])) {
            return ApiResponse::error("Vui lòng nhập tên cho ngôn ngữ mặc định ({$defaultLang->name})", 422);
        }

        // Generate slug if empty
        if (empty($data['slug'])) {
            $defaultName = $defaultLang ? ($data['names'][$defaultLang->code] ?? '') : array_values($data['names'])[0] ?? '';
            $data['slug'] = Str::slug($defaultName);
        }

        $row->update($data);
        $row->load(['parent', 'children', 'creator']);

        // Create notification
        $categoryName = $defaultLang ? ($data['names'][$defaultLang->code] ?? '') : array_values($data['names'])[0] ?? '';
        $dateStr = now()->format('d/m/Y H:i');
        NotificationService::notify(
            'category',
            'updated',
            'Cập nhật danh mục: ' . $categoryName,
            'Danh mục "' . $categoryName . '" đã được cập nhật lúc ' . $dateStr,
            ['id' => $row->id, 'name' => $categoryName]
        );

        return ApiResponse::success($row);
    }

    /**
     * Delete category
     */
    public function destroy($id)
    {
        $row = EPostCategory::findOrFail($id);

        // Check for posts using this category
        if (EPost::where('main_category_id', $id)->exists()) {
            return ApiResponse::error('Không thể xoá danh mục: đã có bài viết sử dụng.', 422);
        }

        // Check for child categories
        if (EPostCategory::where('parent_id', $id)->exists()) {
            return ApiResponse::error('Không thể xoá danh mục: đang có danh mục con.', 422);
        }

        // Store name before delete
        $categoryName = $row->getName('vi') ?? 'Danh mục #' . $id;

        $row->delete();

        // Create notification
        $dateStr = now()->format('d/m/Y H:i');
        NotificationService::notify(
            'category',
            'deleted',
            'Xóa danh mục: ' . $categoryName,
            'Danh mục "' . $categoryName . '" đã bị xóa lúc ' . $dateStr,
            ['id' => $id, 'name' => $categoryName]
        );

        return ApiResponse::success('Xoá danh mục thành công.');
    }

    /**
     * Update positions for multiple categories
     */
    public function updatePositions(Request $r)
    {
        $data = $r->validate([
            'positions' => ['required', 'array'],
            'positions.*.id' => ['required', 'exists:e_post_categories,id'],
            'positions.*.position' => ['required', 'integer', 'min:0'],
            'positions.*.parent_id' => ['nullable', 'exists:e_post_categories,id'],
        ]);

        foreach ($data['positions'] as $item) {
            EPostCategory::where('id', $item['id'])->update([
                'position' => $item['position'],
                'parent_id' => $item['parent_id'] ?? null,
            ]);
        }

        return ApiResponse::success('Cập nhật thứ tự thành công.');
    }

    /**
     * Get categories for dropdown selection
     */
    public function dropdown(Request $request)
    {
        $langCode = $request->get('lang', 'vi');
        $excludeId = $request->get('exclude');

        $categories = EPostCategory::getFlatListForDropdown($langCode, $excludeId ? (int)$excludeId : null);

        return response()->json($categories);
    }

    /**
     * Get categories tree for frontend menu
     */
    public function menuTree(Request $request)
    {
        $langCode = $request->get('lang', 'vi');
        $tree = EPostCategory::getTreeForMenu($langCode);

        return response()->json($tree);
    }
}
