<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EVideo;
use Illuminate\Http\Request;

class VideoApiController extends Controller
{
    /**
     * Get all videos
     */
    public function index()
    {
        $videos = EVideo::orderBy('sort_order')
            ->get()
            ->map(function ($video) {
                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'youtube_url' => $video->youtube_url,
                    'youtube_id' => $video->youtube_id,
                    'thumbnail' => $video->thumbnail,
                    'embed_url' => $video->embed_url,
                    'sort_order' => $video->sort_order,
                    'is_active' => $video->is_active,
                ];
            });

        return response()->json(['videos' => $videos]);
    }

    /**
     * Store a new video
     */
    public function store(Request $request)
    {
        $request->validate([
            'youtube_url' => 'required|url',
            'title' => 'nullable|string|max:255',
        ]);

        // Validate YouTube URL format
        $youtubeId = EVideo::extractYoutubeId($request->youtube_url);
        if (!$youtubeId) {
            return response()->json([
                'success' => false,
                'message' => 'URL YouTube không hợp lệ'
            ], 422);
        }

        // Get max sort order
        $maxOrder = EVideo::max('sort_order') ?? 0;

        $video = EVideo::create([
            'youtube_url' => $request->youtube_url,
            'title' => $request->title,
            'sort_order' => $maxOrder + 1,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Video đã được thêm thành công',
            'video' => [
                'id' => $video->id,
                'title' => $video->title,
                'youtube_url' => $video->youtube_url,
                'youtube_id' => $video->youtube_id,
                'thumbnail' => $video->thumbnail,
                'embed_url' => $video->embed_url,
                'sort_order' => $video->sort_order,
                'is_active' => $video->is_active,
            ],
        ]);
    }

    /**
     * Update a video
     */
    public function update(Request $request, $id)
    {
        $video = EVideo::findOrFail($id);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'youtube_url' => 'nullable|url',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->has('title')) {
            $video->title = $request->title;
        }
        if ($request->has('youtube_url')) {
            $video->youtube_url = $request->youtube_url;
        }
        if ($request->has('is_active')) {
            $video->is_active = $request->is_active;
        }

        $video->save();

        return response()->json([
            'success' => true,
            'message' => 'Video đã được cập nhật',
        ]);
    }

    /**
     * Delete a video
     */
    public function destroy($id)
    {
        $video = EVideo::findOrFail($id);
        $video->delete();

        return response()->json([
            'success' => true,
            'message' => 'Video đã được xóa',
        ]);
    }

    /**
     * Reorder videos
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:e_videos,id',
        ]);

        foreach ($request->order as $index => $id) {
            EVideo::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thứ tự đã được cập nhật',
        ]);
    }

    /**
     * Get active videos for frontend
     */
    public function getActiveVideos()
    {
        $videos = EVideo::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($video) {
                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'youtube_id' => $video->youtube_id,
                    'thumbnail' => $video->thumbnail,
                    'embed_url' => $video->embed_url,
                ];
            });

        return response()->json(['videos' => $videos]);
    }
}
