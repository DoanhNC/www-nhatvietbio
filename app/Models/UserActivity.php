<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $table = 'e_user_activities';

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    /**
     * Get the user associated with this activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get online users count (active in last X minutes).
     */
    public static function getOnlineCount(int $minutes = 5): int
    {
        return static::where('last_activity_at', '>=', now()->subMinutes($minutes))
            ->distinct('session_id')
            ->count('session_id');
    }

    /**
     * Get today's unique visitors count.
     */
    public static function getTodayCount(): int
    {
        return static::whereDate('last_activity_at', today())
            ->distinct('session_id')
            ->count('session_id');
    }

    /**
     * Get this week's unique visitors count.
     */
    public static function getThisWeekCount(): int
    {
        return static::whereBetween('last_activity_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->distinct('session_id')
            ->count('session_id');
    }

    /**
     * Get this month's unique visitors count.
     */
    public static function getThisMonthCount(): int
    {
        return static::whereYear('last_activity_at', now()->year)
            ->whereMonth('last_activity_at', now()->month)
            ->distinct('session_id')
            ->count('session_id');
    }

    /**
     * Get total unique visitors count.
     */
    public static function getTotalCount(): int
    {
        return static::distinct('session_id')->count('session_id');
    }

    /**
     * Get all statistics.
     */
    public static function getStats(): array
    {
        return [
            'online' => static::getOnlineCount(),
            'today' => static::getTodayCount(),
            'this_week' => static::getThisWeekCount(),
            'this_month' => static::getThisMonthCount(),
            'total' => static::getTotalCount(),
        ];
    }

    /**
     * Clean up old records (older than X days).
     */
    public static function cleanup(int $days = 90): int
    {
        return static::where('last_activity_at', '<', now()->subDays($days))->delete();
    }
}
