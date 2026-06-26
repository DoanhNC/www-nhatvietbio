<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EPost;
use App\Models\EPostCategory;
use App\Models\ELanguage;
use App\Models\ESetting;
use App\Models\ESlide;
use App\Models\EVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Trang chủ BMW
     */
    public function index()
    {
        // Lấy slides active từ database
        $slides = ESlide::with('media')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Lấy ngôn ngữ active
        $languages = ELanguage::getActiveLanguages();
        $currentLang = session('locale', 'vi');

        // Lấy 3 tin tức mới nhất từ danh mục "tin-tuc"
        $latestNews = collect();
        $newsCategory = EPostCategory::where('slug', 'tin-tuc')
            ->where('is_active', true)
            ->first();

        if ($newsCategory) {
            $latestNews = EPost::where('main_category_id', $newsCategory->id)
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Lấy videos active
        $videos = EVideo::getActiveVideos();

        // Lấy danh sách ứng dụng từ child categories của danh mục "ung-dung"
        $applicationCategories = collect();
        $ungDungCategory = EPostCategory::where('slug', 'ung-dung')
            ->where('is_active', true)
            ->first();

        if ($ungDungCategory) {
            $applicationCategories = EPostCategory::where('parent_id', $ungDungCategory->id)
                ->where('is_active', true)
                ->orderBy('position')
                ->get();
        }

        return view('web.home.index', compact('slides', 'languages', 'currentLang', 'latestNews', 'videos', 'applicationCategories'));
    }

    /**
     * Danh sách tin tức
     */
    public function news()
    {
        return view('web.home.news');
    }

    /**
     * Chi tiết tin tức
     * @param string $slugOrId - Có thể là slug hoặc ID
     */
    public function newsDetail($slugOrId)
    {
        // Tìm bài viết theo slug hoặc ID
        $post = EPost::with('mainCategory')
            ->where('status', 1)
            ->where(function ($query) use ($slugOrId) {
                $query->where('slug', $slugOrId)
                      ->orWhere('id', $slugOrId);
            })
            ->first();

        if (!$post) {
            abort(404, 'Bài viết không tồn tại');
        }

        // Tăng lượt xem
        $post->incrementViewCount();

        // Lấy ngôn ngữ hiện tại (từ session hoặc default)
        $currentLang = session('locale', 'vi');

        // Lấy TOC nếu bật
        $toc = $post->getToc($currentLang);

        // Lấy bài viết liên quan và sidebar categories nếu danh mục có bật setting
        $latestPosts = collect();
        $relatedPosts = collect();
        $sidebarCategories = collect();
        $showSidebar = false;

        // ========== XÁC ĐỊNH DANH MỤC CHÍNH ==========
        $mainCatId = $post->main_category_id;
        $postCategory = $post->mainCategory;

        // Tìm danh mục chính (parent category)
        if ($postCategory && $postCategory->parent_id) {
            $mainParentCategory = EPostCategory::find($postCategory->parent_id);
        } else {
            $mainParentCategory = $postCategory;
        }

        $childCategories = [];
        $allCategoryIds = [];

        // ========== PHẦN 1: BÀI VIẾT CÙNG DANH MỤC (chỉ hiển thị nếu danh mục chính cho phép) ==========
        if ($mainParentCategory && $mainParentCategory->show_related_posts) {
            $showSidebar = true;

            // Lấy tất cả danh mục con của danh mục chính
            $childCategories = EPostCategory::where('parent_id', $mainParentCategory->id)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();

            // Thêm cả danh mục chính vào danh sách
            $allCategoryIds = array_merge([$mainParentCategory->id], $childCategories);

            // Lấy bài viết từ danh mục cha và các danh mục con
            $latestPosts = EPost::where('id', '!=', $post->id)
                ->where('status', 1)
                ->whereIn('main_category_id', $allCategoryIds)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();

            // Lấy các danh mục parent có show_related_posts để hiển thị sidebar
            $sidebarCategories = EPostCategory::whereNull('parent_id')
                ->where('is_active', true)
                ->where('show_related_posts', true)
                ->orderBy('position')
                ->with(['children' => function ($q) {
                    $q->where('is_active', true)->orderBy('position');
                }])
                ->get();
        }

        // ========== PHẦN 2: BÀI VIẾT LIÊN QUAN (luôn hiển thị nếu bài viết có danh mục liên quan) ==========
        // Lấy danh sách các danh mục liên quan của bài viết (trường categories)
        $relatedCategoryIds = $post->getRawOriginal('categories');
        if (is_string($relatedCategoryIds)) {
            $relatedCategoryIds = json_decode($relatedCategoryIds, true) ?? [];
        }
        if (!is_array($relatedCategoryIds)) {
            $relatedCategoryIds = [];
        }

        // Loại bỏ danh mục đã hiển thị ở phần "Bài viết cùng danh mục" (nếu có)
        if (!empty($allCategoryIds)) {
            $relatedCategoryIds = array_diff($relatedCategoryIds, $allCategoryIds);
        }

        // Lấy thêm các danh mục con của các danh mục liên quan
        if (!empty($relatedCategoryIds)) {
            $relatedChildCatIds = EPostCategory::whereIn('parent_id', $relatedCategoryIds)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
            $relatedCategoryIds = array_unique(array_merge($relatedCategoryIds, $relatedChildCatIds));
        }

        // Lấy bài viết từ các danh mục liên quan
        if (!empty($relatedCategoryIds)) {
            $showSidebar = true; // Bật sidebar nếu có bài viết liên quan

            $relatedPosts = EPost::where('id', '!=', $post->id)
                ->where('status', 1)
                ->where(function ($query) use ($relatedCategoryIds) {
                    $query->whereIn('main_category_id', $relatedCategoryIds);
                    foreach ($relatedCategoryIds as $catId) {
                        $query->orWhereJsonContains('categories', $catId);
                    }
                })
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();
        }

        return view('web.home.news-detail', compact(
            'post',
            'currentLang',
            'toc',
            'latestPosts',
            'relatedPosts',
            'sidebarCategories',
            'showSidebar'
        ));
    }

    /**
     * Trang danh mục - hiển thị bài viết theo danh mục
     * @param string $slugOrId - Slug hoặc ID của danh mục
     */
    public function category($slugOrId)
    {
        // Tìm danh mục theo slug hoặc ID
        $category = EPostCategory::where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->where('is_active', true)
            ->first();

        if (!$category) {
            abort(404, 'Danh mục không tồn tại');
        }

        $currentLang = session('locale', 'vi');

        // Đếm số bài viết trong danh mục
        $postsCount = EPost::where('main_category_id', $category->id)
            ->where('status', 1)
            ->count();

        if ($postsCount === 0) {
            abort(404, 'Danh mục chưa có bài viết');
        }

        // Lấy bài viết mới nhất
        $post = EPost::where('main_category_id', $category->id)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->first();

        // Tăng lượt xem
        $post->incrementViewCount();

        // Lấy TOC nếu bật
        $toc = $post->getToc($currentLang);

        // Xác định hiển thị sidebar
        $latestPosts = collect();
        $relatedPosts = collect();
        $sidebarCategories = collect();
        $showSidebar = false;

        // ========== XÁC ĐỊNH DANH MỤC CHÍNH ==========
        // Nếu danh mục hiện tại có parent_id → nó là danh mục con, lấy parent
        // Nếu không có parent_id → nó chính là danh mục chính
        if ($category->parent_id) {
            $mainParentCategory = EPostCategory::find($category->parent_id);
        } else {
            $mainParentCategory = $category;
        }

        $childCategories = [];
        $allCategoryIds = [];

        // ========== PHẦN 1: BÀI VIẾT CÙNG DANH MỤC (chỉ hiển thị nếu danh mục chính cho phép) ==========
        if ($mainParentCategory && $mainParentCategory->show_related_posts) {
            $showSidebar = true;

            // Lấy tất cả danh mục con của danh mục chính
            $childCategories = EPostCategory::where('parent_id', $mainParentCategory->id)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();

            // Thêm cả danh mục chính vào danh sách
            $allCategoryIds = array_merge([$mainParentCategory->id], $childCategories);

            // Lấy bài viết từ danh mục cha và các danh mục con
            $latestPosts = EPost::where('id', '!=', $post->id)
                ->where('status', 1)
                ->whereIn('main_category_id', $allCategoryIds)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();

            // Lấy các danh mục parent có show_related_posts để hiển thị sidebar
            $sidebarCategories = EPostCategory::whereNull('parent_id')
                ->where('is_active', true)
                ->where('show_related_posts', true)
                ->orderBy('position')
                ->with(['children' => function ($q) {
                    $q->where('is_active', true)->orderBy('position');
                }])
                ->get();
        }

        // ========== PHẦN 2: BÀI VIẾT LIÊN QUAN (luôn hiển thị nếu bài viết có danh mục liên quan) ==========
        // Lấy danh sách các danh mục liên quan của bài viết (trường categories)
        $relatedCategoryIds = $post->getRawOriginal('categories');
        if (is_string($relatedCategoryIds)) {
            $relatedCategoryIds = json_decode($relatedCategoryIds, true) ?? [];
        }
        if (!is_array($relatedCategoryIds)) {
            $relatedCategoryIds = [];
        }

        // Loại bỏ danh mục đã hiển thị ở phần "Bài viết cùng danh mục" (nếu có)
        if (!empty($allCategoryIds)) {
            $relatedCategoryIds = array_diff($relatedCategoryIds, $allCategoryIds);
        }

        // Lấy thêm các danh mục con của các danh mục liên quan
        if (!empty($relatedCategoryIds)) {
            $relatedChildCatIds = EPostCategory::whereIn('parent_id', $relatedCategoryIds)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
            $relatedCategoryIds = array_unique(array_merge($relatedCategoryIds, $relatedChildCatIds));
        }

        // Lấy bài viết từ các danh mục liên quan
        if (!empty($relatedCategoryIds)) {
            $showSidebar = true; // Bật sidebar nếu có bài viết liên quan

            $relatedPosts = EPost::where('id', '!=', $post->id)
                ->where('status', 1)
                ->where(function ($query) use ($relatedCategoryIds) {
                    $query->whereIn('main_category_id', $relatedCategoryIds);
                    foreach ($relatedCategoryIds as $catId) {
                        $query->orWhereJsonContains('categories', $catId);
                    }
                })
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();
        }

        return view('web.home.news-detail', compact(
            'post',
            'currentLang',
            'toc',
            'latestPosts',
            'relatedPosts',
            'sidebarCategories',
            'showSidebar',
            'category'
        ));
    }

    /**
     * Xử lý form liên hệ - gửi email thông báo
     */
    public function sendContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:20',
            'email'   => 'nullable|email|max:255',
            'message' => 'nullable|string|max:2000',
        ], [
            'name.required'  => 'Vui lòng nhập họ tên.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'email.email'    => 'Email không đúng định dạng.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('contact_error', true);
        }

        try {
            // Apply SMTP config from database
            ESetting::applyMailConfig();

            // Get website info for recipient email
            $websiteInfo = ESetting::getWebsiteInfo();
            $toEmail = $websiteInfo['email'] ?? config('mail.from.address');
            $siteName = $websiteInfo['site_name'] ?? 'Website';

            $name    = $request->input('name');
            $phone   = $request->input('phone');
            $email   = $request->input('email', 'Không cung cấp');
            $msg     = $request->input('message', 'Không có lời nhắn');

            $content = "=== LIÊN HỆ MỚI TỪ WEBSITE ===\n\n"
                     . "Họ tên: {$name}\n"
                     . "Số điện thoại: {$phone}\n"
                     . "Email: {$email}\n"
                     . "Lời nhắn:\n{$msg}\n\n"
                     . "---\n"
                     . "Gửi từ: {$siteName}\n"
                     . "Thời gian: " . now()->format('d/m/Y H:i:s');

            Mail::raw($content, function ($message) use ($toEmail, $name, $email, $siteName) {
                $message->to($toEmail)
                    ->subject("[{$siteName}] Liên hệ mới từ: {$name}");

                // Nếu khách hàng có email, đặt reply-to để admin trả lời trực tiếp
                if ($email && $email !== 'Không cung cấp') {
                    $message->replyTo($email, $name);
                }
            });

            Log::info('Contact form sent', [
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
            ]);

            return redirect()->back()->with('contact_success', 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất.');

        } catch (\Exception $e) {
            Log::error('Contact form failed: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('contact_error', 'Gửi liên hệ thất bại. Vui lòng thử lại sau hoặc gọi trực tiếp hotline.');
        }
    }
}
