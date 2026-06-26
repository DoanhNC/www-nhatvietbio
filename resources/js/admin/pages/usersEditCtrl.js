adminApp.controller("UsersEditCtrl", [
    "$scope",
    "$http",
    "$timeout",
    "BASE_API",
    "$toastr",
    function ($scope, $http, $timeout, BASE_API, $toastr) {
        $scope.model = {};
        $scope.groups = [];
        $scope.saving = false;
        $scope.submitted = false;
        $scope.loadingUser = true;

        $scope.initChosen = () => {
            $timeout(() => {
                $("#groupSelect").chosen({
                    width: "100%",
                    no_results_text: "Không tìm thấy",
                });
                // Set initial values
                if ($scope.model.group_ids && $scope.model.group_ids.length) {
                    $("#groupSelect")
                        .val($scope.model.group_ids)
                        .trigger("chosen:updated");
                }
                // Sync Chosen change to AngularJS model
                $("#groupSelect").on("change", function () {
                    $scope.$apply(() => {
                        $scope.model.group_ids = $(this).val() || [];
                    });
                });
            }, 100);
        };

        $scope.loadGroups = () => {
            return $http.get(`${BASE_API}/groups`).then((res) => {
                $scope.groups = res.data.data || [];
            });
        };

        $scope.loadUser = () => {
            $scope.loadingUser = true;
            Promise.all([$scope.loadGroups()])
                .then(() => {
                    return $http.get(`${BASE_API}/users/${pageData.id}`);
                })
                .then((res) => {
                    const user = res.data.data;
                    $scope.model = {
                        ...user,
                        group_ids: (user.groups || []).map((g) => String(g.id)),
                        password: "",
                    };
                    $scope.loadingUser = false;
                    $scope.initChosen();
                    $scope.$apply();
                })
                .catch(() => {
                    $toastr.show("Không tìm thấy người dùng", "error");
                    window.location.href = pageData.listUrl;
                });
        };

        $scope.save = () => {
            $scope.submitted = true;
            if (
                !$scope.model.username ||
                !$scope.model.name ||
                !$scope.model.email
            ) {
                $toastr.show("Vui lòng điền đầy đủ thông tin", "warning");
                return;
            }

            const data = { ...$scope.model };
            if (!data.password) delete data.password;

            $scope.saving = true;
            $http
                .put(`${BASE_API}/users/${pageData.id}`, data)
                .then((res) => {
                    // Only redirect if status is true
                    if (res.data.status === true) {
                        $toastr.show(
                            res.data.message || "Cập nhật thành công",
                            "success"
                        );
                        window.location.href = pageData.listUrl;
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

        $scope.loadUser();
    },
]);
