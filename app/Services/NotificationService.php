<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Permission mapping for notification types
     * null = show to all users (no permission required)
     */
    const PERMISSION_MAP = [
        'category' => 'post_categories.view',
        'post' => 'posts.view',
        'media' => null, // Show to all users
        'settings' => 'settings.view',
    ];

    /**
     * Create a notification
     */
    public static function notify(
        string $type,
        string $action,
        string $title,
        ?string $message = null,
        ?array $data = null
    ): Notification {
        $userId = Auth::guard('admin')->id() ?? Auth::id();
        $permission = self::PERMISSION_MAP[$type] ?? "{$type}.view";

        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'action' => $action,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'permission' => $permission,
        ]);
    }

    /**
     * Get notifications for current user based on their permissions
     * @param int $limit Max notifications to return
     * @param int|null $days Only get notifications from last N days (null = all)
     */
    public static function getForCurrentUser(int $limit = 20, ?int $days = null)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return collect();
        }

        // Eager load groups để tránh N+1 query khi gọi getAllPermissions
        if (!$user->is_root) {
            $user->load('groups');
        }

        $query = Notification::query();

        // Filter by days if specified
        if ($days !== null) {
            $query->where('created_at', '>=', now()->subDays($days)->startOfDay());
        }

        // Root user sees all notifications
        if ($user->is_root) {
            return $query->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        }

        // Get user's permissions (forUserPermissions handles null permission for public notifications)
        $permissions = $user->getAllPermissions();

        return $query->forUserPermissions($permissions)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread count for current user
     */
    public static function getUnreadCount(): int
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return 0;
        }

        // Eager load groups để tránh N+1 query
        if (!$user->is_root) {
            $user->load('groups');
        }

        if ($user->is_root) {
            return Notification::unread()->count();
        }

        $permissions = $user->getAllPermissions();

        // Use forUserPermissions which handles null permission (visible to all)
        return Notification::forUserPermissions($permissions)
            ->unread()
            ->count();
    }

    /**
     * Mark all notifications as read for current user
     */
    public static function markAllAsRead(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return;
        }

        $query = Notification::unread();

        // Eager load groups để tránh N+1 query
        if (!$user->is_root) {
            $user->load('groups');
            $permissions = $user->getAllPermissions();
            $query->forUserPermissions($permissions);
        }

        $query->update(['is_read' => true]);
    }
}
