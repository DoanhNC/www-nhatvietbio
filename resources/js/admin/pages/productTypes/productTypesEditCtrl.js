adminApp.controller("ProductTypesEditCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
        $scope.model = pageData.data;
        $scope.model.parent_id = $scope.model.parent_id || "";
        $scope.saving = false;
        $scope.parents = [];

        $scope.loadParents = () => {
            $http
                .get(`${BASE_API}/product-types`, {
                    params: { per_page: 1000 },
                })
                .then((res) => {
                    const d = res.data;
                    let list = d.data || d;
                    // Loại chính nó khỏi dropdown cha
                    list = list.filter((x) => x.id !== $scope.model.id);
                    $scope.parents = list;
                });
        };

        $scope.save = () => {
            $scope.saving = true;
            const payload = {
                ...$scope.model,
                parent_id: $scope.model.parent_id || null,
            };
            $http
                .put(`${BASE_API}/product-types/${$scope.model.id}`, payload)
                .then(() => {
                    $toastr.show("Cập nhật thành công", "success");
                    window.location.href = pageData.listUrl;
                })
                .catch((err) => {
                    console.error(err);
                    $toastr.show(
                        err?.data?.message || "Cập nhật thất bại",
                        "error"
                    );
                })
                .finally(() => ($scope.saving = false));
        };

        // auto slug từ name (nếu muốn update slug khi đổi tên)
        function toSlug(str) {
            return (str || "")
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .replace(/[^a-z0-9\s-]/g, "")
                .replace(/\s+/g, "-")
                .replace(/-+/g, "-")
                .replace(/^-+|-+$/g, "");
        }
        $scope.$watch("model.name", function (nv, ov) {
            if (nv !== ov && !$scope.model.slug) {
                $scope.model.slug = toSlug(nv);
            }
        });

        $scope.loadParents();
    },
]);
