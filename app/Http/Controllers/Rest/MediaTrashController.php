<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Models\EMediaFile;
use App\Models\EMediaFolder;
use App\Models\EMediaLog;
use App\Services\NotificationService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaTrashController extends Controller
{
    /**
     * Get deleted files and folders (with folder navigation support)
     */
    public function index(Request $request)
    {
        $folderId = $request->query('folder_id');
        $search = $request->query('search');

        // If searching, search across all deleted items
        if ($search) {
            $folders = EMediaFolder::onlyTrashed()
                ->where('name', 'like', "%{$search}%")
                ->orderBy('created_at')
                ->get()
                ->map(function ($folder) {
                    return [
                        'id' => $folder->id,
                        'name' => $folder->name,
                        'path' => $folder->path,
                        'parent_id' => $folder->parent_id,
                        'type' => 'folder',
                        'formatted_size' => '-',
                        'deleted_at' => $folder->deleted_at,
                        'updated_at' => $folder->updated_at,
                    ];
                });

            $files = EMediaFile::onlyTrashed()
                ->where('original_name', 'like', "%{$search}%")
                ->orderBy('created_at')
                ->get()
                ->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'name' => $file->original_name,
                        'path' => $file->path,
                        'folder_id' => $file->folder_id,
                        'type' => 'file',
                        'file_type' => $file->file_type,
                        'extension' => $file->extension,
                        'formatted_size' => $file->formatted_size,
                        'url' => $file->url,
                        'deleted_at' => $file->deleted_at,
                        'updated_at' => $file->updated_at,
                    ];
                });

            return response()->json([
                'folders' => $folders,
                'files' => $files,
                'folder_count' => $folders->count(),
                'file_count' => $files->count(),
            ]);
        }

        // Normal listing - show items in specific folder or root-level deleted items
        if ($folderId) {
            // Show contents of a specific deleted folder
            $folders = EMediaFolder::onlyTrashed()
                ->where('parent_id', $folderId)
                ->orderBy('created_at')
                ->get()
                ->map(fn($f) => $this->mapFolder($f));

            $files = EMediaFile::onlyTrashed()
                ->where('folder_id', $folderId)
                ->orderBy('created_at')
                ->get()
                ->map(fn($f) => $this->mapFile($f));

            // Get current folder for breadcrumb
            $currentFolder = EMediaFolder::withTrashed()->find($folderId);
            $breadcrumb = $this->buildTrashBreadcrumb($currentFolder);
        } else {
            // Show root-level deleted items (folders without deleted parent)
            $folders = EMediaFolder::onlyTrashed()
                ->orderBy('created_at')
                ->get()
                ->filter(function ($folder) {
                    // Show if parent is not trashed (or no parent)
                    if (!$folder->parent_id) return true;
                    $parent = EMediaFolder::withTrashed()->find($folder->parent_id);
                    return $parent && !$parent->trashed();
                })
                ->values()
                ->map(fn($f) => $this->mapFolder($f));

            $files = EMediaFile::onlyTrashed()
                ->orderBy('created_at')
                ->get()
                ->filter(function ($file) {
                    // Show if folder is not trashed (or no folder)
                    if (!$file->folder_id) return true;
                    $folder = EMediaFolder::withTrashed()->find($file->folder_id);
                    return $folder && !$folder->trashed();
                })
                ->values()
                ->map(fn($f) => $this->mapFile($f));

            $breadcrumb = [['id' => null, 'name' => 'Tệp đã xóa', 'path' => '/']];
        }

        return response()->json([
            'folders' => $folders,
            'files' => $files,
            'folder_count' => $folders->count(),
            'file_count' => $files->count(),
            'breadcrumb' => $breadcrumb ?? null,
            'current_folder_id' => $folderId,
        ]);
    }

    private function mapFolder($folder): array
    {
        // Count items inside this folder (both files and subfolders)
        $fileCount = EMediaFile::withTrashed()->where('folder_id', $folder->id)->count();
        $folderCount = EMediaFolder::withTrashed()->where('parent_id', $folder->id)->count();
        $itemCount = $fileCount + $folderCount;

        return [
            'id' => $folder->id,
            'name' => $folder->name,
            'path' => $folder->path,
            'parent_id' => $folder->parent_id,
            'type' => 'folder',
            'formatted_size' => '-',
            'item_count' => $itemCount,
            'file_count' => $fileCount,
            'folder_count' => $folderCount,
            'deleted_at' => $folder->deleted_at,
            'updated_at' => $folder->updated_at,
        ];
    }

    private function mapFile($file): array
    {
        return [
            'id' => $file->id,
            'name' => $file->original_name,
            'path' => $file->path,
            'folder_id' => $file->folder_id,
            'type' => 'file',
            'file_type' => $file->file_type,
            'extension' => $file->extension,
            'formatted_size' => $file->formatted_size,
            'url' => $file->url,
            'deleted_at' => $file->deleted_at,
            'updated_at' => $file->updated_at,
        ];
    }

    private function buildTrashBreadcrumb($folder): array
    {
        $breadcrumb = [['id' => null, 'name' => 'Tệp đã xóa', 'path' => '/']];
        $path = [];

        while ($folder) {
            array_unshift($path, [
                'id' => $folder->id,
                'name' => $folder->name,
                'path' => $folder->path,
            ]);
            $folder = EMediaFolder::withTrashed()->find($folder->parent_id);
        }

        return array_merge($breadcrumb, $path);
    }

    /**
     * Restore a deleted item
     */
    public function restore(Request $request, $type, $id)
    {
        if ($type === 'folder') {
            $item = EMediaFolder::onlyTrashed()->findOrFail($id);

            // First restore parent folders if they are trashed
            $this->restoreParentFolders($item->parent_id);

            // Restore the folder itself
            $item->restore();

            // Restore all child contents
            $this->restoreFolderContents($item);

            // Log action
            EMediaLog::logAction('restore', 'folder', $item->path, $item->name);

            // Create notification
            $dateStr = now()->format('d/m/Y H:i');
            NotificationService::notify(
                'media',
                'restored',
                'Khôi phục thư mục: ' . $item->name,
                'Thư mục "' . $item->name . '" đã được khôi phục lúc ' . $dateStr,
                ['name' => $item->name, 'path' => $item->path]
            );

            return ApiResponse::success('Khôi phục thư mục thành công');
        } else {
            $item = EMediaFile::onlyTrashed()->findOrFail($id);

            // First restore parent folder if it is trashed
            if ($item->folder_id) {
                $this->restoreParentFolders($item->folder_id);
            }

            $item->restore();

            // Log action
            EMediaLog::logAction('restore', 'file', $item->path, $item->original_name);

            // Create notification
            $dateStr = now()->format('d/m/Y H:i');
            NotificationService::notify(
                'media',
                'restored',
                'Khôi phục tệp: ' . mb_substr($item->original_name, 0, 50),
                'Tệp "' . $item->original_name . '" đã được khôi phục lúc ' . $dateStr,
                ['name' => $item->original_name, 'path' => $item->path]
            );

            return ApiResponse::success('Khôi phục tệp thành công');
        }
    }

    /**
     * Restore parent folders recursively (if they are trashed)
     */
    private function restoreParentFolders($folderId): void
    {
        if (!$folderId) return;

        $folder = EMediaFolder::withTrashed()->find($folderId);
        if (!$folder) return;

        // If parent is also trashed, restore it first
        if ($folder->parent_id) {
            $this->restoreParentFolders($folder->parent_id);
        }

        // Restore this folder if trashed
        if ($folder->trashed()) {
            $folder->restore();
        }
    }

    /**
     * Restore all contents of a folder recursively
     */
    private function restoreFolderContents(EMediaFolder $folder): void
    {
        // Restore all files in this folder
        EMediaFile::onlyTrashed()->where('folder_id', $folder->id)->each(function ($file) {
            $file->restore();
        });

        // Restore child folders and their contents
        EMediaFolder::onlyTrashed()->where('parent_id', $folder->id)->each(function ($childFolder) {
            $childFolder->restore();
            $this->restoreFolderContents($childFolder);
        });
    }

    /**
     * Permanently delete an item
     */
    public function forceDelete(Request $request, $type, $id)
    {
        if ($type === 'folder') {
            $item = EMediaFolder::onlyTrashed()->findOrFail($id);
            $path = $item->path;
            $name = $item->name;

            // Delete all contents recursively first
            $deletedCount = $this->forceDeleteFolderContents($item);

            // Delete the folder itself
            $item->forceDelete();

            // Log action
            EMediaLog::logAction('force_delete', 'folder', $path, $name, null, [
                'deleted_files' => $deletedCount,
            ]);

            // Create notification
            $dateStr = now()->format('d/m/Y H:i');
            NotificationService::notify(
                'media',
                'force_deleted',
                'Xóa vĩnh viễn thư mục: ' . $name,
                'Thư mục "' . $name . '" đã bị xóa vĩnh viễn lúc ' . $dateStr . ' (' . $deletedCount . ' tệp)',
                ['name' => $name, 'path' => $path, 'deleted_count' => $deletedCount]
            );

            return ApiResponse::success("Xóa vĩnh viễn thư mục thành công ({$deletedCount} tệp)");
        } else {
            $item = EMediaFile::onlyTrashed()->findOrFail($id);
            $path = $item->path;
            $name = $item->original_name;

            // Delete physical file
            Storage::disk('public')->delete($item->storage_path);

            $item->forceDelete();

            // Log action
            EMediaLog::logAction('force_delete', 'file', $path, $name);

            // Create notification
            $dateStr = now()->format('d/m/Y H:i');
            NotificationService::notify(
                'media',
                'force_deleted',
                'Xóa vĩnh viễn tệp: ' . mb_substr($name, 0, 50),
                'Tệp "' . $name . '" đã bị xóa vĩnh viễn lúc ' . $dateStr,
                ['name' => $name, 'path' => $path]
            );

            return ApiResponse::success('Xóa vĩnh viễn tệp thành công');
        }
    }

    /**
     * Force delete all contents of a folder recursively
     */
    private function forceDeleteFolderContents(EMediaFolder $folder): int
    {
        $deletedCount = 0;

        // Delete all files in this folder (both trashed and non-trashed for cascade)
        $files = EMediaFile::withTrashed()->where('folder_id', $folder->id)->get();
        foreach ($files as $file) {
            Storage::disk('public')->delete($file->storage_path);
            $file->forceDelete();
            $deletedCount++;
        }

        // Delete child folders and their contents
        $childFolders = EMediaFolder::withTrashed()->where('parent_id', $folder->id)->get();
        foreach ($childFolders as $childFolder) {
            $deletedCount += $this->forceDeleteFolderContents($childFolder);
            $childFolder->forceDelete();
        }

        return $deletedCount;
    }

    /**
     * Empty trash - permanently delete all items
     */
    public function emptyTrash()
    {
        $deletedCount = 0;

        // Delete all trashed files (with physical files)
        $trashedFiles = EMediaFile::onlyTrashed()->get();
        foreach ($trashedFiles as $file) {
            Storage::disk('public')->delete($file->storage_path);
            $file->forceDelete();
            $deletedCount++;
        }

        // Delete all trashed folders
        $trashedFolders = EMediaFolder::onlyTrashed()->get();
        foreach ($trashedFolders as $folder) {
            $folder->forceDelete();
            $deletedCount++;
        }

        // Log action
        EMediaLog::logAction('empty_trash', 'folder', '/', null, null, [
            'deleted_count' => $deletedCount,
        ]);

        // Create notification
        $dateStr = now()->format('d/m/Y H:i');
        NotificationService::notify(
            'media',
            'trash_emptied',
            'Dọn dẹp thùng rác',
            'Đã xóa vĩnh viễn ' . $deletedCount . ' mục trong thùng rác lúc ' . $dateStr,
            ['deleted_count' => $deletedCount]
        );

        return ApiResponse::success("Đã dọn dẹp {$deletedCount} mục");
    }
}
