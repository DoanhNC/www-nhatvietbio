<?php
// routes/web.php
use Illuminate\Support\Facades\Route;
// controller liên quan đến giao diện web
use App\Http\Controllers\Web\{
    HomeController
};
// controller liên quan đến giao diện quản trị admin
use App\Http\Controllers\Admin\{
    AuthAdminController,
    DashboardController,
    GroupsController,
    MediaController,
    PostCategoryController,
    PostController,
    SettingsController,
    SlideController,
    UsersController,
    VideoController
};

// Slide API Controller
use App\Http\Controllers\Api\SlideApiController;
use App\Http\Controllers\Api\VideoApiController;

// controller liên quan đến REST API quản trị
use App\Http\Controllers\Rest\{
    CkUploadController,
    EmailTemplatesController,
    GroupsRestController,
    LanguagesController,
    MediaFoldersController,
    MediaFilesController,
    MediaLogsController,
    MediaSettingsController,
    MediaTrashController,
    NotificationsController,
    PostCategoriesController,
    PostsController,
    UserActivityController,
    UsersRestController,
    WebsiteSettingsController
};

// --------------------------------------Route BMW Technology-------------------------------------------------------------------------------
// Trang chủ - Web theme (Laravel Blade)
Route::get('/', [HomeController::class, 'index'])->name('bmw.home');
// Danh sách tin tức
Route::get('/news', [HomeController::class, 'news'])->name('bmw.news');
// Trang danh mục
Route::get('/category/{slugOrId}', [HomeController::class, 'category'])->name('bmw.category');
// Chi tiết tin tức
Route::get('/news/{id}', [HomeController::class, 'newsDetail'])->name('bmw.news.detail');
// Gửi form liên hệ
Route::post('/contact', [HomeController::class, 'sendContact'])->name('bmw.contact.send');

// Public API - Translations for frontend (no auth required)
Route::get('/api/translations', [LanguagesController::class, 'getTranslations'])->name('api.translations');

// Change language route
Route::get('/change-language/{code}', function ($code) {
    $language = \App\Models\ELanguage::where('code', $code)->where('is_active', true)->first();
    if ($language) {
        session(['locale' => $code]);
        app()->setLocale($code);
    }
    return redirect()->back();
})->name('change.language');


// --------------------------------------Route quản trị admin--------------------------------------------------------------------------------
// giao diện trang login
Route::get('/admin/login', [AuthAdminController::class, 'loginView'])->name('admin.login');
// thực hiện đăng nhập
Route::post('/admin/login',    [AuthAdminController::class, 'loginSubmit']);

// Quên mật khẩu
Route::get('/admin/forgot-password', [AuthAdminController::class, 'forgotPasswordView'])->name('admin.forgot_password');
Route::post('/admin/forgot-password', [AuthAdminController::class, 'sendResetLink']);
Route::get('/admin/reset-password/{token}', [AuthAdminController::class, 'resetPasswordView'])->name('admin.reset_password');
Route::post('/admin/reset-password', [AuthAdminController::class, 'resetPassword']);

// thực hiện đăng xuất và đổi mật khẩu
Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/logout', [AuthAdminController::class, 'logout']);
    Route::post('/admin/change-password', [AuthAdminController::class, 'changePassword']);
});

// dùng middleware để bảo vệ các route admin => được cấu hình trong config/auth.php
// bảo vệ bằng guard auth theo session
Route::middleware('auth:admin')->group(function () {
    // Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Post Categories
    Route::get('/admin/post-categories',           [PostCategoryController::class, 'index'])->name('admin.post_categories');
    Route::get('/admin/post-categories/create',    [PostCategoryController::class, 'create'])->name('admin.post_categories.create');
    Route::get('/admin/post-categories/{id}/edit', [PostCategoryController::class, 'edit'])->name('admin.post_categories.edit');

    // Posts
    Route::get('/admin/posts',           [PostController::class, 'index'])->name('admin.posts');
    Route::get('/admin/posts/create',    [PostController::class, 'create'])->name('admin.posts.create');
    Route::get('/admin/posts/{id}/edit', [PostController::class, 'edit'])->name('admin.posts.edit');

    // Media
    Route::get('/admin/media', [MediaController::class, 'index'])->name('admin.media');

    // Slides (no permission required)
    Route::get('/admin/slides', [SlideController::class, 'index'])->name('admin.slides');

    // Videos (no permission required)
    Route::get('/admin/videos', [VideoController::class, 'index'])->name('admin.videos');

    // ============ ROOT ADMIN ONLY ROUTES ============
    Route::middleware('root_admin')->group(function () {
        // Users Management
        Route::get('/admin/users', [UsersController::class, 'index'])->name('admin.users');
        Route::get('/admin/users/create', [UsersController::class, 'create'])->name('admin.users.create');
        Route::get('/admin/users/{id}/edit', [UsersController::class, 'edit'])->name('admin.users.edit');

        // Groups Management
        Route::get('/admin/groups', [GroupsController::class, 'index'])->name('admin.groups');
        Route::get('/admin/groups/create', [GroupsController::class, 'create'])->name('admin.groups.create');
        Route::get('/admin/groups/{id}/edit', [GroupsController::class, 'edit'])->name('admin.groups.edit');

        // Settings - unified system settings page (ROOT ONLY)
        Route::get('/admin/settings', [SettingsController::class, 'system'])->name('admin.settings');
        Route::get('/admin/settings/system', [SettingsController::class, 'system'])->name('admin.settings.system');
        Route::redirect('/admin/settings/languages', '/admin/settings/system');
    });

    // ============ PERMISSION-BASED SETTINGS ROUTES ============
    // Website Settings (requires settings.view permission)
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/admin/settings/website', [SettingsController::class, 'website'])->name('admin.settings.website');
    });

    // Partials (for directive templates)
    Route::get('/admin/partials/media-picker', function () {
        return view('admin.partials.media-picker');
    });

    // route liên quan đến quản trị REST API
    Route::prefix('/admin/v1/rest')->group(function () {
        // CKEditor upload
        Route::post('uploads/ckeditor', [CkUploadController::class, 'ckeditor']);

        // User Activity Stats (for dashboard)
        Route::get('user-activity/stats', [UserActivityController::class, 'stats']);

        // ============ NOTIFICATIONS ============
        Route::get('notifications', [NotificationsController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationsController::class, 'unreadCount']);
        Route::post('notifications/{id}/read', [NotificationsController::class, 'markAsRead']);
        Route::post('notifications/read-all', [NotificationsController::class, 'markAllAsRead']);

        // ============ POST CATEGORIES ============
        // View routes (need categories.view)
        Route::middleware('permission:categories.view')->group(function () {
            Route::get('post-categories', [PostCategoriesController::class, 'index']);
            Route::get('post-categories/dropdown', [PostCategoriesController::class, 'dropdown']);
            Route::get('post-categories/menu-tree', [PostCategoriesController::class, 'menuTree']);
            Route::get('post-categories/{id}', [PostCategoriesController::class, 'show']);
        });
        // Manage routes (need categories.manage - create/edit/delete)
        Route::middleware('permission:categories.manage')->group(function () {
            Route::post('post-categories', [PostCategoriesController::class, 'store']);
            Route::put('post-categories/{id}', [PostCategoriesController::class, 'update']);
            Route::patch('post-categories/{id}', [PostCategoriesController::class, 'update']);
            Route::post('post-categories/positions', [PostCategoriesController::class, 'updatePositions']);
            Route::delete('post-categories/{id}', [PostCategoriesController::class, 'destroy']);
        });

        // ============ POSTS ============
        // View routes (need posts.view)
        Route::middleware('permission:posts.view')->group(function () {
            Route::get('posts', [PostsController::class, 'index']);
            Route::get('posts/stats', [PostsController::class, 'stats']);
            Route::get('posts/top-viewed', [PostsController::class, 'topViewed']);
            Route::get('posts/{id}', [PostsController::class, 'show']);
        });
        // Manage routes (need posts.manage - create/edit/delete)
        Route::middleware('permission:posts.manage')->group(function () {
            Route::post('posts', [PostsController::class, 'store']);
            Route::put('posts/{id}', [PostsController::class, 'update']);
            Route::patch('posts/{id}', [PostsController::class, 'update']);
            Route::delete('posts/{id}', [PostsController::class, 'destroy']);
        });

        // Languages
        Route::post('languages/convert-php', [LanguagesController::class, 'convertPhpToJson']);
        Route::apiResource('languages', LanguagesController::class);
        Route::post('languages/{id}/default', [LanguagesController::class, 'setDefault']);
        Route::post('languages/positions', [LanguagesController::class, 'updatePositions']);

        // Media - Tree route must be BEFORE apiResource to avoid conflicts
        Route::get('media/folders/tree', [MediaFoldersController::class, 'tree']);
        Route::apiResource('media/folders', MediaFoldersController::class);
        Route::apiResource('media/files', MediaFilesController::class);
        Route::get('media/logs', [MediaLogsController::class, 'index']);
        Route::get('media/settings', [MediaSettingsController::class, 'show']);
        Route::put('media/settings', [MediaSettingsController::class, 'update']);

        // Slides API (no permission required)
        Route::get('slides', [SlideApiController::class, 'index']);
        Route::post('slides', [SlideApiController::class, 'store']);
        Route::put('slides/{id}', [SlideApiController::class, 'update']);
        Route::delete('slides/{id}', [SlideApiController::class, 'destroy']);
        Route::post('slides/reorder', [SlideApiController::class, 'reorder']);

        // Videos API (no permission required)
        Route::get('videos', [VideoApiController::class, 'index']);
        Route::post('videos', [VideoApiController::class, 'store']);
        Route::put('videos/{id}', [VideoApiController::class, 'update']);
        Route::delete('videos/{id}', [VideoApiController::class, 'destroy']);
        Route::post('videos/reorder', [VideoApiController::class, 'reorder']);

        // Media Trash
        Route::get('media/trash', [MediaTrashController::class, 'index']);
        Route::post('media/trash/{type}/{id}/restore', [MediaTrashController::class, 'restore']);
        Route::delete('media/trash/{type}/{id}', [MediaTrashController::class, 'forceDelete']);
        Route::delete('media/trash', [MediaTrashController::class, 'emptyTrash']);

        // ============ ROOT ADMIN ONLY API ROUTES ============
        Route::middleware('root_admin')->group(function () {
            // Users API
            Route::apiResource('users', UsersRestController::class);

            // Groups API
            Route::get('groups/permissions', [GroupsRestController::class, 'permissions']);
            Route::apiResource('groups', GroupsRestController::class);
        });

        // ============ WEBSITE SETTINGS API ROUTES ============
        // View routes (need settings.view)
        Route::middleware('permission:settings.view')->group(function () {
            Route::get('website-settings', [WebsiteSettingsController::class, 'index']);
            Route::get('website-settings/analytics', [WebsiteSettingsController::class, 'getAnalytics']);
            Route::get('website-settings/theme-colors', [WebsiteSettingsController::class, 'getThemeColors']);
            Route::get('email-templates', [EmailTemplatesController::class, 'index']);
            Route::get('email-templates/{id}', [EmailTemplatesController::class, 'show']);
        });
        // Manage routes (need settings.manage)
        Route::middleware('permission:settings.manage')->group(function () {
            Route::put('website-settings', [WebsiteSettingsController::class, 'updateWebsite']);
            Route::post('website-settings/logo', [WebsiteSettingsController::class, 'updateLogo']);
            Route::post('website-settings/favicon', [WebsiteSettingsController::class, 'updateFavicon']);
            Route::put('website-settings/smtp', [WebsiteSettingsController::class, 'updateSmtp']);
            Route::post('website-settings/test-email', [WebsiteSettingsController::class, 'testEmail']);
            Route::put('website-settings/analytics', [WebsiteSettingsController::class, 'updateAnalytics']);
            Route::put('website-settings/theme-colors', [WebsiteSettingsController::class, 'updateThemeColors']);
            Route::put('email-templates/{id}', [EmailTemplatesController::class, 'update']);
            Route::post('email-templates/{id}/test', [EmailTemplatesController::class, 'test']);
        });
    });
});
