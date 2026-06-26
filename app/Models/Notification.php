<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'e_notifications';

    protected $fillable = [
        'user_id',
        'type',
        'action',
        'title',
        'message',
        'data',
        'permission',
        'is_read',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to filter by permissions user has
     * Also includes notifications with null permission or media.view (visible to all)
     */
    public function scopeForUserPermissions($query, array $permissions)
    {
        // Always include media.view for all users (media notifications are public)
        $defaultPermissions = ['media.view'];
        $allPermissions = array_unique(array_merge($permissions, $defaultPermissions));

        return $query->where(function ($q) use ($allPermissions) {
            $q->whereIn('permission', $allPermissions)
                ->orWhereNull('permission');
        });
    }

    /**
     * Mark as read
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }
}
