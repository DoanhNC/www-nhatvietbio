adminApp.controller("AccountsEditCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
        $scope.model = pageData.data;
        $scope.model.status = String($scope.model.status ?? "1");
        $scope.saving = false;

        // Load addresses
        $scope.addresses = $scope.model.addresses || [];

        $scope.loadAddresses = () =>
            $http
                .get(`${BASE_API}/accounts/${$scope.model.id}/addresses`)
                .then((r) => ($scope.addresses = r.data || []));

        $scope.openAddrCreate = () => {
            $scope.addrIsEdit = false;
            $scope.addrModel = {
                label: "",
                receiver_name: "",
                phone: "",
                address: "",
                is_default: false,
            };
            $("#addrModal").modal("show");
        };
        $scope.openAddrEdit = (a) => {
            $scope.addrIsEdit = true;
            $scope.addrModel = {
                id: a.id,
                label: a.label,
                receiver_name: a.receiver_name,
                phone: a.phone,
                address: a.address,
                is_default: !!a.is_default,
            };
            $("#addrModal").modal("show");
        };

        $scope.addrSaving = false;
        $scope.addrSave = () => {
            $scope.addrSaving = true;
            const payload = {
                label: $scope.addrModel.label,
                receiver_name: $scope.addrModel.receiver_name,
                phone: $scope.addrModel.phone,
                address: $scope.addrModel.address,
                is_default: $scope.addrModel.is_default ? 1 : 0,
            };
            const req = $scope.addrIsEdit
                ? $http.put(
                      `${BASE_API}/accounts/${$scope.model.id}/addresses/${$scope.addrModel.id}`,
                      payload
                  )
                : $http.post(
                      `${BASE_API}/accounts/${$scope.model.id}/addresses`,
                      payload
                  );

            req.then((res) => {
                const { status, data } = res.data || {};
                $toastr.show(
                    typeof data === "string" ? data : "Lưu địa chỉ thành công",
                    status ? "success" : "error"
                );
                if (status) {
                    $("#addrModal").modal("hide");
                    $scope.loadAddresses();
                }
            })
                .catch((err) => {
                    const data = err?.data?.data;
                    $toastr.show(
                        typeof data === "string"
                            ? data
                            : "Lưu địa chỉ thất bại",
                        "error"
                    );
                })
                .finally(() => ($scope.addrSaving = false));
        };

        $scope.addrRemove = (a) => {
            if (!confirm("Xoá địa chỉ này?")) return;
            $http
                .delete(
                    `${BASE_API}/accounts/${$scope.model.id}/addresses/${a.id}`
                )
                .then((res) => {
                    const { status, data } = res.data || {};
                    $toastr.show(
                        typeof data === "string" ? data : "Đã xoá",
                        "success"
                    );
                    if (status) $scope.loadAddresses();
                })
                .catch((err) => {
                    const data = err?.data?.data;
                    $toastr.show(
                        typeof data === "string" ? data : "Xoá thất bại",
                        "error"
                    );
                });
        };

        $scope.addrMakeDefault = (a) => {
            $http
                .post(
                    `${BASE_API}/accounts/${$scope.model.id}/addresses/${a.id}/default`
                )
                .then((res) => {
                    const { status, data } = res.data || {};
                    $toastr.show(
                        typeof data === "string" ? data : "Đã đặt mặc định",
                        status ? "success" : "error"
                    );
                    if (status) $scope.loadAddresses();
                })
                .catch((err) => {
                    const data = err?.data?.data;
                    $toastr.show(
                        typeof data === "string" ? data : "Thất bại",
                        "error"
                    );
                });
        };

        $scope.save = () => {
            $scope.saving = true;
            const payload = { ...$scope.model };
            if (!payload.password) delete payload.password; // không đổi password nếu để trống
            $http
                .put(`${BASE_API}/accounts/${payload.id}`, payload)
                .then((res) => {
                    const { status, data } = res.data || {};
                    $toastr.show(
                        typeof data === "string" ? data : "Cập nhật thành công",
                        status ? "success" : "error"
                    );
                    if (status) window.location.href = pageData.listUrl;
                })
                .catch((err) => {
                    const data = err?.data?.data;
                    $toastr.show(
                        typeof data === "string" ? data : "Cập nhật thất bại",
                        "error"
                    );
                })
                .finally(() => ($scope.saving = false));
        };
    },
]);
