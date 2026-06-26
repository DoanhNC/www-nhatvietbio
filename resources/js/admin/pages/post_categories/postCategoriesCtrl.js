adminApp.controller("PostCategoriesCtrl", [
    "$scope",
    "$http",
    "$q",
    "BASE_API",
    "$toastr",
    "$confirm",
    function ($scope, $http, $q, BASE_API, $toastr, $confirm) {
        $scope.filter = {
            keyword: "",
            orderby: "position",
            order: "asc",
            per_page: 100,
        };
        $scope.rows = [];
        $scope.languages = [];
        $scope.meta = null;
        $scope.loading = false;

        // Canceller for HTTP requests
        var loadCanceller = null;
        var currentRequestId = 0;

        // Load và sắp xếp: cha trước, con theo sau
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
                .get(`${BASE_API}/post-categories`, {
                    params: {
                        filter: JSON.stringify($scope.filter),
                        tree: true,
                    },
                    timeout: loadCanceller.promise,
                })
                .then((res) => {
                    // Only process if this is still the current request
                    if (thisRequestId !== currentRequestId) return;

                    const d = res.data;
                    $scope.languages = d.languages || [];

                    // Flatten tree: parent + children ngay sau
                    const flatList = [];
                    const parents = d.data || [];

                    parents.forEach((parent) => {
                        flatList.push(parent);
                        if (parent.children && parent.children.length > 0) {
                            parent.children.forEach((child) => {
                                flatList.push(child);
                            });
                        }
                    });

                    $scope.rows = flatList;
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
            (window.location.href = "/admin/post-categories/create");
        $scope.openEdit = (r) =>
            (window.location.href = `/admin/post-categories/${r.id}/edit`);

        // Helper: Get category name for display
        $scope.getCategoryName = (r) => {
            if (r.names && typeof r.names === "object") {
                return r.names["vi"] || r.names["en"] || "-";
            }
            return r.name || "-";
        };

        $scope.remove = (r) => {
            const name = $scope.getCategoryName(r);
            $confirm.show({
                title: "Xóa danh mục",
                message: `Bạn có chắc muốn xóa danh mục "${name}"?`,
                icon: "fa-folder",
                confirmText: "Xóa danh mục",
                confirmIcon: "fa-trash",
                danger: true,
                onConfirm: () => {
                    $http
                        .delete(`${BASE_API}/post-categories/${r.id}`)
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
                            const message =
                                err?.data?.message || err?.data?.data;
                            $toastr.show(
                                typeof message === "string"
                                    ? message
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
                    $scope.load();
                }
            },
            true
        );

        // Move category position up (swap with previous) - only for parents
        $scope.moveUp = (row) => {
            if (row.parent_id) return; // Only parents can be moved

            const parentRows = $scope.rows.filter((r) => !r.parent_id);
            const index = parentRows.findIndex((r) => r.id === row.id);

            if (index <= 0) return;

            const current = parentRows[index];
            const prev = parentRows[index - 1];

            const positions = [
                { id: current.id, position: prev.position, parent_id: null },
                { id: prev.id, position: current.position, parent_id: null },
            ];

            $http
                .post(`${BASE_API}/post-categories/positions`, { positions })
                .then(() => {
                    $toastr.show("Đã di chuyển lên", "success");
                    $scope.load();
                })
                .catch(() => $toastr.show("Lỗi khi di chuyển", "error"));
        };

        // Move category position down (swap with next) - only for parents
        $scope.moveDown = (row) => {
            if (row.parent_id) return; // Only parents can be moved

            const parentRows = $scope.rows.filter((r) => !r.parent_id);
            const index = parentRows.findIndex((r) => r.id === row.id);

            if (index < 0 || index >= parentRows.length - 1) return;

            const current = parentRows[index];
            const next = parentRows[index + 1];

            const positions = [
                { id: current.id, position: next.position, parent_id: null },
                { id: next.id, position: current.position, parent_id: null },
            ];

            $http
                .post(`${BASE_API}/post-categories/positions`, { positions })
                .then(() => {
                    $toastr.show("Đã di chuyển xuống", "success");
                    $scope.load();
                })
                .catch(() => $toastr.show("Lỗi khi di chuyển", "error"));
        };

        $scope.load();
    },
]);
