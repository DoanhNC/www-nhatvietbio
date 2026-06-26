<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EMediaSetting extends Model
{
    protected $table = 'e_media_settings';

    protected $fillable = [
        'setting_key',
        'setting_value',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();

        if (!$setting) {
            return $default;
        }

        // Try to decode JSON if it looks like JSON
        $value = $setting->setting_value;
        if (str_starts_with($value, '[') || str_starts_with($value, '{')) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Return numeric value if it's a number
        if (is_numeric($value)) {
            return (int) $value;
        }

        return $value;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value): void
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        self::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => (string) $value]
        );
    }

    /**
     * Get max storage in bytes
     */
    public static function getMaxStorageBytes(): int
    {
        return (int) self::get('max_storage_bytes', 2147483648); // 2GB default
    }

    /**
     * Get max file size in bytes
     */
    public static function getMaxFileSizeBytes(): int
    {
        return (int) self::get('max_file_size_bytes', 52428800); // 50MB default
    }

    /**
     * Get allowed extensions
     */
    public static function getAllowedExtensions(): array
    {
        return self::get('allowed_extensions', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
    }

    /**
     * Get convert to WebP setting
     */
    public static function getConvertToWebp(): bool
    {
        return (bool) self::get('convert_to_webp', false);
    }

    /**
     * Get total used storage in bytes
     */
    public static function getUsedStorageBytes(): int
    {
        return EMediaFile::sum('file_size');
    }

    /**
     * Get storage stats
     */
    public static function getStorageStats(): array
    {
        $maxBytes = self::getMaxStorageBytes();
        $usedBytes = self::getUsedStorageBytes();
        $freeBytes = max(0, $maxBytes - $usedBytes);
        $usedPercent = $maxBytes > 0 ? round(($usedBytes / $maxBytes) * 100, 1) : 0;

        return [
            'max_bytes' => $maxBytes,
            'used_bytes' => $usedBytes,
            'free_bytes' => $freeBytes,
            'used_percent' => $usedPercent,
            'free_percent' => round(100 - $usedPercent, 1),
            'max_formatted' => self::formatBytes($maxBytes),
            'used_formatted' => self::formatBytes($usedBytes),
            'free_formatted' => self::formatBytes($freeBytes),
        ];
    }

    /**
     * Format bytes to human readable
     */
    private static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
