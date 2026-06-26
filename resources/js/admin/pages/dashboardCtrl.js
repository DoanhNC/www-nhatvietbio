adminApp.controller("DashboardCtrl", [
    "$scope",
    "$http",
    "$interval",
    "$timeout",
    "BASE_API",
    "$toastr",
    function ($scope, $http, $interval, $timeout, BASE_API, $toastr) {
        // Posts stats state
        $scope.postsStats = { total: 0, published: 0, draft: 0 };
        $scope.statsFilter = {
            from_date: null,
            to_date: null,
        };
        $scope.statsActiveRange = -1; // -1=all, 0=today, 7, 30
        $scope.loadingStats = false;

        // Visitor stats state
        $scope.visitorStats = {
            online: 0,
            today: 0,
            this_week: 0,
            this_month: 0,
            total: 0,
        };
        $scope.loadingVisitorStats = false;

        // Media stats state
        $scope.mediaStats = {
            used_bytes: 0,
            max_bytes: 0,
            formatted_used: "0 B",
            formatted_max: "0 B",
            percentage: 0,
        };

        // Website config state
        $scope.websiteConfig = {};
        $scope.smtpConfig = {};

        // Top viewed posts state
        $scope.topPosts = [];
        $scope.loadingTopPosts = false;

        // Chart instances
        let visitorChart = null;
        let storageChart = null;

        // Set stats date range (quick select)
        $scope.setStatsRange = (days) => {
            $scope.statsActiveRange = days;
            const today = new Date();

            if (days === -1) {
                // All time - no date filter
                $scope.statsFilter.from_date = null;
                $scope.statsFilter.to_date = null;
            } else {
                const from = new Date();
                from.setDate(today.getDate() - days);
                $scope.statsFilter.from_date = from.toISOString().slice(0, 10);
                $scope.statsFilter.to_date = today.toISOString().slice(0, 10);
            }
            $scope.loadPostsStats();
        };

        // Load posts stats from API
        $scope.loadPostsStats = () => {
            $scope.loadingStats = true;
            $http
                .get(`${BASE_API}/posts/stats`, {
                    params: $scope.statsFilter,
                })
                .then((res) => {
                    $scope.postsStats = {
                        total: res.data.total || 0,
                        published: res.data.published || 0,
                        draft: res.data.draft || 0,
                    };
                })
                .catch(() => $toastr.show("Tải thống kê thất bại", "error"))
                .finally(() => ($scope.loadingStats = false));
        };

        // Update visitor donut chart
        const updateVisitorChart = () => {
            const ctx = document.getElementById("visitorPieChart");
            if (!ctx) return;

            const data = [
                $scope.visitorStats.online || 0,
                $scope.visitorStats.today || 0,
                $scope.visitorStats.this_week || 0,
                $scope.visitorStats.this_month || 0,
            ];

            if (visitorChart) {
                visitorChart.data.datasets[0].data = data;
                visitorChart.update();
            } else {
                visitorChart = new Chart(ctx, {
                    type: "doughnut",
                    data: {
                        labels: ["Online", "Hôm nay", "Tuần này", "Tháng này"],
                        datasets: [
                            {
                                data: data,
                                backgroundColor: [
                                    "#1cc88a",
                                    "#f6c23e",
                                    "#4e73df",
                                    "#e74a3b",
                                ],
                                hoverBackgroundColor: [
                                    "#17a673",
                                    "#dda20a",
                                    "#2e59d9",
                                    "#be2617",
                                ],
                                hoverBorderColor: "rgba(234, 236, 244, 1)",
                            },
                        ],
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyColor: "#858796",
                                titleColor: "#6e707e",
                                borderColor: "#dddfeb",
                                borderWidth: 1,
                                padding: 15,
                                displayColors: false,
                            },
                        },
                        cutout: "80%",
                    },
                });
            }
        };

        // Update storage donut chart
        const updateStorageChart = () => {
            const ctx = document.getElementById("storagePieChart");
            if (!ctx) return;

            const usedBytes = $scope.mediaStats.used_bytes || 0;
            const maxBytes = $scope.mediaStats.max_bytes || 1;
            const freeBytes = Math.max(0, maxBytes - usedBytes);

            if (storageChart) {
                storageChart.data.datasets[0].data = [usedBytes, freeBytes];
                storageChart.update();
            } else {
                storageChart = new Chart(ctx, {
                    type: "doughnut",
                    data: {
                        labels: ["Đã dùng", "Còn trống"],
                        datasets: [
                            {
                                data: [usedBytes, freeBytes],
                                backgroundColor: ["#f6c23e", "#e0e0e0"],
                                hoverBackgroundColor: ["#dda20a", "#c0c0c0"],
                                hoverBorderColor: "rgba(234, 236, 244, 1)",
                            },
                        ],
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyColor: "#858796",
                                titleColor: "#6e707e",
                                borderColor: "#dddfeb",
                                borderWidth: 1,
                                padding: 15,
                                displayColors: false,
                                callbacks: {
                                    label: function (context) {
                                        const bytes = context.raw;
                                        return formatBytes(bytes);
                                    },
                                },
                            },
                        },
                        cutout: "80%",
                    },
                });
            }
        };

        // Format bytes helper
        const formatBytes = (bytes) => {
            if (bytes === 0) return "0 B";
            const k = 1024;
            const sizes = ["B", "KB", "MB", "GB", "TB"];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return (
                parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i]
            );
        };

        // Load visitor stats from API
        $scope.loadVisitorStats = () => {
            $scope.loadingVisitorStats = true;
            $http
                .get(`${BASE_API}/user-activity/stats`)
                .then((res) => {
                    if (res.data.status && res.data.data) {
                        $scope.visitorStats = res.data.data;
                        $timeout(() => updateVisitorChart(), 100);
                    }
                })
                .catch(() => {})
                .finally(() => ($scope.loadingVisitorStats = false));
        };

        // Load media stats from API
        $scope.loadMediaStats = (showToast = false) => {
            $http
                .get(`${BASE_API}/media/settings`)
                .then((res) => {
                    const storage = res.data.storage || {};
                    const usedBytes = storage.used_bytes || 0;
                    const maxBytes = storage.max_bytes || 0;
                    const freeBytes =
                        storage.free_bytes || Math.max(0, maxBytes - usedBytes);
                    $scope.mediaStats = {
                        used_bytes: usedBytes,
                        max_bytes: maxBytes,
                        free_bytes: freeBytes,
                        formatted_used:
                            storage.used_formatted || formatBytes(usedBytes),
                        formatted_max:
                            storage.max_formatted || formatBytes(maxBytes),
                        formatted_free:
                            storage.free_formatted || formatBytes(freeBytes),
                        percentage: storage.used_percent || 0,
                    };
                    $timeout(() => updateStorageChart(), 100);
                    if (showToast) {
                        $toastr.show(
                            "Đã làm mới thông tin dung lượng",
                            "success"
                        );
                    }
                })
                .catch(() => {
                    if (showToast) {
                        $toastr.show("Làm mới thông tin thất bại", "error");
                    }
                });
        };

        // Load website config
        $scope.loadWebsiteConfig = () => {
            $http
                .get(`${BASE_API}/website-settings`)
                .then((res) => {
                    if (res.data.status && res.data.data) {
                        $scope.websiteConfig = res.data.data.website || {};
                        $scope.smtpConfig = res.data.data.smtp || {};
                    }
                })
                .catch(() => {});
        };

        // Load top viewed posts
        $scope.loadTopPosts = () => {
            $scope.loadingTopPosts = true;
            $http
                .get(`${BASE_API}/posts/top-viewed?limit=5`)
                .then((res) => {
                    if (res.data.status && res.data.data) {
                        $scope.topPosts = res.data.data;
                    }
                })
                .catch(() => {})
                .finally(() => ($scope.loadingTopPosts = false));
        };

        // Init - load all stats
        $scope.loadPostsStats();
        $scope.loadVisitorStats();
        $scope.loadMediaStats();
        $scope.loadWebsiteConfig();
        $scope.loadTopPosts();

        // Auto-refresh visitor stats every 30 seconds
        const visitorRefresh = $interval(() => {
            $scope.loadVisitorStats();
        }, 30000);

        // Cleanup on scope destroy
        $scope.$on("$destroy", () => {
            $interval.cancel(visitorRefresh);
            if (visitorChart) visitorChart.destroy();
            if (storageChart) storageChart.destroy();
        });
    },
]);
