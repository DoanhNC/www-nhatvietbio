adminApp.controller("UnitsEditCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
        $scope.model = pageData.data;
        $scope.saving = false;

        $scope.save = () => {
            $scope.saving = true;
            $http
                .put(`${BASE_API}/units/${$scope.model.id}`, $scope.model)
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
    },
]);
