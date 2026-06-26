<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Models\EMediaFile;
use App\Models\EMediaFolder;
use App\Models\EMediaLog;
use App\Models\EMediaSetting;
use App\Services\NotificationService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaFilesController extends Controller
{
    /**
     * Get files (with optional search)
     */
    public function index(Request $request)
    {
        $folderId = $request->query('folder_id');
        $search = $request->query('search');

        // If searching, search across all folders and files
        if ($search) {
            // Search folders
            $folders = EMediaFolder::where('name', 'like', "%{$search}%")
                ->orderBy('name')
                ->get()
                ->map(function ($folder) {
                    return [
                        'id' => $folder->id,
                        'name' => $folder->name,
                        'path' => $folder->path,
                        'type' => 'folder',
                        'parent_id' => $folder->parent_id,
                        'size' => $folder->getSize(),
                        'formatted_size' => $folder->formatted_size,
                        'created_at' => $folder->created_at,
                        'updated_at' => $folder->updated_at,
                    ];
                });

            // Search files
            $files = EMediaFile::where('original_name', 'like', "%{$search}%")
                ->orderBy('original_name')
                ->get()
                ->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'name' => $file->original_name,
                        'path' => $file->path,
                        'folder_id' => $file->folder_id,
                        'file_type' => $file->file_type,
                        'extension' => $file->extension,
                        'size' => $file->file_size,
                        'formatted_size' => $file->formatted_size,
                        'url' => $file->url,
                        'type_label' => $file->getTypeLabel(),
                        'created_at' => $file->created_at,
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

        // Normal listing
        $query = EMediaFile::query();

        if ($folderId) {
            $query->where('folder_id', $folderId);
        } else {
            $query->whereNull('folder_id');
        }

        $files = $query->orderBy('original_name')->get();

        return response()->json([
            'files' => $files,
            'count' => $files->count(),
        ]);
    }

    /**
     * Upload files
     */
    public function store(Request $request)
    {
        // Get files from request
        $files = $request->file('files');

        // If no files found, return error
        if (empty($files)) {
            return ApiResponse::error('Không có file nào được gửi lên', 422);
        }

        $folderId = $request->input('folder_id');

        // Validate folder_id if provided
        if ($folderId) {
            $folder = EMediaFolder::find($folderId);
            if (!$folder) {
                return ApiResponse::error('Thư mục không tồn tại', 422);
            }
            $basePath = $folder->path;
        } else {
            $folder = null;
            $basePath = '';
        }

        // Check storage quota
        $allowedExtensions = EMediaSetting::getAllowedExtensions();
        $maxStorageBytes = EMediaSetting::getMaxStorageBytes();
        $usedStorageBytes = EMediaSetting::getUsedStorageBytes();
        $convertToWebp = EMediaSetting::getConvertToWebp();

        $uploadedFiles = [];
        $errors = [];

        // Image extensions that can be converted to WebP
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];

        foreach ($files as $file) {
            // Check if file is valid
            if (!$file->isValid()) {
                $errors[] = "File không hợp lệ: " . $file->getErrorMessage();
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension());
            $originalName = $file->getClientOriginalName();
            $fileSize = $file->getSize();

            // Check extension
            if (!in_array($extension, $allowedExtensions)) {
                $errors[] = "File {$originalName}: định dạng không được hỗ trợ";
                continue;
            }

            // Check storage quota
            if (($usedStorageBytes + $fileSize) > $maxStorageBytes) {
                $errors[] = "File {$originalName}: vượt quá dung lượng cho phép";
                continue;
            }

            // Determine if we should convert this image to WebP
            $shouldConvert = $convertToWebp && in_array($extension, $imageExtensions);
            $finalExtension = $shouldConvert ? 'webp' : $extension;
            $finalOriginalName = $shouldConvert
                ? pathinfo($originalName, PATHINFO_FILENAME) . '.webp'
                : $originalName;

            // Generate unique stored name
            $storedName = Str::uuid() . '.' . $finalExtension;
            $storagePath = 'uploads/media/' . date('Y/m') . '/' . $storedName;
            $fullStoragePath = public_path($storagePath);

            // Ensure directory exists
            $directory = dirname($fullStoragePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            if ($shouldConvert) {
                // Convert image to WebP
                try {
                    $image = null;
                    $mimeType = $file->getMimeType();

                    switch ($mimeType) {
                        case 'image/jpeg':
                        case 'image/jpg':
                            $image = imagecreatefromjpeg($file->getRealPath());
                            break;
                        case 'image/png':
                            $image = imagecreatefrompng($file->getRealPath());
                            // Preserve transparency
                            imagepalettetotruecolor($image);
                            imagealphablending($image, true);
                            imagesavealpha($image, true);
                            break;
                        case 'image/gif':
                            $image = imagecreatefromgif($file->getRealPath());
                            break;
                        case 'image/bmp':
                            $image = imagecreatefrombmp($file->getRealPath());
                            break;
                    }

                    if ($image) {
                        // Save as WebP with quality 80
                        imagewebp($image, $fullStoragePath, 80);
                        imagedestroy($image);

                        // Get new file size
                        $fileSize = filesize($fullStoragePath);
                        $mimeTypeForDb = 'image/webp';
                    } else {
                        // Fallback: store original file
                        $file->move(dirname($fullStoragePath), $storedName);
                        $finalExtension = $extension;
                        $finalOriginalName = $originalName;
                        $mimeTypeForDb = $file->getClientMimeType();
                    }
                } catch (\Exception $e) {
                    // Fallback: store original file if conversion fails
                    $storedName = Str::uuid() . '.' . $extension;
                    $storagePath = 'uploads/media/' . date('Y/m') . '/' . $storedName;
                    $fullStoragePath = public_path($storagePath);
                    if (!is_dir(dirname($fullStoragePath))) {
                        mkdir(dirname($fullStoragePath), 0755, true);
                    }
                    $file->move(dirname($fullStoragePath), $storedName);
                    $finalExtension = $extension;
                    $finalOriginalName = $originalName;
                    $mimeTypeForDb = $file->getClientMimeType();
                    $fileSize = filesize($fullStoragePath);
                }
            } else {
                // Store file normally
                $file->move(dirname($fullStoragePath), $storedName);
                $mimeTypeForDb = $file->getClientMimeType();
            }

            // Build virtual path
            $virtualPath = $basePath . '/' . $finalOriginalName;

            // Create database record
            $mediaFile = EMediaFile::create([
                'folder_id' => $folderId,
                'original_name' => $finalOriginalName,
                'stored_name' => $storedName,
                'path' => $virtualPath,
                'storage_path' => $storagePath,
                'mime_type' => $mimeTypeForDb,
                'file_type' => EMediaFile::getFileTypeFromMime($mimeTypeForDb),
                'file_size' => $fileSize,
                'extension' => $finalExtension,
                'created_by' => Auth::id(),
            ]);

            $uploadedFiles[] = $mediaFile;
            $usedStorageBytes += $fileSize;

            // Log action
            EMediaLog::logAction('upload', 'file', $virtualPath, $finalOriginalName, null, [
                'size' => $fileSize,
                'mime_type' => $mimeTypeForDb,
                'converted_to_webp' => $shouldConvert,
            ]);
        }

        if (count($errors) > 0 && count($uploadedFiles) === 0) {
            return ApiResponse::error(implode('; ', $errors), 422);
        }

        // Create notification if files were uploaded
        if (count($uploadedFiles) > 0) {
            $fileCount = count($uploadedFiles);
            $dateStr = now()->format('d/m/Y H:i');
            NotificationService::notify(
                'media',
                'created',
                'Upload ' . $fileCount . ' file' . ($fileCount > 1 ? 's' : ''),
                'Đã upload ' . $fileCount . ' file vào thư mục ' . ($folder ? $folder->name : 'gốc') . ' lúc ' . $dateStr,
                ['file_count' => $fileCount, 'folder' => $folder ? $folder->name : null]
            );
        }

        return ApiResponse::success([
            'uploaded' => $uploadedFiles,
            'errors' => $errors,
        ], 201);
    }

    /**
     * Get file details
     */
    public function show($id)
    {
        $file = EMediaFile::findOrFail($id);

        return response()->json([
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
            'type_label' => $file->getTypeLabel(),
        ]);
    }

    /**
     * Rename a file
     */
    public function update(Request $request, $id)
    {
        $file = EMediaFile::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $oldPath = $file->path;
        $oldName = $file->original_name;

        // Keep the same extension
        $newName = $data['name'];
        if (!str_ends_with(strtolower($newName), '.' . $file->extension)) {
            $newName .= '.' . $file->extension;
        }

        // Build new path
        $basePath = $file->folder ? $file->folder->path : '';
        $newPath = $basePath . '/' . $newName;

        $file->update([
            'original_name' => $newName,
            'path' => $newPath,
        ]);

        // Log action
        EMediaLog::logAction('rename', 'file', $newPath, $newName, $oldPath);

        return ApiResponse::success($file);
    }

    /**
     * Delete a file
     */
    public function destroy($id)
    {
        $file = EMediaFile::findOrFail($id);

        $path = $file->path;
        $name = $file->original_name;

        // Delete physical file
        Storage::disk('public')->delete($file->storage_path);

        // Delete database record
        $file->delete();

        // Log action
        EMediaLog::logAction('delete', 'file', $path, $name);

        // Create notification
        $dateStr = now()->format('d/m/Y H:i');
        NotificationService::notify(
            'media',
            'deleted',
            'Xóa file: ' . mb_substr($name, 0, 50),
            'File "' . $name . '" đã bị xóa lúc ' . $dateStr,
            ['name' => $name, 'path' => $path]
        );

        return ApiResponse::success('Xóa tệp thành công');
    }
}
