adminApp.controller("ProductTypesCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
        $scope.filter = {
            name: "",
            parent_id: "", // ""=tất cả, "null"=root, số=id cha
            orderby: "id",
            order: "desc",
            per_page: 10,
        };
        $scope.page = 1;
        $scope.rows = [];
        $scope.meta = null;
        $scope.loading = false;
        $scope.parents = []; // dropdown cha (tải ~all cho filter)

        $scope.loadParents = () => {
            $http
                .get(`${BASE_API}/product-types`, {
                    params: { per_page: 1000 },
                })
                .then((res) => {
                    const d = res.data;
                    $scope.parents = d.data || d;
                });
        };

        $scope.load = () => {
            $scope.loading = true;
            $http
                .get(`${BASE_API}/product-types`, {
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
            $scope.filter.parent_id = "";
        };

        $scope.openCreate = () => {
            window.location.href = "/admin/product-types/create";
        };
        $scope.openEdit = (r) => {
            window.location.href = `/admin/product-types/${r.id}/edit`;
        };

        $scope.remove = (r) => {
            if (!confirm("Bạn có chắc muốn xóa danh mục này?")) return;

            $http
                .delete(`${BASE_API}/product-types/${r.id}`)
                .then((res) => {
                    const { status, data } = res.data || {};
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

        // init
        //$scope.loadParents();
        $scope.load();
    },
]);
