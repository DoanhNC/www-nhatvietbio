<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class EMediaFile extends Model
{
    use SoftDeletes;

    protected $table = 'e_media_files';

    protected $fillable = [
        'folder_id',
        'original_name',
        'stored_name',
        'path',
        'storage_path',
        'mime_type',
        'file_type',
        'file_size',
        'extension',
        'created_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    protected $appends = ['url', 'formatted_size'];

    /**
     * Get folder
     */
    public function folder()
    {
        return $this->belongsTo(EMediaFolder::class, 'folder_id');
    }

    /**
     * Get creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get file URL
     */
    public function getUrlAttribute(): string
    {
        return '/' . $this->storage_path;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        return $this->formatBytes($this->file_size);
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Determine file type from mime type
     */
    public static function getFileTypeFromMime(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mime, 'audio/')) {
            return 'audio';
        }
        if (in_array($mime, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
        ])) {
            return 'document';
        }

        return 'other';
    }

    /**
     * Get icon class based on file type
     */
    public function getIconClassAttribute(): string
    {
        return match ($this->file_type) {
            'image' => 'fa-file-image',
            'video' => 'fa-file-video',
            'audio' => 'fa-file-audio',
            'document' => 'fa-file-alt',
            default => 'fa-file',
        };
    }

    /**
     * Get label for file type
     */
    public function getTypeLabel(): string
    {
        return match ($this->file_type) {
            'image' => 'Hình ảnh',
            'video' => 'Video',
            'audio' => 'Âm thanh',
            'document' => 'Tài liệu',
            default => 'Tệp tin',
        };
    }
}
