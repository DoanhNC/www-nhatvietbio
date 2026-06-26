<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EPost;
use App\Models\EMediaFile;

class PostController extends Controller
{
    private $directories = [
        'admin.posts'        => 'Bài viết',
        'admin.posts.create' => 'Thêm mới',
        'admin.posts.edit'   => 'Chỉnh sửa',
    ];

    public function index()
    {
        return view('admin.posts.posts', ['directories' => $this->directories]);
    }

    public function create()
    {
        return view('admin.posts.postsCreate', ['directories' => $this->directories]);
    }

    public function edit($id)
    {
        $row = EPost::with(['mainCategory', 'creator'])->findOrFail($id);

        // Enrich attachments with original_name from e_media_files
        $data = $row->toArray();
        if (!empty($data['attachments']) && is_array($data['attachments'])) {
            $enrichedAttachments = [];
            foreach ($data['attachments'] as $attachment) {
                $url = is_array($attachment) ? ($attachment['url'] ?? null) : $attachment;
                if ($url) {
                    // URL is /uploads/... , storage_path is uploads/... (without leading /)
                    $storagePath = ltrim($url, '/');
                    $mediaFile = EMediaFile::where('storage_path', $storagePath)->first();
                    if ($mediaFile) {
                        $enrichedAttachments[] = [
                            'name' => $mediaFile->original_name,
                            'path' => $mediaFile->path,
                            'url' => $url,
                        ];
                    } else {
                        // Fallback: extract name from URL
                        $enrichedAttachments[] = [
                            'name' => $attachment['name'] ?? basename($url),
                            'path' => $url,
                            'url' => $url,
                        ];
                    }
                }
            }
            $data['attachments'] = $enrichedAttachments;
        }

        return view('admin.posts.postsEdit', [
            'directories' => $this->directories,
            'data'        => json_encode($data),
        ]);
    }
}
