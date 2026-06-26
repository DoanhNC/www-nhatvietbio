<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Models\EMediaFolder;
use App\Models\EMediaFile;
use App\Models\EMediaLog;
use App\Services\NotificationService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MediaFoldersController extends Controller
{
    /**
     * Get folder tree structure
     */
    public function tree()
    {
        return response()->json([
            'tree' => EMediaFolder::getTree(),
        ]);
    }

    /**
     * Get folder tree or contents of a specific folder
     */
    public function index(Request $request)
    {
        $parentId = $request->query('parent_id');

        // If requesting tree structure
        if ($request->query('tree') === 'true') {
            return response()->json([
                'tree' => EMediaFolder::getTree(),
            ]);
        }

        // Get folders in specified parent (or root if null)
        $folders = EMediaFolder::where('parent_id', $parentId)
            ->orderBy('created_at')
            ->get()
            ->map(function ($folder) {
                // Count items inside this folder
                $fileCount = EMediaFile::where('folder_id', $folder->id)->count();
                $subfolderCount = EMediaFolder::where('parent_id', $folder->id)->count();
                $itemCount = $fileCount + $subfolderCount;

                return [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'path' => $folder->path,
                    'type' => 'folder',
                    'size' => $folder->getSize(),
                    'formatted_size' => $folder->formatted_size,
                    'item_count' => $itemCount,
                    'file_count' => $fileCount,
                    'folder_count' => $subfolderCount,
                    'created_at' => $folder->created_at,
                    'updated_at' => $folder->updated_at,
                ];
            });

        // Get files in specified parent (or root if null)
        $files = EMediaFile::where('folder_id', $parentId)
            ->orderBy('created_at')
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->original_name,
                    'path' => $file->path,
                    'type' => 'file',
                    'file_type' => $file->file_type,
                    'extension' => $file->extension,
                    'size' => $file->file_size,
                    'formatted_size' => $file->formatted_size,
                    'url' => $file->url,
                    'mime_type' => $file->mime_type,
                    'created_at' => $file->created_at,
                    'updated_at' => $file->updated_at,
                ];
            });

        // Get current folder info for breadcrumb
        $currentFolder = $parentId ? EMediaFolder::find($parentId) : null;
        $breadcrumb = $this->buildBreadcrumb($currentFolder);

        return response()->json([
            'current_folder' => $currentFolder,
            'breadcrumb' => $breadcrumb,
            'folders' => $folders,
            'files' => $files,
            'folder_count' => $folders->count(),
            'file_count' => $files->count(),
        ]);
    }

    /**
     * Create a new folder
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[^\/\\:*?"<>|]+$/'],
            'parent_id' => ['nullable', 'exists:e_media_folders,id'],
        ], [
            'name.required' => 'Vui lòng nhập tên thư mục',
            'name.regex' => 'Tên thư mục không được chứa các ký tự: \\ / : * ? " < > |',
        ]);

        $parentId = $data['parent_id'] ?? null;

        // Build path
        if ($parentId) {
            $parent = EMediaFolder::findOrFail($parentId);
            $path = $parent->path . '/' . $data['name'];
        } else {
            $path = '/' . $data['name'];
        }

        // Check if folder already exists
        if (EMediaFolder::where('path', $path)->exists()) {
            return ApiResponse::error('Thư mục đã tồn tại', 422);
        }

        $folder = EMediaFolder::create([
            'name' => $data['name'],
            'parent_id' => $parentId,
            'path' => $path,
            'created_by' => Auth::id(),
        ]);

        // Log action
        EMediaLog::logAction('create_folder', 'folder', $path, $data['name']);

        // Create notification
        $dateStr = now()->format('d/m/Y H:i');
        NotificationService::notify(
            'media',
            'created',
            'Tạo thư mục: ' . $data['name'],
            'Thư mục "' . $data['name'] . '" đã được tạo lúc ' . $dateStr,
            ['name' => $data['name'], 'path' => $path]
        );

        return ApiResponse::success($folder, 201);
    }

    /**
     * Get a specific folder
     */
    public function show($id)
    {
        $folder = EMediaFolder::findOrFail($id);

        return response()->json([
            'id' => $folder->id,
            'name' => $folder->name,
            'path' => $folder->path,
            'type' => 'folder',
            'size' => $folder->getSize(),
            'formatted_size' => $folder->formatted_size,
            'created_at' => $folder->created_at,
            'updated_at' => $folder->updated_at,
            'type_label' => 'Thư mục',
        ]);
    }

    /**
     * Rename a folder
     */
    public function update(Request $request, $id)
    {
        $folder = EMediaFolder::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[^\/\\:*?"<>|]+$/'],
        ]);

        $oldPath = $folder->path;

        // Build new path
        if ($folder->parent_id) {
            $parent = EMediaFolder::find($folder->parent_id);
            $newPath = $parent->path . '/' . $data['name'];
        } else {
            $newPath = '/' . $data['name'];
        }

        // Check if new path already exists
        if ($newPath !== $oldPath && EMediaFolder::where('path', $newPath)->exists()) {
            return ApiResponse::error('Thư mục đã tồn tại', 422);
        }

        // Update folder and all child paths
        $folder->update([
            'name' => $data['name'],
            'path' => $newPath,
        ]);

        // Update paths of all children
        $this->updateChildPaths($folder, $oldPath, $newPath);

        // Log action
        EMediaLog::logAction('rename', 'folder', $newPath, $data['name'], $oldPath);

        return ApiResponse::success($folder);
    }

    /**
     * Delete a folder (soft delete with all contents - Windows-like behavior)
     */
    public function destroy($id)
    {
        $folder = EMediaFolder::findOrFail($id);

        $path = $folder->path;
        $name = $folder->name;

        // Recursively soft delete all contents
        $this->deleteFolderContents($folder);

        // Soft delete the folder itself
        $folder->delete();

        // Log action
        EMediaLog::logAction('delete', 'folder', $path, $name);

        // Create notification
        $dateStr = now()->format('d/m/Y H:i');
        NotificationService::notify(
            'media',
            'deleted',
            'Xóa thư mục: ' . $name,
            'Thư mục "' . $name . '" đã bị xóa lúc ' . $dateStr,
            ['name' => $name, 'path' => $path]
        );

        return ApiResponse::success('Xóa thư mục thành công');
    }

    /**
     * Recursively soft delete folder contents
     */
    private function deleteFolderContents(EMediaFolder $folder): void
    {
        // Delete all files in this folder
        EMediaFile::where('folder_id', $folder->id)->each(function ($file) {
            $file->delete();
        });

        // Recursively delete child folders
        EMediaFolder::where('parent_id', $folder->id)->each(function ($childFolder) {
            $this->deleteFolderContents($childFolder);
            $childFolder->delete();
        });
    }

    /**
     * Build breadcrumb array from folder
     */
    private function buildBreadcrumb(?EMediaFolder $folder): array
    {
        $breadcrumb = [];

        while ($folder) {
            array_unshift($breadcrumb, [
                'id' => $folder->id,
                'name' => $folder->name,
                'path' => $folder->path,
            ]);
            $folder = $folder->parent;
        }

        // Add root
        array_unshift($breadcrumb, [
            'id' => null,
            'name' => 'Tất cả',
            'path' => '/',
        ]);

        return $breadcrumb;
    }

    /**
     * Update child folder paths when parent is renamed
     */
    private function updateChildPaths(EMediaFolder $parent, string $oldPath, string $newPath): void
    {
        $children = EMediaFolder::where('parent_id', $parent->id)->get();

        foreach ($children as $child) {
            $childOldPath = $child->path;
            $childNewPath = str_replace($oldPath, $newPath, $childOldPath);

            $child->update(['path' => $childNewPath]);

            $this->updateChildPaths($child, $childOldPath, $childNewPath);
        }

        // Also update file paths
        $files = EMediaFile::where('folder_id', $parent->id)->get();
        foreach ($files as $file) {
            $fileNewPath = str_replace($oldPath, $newPath, $file->path);
            $file->update(['path' => $fileNewPath]);
        }
    }
}
