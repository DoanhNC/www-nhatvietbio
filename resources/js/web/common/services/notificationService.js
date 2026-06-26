/**
 * Notification Service
 * Quản lý thông báo: lấy, đánh dấu đã đọc, xóa
 */
export default function (webApp) {
    webApp.factory("notificationService", [
        "$http",
        "$rootScope",
        "$interval",
        function ($http, $rootScope, $interval) {
            let pollInterval = null;
            const POLL_INTERVAL = 30000; // 30 seconds

            return {
                /**
                 * Lấy danh sách thông báo
                 */
                getNotifications: function (page = 1, status = "all") {
                    return $http.get("/rest/web/notifications", {
                        params: {
                            page: page,
                            status: status,
                            per_page: 20,
                        },
                    });
                },

                /**
                 * Lấy số thông báo chưa đọc
                 */
                getUnreadCount: function () {
                    return $http.get("/rest/web/notifications/unread-count");
                },

                /**
                 * Lấy chi tiết thông báo
                 */
                getNotification: function (id) {
                    return $http.get("/rest/web/notifications/" + id);
                },

                /**
                 * Đánh dấu thông báo là đã đọc
                 */
                markAsRead: function (id) {
                    return $http.post(
                        "/rest/web/notifications/" + id + "/mark-as-read"
                    );
                },

                /**
                 * Đánh dấu tất cả thông báo là đã đọc
                 */
                markAllAsRead: function () {
                    return $http.post(
                        "/rest/web/notifications/mark-all-as-read"
                    );
                },

                /**
                 * Xóa thông báo
                 */
                deleteNotification: function (id) {
                    return $http.delete("/rest/web/notifications/" + id);
                },

                /**
                 * Xóa tất cả thông báo
                 */
                deleteAllNotifications: function () {
                    return $http.delete("/rest/web/notifications");
                },

                /**
                 * Start polling notifications (auto refresh)
                 */
                startPolling: function () {
                    if (pollInterval) return; // Already polling

                    pollInterval = $interval(
                        function () {
                            this.getUnreadCount().then(function (res) {
                                if (res.data && res.data.data) {
                                    $rootScope.$broadcast(
                                        "notification:unreadCountUpdated",
                                        res.data.data.unread_count
                                    );
                                }
                            });
                        }.bind(this),
                        POLL_INTERVAL
                    );
                },

                /**
                 * Stop polling notifications
                 */
                stopPolling: function () {
                    if (pollInterval) {
                        $interval.cancel(pollInterval);
                        pollInterval = null;
                    }
                },
            };
        },
    ]);
}
