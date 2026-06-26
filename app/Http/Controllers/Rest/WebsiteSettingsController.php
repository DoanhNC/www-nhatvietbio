<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Models\ESetting;
use App\Services\NotificationService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class WebsiteSettingsController extends Controller
{
    /**
     * Get all settings
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => [
                'website' => ESetting::getWebsiteInfo(),
                'smtp' => $this->getSmtpForDisplay(),
                'logo' => ESetting::getLogo(),
                'favicon' => ESetting::getFavicon(),
            ],
        ]);
    }

    /**
     * Get SMTP settings (include password for display)
     */
    private function getSmtpForDisplay(): array
    {
        $smtp = ESetting::getEmailSmtp();
        return $smtp;
    }

    /**
     * Update website info settings
     */
    public function updateWebsite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'nullable|url|max:255',
            'name' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'hotline' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'working_hours' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'map_embed' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        ESetting::setGroup('website', $request->only([
            'url',
            'name',
            'company',
            'hotline',
            'phone',
            'email',
            'address',
            'working_hours',
            'description',
            'map_embed'
        ]));

        // Create notification
        NotificationService::notify(
            'settings',
            'updated',
            'Cập nhật cấu hình website',
            'Thông tin website đã được cập nhật',
            ['type' => 'website']
        );

        return ApiResponse::success(null, 200, 'Cập nhật thông tin website thành công');
    }

    /**
     * Update logo (từ Media Picker - nhận URL)
     */
    public function updateLogo(Request $request)
    {
        // Nếu có logo_url từ Media Picker
        if ($request->filled('logo_url')) {
            $logoUrl = $request->input('logo_url');

            // Lưu storage_path trực tiếp (đã là relative path từ upload folder)
            ESetting::setLogo($logoUrl);

            // Create notification
            NotificationService::notify(
                'settings',
                'updated',
                'Cập nhật logo website',
                'Logo website đã được cập nhật',
                ['type' => 'logo']
            );

            return ApiResponse::success([
                'logo' => ESetting::getLogo()
            ], 200, 'Cập nhật logo thành công');
        }

        // Nếu request xóa logo
        if ($request->input('remove_logo')) {
            ESetting::setLogo(null);

            return ApiResponse::success([
                'logo' => null
            ], 200, 'Đã xóa logo');
        }

        return ApiResponse::error('Không có logo được chọn', 422);
    }

    /**
     * Update favicon (từ Media Picker - nhận URL)
     */
    public function updateFavicon(Request $request)
    {
        // Nếu có favicon_url từ Media Picker
        if ($request->filled('favicon_url')) {
            $faviconUrl = $request->input('favicon_url');

            // Lưu storage_path trực tiếp
            ESetting::setFavicon($faviconUrl);

            // Create notification
            NotificationService::notify(
                'settings',
                'updated',
                'Cập nhật favicon website',
                'Favicon website đã được cập nhật',
                ['type' => 'favicon']
            );

            return ApiResponse::success([
                'favicon' => ESetting::getFavicon()
            ], 200, 'Cập nhật favicon thành công');
        }

        // Nếu request xóa favicon
        if ($request->input('remove_favicon')) {
            ESetting::setFavicon(null);

            return ApiResponse::success([
                'favicon' => null
            ], 200, 'Đã xóa favicon');
        }

        return ApiResponse::error('Không có favicon được chọn', 422);
    }

    /**
     * Update SMTP settings
     */
    public function updateSmtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_active' => 'required|in:0,1',
            'host' => 'nullable|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'username' => 'nullable|email|max:255',
            'password' => 'nullable|string|max:255',
            'encryption' => 'nullable|in:tls,ssl,null',
            'from_name' => 'nullable|string|max:255',
            'from_email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $data = $request->only([
            'is_active',
            'host',
            'port',
            'username',
            'encryption',
            'from_name',
            'from_email'
        ]);

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        ESetting::setEmailSmtp($data);

        // Create notification
        NotificationService::notify(
            'settings',
            'updated',
            'Cập nhật cấu hình email',
            'Cấu hình SMTP đã được cập nhật',
            ['type' => 'smtp']
        );

        return ApiResponse::success(null, 200, 'Cấu hình email đã được lưu');
    }

    /**
     * Test email sending
     */
    public function testEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'content' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        try {
            // Apply SMTP settings from database
            ESetting::applyMailConfig();

            $content = $request->content ?: 'Đây là email thử nghiệm từ hệ thống.';
            $toEmail = $request->email;

            Mail::raw($content, function ($message) use ($toEmail) {
                $message->to($toEmail)
                    ->subject('Email thử nghiệm');
            });

            return ApiResponse::success(null, 200, 'Gửi email thử nghiệm thành công');
        } catch (\Exception $e) {
            return ApiResponse::error('Gửi email thất bại: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get analytics configuration
     */
    public function getAnalytics()
    {
        return response()->json([
            'status' => true,
            'data' => [
                'services' => ESetting::getAnalyticsServices(),
                'types' => ESetting::getAnalyticsTypes(),
            ],
        ]);
    }

    /**
     * Update analytics configuration
     */
    public function updateAnalytics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'services' => 'required|array',
            'services.*.type' => 'required|string',
            'services.*.code' => 'nullable|string',
            'services.*.is_active' => 'required|in:0,1',
            'services.*.position' => 'nullable|in:head,body',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $services = $request->input('services', []);

        // Clean and validate services
        $cleanedServices = [];
        foreach ($services as $service) {
            $cleanedServices[] = [
                'type' => $service['type'],
                'code' => $service['code'] ?? '',
                'is_active' => $service['is_active'] ?? '0',
                'position' => $service['position'] ?? 'head',
            ];
        }

        ESetting::setAnalyticsServices($cleanedServices);

        // Create notification
        NotificationService::notify(
            'settings',
            'updated',
            'Cập nhật cấu hình Analytics',
            'Cấu hình thống kê & tracking đã được cập nhật',
            ['type' => 'analytics']
        );

        return ApiResponse::success([
            'services' => ESetting::getAnalyticsServices(),
        ], 200, 'Cấu hình analytics đã được lưu');
    }

    /**
     * Get theme colors configuration
     */
    public function getThemeColors()
    {
        return response()->json([
            'status' => true,
            'data' => [
                'colors' => ESetting::getThemeColors(),
                'defaults' => ESetting::getDefaultThemeColors(),
                'metadata' => ESetting::getThemeColorsMetadata(),
            ],
        ]);
    }

    /**
     * Update theme colors
     */
    public function updateThemeColors(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'color_primary' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_primary_dark' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_secondary' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_secondary_dark' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_menu_hover' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_icon' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_text_secondary' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_text_tertiary' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_background' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $colors = $request->only([
            'color_primary',
            'color_primary_dark',
            'color_secondary',
            'color_secondary_dark',
            'color_menu_hover',
            'color_icon',
            'color_text',
            'color_text_secondary',
            'color_text_tertiary',
            'color_background',
        ]);

        ESetting::setThemeColors($colors);

        // Create notification
        NotificationService::notify(
            'settings',
            'updated',
            'Cập nhật tông màu website',
            'Cấu hình tông màu đã được cập nhật',
            ['type' => 'theme_colors']
        );

        return ApiResponse::success([
            'colors' => ESetting::getThemeColors(),
        ], 200, 'Cấu hình tông màu đã được lưu');
    }
}
