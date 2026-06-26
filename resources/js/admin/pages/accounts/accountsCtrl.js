adminApp.controller("AccountsCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
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

        $scope.load = () => {
            $scope.loading = true;
            $http
                .get(`${BASE_API}/accounts`, {
                    params: {
                        filter: $scope.filter,
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
                .catch(() => $toastr.show("Tải dữ liệu thất bại", "error"))
                .finally(() => ($scope.loading = false));
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
            (window.location.href = "/admin/accounts/create");
        $scope.openEdit = (r) =>
            (window.location.href = `/admin/accounts/${r.id}/edit`);
        $scope.remove = (r) => {
            if (!confirm("Xoá tài khoản này?")) return;
            $http
                .delete(`${BASE_API}/accounts/${r.id}`)
                .then((res) => {
                    const { status, data } = res.data || {};
                    $toastr.show(
                        typeof data === "string" ? data : "Xoá thành công",
                        status ? "success" : "error"
                    );
                    if (status) $scope.load();
                })
                .catch((err) => {
                    const data = err?.data?.data;
                    $toastr.show(
                        typeof data === "string" ? data : "Xoá thất bại",
                        "error"
                    );
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
