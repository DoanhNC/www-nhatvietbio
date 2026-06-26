<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ESlide extends Model
{
    protected $table = 'e_slides';

    protected $fillable = [
        'media_id',
        'title',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the media file associated with the slide
     */
    public function media()
    {
        return $this->belongsTo(EMediaFile::class, 'media_id');
    }

    /**
     * Get all active slides ordered by sort_order
     */
    public static function getActiveSlides()
    {
        return self::with('media')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get all slides with media for admin
     */
    public static function getAllWithMedia()
    {
        return self::with('media')
            ->orderBy('sort_order')
            ->get();
    }
}
