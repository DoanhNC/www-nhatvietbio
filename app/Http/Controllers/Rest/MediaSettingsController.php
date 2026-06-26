<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Models\EMediaSetting;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class MediaSettingsController extends Controller
{
    /**
     * Get storage stats and settings
     */
    public function show()
    {
        return response()->json([
            'storage' => EMediaSetting::getStorageStats(),
            'settings' => [
                'max_storage_bytes' => EMediaSetting::getMaxStorageBytes(),
                'max_file_size_bytes' => EMediaSetting::getMaxFileSizeBytes(),
                'allowed_extensions' => EMediaSetting::getAllowedExtensions(),
                'convert_to_webp' => EMediaSetting::getConvertToWebp(),
            ],
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'max_storage_bytes' => ['nullable', 'integer', 'min:104857600'], // Min 100MB
            'max_file_size_bytes' => ['nullable', 'integer', 'min:1048576'], // Min 1MB
            'allowed_extensions' => ['nullable', 'array'],
            'convert_to_webp' => ['nullable', 'boolean'],
        ]);

        if (isset($data['max_storage_bytes'])) {
            EMediaSetting::set('max_storage_bytes', $data['max_storage_bytes']);
        }

        if (isset($data['max_file_size_bytes'])) {
            EMediaSetting::set('max_file_size_bytes', $data['max_file_size_bytes']);
        }

        if (isset($data['allowed_extensions'])) {
            EMediaSetting::set('allowed_extensions', $data['allowed_extensions']);
        }

        if (array_key_exists('convert_to_webp', $data)) {
            EMediaSetting::set('convert_to_webp', $data['convert_to_webp'] ? '1' : '0');
        }

        return ApiResponse::success('Cập nhật cài đặt thành công');
    }
}
