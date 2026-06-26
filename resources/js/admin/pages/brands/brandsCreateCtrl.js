adminApp.controller("BrandsCreateCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
        $scope.defaultModel = function () {
            return { name: "", slug: "", origin: "" };
        };

        $scope.model = $scope.defaultModel();
        $scope.saving = false;
        // thực hiện lưu
        $scope.save = () => {
            $scope.saving = true;
            const req = $http.post(`${BASE_API}/brands`, $scope.model);
            req.then(() => {
                $("#brandModal").modal("hide");
                $toastr.show("Lưu thành công", "success");
                //reset form
                $scope.model = $scope.defaultModel();
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
