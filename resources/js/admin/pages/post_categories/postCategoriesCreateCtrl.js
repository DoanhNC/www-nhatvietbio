adminApp.controller("PostCategoriesCreateCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
        $scope.model = {
            names: {},
            parent_id: "",
            is_active: true,
            show_in_menu: true,
            show_related_posts: true,
        };
        $scope.languages = [];
        $scope.parentCategories = [];
        $scope.loadingLanguages = true;
        $scope.saving = false;
        $scope.submitted = false;

        // Load languages and parent categories
        $scope.init = () => {
            $http
                .get(`${BASE_API}/post-categories/dropdown`)
                .then((res) => {
                    $scope.parentCategories = res.data || [];
                })
                .catch(() => {});

            $http
                .get(`${BASE_API}/languages`, {
                    params: { active_only: "true" },
                })
                .then((res) => {
                    $scope.languages = res.data.languages || [];
                    // Initialize names object with empty values
                    $scope.languages.forEach((lang) => {
                        $scope.model.names[lang.code] = "";
                    });
                })
                .catch(() => $toastr.show("Không thể tải ngôn ngữ", "error"))
                .finally(() => ($scope.loadingLanguages = false));
        };

        $scope.validate = () => {
            // Find default language and check if it has value
            const defaultLang = $scope.languages.find((l) => l.is_default);
            if (defaultLang && !$scope.model.names[defaultLang.code]) {
                return false;
            }
            // Check if at least one name is provided
            const hasAnyName = Object.values($scope.model.names).some(
                (v) => v && v.trim()
            );
            return hasAnyName;
        };

        $scope.save = () => {
            $scope.submitted = true;

            if (!$scope.validate()) {
                $toastr.show(
                    "Vui lòng nhập tên cho ngôn ngữ mặc định",
                    "error"
                );
                return;
            }

            $scope.saving = true;
            $http
                .post(`${BASE_API}/post-categories`, $scope.model)
                .then((res) => {
                    const { status, data } = res.data || {};
                    $toastr.show(
                        typeof data === "string" ? data : "Đã lưu",
                        "success"
                    );
                    // Redirect to list
                    window.location.href = "/admin/post-categories";
                })
                .catch((err) => {
                    const data = err?.data?.data || err?.data?.message;
                    $toastr.show(
                        typeof data === "string" ? data : "Lưu thất bại",
                        "error"
                    );
                })
                .finally(() => ($scope.saving = false));
        };

        $scope.init();
    },
]);
