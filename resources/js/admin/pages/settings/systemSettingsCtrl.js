// System Settings Controller - Admin
adminApp.controller("SystemSettingsCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    function ($scope, $http, BASE_API, $toastr) {
        const API = `${BASE_API}/media/settings`;

        // State
        $scope.loading = true;
        $scope.saving = false;
        $scope.storage = {};
        $scope.settings = {};

        // Load settings
        $scope.load = () => {
            $scope.loading = true;
            $http
                .get(API)
                .then((res) => {
                    $scope.storage = res.data.storage || {};
                    const settings = res.data.settings || {};

                    // Convert bytes to GB/MB for display
                    $scope.settings = {
                        max_storage_gb: Math.round(
                            settings.max_storage_bytes / (1024 * 1024 * 1024)
                        ),
                        max_file_size_mb: Math.round(
                            settings.max_file_size_bytes / (1024 * 1024)
                        ),
                        allowed_extensions_str: (
                            settings.allowed_extensions || []
                        ).join(", "),
                        convert_to_webp: settings.convert_to_webp || false,
                    };
                })
                .catch(() => {
                    $toastr.show("Tải cài đặt thất bại", "error");
                })
                .finally(() => {
                    $scope.loading = false;
                });
        };

        // Save settings
        $scope.saveSettings = () => {
            $scope.saving = true;

            // Convert GB/MB back to bytes
            const data = {
                max_storage_bytes:
                    $scope.settings.max_storage_gb * 1024 * 1024 * 1024,
                max_file_size_bytes:
                    $scope.settings.max_file_size_mb * 1024 * 1024,
                allowed_extensions: $scope.settings.allowed_extensions_str
                    .split(",")
                    .map((ext) => ext.trim().toLowerCase())
                    .filter((ext) => ext),
                convert_to_webp: $scope.settings.convert_to_webp,
            };

            $http
                .put(API, data)
                .then(() => {
                    $toastr.show("Lưu cài đặt thành công!", "success");
                    $scope.load(); // Reload to update storage stats
                })
                .catch((err) => {
                    $toastr.show(
                        err.data?.message || "Lỗi lưu cài đặt",
                        "error"
                    );
                })
                .finally(() => {
                    $scope.saving = false;
                });
        };

        // Init
        $scope.load();
    },
]);
