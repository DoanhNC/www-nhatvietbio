adminApp.controller("PostCategoriesEditCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
        $scope.model = {
            id: pageData.id,
            names: {},
            parent_id: "",
            is_active: true,
        };
        $scope.languages = [];
        $scope.parentCategories = [];
        $scope.loading = true;
        $scope.saving = false;
        $scope.submitted = false;

        // Load category data
        $scope.init = () => {
            // Load the category
            $http
                .get(`${BASE_API}/post-categories/${pageData.id}`)
                .then((res) => {
                    const data = res.data.data || res.data;
                    $scope.languages = res.data.languages || [];
                    $scope.model = {
                        id: data.id,
                        names: data.names || {},
                        parent_id: data.parent_id || "",
                        is_active: data.is_active !== false,
                        show_in_menu: data.show_in_menu !== false,
                        show_related_posts: data.show_related_posts === true,
                        slug: data.slug || "",
                    };
                })
                .catch(() => $toastr.show("Không thể tải dữ liệu", "error"))
                .finally(() => ($scope.loading = false));

            // Load parent categories for dropdown
            $http
                .get(`${BASE_API}/post-categories/dropdown`, {
                    params: { exclude: pageData.id },
                })
                .then((res) => {
                    $scope.parentCategories = res.data || [];
                })
                .catch(() => {});
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
                .put(
                    `${BASE_API}/post-categories/${$scope.model.id}`,
                    $scope.model
                )
                .then((res) => {
                    const { status, data } = res.data || {};
                    $toastr.show(
                        typeof data === "string" ? data : "Cập nhật thành công",
                        status ? "success" : "error"
                    );
                    if (status) window.location.href = pageData.listUrl;
                })
                .catch((err) => {
                    const data = err?.data?.data || err?.data?.message;
                    $toastr.show(
                        typeof data === "string" ? data : "Cập nhật thất bại",
                        "error"
                    );
                })
                .finally(() => ($scope.saving = false));
        };

        $scope.init();
    },
]);
