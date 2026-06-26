adminApp.controller("GroupsCreateCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
        $scope.model = {
            name: "",
            description: "",
            permissions: [],
        };
        $scope.availablePermissions = {};
        $scope.saving = false;
        $scope.submitted = false;

        $scope.loadPermissions = () => {
            $http.get(`${BASE_API}/groups/permissions`).then((res) => {
                $scope.availablePermissions = res.data.data || {};
            });
        };

        $scope.togglePermission = (key) => {
            const idx = $scope.model.permissions.indexOf(key);
            if (idx > -1) {
                $scope.model.permissions.splice(idx, 1);
            } else {
                $scope.model.permissions.push(key);
            }
        };

        $scope.save = () => {
            $scope.submitted = true;
            if (!$scope.model.name) {
                $toastr.show("Vui lòng nhập tên nhóm", "warning");
                return;
            }

            $scope.saving = true;
            $http
                .post(`${BASE_API}/groups`, $scope.model)
                .then((res) => {
                    if (res.data.status === true) {
                        $toastr.show(
                            res.data.message || "Tạo thành công",
                            "success"
                        );
                        window.location.href = "/admin/groups";
                    } else {
                        $toastr.show(
                            res.data.message || "Có lỗi xảy ra",
                            "error"
                        );
                    }
                })
                .catch((err) => {
                    const msg =
                        err.data?.message ||
                        Object.values(err.data?.errors || {})[0]?.[0] ||
                        "Lỗi xảy ra";
                    $toastr.show(msg, "error");
                })
                .finally(() => ($scope.saving = false));
        };

        $scope.loadPermissions();
    },
]);
