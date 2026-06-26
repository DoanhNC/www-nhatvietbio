<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Models\EPost;
use App\Models\ELanguage;
use App\Services\NotificationService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PostsController extends Controller
{
    /**
     * List posts with filtering and pagination
     */
    public function index(Request $request)
    {
        $filter   = json_decode($request->query('filter', '[]'), true);
        $keyword  = data_get($filter, 'keyword');
        $status   = data_get($filter, 'status');
        $catId    = data_get($filter, 'category_id');
        $feat     = data_get($filter, 'is_featured');
        $orderby  = data_get($filter, 'orderby', 'id');
        $order    = strtolower(data_get($filter, 'order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage  = (int) data_get($filter, 'per_page', 20);

        $sortable = ['id', 'status', 'is_featured', 'position', 'view_count', 'created_at', 'updated_at'];
        if (!in_array($orderby, $sortable, true)) $orderby = 'id';

        $q = EPost::with('mainCategory');

        // Search in JSON titles and contents using LIKE (more reliable for partial match)
        if ($keyword) {
            $q->where(function ($x) use ($keyword) {
                $x->where('titles', 'LIKE', "%{$keyword}%")
                    ->orWhere('contents', 'LIKE', "%{$keyword}%")
                    ->orWhere('slug', 'LIKE', "%{$keyword}%");
            });
        }

        if ($status !== null && $status !== '') $q->where('status', (int)$status);
        if ($feat !== null && $feat !== '') $q->where('is_featured', (bool)$feat);

        // Filter by main category
        if ($catId !== null && $catId !== '') {
            $q->where('main_category_id', (int)$catId);
        }

        $q->orderBy($orderby, $order);
        $rows = $q->paginate($perPage)->appends($request->all());

        return response()->json($rows);
    }

    /**
     * Store a new post
     */
    public function store(Request $request)
    {
        // Pre-process: Convert categories to integers
        $this->preprocessCategories($request);

        $languages = ELanguage::where('is_active', true)->pluck('code')->toArray();

        // Build dynamic validation rules
        $rules = [
            'categories'         => ['nullable', 'array'],
            'categories.*'       => ['integer', 'exists:e_post_categories,id'],
            'main_category_id'   => ['nullable', 'integer', 'exists:e_post_categories,id'],
            'author_id'          => ['nullable', 'integer', 'exists:users,id'],
            'position'           => ['nullable', 'integer', 'min:0'],
            'view_count'         => ['nullable', 'integer', 'min:0'],
            'is_featured'        => ['nullable', 'boolean'],
            'show_toc'           => ['nullable', 'boolean'],
            'status'             => ['nullable', 'integer', 'in:0,1'],
            'main_image'         => ['nullable', 'string'],
            'album_images'       => ['nullable', 'array'],
            'tags'               => ['nullable', 'array', 'max:10'],
            'attachments'        => ['nullable', 'array'],
            'video_urls'         => ['nullable', 'array'],
            'related_posts'      => ['nullable', 'array'],
            'json_data'          => ['nullable'],
            // SEO fields (global)
            'slug'               => ['required', 'string', 'max:500'],
            'seo_title'          => ['nullable', 'string', 'max:255'],
            'seo_description'    => ['nullable', 'string'],
            'seo_keywords'       => ['nullable', 'string', 'max:500'],
        ];

        // Required for ALL active languages (content only)
        foreach ($languages as $lang) {
            $rules["titles.$lang"] = ['required', 'string', 'max:255'];
            $rules["short_descriptions.$lang"] = ['required', 'string'];
            $rules["contents.$lang"] = ['required', 'string'];
        }

        $data = $request->validate($rules);

        // Set defaults
        $data['created_by'] = Auth::id();
        $data['position'] = (int)($data['position'] ?? 0);
        $data['view_count'] = (int)($data['view_count'] ?? 0);
        $data['is_featured'] = (bool)($data['is_featured'] ?? false);
        $data['show_toc'] = (bool)($data['show_toc'] ?? false);
        $data['status'] = (int)($data['status'] ?? 1);

        // Set main_category_id to first category if not provided
        if (empty($data['main_category_id']) && !empty($data['categories'])) {
            $data['main_category_id'] = $data['categories'][0];
        }

        $row = EPost::create($data);

        // Create notification
        $defaultLang = ELanguage::getDefault();
        $postTitle = $defaultLang ? ($data['titles'][$defaultLang->code] ?? '') : array_values($data['titles'])[0] ?? '';
        $dateStr = now()->format('d/m/Y H:i');
        NotificationService::notify(
            'post',
            'created',
            'Bài viết mới: ' . mb_substr($postTitle, 0, 50),
            'Bài viết "' . $postTitle . '" đã được tạo lúc ' . $dateStr,
            ['id' => $row->id, 'title' => $postTitle]
        );

        return ApiResponse::success($row, 201);
    }

    /**
     * Show a single post
     */
    public function show($id)
    {
        return EPost::with(['mainCategory', 'author', 'creator'])->findOrFail($id);
    }

    /**
     * Update an existing post
     */
    public function update(Request $request, $id)
    {
        $row = EPost::findOrFail($id);

        // Pre-process: Convert categories to integers
        $this->preprocessCategories($request);

        $languages = ELanguage::where('is_active', true)->pluck('code')->toArray();

        // Build dynamic validation rules
        $rules = [
            'categories'         => ['nullable', 'array'],
            'categories.*'       => ['integer', 'exists:e_post_categories,id'],
            'main_category_id'   => ['nullable', 'integer', 'exists:e_post_categories,id'],
            'author_id'          => ['nullable', 'integer', 'exists:users,id'],
            'position'           => ['nullable', 'integer', 'min:0'],
            'view_count'         => ['nullable', 'integer', 'min:0'],
            'is_featured'        => ['nullable', 'boolean'],
            'show_toc'           => ['nullable', 'boolean'],
            'status'             => ['nullable', 'integer', 'in:0,1'],
            'main_image'         => ['nullable', 'string'],
            'album_images'       => ['nullable', 'array'],
            'tags'               => ['nullable', 'array', 'max:10'],
            'attachments'        => ['nullable', 'array'],
            'video_urls'         => ['nullable', 'array'],
            'related_posts'      => ['nullable', 'array'],
            'json_data'          => ['nullable'],
            // SEO fields (global)
            'slug'               => ['required', 'string', 'max:500'],
            'seo_title'          => ['nullable', 'string', 'max:255'],
            'seo_description'    => ['nullable', 'string'],
            'seo_keywords'       => ['nullable', 'string', 'max:500'],
        ];

        // Required for ALL active languages (content only)
        foreach ($languages as $lang) {
            $rules["titles.$lang"] = ['required', 'string', 'max:255'];
            $rules["short_descriptions.$lang"] = ['required', 'string'];
            $rules["contents.$lang"] = ['required', 'string'];
        }

        $data = $request->validate($rules);

        // Set main_category_id to first category if not provided
        if (empty($data['main_category_id']) && !empty($data['categories'])) {
            $data['main_category_id'] = $data['categories'][0];
        }

        $row->update($data);

        // Create notification
        $defaultLang = ELanguage::getDefault();
        $postTitle = $defaultLang ? ($data['titles'][$defaultLang->code] ?? '') : array_values($data['titles'])[0] ?? '';
        $dateStr = now()->format('d/m/Y H:i');
        NotificationService::notify(
            'post',
            'updated',
            'Cập nhật bài viết: ' . mb_substr($postTitle, 0, 50),
            'Bài viết "' . $postTitle . '" đã được cập nhật lúc ' . $dateStr,
            ['id' => $row->id, 'title' => $postTitle]
        );

        return ApiResponse::success($row);
    }

    /**
     * Delete a post
     */
    public function destroy($id)
    {
        $row = EPost::findOrFail($id);
        $defaultLang = ELanguage::getDefault();
        $postTitle = $row->getTitle($defaultLang->code ?? 'vi') ?? 'Bài viết #' . $id;

        $row->delete();

        // Create notification
        $dateStr = now()->format('d/m/Y H:i');
        NotificationService::notify(
            'post',
            'deleted',
            'Xóa bài viết: ' . mb_substr($postTitle, 0, 50),
            'Bài viết "' . $postTitle . '" đã bị xóa lúc ' . $dateStr,
            ['id' => $id, 'title' => $postTitle]
        );

        return ApiResponse::success('Xoá bài viết thành công.');
    }

    /**
     * Increment view count
     */
    public function incrementView($id)
    {
        $post = EPost::findOrFail($id);
        $post->incrementViewCount();
        return ApiResponse::success(['view_count' => $post->view_count]);
    }

    /**
     * Pre-process categories array to convert all items to integers.
     * Throws validation error if any item cannot be converted.
     */
    private function preprocessCategories(Request $request): void
    {
        $categories = $request->input('categories');

        if (!is_array($categories)) {
            return;
        }

        $converted = [];
        $errors = [];

        foreach ($categories as $index => $value) {
            // Try to parse to integer
            if (is_numeric($value)) {
                $converted[] = (int) $value;
            } elseif (is_string($value)) {
                // Try to extract number from string like "number:2"
                if (preg_match('/^number:(\d+)$/', $value, $matches)) {
                    $converted[] = (int) $matches[1];
                } elseif (ctype_digit($value)) {
                    $converted[] = (int) $value;
                } else {
                    $errors["categories.$index"] = ["Giá trị danh mục không hợp lệ: '$value'. Phải là số nguyên."];
                }
            } elseif (is_int($value)) {
                $converted[] = $value;
            } else {
                $errors["categories.$index"] = ["Giá trị danh mục không hợp lệ. Phải là số nguyên."];
            }
        }

        if (!empty($errors)) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                response()->json([
                    'message' => 'Dữ liệu danh mục không hợp lệ.',
                    'errors' => $errors,
                ], 422)
            );
        }

        // Replace the categories with converted integers
        $request->merge(['categories' => $converted]);
    }

    /**
     * Get post statistics for date range
     */
    public function stats(Request $request)
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        // Build query
        $q = EPost::query();

        // Only apply date filter if both dates are provided
        if ($fromDate && $toDate) {
            $q->whereDate('created_at', '>=', $fromDate);
            $q->whereDate('created_at', '<=', $toDate);
        }

        // Total count
        $total = (clone $q)->count();

        // By status
        $published = (clone $q)->where('status', 1)->count();
        $draft = (clone $q)->where('status', 0)->count();

        // By date (for chart if needed)
        $byDate = (clone $q)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'total' => $total,
            'published' => $published,
            'draft' => $draft,
            'by_date' => $byDate,
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);
    }

    /**
     * Get top viewed posts
     */
    public function topViewed(Request $request)
    {
        $limit = $request->get('limit', 5);
        $defaultLang = ELanguage::getDefault();
        $langCode = $defaultLang ? $defaultLang->code : 'vi';

        $posts = EPost::where('status', 1)
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get(['id', 'titles', 'view_count', 'created_at', 'slug']);

        $result = $posts->map(function ($post) use ($langCode) {
            return [
                'id' => $post->id,
                'title' => $post->getTitle($langCode) ?? 'Bài viết #' . $post->id,
                'view_count' => $post->view_count ?? 0,
                'created_at' => $post->created_at?->format('d/m/Y'),
                'url' => '/posts/' . $post->slug,
            ];
        });

        return ApiResponse::success($result);
    }
}
