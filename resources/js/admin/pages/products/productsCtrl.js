adminApp.controller("ProductsCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
        $scope.filter = {
            keyword: "",
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
                .get(`${BASE_API}/products`, {
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

        $scope.sortIcon = (f) => ({
            "fa-sort": $scope.filter.orderby !== f,
            "fa-sort-up":
                $scope.filter.orderby === f && $scope.filter.order === "asc",
            "fa-sort-down":
                $scope.filter.orderby === f && $scope.filter.order === "desc",
        });

        $scope.toggleSort = (f) => {
            if ($scope.filter.orderby === f) {
                $scope.filter.order =
                    $scope.filter.order === "asc" ? "desc" : "asc";
            } else {
                $scope.filter.orderby = f;
                $scope.filter.order = "asc";
            }
        };

        $scope.openCreate = () =>
            (window.location.href = "/admin/products/create");
        $scope.openEdit = (r) =>
            (window.location.href = `/admin/products/${r.id}/edit`);
        $scope.remove = (r) => {
            if (!confirm("Xoá sản phẩm này?")) return;
            $http
                .delete(`${BASE_API}/products/${r.id}`)
                .then(() => {
                    $toastr.show("Đã xoá", "success");
                    $scope.load();
                })
                .catch(() => $toastr.show("Xoá thất bại", "error"));
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

        $scope.pageData = {
            mediaList: [
                { id: 1, name: "c", type: "folder" },
                { id: 2, name: "banner-left.jpg", type: "file" },
                { id: 3, name: "instagram1.jpg", type: "file" },
                { id: 4, name: "instagram2.webp", type: "file" },
                { id: 5, name: "instagram3.webp", type: "file" },
                { id: 6, name: "instagram4.jpg", type: "file" },
                { id: 7, name: "instagram5.webp", type: "file" },
                { id: 8, name: "instagram6.webp", type: "file" },
                { id: 9, name: "morgan.jpg", type: "file" },
            ],
        };
        $scope.form = { selection: [] };
        $scope.handleConfirm = function (files) {
            console.log("CONFIRM:", files);
        };
    },
]);
