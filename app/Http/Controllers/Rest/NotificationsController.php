<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    /**
     * Get notifications for current user
     * Query params:
     * - limit: max notifications (default 50)
     * - days: only get last N days (default 3)
     * - all: if 1, get all notifications (ignore days filter)
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 50);
        $all = $request->boolean('all', false);
        $days = $all ? null : (int) $request->get('days', 3);

        $notifications = NotificationService::getForCurrentUser($limit, $days);

        return ApiResponse::success([
            'notifications' => $notifications,
            'unread_count' => NotificationService::getUnreadCount(),
            'showing_days' => $days,
        ]);
    }

    /**
     * Get unread count only
     */
    public function unreadCount()
    {
        return ApiResponse::success([
            'count' => NotificationService::getUnreadCount(),
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return ApiResponse::error('Thông báo không tồn tại', 404);
        }

        $notification->markAsRead();

        return ApiResponse::success(null, 200, 'Đã đánh dấu đã đọc');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        NotificationService::markAllAsRead();

        return ApiResponse::success(null, 200, 'Đã đánh dấu tất cả đã đọc');
    }
}
