// Giống 100% cách admin dùng: adminApp.controller("PostsCtrl", [...])
// webApp là biến toàn cục đã được gán trong webApp.js: window.webApp = webApp
webApp.controller("SameCategoryPostsCtrl", [
    "$scope",
    "$http",
    "$q",
    "$toastr",
    function ($scope, $http, $q, $toastr) {
        $scope.posts = [];
        $scope.meta = null;
        $scope.pages = [];
        $scope.loading = false;

        // Lấy thông số từ window.sidebarConfig được cấu hình sẵn trong Blade
        const categoryId = window.sidebarConfig
            ? window.sidebarConfig.categoryId
            : 0;
        const excludeId = window.sidebarConfig
            ? window.sidebarConfig.excludeId
            : 0;

        // Canceller for HTTP requests (giống admin PostsCtrl)
        var loadCanceller = null;
        var currentRequestId = 0;

        function getPageRange(current, last) {
            if (last <= 5) {
                let pages = [];
                for (let i = 1; i <= last; i++) {
                    pages.push(i);
                }
                return pages;
            }

            let pages = [];
            
            // Luôn thêm trang đầu tiên
            pages.push(1);
            
            let start = Math.max(2, current - 1);
            let end = Math.min(last - 1, current + 1);
            
            if (start > 2) {
                pages.push('...');
            }
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            if (end < last - 1) {
                pages.push('...');
            }
            
            // Luôn thêm trang cuối cùng
            pages.push(last);
            
            return pages;
        }

        $scope.load = (page) => {
            if (loadCanceller) {
                loadCanceller.resolve();
            }
            loadCanceller = $q.defer();
            var thisRequestId = ++currentRequestId;

            $scope.loading = true;
            $http
                .get("/api/category-posts", {
                    params: {
                        category_id: categoryId,
                        exclude_id: excludeId,
                        page: page,
                    },
                    timeout: loadCanceller.promise,
                })
                .then((res) => {
                    if (thisRequestId !== currentRequestId) return;
                    const d = res.data;
                    if (d.success) {
                        $scope.posts = d.data || [];
                        $scope.meta = d.meta;
                        $scope.pages = getPageRange(d.meta.current_page, d.meta.last_page);
                    }
                })
                .catch((err) => {
                    if (err && err.xhrStatus === "abort") return;
                    if (thisRequestId !== currentRequestId) return;
                    console.error("Lỗi khi tải bài viết cùng danh mục:", err);
                    $toastr.show("Tải bài viết liên quan thất bại", "error");
                })
                .finally(() => {
                    if (thisRequestId === currentRequestId) {
                        $scope.loading = false;
                    }
                });
        };

        $scope.goto = (p) => {
            if (p === '...') return;
            if (!$scope.meta) return;
            if (p < 1 || p > $scope.meta.last_page) return;
            $scope.load(p);
        };

        // Khởi chạy tải trang đầu tiên
        if (categoryId) {
            $scope.load(1);
        }
    },
]);
