adminApp.controller("UsersCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    "$confirm",
    function ($scope, $http, BASE_API, $toastr, $confirm) {
        $scope.filter = {
            keyword: "",
            status: "",
            orderby: "id",
            order: "asc",
            per_page: 50,
        };
        $scope.page = 1;
        $scope.rows = [];
        $scope.meta = null;
        $scope.loading = false;

        $scope.load = () => {
            $scope.loading = true;
            $http
                .get(`${BASE_API}/users`, {
                    params: {
                        search: $scope.filter.keyword,
                        status: $scope.filter.status,
                        page: $scope.page,
                    },
                })
                .then((res) => {
                    const d = res.data;
                    $scope.rows = d.data || [];
                    $scope.meta = {
                        current_page: d.current_page || 1,
                        last_page: d.last_page || 1,
                        total: d.total || 0,
                    };
                })
                .catch(() => $toastr.show("Tải dữ liệu thất bại", "error"))
                .finally(() => ($scope.loading = false));
        };

        $scope.resetFilter = () => {
            $scope.filter.keyword = "";
            $scope.filter.status = "";
            $scope.load();
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
            $scope.load();
        };

        $scope.openCreate = () =>
            (window.location.href = "/admin/users/create");
        $scope.openEdit = (r) =>
            (window.location.href = `/admin/users/${r.id}/edit`);

        $scope.remove = (r) => {
            $confirm.show({
                title: "Xóa người dùng",
                message: `Bạn có chắc muốn xóa người dùng "${r.username}"?`,
                icon: "fa-user",
                confirmText: "Xóa người dùng",
                confirmIcon: "fa-trash",
                danger: true,
                onConfirm: () => {
                    $http
                        .delete(`${BASE_API}/users/${r.id}`)
                        .then((res) => {
                            $toastr.show(
                                res.data.message || "Xoá thành công",
                                "success"
                            );
                            $scope.load();
                        })
                        .catch((err) => {
                            const message =
                                err?.data?.message || "Xoá thất bại";
                            $toastr.show(message, "error");
                        });
                },
            });
        };

        $scope.load();
    },
]);
