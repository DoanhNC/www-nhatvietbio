// Languages Controller - Admin Settings
// Following Posts pattern for consistency

adminApp.controller("LanguagesCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    "$confirm",
    function ($scope, $http, BASE_API, $toastr, $confirm) {
        const API = `${BASE_API}/languages`;

        // State
        $scope.rows = [];
        $scope.loading = false;
        $scope.saving = false;
        $scope.form = {};
        $scope.currentLang = {};
        $scope.flatTrans = {};
        $scope.transSearch = "";
        $scope.filteredKeys = []; // Keys after search filter
        $scope.search = { text: "" }; // Object-based model for modal scope
        $scope.displayedKeys = []; // Keys to display after filtering
        $scope.newlyAddedKeys = [];
        $scope.newKey = { key: "", value: "", error: null };
        $scope.jsonImport = {
            content: "",
            error: null,
            preview: null,
            keyCount: 0,
            parsedData: null,
        };

        // Load all languages
        $scope.load = () => {
            $scope.loading = true;
            $http
                .get(API)
                .then((res) => {
                    $scope.rows = res.data.languages || [];
                })
                .catch(() => $toastr.show("Tải dữ liệu thất bại", "error"))
                .finally(() => ($scope.loading = false));
        };

        // Open create modal
        $scope.openCreate = () => {
            $scope.form = {
                code: "",
                name: "",
                flag_icon: "",
                is_active: true,
                copy_from: "",
            };
            $("#langModal").modal("show");
        };

        // Open media picker modal
        $scope.openMediaPicker = () => {
            $("#mediaPickerModal").modal("show");
        };

        // Handle media selection from picker
        $scope.onMediaSelect = (files) => {
            if (files && files.url) {
                $scope.form.flag_icon = files.url;
            }
            $("#mediaPickerModal").modal("hide");
        };

        // Open edit modal
        $scope.openEdit = (r) => {
            $scope.form = angular.copy(r);
            $("#langModal").modal("show");
        };

        // Save (create or update)
        $scope.save = () => {
            if (!$scope.form.code || !$scope.form.name) {
                $toastr.show("Vui lòng nhập mã và tên ngôn ngữ", "warning");
                return;
            }

            $scope.saving = true;
            const isEdit = !!$scope.form.id;
            const req = isEdit
                ? $http.put(`${API}/${$scope.form.id}`, $scope.form)
                : $http.post(API, $scope.form);

            req.then((res) => {
                const defaultMsg = isEdit
                    ? "Cập nhật thành công"
                    : "Thêm thành công";
                const msg = res.data?.message || res.data?.data || defaultMsg;
                $toastr.show(msg, "success");
                $("#langModal").modal("hide");
                $scope.load();
            })
                .catch((err) => {
                    $toastr.show(
                        err.data?.message || "Lỗi lưu ngôn ngữ",
                        "error"
                    );
                })
                .finally(() => ($scope.saving = false));
        };

        // Remove
        $scope.remove = (r) => {
            $confirm.show({
                title: "Xóa ngôn ngữ",
                message: `Bạn có chắc muốn xóa ngôn ngữ "${r.name}"?`,
                icon: "fa-globe",
                confirmText: "Xóa ngôn ngữ",
                confirmIcon: "fa-trash",
                danger: true,
                onConfirm: () => {
                    $http
                        .delete(`${API}/${r.id}`)
                        .then((res) => {
                            $toastr.show(
                                res.data?.data || "Xóa thành công",
                                "success"
                            );
                            $scope.load();
                        })
                        .catch((err) => {
                            $toastr.show(
                                err.data?.message || "Xóa thất bại",
                                "error"
                            );
                        });
                },
            });
        };

        // Set as default
        $scope.setDefault = (r) => {
            $http
                .post(`${API}/${r.id}/default`)
                .then(() => {
                    $toastr.show(`Đã đặt "${r.name}" làm mặc định`, "success");
                    $scope.load();
                })
                .catch((err) => {
                    $toastr.show(
                        err.data?.message || "Lỗi đặt mặc định",
                        "error"
                    );
                });
        };

        // Open translations editor
        $scope.openTranslations = (r) => {
            $scope.currentLang = r;
            $scope.transTab = "edit"; // Default tab
            $scope.transSearch = ""; // Reset search
            // Use object to avoid AngularJS scope inheritance issues with Bootstrap modal
            $scope.trans = {
                jsonContent: "",
                jsonError: null,
                jsonPreview: null,
                jsonKeyCount: 0,
            };
            // Initialize newKey for adding new translation keys
            $scope.newKey = { key: "", value: "", error: null };
            // Track newly added keys to show them first
            $scope.newlyAddedKeys = [];
            $http.get(`${API}/${r.id}`).then((res) => {
                $scope.flatTrans = res.data.flat_translations || {};
                // Initialize filtered keys after data is loaded
                $scope.updateFilteredKeys();
                // Initialize displayedKeys
                $scope.filterTranslations();
                $("#translationsModal").modal("show");
            });
        };

        // Open add key modal
        $scope.openAddKeyModal = () => {
            $scope.newKey = { key: "", value: "", error: null };
            $("#addKeyModal").modal("show");
        };

        // Get ordered keys - newly added first, then alphabetically
        $scope.getOrderedKeys = () => {
            if (!$scope.flatTrans) return [];
            const allKeys = Object.keys($scope.flatTrans);
            const newKeys = $scope.newlyAddedKeys
                ? $scope.newlyAddedKeys.filter((k) => allKeys.includes(k))
                : [];
            const existingKeys = allKeys
                .filter((k) => !($scope.newlyAddedKeys || []).includes(k))
                .sort();
            // Return new keys (reversed to show latest first) followed by existing sorted keys
            return [...newKeys.reverse(), ...existingKeys];
        };

        // Update filtered keys based on search - LIKE style matching
        $scope.updateFilteredKeys = () => {
            const allKeys = $scope.getOrderedKeys();
            if (!$scope.transSearch || $scope.transSearch.trim() === "") {
                $scope.filteredKeys = allKeys;
                return;
            }
            const search = $scope.transSearch.toLowerCase().trim();
            $scope.filteredKeys = allKeys.filter((key) => {
                const value = $scope.flatTrans[key] || "";
                // LIKE matching - key contains search OR value contains search
                return (
                    key.toLowerCase().includes(search) ||
                    value.toLowerCase().includes(search)
                );
            });
        };

        // Watch transSearch for changes and update filtered keys
        $scope.$watch("transSearch", (newVal, oldVal) => {
            if (newVal !== oldVal) {
                $scope.updateFilteredKeys();
            }
        });

        // Watch flatTrans for changes (when data is loaded)
        $scope.$watch(
            "flatTrans",
            () => {
                $scope.updateFilteredKeys();
            },
            true
        );

        // Filter translations based on search input - called by ng-change
        $scope.filterTranslations = () => {
            const allKeys = $scope.getOrderedKeys();
            const searchText = ($scope.search.text || "").toLowerCase().trim();

            console.log(
                "filterTranslations called, search:",
                searchText,
                "total keys:",
                allKeys.length
            );

            if (!searchText) {
                $scope.displayedKeys = allKeys;
            } else {
                $scope.displayedKeys = allKeys.filter((key) => {
                    const value = $scope.flatTrans[key] || "";
                    return (
                        key.toLowerCase().includes(searchText) ||
                        value.toLowerCase().includes(searchText)
                    );
                });
            }
            console.log("Filtered keys count:", $scope.displayedKeys.length);
        };

        // Add new translation key
        $scope.addNewKey = () => {
            $scope.newKey.error = null;

            if (!$scope.newKey.key || !$scope.newKey.value) {
                $scope.newKey.error = "Vui lòng nhập key và giá trị";
                return;
            }

            // Check if key already exists
            if ($scope.flatTrans.hasOwnProperty($scope.newKey.key)) {
                $scope.newKey.error =
                    "Key đã tồn tại! Vui lòng sử dụng key khác.";
                return;
            }

            // Add new key to flatTrans
            $scope.flatTrans[$scope.newKey.key] = $scope.newKey.value;
            // Track as newly added
            $scope.newlyAddedKeys.push($scope.newKey.key);
            $toastr.show(`Đã thêm key "${$scope.newKey.key}"`, "success");

            // Clear form and close modal
            $scope.newKey = { key: "", value: "", error: null };
            $("#addKeyModal").modal("hide");
        };

        // Delete translation key
        $scope.deleteKey = (key) => {
            if (confirm(`Bạn có chắc muốn xóa key "${key}"?`)) {
                delete $scope.flatTrans[key];
                // Remove from newlyAddedKeys if exists
                const idx = $scope.newlyAddedKeys.indexOf(key);
                if (idx !== -1) {
                    $scope.newlyAddedKeys.splice(idx, 1);
                }
                // Update displayed keys
                $scope.filterTranslations();
                $toastr.show(`Đã xóa key "${key}"`, "success");
            }
        };

        // Open Add JSON Modal
        $scope.openAddJsonModal = () => {
            $scope.jsonImport = {
                content: "",
                error: null,
                preview: null,
                keyCount: 0,
                parsedData: null,
            };
            $("#addJsonModal").modal("show");
        };

        // Validate JSON Import content
        $scope.validateJsonImport = () => {
            $scope.jsonImport.error = null;
            $scope.jsonImport.preview = null;
            $scope.jsonImport.keyCount = 0;
            $scope.jsonImport.parsedData = null;

            if (!$scope.jsonImport.content) {
                $scope.jsonImport.error = "Vui lòng nhập nội dung JSON";
                return;
            }

            try {
                const parsed = JSON.parse($scope.jsonImport.content);
                if (
                    typeof parsed !== "object" ||
                    parsed === null ||
                    Array.isArray(parsed)
                ) {
                    $scope.jsonImport.error = "JSON phải là một object {}";
                    return;
                }

                // Flatten the parsed JSON to get flat key-value pairs
                const flatData = flattenObject(parsed);
                const keyCount = Object.keys(flatData).length;

                if (keyCount === 0) {
                    $scope.jsonImport.error = "JSON không có key nào";
                    return;
                }

                $scope.jsonImport.parsedData = flatData;
                $scope.jsonImport.keyCount = keyCount;
                $scope.jsonImport.preview = true;
                $toastr.show(
                    `JSON hợp lệ! Tìm thấy ${keyCount} key(s)`,
                    "success"
                );
            } catch (e) {
                $scope.jsonImport.error = "JSON không hợp lệ: " + e.message;
            }
        };

        // Apply JSON Import - add keys to interface (not save to DB yet)
        $scope.applyJsonImport = () => {
            if (!$scope.jsonImport.parsedData) {
                $scope.jsonImport.error = "Vui lòng kiểm tra JSON trước";
                return;
            }

            const flatData = $scope.jsonImport.parsedData;
            let addedCount = 0;
            let updatedCount = 0;

            // Add or update keys in flatTrans
            angular.forEach(flatData, (value, key) => {
                if ($scope.flatTrans.hasOwnProperty(key)) {
                    // Key exists - update value
                    $scope.flatTrans[key] = value;
                    updatedCount++;
                } else {
                    // New key - add and track as newly added
                    $scope.flatTrans[key] = value;
                    if ($scope.newlyAddedKeys.indexOf(key) === -1) {
                        $scope.newlyAddedKeys.push(key);
                    }
                    addedCount++;
                }
            });

            // Update displayed keys
            $scope.filterTranslations();

            // Close modal and show notification
            $("#addJsonModal").modal("hide");

            let message = "";
            if (addedCount > 0 && updatedCount > 0) {
                message = `Đã thêm ${addedCount} key mới và cập nhật ${updatedCount} key`;
            } else if (addedCount > 0) {
                message = `Đã thêm ${addedCount} key mới`;
            } else if (updatedCount > 0) {
                message = `Đã cập nhật ${updatedCount} key`;
            }
            $toastr.show(
                message + ". Nhấn 'Lưu bản dịch' để lưu vào database.",
                "success"
            );
        };

        // Helper: Flatten nested object to dot notation
        function flattenObject(obj, prefix = "") {
            let result = {};
            for (let key in obj) {
                if (obj.hasOwnProperty(key)) {
                    const newKey = prefix ? `${prefix}.${key}` : key;
                    if (
                        typeof obj[key] === "object" &&
                        obj[key] !== null &&
                        !Array.isArray(obj[key])
                    ) {
                        Object.assign(result, flattenObject(obj[key], newKey));
                    } else {
                        result[newKey] = obj[key];
                    }
                }
            }
            return result;
        }

        // Validate JSON content
        $scope.validateJson = () => {
            $scope.trans.jsonError = null;
            $scope.trans.jsonPreview = null;
            $scope.trans.jsonKeyCount = 0;

            if (!$scope.trans.jsonContent) return;

            try {
                const parsed = JSON.parse($scope.trans.jsonContent);
                if (typeof parsed !== "object" || parsed === null) {
                    $scope.trans.jsonError = "JSON phải là một object {}";
                    return;
                }
                $scope.trans.jsonPreview = parsed;
                $scope.trans.jsonKeyCount = countKeys(parsed);
                $toastr.show(
                    `JSON hợp lệ! (${$scope.trans.jsonKeyCount} keys)`,
                    "success"
                );
            } catch (e) {
                $scope.trans.jsonError = "JSON không hợp lệ: " + e.message;
            }
        };

        // Count all keys in nested object
        function countKeys(obj) {
            let count = 0;
            for (let key in obj) {
                if (typeof obj[key] === "object" && obj[key] !== null) {
                    count += countKeys(obj[key]);
                } else {
                    count++;
                }
            }
            return count;
        }

        // Save JSON translations directly to database
        $scope.saveJsonTranslations = () => {
            $scope.trans.jsonError = null;

            if (!$scope.trans.jsonContent) {
                $scope.trans.jsonError = "Vui lòng nhập nội dung JSON";
                return;
            }

            let parsed;
            try {
                parsed = JSON.parse($scope.trans.jsonContent);
                if (typeof parsed !== "object" || parsed === null) {
                    $scope.trans.jsonError = "JSON phải là một object {}";
                    return;
                }
            } catch (e) {
                $scope.trans.jsonError = "JSON không hợp lệ: " + e.message;
                return;
            }

            console.log("Saving translations:", {
                langId: $scope.currentLang.id,
                parsed: parsed,
                keysCount: countKeys(parsed),
            });

            $scope.saving = true;

            $http
                .put(`${API}/${$scope.currentLang.id}`, {
                    translations: parsed,
                })
                .then((res) => {
                    console.log("Save response:", res);
                    $toastr.show("Lưu bản dịch thành công!", "success");
                    $("#translationsModal").modal("hide");
                    $scope.load();
                })
                .catch((err) => {
                    console.error("Save error:", err);
                    $scope.trans.jsonError =
                        err.data?.message || "Lỗi lưu bản dịch";
                })
                .finally(() => ($scope.saving = false));
        };

        // Convert PHP array to JSON
        $scope.convertPhpToJson = () => {
            if (!$scope.phpContent) return;
            $scope.converting = true;
            $scope.convertError = null;
            $scope.convertedJson = null;

            $http
                .post(`${API}/convert-php`, { php_content: $scope.phpContent })
                .then((res) => {
                    if (res.data.status) {
                        $scope.convertedJson = res.data.json;
                        $toastr.show("Convert thành công!", "success");
                    } else {
                        $scope.convertError = res.data.message || "Lỗi convert";
                    }
                })
                .catch((err) => {
                    $scope.convertError =
                        err.data?.message || "Lỗi convert PHP";
                })
                .finally(() => ($scope.converting = false));
        };

        // Apply converted JSON and save
        $scope.applyConvertedJson = () => {
            if (!$scope.convertedJson) return;
            $scope.saving = true;

            $http
                .put(`${API}/${$scope.currentLang.id}`, {
                    translations: $scope.convertedJson,
                })
                .then(() => {
                    $toastr.show("Lưu bản dịch thành công", "success");
                    $("#translationsModal").modal("hide");
                    $scope.load();
                })
                .catch((err) => {
                    $toastr.show(err.data?.message || "Lỗi lưu", "error");
                })
                .finally(() => ($scope.saving = false));
        };

        // Save translations (handles both edit and import tabs)
        $scope.saveTranslations = () => {
            let translations;

            // Check which tab is active
            if ($scope.transTab === "import") {
                // Import JSON tab - parse and save JSON content
                if (!$scope.trans.jsonContent) {
                    $scope.trans.jsonError = "Vui lòng nhập nội dung JSON";
                    return;
                }

                try {
                    translations = JSON.parse($scope.trans.jsonContent);
                    if (
                        typeof translations !== "object" ||
                        translations === null
                    ) {
                        $scope.trans.jsonError = "JSON phải là một object {}";
                        return;
                    }
                } catch (e) {
                    $scope.trans.jsonError = "JSON không hợp lệ: " + e.message;
                    return;
                }
            } else {
                // Edit tab - convert flat to nested
                translations = {};
                angular.forEach($scope.flatTrans, (value, key) => {
                    setNested(translations, key, value);
                });
            }

            $scope.saving = true;

            $http
                .put(`${API}/${$scope.currentLang.id}`, {
                    translations: translations,
                })
                .then(() => {
                    $toastr.show("Lưu bản dịch thành công", "success");
                    $("#translationsModal").modal("hide");
                    $scope.load();
                })
                .catch((err) => {
                    const errMsg = err.data?.message || "Lỗi lưu";
                    if ($scope.transTab === "import") {
                        $scope.trans.jsonError = errMsg;
                    } else {
                        $toastr.show(errMsg, "error");
                    }
                })
                .finally(() => ($scope.saving = false));
        };

        // Helper: set nested value from dot notation
        function setNested(obj, key, value) {
            const parts = key.split(".");
            let current = obj;
            for (let i = 0; i < parts.length - 1; i++) {
                if (!current[parts[i]]) current[parts[i]] = {};
                current = current[parts[i]];
            }
            current[parts[parts.length - 1]] = value;
        }

        // Search filter for translations
        $scope.matchSearch = (key, value) => {
            if (!$scope.transSearch) return true;
            const search = $scope.transSearch.toLowerCase();
            return (
                key.toLowerCase().includes(search) ||
                (value && value.toLowerCase().includes(search))
            );
        };

        // Init
        $scope.load();
    },
]);
