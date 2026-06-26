import angular from "angular";
import { adminCore } from "../common/app.js";

const app = angular.module("adminLoginApp", [adminCore.name]);

console.log("12345-----------", 12345);

app.controller("LoginCtrl", [
    "$scope",
    "$http",
    "$window",
    "BASE_API",
    "$toastr",
    function ($scope, $http, $window, BASE_API, $toastr) {
        $scope.login = "";
        $scope.password = "";
        $scope.loading = false;

        $scope.doLogin = function () {
            // console.log("-----------", $scope.login, $scope.password);

            // return;
            if (!$scope.login || !$scope.password) {
                $toastr.show("Nhập tài khoản & mật khẩu", "warning");
                return;
            }
            $scope.loading = true;
            $http
                .post(`/admin/login`, {
                    login: $scope.login,
                    password: $scope.password,
                })
                .then((res) => {
                    $toastr.show("Đăng nhập thành công", "success");
                    $window.location.href = "/admin/dashboard";
                })
                .catch((err) => {
                    $toastr.show(
                        err?.data?.message || "Đăng nhập thất bại",
                        "error"
                    );
                })
                .finally(() => {
                    $scope.loading = false;
                });
        };
    },
]);

export default app;
