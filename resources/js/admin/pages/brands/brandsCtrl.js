adminApp.controller("BrandsCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
        $scope.filter = {
            name: "",
            orderby: "id",
            order: "desc",
        };
        $scope.page = 1;
        $scope.per_page = 10;
        $scope.rows = [];
        $scope.meta = null;
        $scope.loading = false;

        $scope.load = () => {
            $scope.loading = true;
            $http
                .get(`${BASE_API}/brands`, {
                    params: {
                        filter: $scope.filter || undefined,
                        page: $scope.page,
                        per_page: $scope.per_page,
                    },
                })
                .then((res) => {
                    const d = res.data;
                    $scope.rows = d.data || d;
                    $scope.meta = d.meta || {
                        current_page: d.current_page,
                        last_page: d.last_page,
                    };
                })
                .catch((e) => {
                    console.error(e);
                    $toastr.show("Tải dữ liệu thất bại", "Lỗi");
                })
                .finally(() => {
                    $scope.loading = false;
                });
        };

        $scope.search = () => {
            $scope.page = 1;
            $scope.load();
        };
        $scope.goto = (p) => {
            if (!$scope.meta) return;
            if (p < 1 || p > $scope.meta.last_page) return;
            $scope.page = p;
            $scope.load();
        };

        $scope.openCreate = () => {
            window.location.href = "/admin/brands/create";
        };

        $scope.openEdit = (r) => {
            window.location.href = `/admin/brands/${r.id}/edit`;
        };

        $scope.remove = (r) => {
            if (!confirm("Bạn có chắc muốn xóa thương hiệu này?")) return;

            $http
                .delete(`${BASE_API}/brands/${r.id}`)
                .then((res) => {
                    const { status, data } = res.data || {};
                    // data ở đây là chuỗi thông báo
                    $toastr.show(
                        typeof data === "string"
                            ? data
                            : status
                            ? "Xóa thành công"
                            : "Xóa thất bại",
                        status ? "success" : "error"
                    );
                    if (status) $scope.load();
                })
                .catch((err) => {
                    const data = err?.data?.data;
                    const msg =
                        typeof data === "string" ? data : "Xóa thất bại";
                    $toastr.show(msg, "error");
                });
        };

        $scope.resetFilter = () => {
            $scope.filter.name = "";
        };

        $scope.$watch(
            "filter",
            function (nv, ov) {
                if (nv !== ov) {
                    $scope.meta.current_page = 1;
                    $scope.load();
                }
            },
            true
        );

        $scope.toggleSort = function (field) {
            if ($scope.filter.orderby === field) {
                $scope.filter.order =
                    $scope.filter.order === "asc" ? "desc" : "asc";
            } else {
                $scope.filter.orderby = field;
                $scope.filter.order = "asc";
            }
            // Không cần gọi load() ở đây vì $watch(filter) sẽ tự bắn
        };

        $scope.load();
    },
]);
