adminApp.controller("BrandsEditCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
        $scope.model = pageData.data;
        $scope.saving = false;

        $scope.save = () => {
            $scope.saving = true;
            const req = $http.put(
                `${BASE_API}/brands/${$scope.model.id}`,
                $scope.model
            );

            req.then(() => {
                $toastr.show("Cập nhật thương hiệu thành công", "success");
                // tự động back về trang danh sách
                window.location.href = pageData.listUrl;
            })
                .catch((err) => {
                    console.error(err);
                    $toastr.show(err?.data?.message || "Lưu thất bại", "error");
                })
                .finally(() => {
                    $scope.saving = false;
                });
        };

        // thực hiện tạo slug theo tên
        function toSlug(str) {
            return str
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "") // loại bỏ dấu tiếng Việt
                .replace(/[^a-z0-9\s-]/g, "") // loại bỏ ký tự đặc biệt
                .replace(/\s+/g, "-") // thay khoảng trắng bằng dấu gạch ngang
                .replace(/-+/g, "-") // loại bỏ gạch ngang liên tiếp
                .replace(/^-+|-+$/g, ""); // loại bỏ gạch ngang ở đầu/cuối
        }

        // Watch name để cập nhật slug
        $scope.$watch("model.name", function (newVal, oldVal) {
            if (!$scope.isEdit && newVal !== oldVal) {
                $scope.model.slug = toSlug(newVal || "");
            }
        });
    },
]);
