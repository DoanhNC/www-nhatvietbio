<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ESlide;
use Illuminate\Http\Request;

class SlideApiController extends Controller
{
    /**
     * Get all slides with media
     */
    public function index()
    {
        $slides = ESlide::with('media')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($slide) {
                return [
                    'id' => $slide->id,
                    'media_id' => $slide->media_id,
                    'title' => $slide->title,
                    'sort_order' => $slide->sort_order,
                    'is_active' => $slide->is_active,
                    'media' => $slide->media ? [
                        'id' => $slide->media->id,
                        'url' => $slide->media->url,
                        'original_name' => $slide->media->original_name,
                    ] : null,
                ];
            });

        return response()->json(['slides' => $slides]);
    }

    /**
     * Store a new slide
     */
    public function store(Request $request)
    {
        $request->validate([
            'media_id' => 'required|exists:e_media_files,id',
            'title' => 'nullable|string|max:255',
        ]);

        // Get max sort order
        $maxOrder = ESlide::max('sort_order') ?? 0;

        $slide = ESlide::create([
            'media_id' => $request->media_id,
            'title' => $request->title,
            'sort_order' => $maxOrder + 1,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        $slide->load('media');

        return response()->json([
            'success' => true,
            'message' => 'Slide đã được thêm thành công',
            'slide' => [
                'id' => $slide->id,
                'media_id' => $slide->media_id,
                'title' => $slide->title,
                'sort_order' => $slide->sort_order,
                'is_active' => $slide->is_active,
                'media' => $slide->media ? [
                    'id' => $slide->media->id,
                    'url' => $slide->media->url,
                    'original_name' => $slide->media->original_name,
                ] : null,
            ],
        ]);
    }

    /**
     * Update a slide
     */
    public function update(Request $request, $id)
    {
        $slide = ESlide::findOrFail($id);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->has('title')) {
            $slide->title = $request->title;
        }
        if ($request->has('is_active')) {
            $slide->is_active = $request->is_active;
        }

        $slide->save();

        return response()->json([
            'success' => true,
            'message' => 'Slide đã được cập nhật',
        ]);
    }

    /**
     * Delete a slide
     */
    public function destroy($id)
    {
        $slide = ESlide::findOrFail($id);
        $slide->delete();

        return response()->json([
            'success' => true,
            'message' => 'Slide đã được xóa',
        ]);
    }

    /**
     * Reorder slides
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:e_slides,id',
        ]);

        foreach ($request->order as $index => $id) {
            ESlide::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thứ tự đã được cập nhật',
        ]);
    }

    /**
     * Get active slides for frontend
     */
    public function getActiveSlides()
    {
        $slides = ESlide::with('media')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($slide) {
                return [
                    'id' => $slide->id,
                    'title' => $slide->title,
                    'image_url' => $slide->media ? $slide->media->url : null,
                ];
            });

        return response()->json(['slides' => $slides]);
    }
}
