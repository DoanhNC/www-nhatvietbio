adminApp.controller("AccountsCreateCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    "$q",
    function ($scope, $http, BASE_API, $toastr, $q) {
        function defaultModel() {
            return {
                email: "",
                password: "",
                full_name: "",
                phone: "",
                status: "1",
            };
        }
        function resetForm() {
            $scope.model = defaultModel();
            $scope.addresses = []; // staged
            setTimeout(() => {
                const el = document.querySelector(
                    'input[ng-model="model.email"]'
                );
                if (el) el.focus();
            }, 0);
        }

        $scope.model = defaultModel();
        $scope.saving = false;

        // ===== Addresses (staged) =====
        $scope.addresses = [];
        $scope.addrModel = {};
        $scope.addrIsEdit = false;
        $scope.addrSaving = false;

        function makeTempId() {
            return -Math.floor(Date.now() + Math.random() * 100000);
        }

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
                _staged: true,
                _tempId: a._tempId,
                label: a.label,
                receiver_name: a.receiver_name,
                phone: a.phone,
                address: a.address,
                is_default: !!a.is_default,
            };
            $("#addrModal").modal("show");
        };

        $scope.addrSave = () => {
            $scope.addrSaving = true;
            if ($scope.addrIsEdit) {
                const i = $scope.addresses.findIndex(
                    (x) => x._staged && x._tempId === $scope.addrModel._tempId
                );
                if (i >= 0) {
                    $scope.addresses[i] = {
                        ...$scope.addresses[i],
                        ...$scope.addrModel,
                    };
                }
                $("#addrModal").modal("hide");
                $scope.addrSaving = false;
                return;
            }
            $scope.addresses.push({
                _staged: true,
                _tempId: makeTempId(),
                ...$scope.addrModel,
            });
            // Nếu set default thì bỏ default ở staged khác
            if ($scope.addrModel.is_default)
                $scope.addresses.forEach((x) => {
                    if (x !== $scope.addresses[$scope.addresses.length - 1])
                        x.is_default = false;
                });
            $("#addrModal").modal("hide");
            $scope.addrSaving = false;
        };

        $scope.addrRemove = (a) => {
            $scope.addresses = $scope.addresses.filter(
                (x) => !(x._staged && x._tempId === a._tempId)
            );
        };
        $scope.addrMakeDefault = (a) => {
            $scope.addresses.forEach((x) => (x.is_default = false));
            a.is_default = true;
        };

        function flushStagedAddresses(accountId) {
            if (!$scope.addresses.length) return $q.resolve();
            const tasks = $scope.addresses.map((a) =>
                $http.post(`${BASE_API}/accounts/${accountId}/addresses`, {
                    label: a.label,
                    receiver_name: a.receiver_name,
                    phone: a.phone,
                    address: a.address,
                    is_default: a.is_default ? 1 : 0,
                })
            );
            return $q.all(tasks);
        }

        // ===== Save account =====
        $scope.save = () => {
            $scope.saving = true;
            $http
                .post(`${BASE_API}/accounts`, $scope.model)
                .then((res) => {
                    const { status, data } = res.data || {};
                    if (!status) {
                        $toastr.show(
                            typeof data === "string" ? data : "Lưu thất bại",
                            "error"
                        );
                        return;
                    }
                    const id = data?.id; // payload account
                    if (!id) {
                        $toastr.show("Không nhận được ID tài khoản", "error");
                        return;
                    }
                    return flushStagedAddresses(id).then(() => {
                        $toastr.show("Đã lưu tài khoản", "success");
                        resetForm();
                    });
                })
                .catch((err) => {
                    const data = err?.data?.data;
                    $toastr.show(
                        typeof data === "string" ? data : "Lưu thất bại",
                        "error"
                    );
                })
                .finally(() => ($scope.saving = false));
        };
    },
]);
