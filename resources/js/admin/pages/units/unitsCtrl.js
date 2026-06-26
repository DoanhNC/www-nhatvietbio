adminApp.controller("UnitsCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
        $scope.filter = {
            name: "",
            orderby: "id",
            order: "desc",
            per_page: 10,
        };
        $scope.page = 1;
        $scope.rows = [];
        $scope.meta = null;
        $scope.loading = false;

        $scope.load = () => {
            $scope.loading = true;
            $http
                .get(`${BASE_API}/units`, {
                    params: {
                        filter: $scope.filter || undefined,
                        page: $scope.page,
                        per_page: $scope.filter.per_page,
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
                    $toastr.show("Tải dữ liệu thất bại", "error");
                })
                .finally(() => ($scope.loading = false));
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

        $scope.toggleSort = (field) => {
            if ($scope.filter.orderby === field) {
                $scope.filter.order =
                    $scope.filter.order === "asc" ? "desc" : "asc";
            } else {
                $scope.filter.orderby = field;
                $scope.filter.order = "asc";
            }
            // $watch(filter) sẽ tự load
        };

        $scope.resetFilter = () => {
            $scope.filter.name = "";
        };

        $scope.openCreate = () => {
            window.location.href = "/admin/units/create";
        };
        $scope.openEdit = (r) => {
            window.location.href = `/admin/units/${r.id}/edit`;
        };

        $scope.remove = (r) => {
            if (!confirm("Bạn có chắc muốn xóa đơn vị này?")) return;

            $http
                .delete(`${BASE_API}/units/${r.id}`)
                .then((res) => {
                    const { status, data } = res.data || {};
                    $toastr.show(
                        typeof data === "string"
                            ? data
                            : status
                            ? "Xóa thành công"
                            : "Xóa thất bại",
                        status ? "Thành công" : "Lỗi"
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
    },
]);
