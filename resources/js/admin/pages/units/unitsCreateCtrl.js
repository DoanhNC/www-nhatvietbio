adminApp.controller("UnitsCreateCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
        $scope.model = { code: "", name: "" };
        $scope.saving = false;

        $scope.save = () => {
            $scope.saving = true;
            $http
                .post(`${BASE_API}/units`, $scope.model)
                .then(() => {
                    $toastr.show("Lưu thành công", "success");
                    $scope.model = { code: "", name: "" }; // reset
                })
                .catch((err) => {
                    console.error(err);
                    $toastr.show(err?.data?.message || "Lưu thất bại", "error");
                })
                .finally(() => ($scope.saving = false));
        };
    },
]);
