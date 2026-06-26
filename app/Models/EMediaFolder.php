<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EMediaFolder extends Model
{
    use SoftDeletes;

    protected $table = 'e_media_folders';

    protected $fillable = [
        'name',
        'parent_id',
        'path',
        'created_by',
    ];

    /**
     * Get parent folder
     */
    public function parent()
    {
        return $this->belongsTo(EMediaFolder::class, 'parent_id');
    }

    /**
     * Get child folders
     */
    public function children()
    {
        return $this->hasMany(EMediaFolder::class, 'parent_id')->orderBy('created_at');
    }

    /**
     * Get all files in this folder
     */
    public function files()
    {
        return $this->hasMany(EMediaFile::class, 'folder_id');
    }

    /**
     * Get creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate total size of all files in this folder (including subfolders)
     */
    public function getSize(): int
    {
        $size = $this->files()->sum('file_size');

        foreach ($this->children as $child) {
            $size += $child->getSize();
        }

        return $size;
    }

    /**
     * Get formatted size
     */
    public function getFormattedSizeAttribute(): string
    {
        return $this->formatBytes($this->getSize());
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
     * Get folder tree recursively
     */
    public static function getTree($parentId = null): array
    {
        $folders = self::where('parent_id', $parentId)
            ->orderBy('created_at')
            ->get();

        $tree = [];
        foreach ($folders as $folder) {
            $tree[] = [
                'id' => $folder->id,
                'name' => $folder->name,
                'path' => $folder->path,
                'children' => self::getTree($folder->id),
            ];
        }

        return $tree;
    }
}
