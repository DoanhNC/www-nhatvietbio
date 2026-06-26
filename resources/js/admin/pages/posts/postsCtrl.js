adminApp.controller("PostsCtrl", [
    "$scope",
    "$http",
    "$q",
    "BASE_API",
    "$toastr",
    "$confirm",
    function ($scope, $http, $q, BASE_API, $toastr, $confirm) {
        $scope.filter = {
            keyword: "",
            status: "",
            orderby: "id",
            order: "desc",
            per_page: 10,
        };
        $scope.page = 1;
        $scope.rows = [];
        $scope.meta = null;
        $scope.loading = false;

        // Canceller for HTTP requests
        var loadCanceller = null;
        var currentRequestId = 0;

        $scope.load = () => {
            // Cancel previous request if exists
            if (loadCanceller) {
                loadCanceller.resolve();
            }
            loadCanceller = $q.defer();

            // Track this request
            var thisRequestId = ++currentRequestId;

            $scope.loading = true;
            $http
                .get(`${BASE_API}/posts`, {
                    params: {
                        filter: $scope.filter,
                        page: $scope.page,
                        per_page: $scope.filter.per_page,
                    },
                    timeout: loadCanceller.promise,
                })
                .then((res) => {
                    // Only process if this is still the current request
                    if (thisRequestId !== currentRequestId) return;

                    const d = res.data;
                    $scope.rows = d.data || d;
                    $scope.meta = d.meta || {
                        current_page: d.current_page,
                        last_page: d.last_page,
                    };
                })
                .catch((err) => {
                    // Ignore cancelled requests
                    if (err && err.xhrStatus === "abort") return;
                    if (thisRequestId !== currentRequestId) return;
                    $toastr.show("Tải dữ liệu thất bại", "error");
                })
                .finally(() => {
                    // Only set loading false if this is still the current request
                    if (thisRequestId === currentRequestId) {
                        $scope.loading = false;
                    }
                });
        };

        $scope.resetFilter = () => {
            $scope.filter.keyword = "";
            $scope.filter.status = "";
        };
        $scope.goto = (p) => {
            if (!$scope.meta) return;
            if (p < 1 || p > $scope.meta.last_page) return;
            $scope.page = p;
            $scope.load();
        };
        $scope.sortIcon = (f) => ({
            "fa-sort": $scope.filter.orderby !== f,
            "fa-sort-up":
                $scope.filter.orderby === f && $scope.filter.order === "asc",
            "fa-sort-down":
                $scope.filter.orderby === f && $scope.filter.order === "desc",
        });
        $scope.toggleSort = (f) => {
            if ($scope.filter.orderby === f)
                $scope.filter.order =
                    $scope.filter.order === "asc" ? "desc" : "asc";
            else {
                $scope.filter.orderby = f;
                $scope.filter.order = "asc";
            }
        };

        $scope.openCreate = () =>
            (window.location.href = "/admin/posts/create");
        $scope.openEdit = (r) =>
            (window.location.href = `/admin/posts/${r.id}/edit`);

        // Helper: Get title for display (multi-lang support)
        $scope.getTitle = (r) => {
            if (r.titles && typeof r.titles === "object") {
                return (
                    r.titles["vi"] || r.titles["en"] || r.titles["ja"] || "-"
                );
            }
            return r.title || "-";
        };

        // Helper: Get category name (multi-lang support) - kept for backwards compatibility
        $scope.getCategoryName = (r) => {
            if (r.main_category && r.main_category.names) {
                return (
                    r.main_category.names["vi"] ||
                    r.main_category.names["en"] ||
                    "-"
                );
            }
            if (r.main_category && r.main_category.name) {
                return r.main_category.name;
            }
            return "-";
        };

        // Helper: Get main category name
        $scope.getMainCategoryName = (r) => {
            if (r.main_category && r.main_category.names) {
                return (
                    r.main_category.names["vi"] ||
                    r.main_category.names["en"] ||
                    "-"
                );
            }
            if (r.main_category && r.main_category.name) {
                return r.main_category.name;
            }
            return "-";
        };

        // Helper: Get related categories display (comma-separated)
        $scope.getRelatedCategoriesDisplay = (r) => {
            if (
                !r.categories ||
                !Array.isArray(r.categories) ||
                r.categories.length === 0
            ) {
                return "-";
            }
            // Filter out main category from related categories
            const relatedCats = r.categories.filter((cat) => {
                if (r.main_category_id && cat.id === r.main_category_id)
                    return false;
                return true;
            });
            if (relatedCats.length === 0) return "-";
            return (
                relatedCats
                    .map((cat) => {
                        if (cat.names) {
                            return (
                                cat.names["vi"] ||
                                cat.names["en"] ||
                                cat.name ||
                                ""
                            );
                        }
                        return cat.name || "";
                    })
                    .filter((n) => n)
                    .join(", ") || "-"
            );
        };

        // Load categories for filter
        $scope.categories = [];
        $scope.loadCategories = () => {
            $http.get(`${BASE_API}/post-categories/dropdown`).then((res) => {
                $scope.categories = res.data || [];
            });
        };

        $scope.remove = (r) => {
            const title = $scope.getTitle(r);
            $confirm.show({
                title: "Xóa bài viết",
                message: `Bạn có chắc muốn xóa bài viết "${title}"?`,
                icon: "fa-newspaper",
                confirmText: "Xóa bài viết",
                confirmIcon: "fa-trash",
                danger: true,
                onConfirm: () => {
                    $http
                        .delete(`${BASE_API}/posts/${r.id}`)
                        .then((res) => {
                            const { status, data } = res.data || {};
                            $toastr.show(
                                typeof data === "string"
                                    ? data
                                    : "Xoá thành công",
                                status ? "success" : "error"
                            );
                            if (status) $scope.load();
                        })
                        .catch((err) => {
                            const data = err?.data?.data;
                            $toastr.show(
                                typeof data === "string"
                                    ? data
                                    : "Xoá thất bại",
                                "error"
                            );
                        });
                },
            });
        };

        $scope.$watch(
            "filter",
            (nv, ov) => {
                if (nv !== ov) {
                    $scope.page = 1;
                    $scope.load();
                }
            },
            true
        );
        $scope.load();
        $scope.loadCategories(); // Load categories for filter dropdown
    },
]);
