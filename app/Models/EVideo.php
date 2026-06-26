<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EVideo extends Model
{
    protected $table = 'e_videos';

    protected $fillable = [
        'title',
        'youtube_url',
        'youtube_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot method to auto-extract YouTube ID when saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($video) {
            if ($video->youtube_url) {
                $video->youtube_id = self::extractYoutubeId($video->youtube_url);
            }
        });
    }

    /**
     * Extract YouTube video ID from URL
     */
    public static function extractYoutubeId($url)
    {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
        preg_match($pattern, $url, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Get YouTube thumbnail URL
     */
    public function getThumbnailAttribute()
    {
        if ($this->youtube_id) {
            return "https://img.youtube.com/vi/{$this->youtube_id}/mqdefault.jpg";
        }
        return null;
    }

    /**
     * Get YouTube embed URL
     */
    public function getEmbedUrlAttribute()
    {
        if ($this->youtube_id) {
            return "https://www.youtube.com/embed/{$this->youtube_id}";
        }
        return null;
    }

    /**
     * Get all active videos ordered by sort_order
     */
    public static function getActiveVideos()
    {
        return self::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get all videos for admin
     */
    public static function getAllVideos()
    {
        return self::orderBy('sort_order')->get();
    }
}
