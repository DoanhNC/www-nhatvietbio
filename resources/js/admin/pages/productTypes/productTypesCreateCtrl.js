adminApp.controller("ProductTypesCreateCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
        $scope.model = { name: "", slug: "", parent_id: "" };
        $scope.saving = false;
        $scope.parents = [];

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

        $scope.save = () => {
            $scope.saving = true;
            const payload = {
                ...$scope.model,
                parent_id: $scope.model.parent_id || null,
            };
            $http
                .post(`${BASE_API}/product-types`, payload)
                .then(() => {
                    $toastr.show("Lưu thành công", "success");
                    $scope.model = { name: "", slug: "", parent_id: "" };
                })
                .catch((err) => {
                    console.error(err);
                    $toastr.show(err?.data?.message || "Lưu thất bại", "error");
                })
                .finally(() => ($scope.saving = false));
        };

        // auto slug từ name
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
            if (nv !== ov) $scope.model.slug = toSlug(nv);
        });

        $scope.loadParents();
    },
]);
