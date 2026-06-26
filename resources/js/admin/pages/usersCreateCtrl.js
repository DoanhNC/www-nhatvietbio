adminApp.controller("UsersCreateCtrl", [
    "$scope",
    "$http",
    "$timeout",
    "BASE_API",
    "$toastr",
    function ($scope, $http, $timeout, BASE_API, $toastr) {
        $scope.model = {
            username: "",
            name: "",
            email: "",
            password: "",
            status: true,
            group_ids: [],
        };
        $scope.groups = [];
        $scope.saving = false;
        $scope.submitted = false;

        $scope.loadGroups = () => {
            $http.get(`${BASE_API}/groups`).then((res) => {
                $scope.groups = res.data.data || [];
                // Initialize Chosen after groups loaded
                $timeout(() => {
                    $("#groupSelect").chosen({
                        width: "100%",
                        no_results_text: "Không tìm thấy",
                    });
                    // Sync Chosen change to AngularJS model
                    $("#groupSelect").on("change", function () {
                        $scope.$apply(() => {
                            $scope.model.group_ids = $(this).val() || [];
                        });
                    });
                }, 100);
            });
        };

        $scope.save = () => {
            $scope.submitted = true;
            if (
                !$scope.model.username ||
                !$scope.model.name ||
                !$scope.model.email ||
                !$scope.model.password
            ) {
                $toastr.show("Vui lòng điền đầy đủ thông tin", "warning");
                return;
            }

            $scope.saving = true;
            $http
                .post(`${BASE_API}/users`, $scope.model)
                .then((res) => {
                    // Only redirect if status is true
                    if (res.data.status === true) {
                        $toastr.show(
                            res.data.message || "Tạo thành công",
                            "success"
                        );
                        window.location.href = "/admin/users";
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

        $scope.loadGroups();
    },
]);
