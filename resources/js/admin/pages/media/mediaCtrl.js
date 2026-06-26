adminApp.controller("MediaCtrl", [
    "$scope",
    "$http",
    "$rootScope",
    "BASE_API",
    "$toastr",
    "$timeout",
    function ($scope, $http, $rootScope, BASE_API, $toastr, $timeout) {
        // State
        $scope.currentFolderId = null;
        $scope.currentView = "files"; // 'files' or 'trash'
        $scope.folders = [];
        $scope.files = [];
        $scope.folderTree = [];
        $scope.breadcrumb = [];
        $scope.folderCount = 0;
        $scope.fileCount = 0;
        $scope.selectedItem = null;
        $scope.selectedItems = []; // Multi-select array
        $scope.loading = false;
        $scope.viewMode = "grid";
        $scope.searchKeyword = "";

        // Trash state
        $scope.trashFolders = [];
        $scope.trashFiles = [];

        // Storage stats
        $scope.storageStats = {
            used_percent: 0,
            free_percent: 100,
            used_formatted: "0 B",
            free_formatted: "0 B",
            max_formatted: "0 B",
        };

        // Context menus
        $scope.contextMenu = { visible: false, x: 0, y: 0 };
        $scope.itemContextMenu = { visible: false, x: 0, y: 0, item: null };
        $scope.trashContextMenu = { visible: false, x: 0, y: 0 };
        $scope.trashItemContextMenu = {
            visible: false,
            x: 0,
            y: 0,
            item: null,
        };

        // Modal state
        $scope.newFolderName = "";
        $scope.creatingFolder = false;
        $scope.uploadFiles = [];
        $scope.uploading = false;
        $scope.renameValue = "";
        $scope.renameItem_ = null;
        $scope.renaming = false;
        $scope.isDragover = false;

        // Confirm modal state
        $scope.confirmModal = {
            title: "",
            message: "",
            icon: "fa-question-circle",
            confirmText: "Xác nhận",
            confirmIcon: "fa-check",
            danger: false,
            onConfirm: null,
        };

        $scope.showConfirm = (options) => {
            $scope.confirmModal = {
                title: options.title || "Xác nhận",
                message: options.message || "Bạn có chắc chắn?",
                icon: options.icon || "fa-question-circle",
                confirmText: options.confirmText || "Xác nhận",
                confirmIcon: options.confirmIcon || "fa-check",
                danger: options.danger || false,
                onConfirm: () => {
                    $("#confirmModal").modal("hide");
                    if (options.onConfirm) options.onConfirm();
                },
            };
            $("#confirmModal").modal("show");
        };

        // History
        $scope.historyFilter = {};
        $scope.historyLogs = [];
        $scope.loadingHistory = false;

        // Settings state
        $scope.settingsForm = {
            convert_to_webp: false,
        };
        $scope.savingSettings = false;

        // Navigation history
        const navHistory = [];

        // Load folder tree
        $scope.loadFolderTree = () => {
            $http
                .get(`${BASE_API}/media/folders`, { params: { tree: "true" } })
                .then((res) => {
                    $scope.folderTree = res.data.tree || [];
                });
        };

        // Load contents of current folder
        $scope.loadContents = () => {
            $scope.loading = true;
            $scope.selectedItem = null;
            $http
                .get(`${BASE_API}/media/folders`, {
                    params: { parent_id: $scope.currentFolderId },
                })
                .then((res) => {
                    const d = res.data;
                    $scope.folders = d.folders || [];
                    $scope.files = d.files || [];
                    $scope.breadcrumb = d.breadcrumb || [];
                    $scope.folderCount = d.folder_count || 0;
                    $scope.fileCount = d.file_count || 0;
                })
                .catch(() => $toastr.show("Tải dữ liệu thất bại", "error"))
                .finally(() => ($scope.loading = false));
        };

        // Load storage stats
        $scope.loadStorageStats = () => {
            $http.get(`${BASE_API}/media/settings`).then((res) => {
                $scope.storageStats = res.data.storage || {};
                // Load settings
                if (res.data.settings) {
                    $scope.settingsForm.convert_to_webp =
                        !!res.data.settings.convert_to_webp;
                }
                $scope.drawStorageChart();
            });
        };

        // Draw storage pie chart
        $scope.drawStorageChart = () => {
            $timeout(() => {
                const canvas = document.getElementById("storageChart");
                if (!canvas) return;

                if ($scope.storageChart) {
                    $scope.storageChart.destroy();
                }

                const ctx = canvas.getContext("2d");
                $scope.storageChart = new Chart(ctx, {
                    type: "doughnut",
                    data: {
                        datasets: [
                            {
                                data: [
                                    $scope.storageStats.used_percent,
                                    $scope.storageStats.free_percent,
                                ],
                                backgroundColor: ["#f6ad55", "#e2e8f0"],
                                borderWidth: 0,
                            },
                        ],
                    },
                    options: {
                        cutout: "70%",
                        plugins: { legend: { display: false } },
                    },
                });
            }, 100);
        };

        // Navigate to folder
        $scope.navigateTo = (folderId) => {
            // Clear selection when switching to normal view
            $scope.currentView = "files";
            $scope.selectedItems = [];
            $scope.selectedItem = null;
            $scope.searchKeyword = ""; // Clear search

            if ($scope.currentFolderId !== null) {
                navHistory.push($scope.currentFolderId);
            }
            $scope.currentFolderId = folderId;
            $scope.loadContents();
            $scope.hideContextMenus();
        };

        // Go back in history
        $scope.goBack = () => {
            if (navHistory.length > 0) {
                $scope.currentFolderId = navHistory.pop();
                $scope.loadContents();
            }
        };

        // Go up to parent folder
        $scope.goUp = () => {
            if ($scope.breadcrumb.length > 1) {
                const parentIndex = $scope.breadcrumb.length - 2;
                $scope.currentFolderId = $scope.breadcrumb[parentIndex].id;
                $scope.loadContents();
            }
        };

        // Select item with multi-select support
        $scope.selectItem = (e, item, type) => {
            const itemWithType = { ...item, type };
            $scope.hideContextMenus();

            if (e && (e.ctrlKey || e.metaKey)) {
                // Ctrl+click: toggle selection
                const idx = $scope.selectedItems.findIndex(
                    (i) => i.id === item.id && i.type === type
                );
                if (idx > -1) {
                    $scope.selectedItems.splice(idx, 1);
                } else {
                    $scope.selectedItems.push(itemWithType);
                }
                $scope.selectedItem =
                    $scope.selectedItems.length === 1
                        ? $scope.selectedItems[0]
                        : $scope.selectedItems.length > 1
                        ? $scope.selectedItems[$scope.selectedItems.length - 1]
                        : null;
            } else {
                // Normal click: single selection
                $scope.selectedItems = [itemWithType];
                $scope.selectedItem = itemWithType;

                // If in search mode, only highlight parent folder in sidebar (don't navigate)
                if ($scope.searchKeyword && item.path) {
                    if (type === "folder") {
                        $scope.expandFolderPath(item.id);
                        $scope.currentFolderId = item.id;
                    } else {
                        const folderId = item.folder_id;
                        if (folderId) {
                            $scope.expandFolderPath(folderId);
                            $scope.currentFolderId = folderId;
                        } else {
                            $scope.currentFolderId = null;
                        }
                    }
                    return;
                }

                // Load detailed info for file/folder
                if (type === "file") {
                    $http
                        .get(`${BASE_API}/media/files/${item.id}`)
                        .then((res) => {
                            $scope.selectedItem = { ...res.data, type: "file" };
                        });
                } else {
                    $http
                        .get(`${BASE_API}/media/folders/${item.id}`)
                        .then((res) => {
                            $scope.selectedItem = {
                                ...res.data,
                                type: "folder",
                            };
                        });
                }
            }
        };

        // Expand folder tree path to show a specific folder
        $scope.expandFolderPath = (folderId) => {
            const expandRecursive = (folders, targetId) => {
                for (let folder of folders) {
                    if (folder.id === targetId) {
                        return true;
                    }
                    if (folder.children && folder.children.length) {
                        if (expandRecursive(folder.children, targetId)) {
                            folder.expanded = true;
                            return true;
                        }
                    }
                }
                return false;
            };
            expandRecursive($scope.folderTree, folderId);
        };

        // Search
        $scope.search = () => {
            if (!$scope.searchKeyword) {
                // If no keyword, reload current view
                if ($scope.currentView === "trash") {
                    $scope.loadTrash();
                } else {
                    $scope.loadContents();
                }
                return;
            }
            $scope.loading = true;

            // Use different endpoint based on current view
            const endpoint =
                $scope.currentView === "trash"
                    ? `${BASE_API}/media/trash`
                    : `${BASE_API}/media/files`;

            $http
                .get(endpoint, {
                    params: { search: $scope.searchKeyword },
                })
                .then((res) => {
                    if ($scope.currentView === "trash") {
                        $scope.trashFolders = res.data.folders || [];
                        $scope.trashFiles = res.data.files || [];
                        $scope.folderCount = res.data.folder_count || 0;
                        $scope.fileCount = res.data.file_count || 0;
                        $scope.trashBreadcrumb = [
                            {
                                id: null,
                                name:
                                    "Kết quả tìm kiếm: " + $scope.searchKeyword,
                            },
                        ];
                    } else {
                        $scope.folders = res.data.folders || [];
                        $scope.files = res.data.files || [];
                        $scope.folderCount = res.data.folder_count || 0;
                        $scope.fileCount = res.data.file_count || 0;
                        $scope.breadcrumb = [
                            {
                                id: null,
                                name:
                                    "Kết quả tìm kiếm: " + $scope.searchKeyword,
                            },
                        ];
                    }
                })
                .catch(() => $toastr.show("Tìm kiếm thất bại", "error"))
                .finally(() => ($scope.loading = false));
        };

        // Context menu helpers
        $scope.showContextMenu = (e) => {
            e.preventDefault();
            $scope.hideContextMenus();
            $scope.contextMenu = { visible: true, x: e.clientX, y: e.clientY };
        };

        $scope.showItemContextMenu = (e, item, type) => {
            e.preventDefault();
            e.stopPropagation();
            $scope.hideContextMenus();

            // Auto-select item on right-click (if not already in multi-select)
            const itemWithType = { ...item, type };
            if (!$scope.isItemSelected(item, type)) {
                $scope.selectedItems = [itemWithType];
            }
            $scope.selectedItem = itemWithType;

            $scope.itemContextMenu = {
                visible: true,
                x: e.clientX,
                y: e.clientY,
                item: itemWithType,
            };
        };

        $scope.hideContextMenus = () => {
            $scope.contextMenu.visible = false;
            $scope.itemContextMenu.visible = false;
            $scope.trashContextMenu.visible = false;
            $scope.trashItemContextMenu.visible = false;
        };

        // Open image in new tab
        $scope.openImageInNewTab = (item) => {
            $scope.hideContextMenus();
            if (item.url) {
                window.open(item.url, "_blank");
            }
        };

        $scope.selectTrashItem = (e, item, type) => {
            console.log("selectTrashItem called", { e, item, type });
            const itemWithType = { ...item, type };

            if (e && (e.ctrlKey || e.metaKey)) {
                const idx = $scope.selectedItems.findIndex(
                    (i) => i.id === item.id && i.type === type
                );
                if (idx > -1) {
                    $scope.selectedItems.splice(idx, 1);
                } else {
                    $scope.selectedItems.push(itemWithType);
                }
                $scope.selectedItem =
                    $scope.selectedItems.length === 1
                        ? $scope.selectedItems[0]
                        : $scope.selectedItems.length > 1
                        ? $scope.selectedItems[$scope.selectedItems.length - 1]
                        : null;
            } else {
                $scope.selectedItems = [itemWithType];
                $scope.selectedItem = itemWithType;
            }
        };

        $scope.isItemSelected = (item, type) => {
            return $scope.selectedItems.some(
                (i) => i.id === item.id && i.type === type
            );
        };

        $scope.clearSelection = () => {
            $scope.selectedItems = [];
            $scope.selectedItem = null;
        };

        // Bulk delete (normal view)
        $scope.deleteSelectedItems = () => {
            $scope.hideContextMenus();
            const count = $scope.selectedItems.length;

            $scope.showConfirm({
                title: "Xóa nhiều mục",
                message: `Bạn có chắc muốn xóa ${count} mục đã chọn?`,
                icon: "fa-trash",
                confirmText: "Xóa tất cả",
                confirmIcon: "fa-trash",
                danger: true,
                onConfirm: () => {
                    const promises = $scope.selectedItems.map((item) => {
                        const endpoint =
                            item.type === "folder"
                                ? `${BASE_API}/media/folders/${item.id}`
                                : `${BASE_API}/media/files/${item.id}`;
                        return $http.delete(endpoint);
                    });

                    Promise.all(promises)
                        .then(() => {
                            $toastr.show(`Đã xóa ${count} mục`, "success");
                            $scope.clearSelection();
                            $scope.loadContents();
                            $scope.loadFolderTree();
                            $scope.loadStorageStats();
                            $rootScope.$broadcast("notification:refresh");
                        })
                        .catch(() => {
                            $toastr.show("Có lỗi xảy ra khi xóa", "error");
                        });
                },
            });
        };

        // Bulk restore (trash view)
        $scope.restoreSelectedItems = () => {
            $scope.hideContextMenus();
            const count = $scope.selectedItems.length;

            $scope.showConfirm({
                title: "Khôi phục nhiều mục",
                message: `Bạn có chắc muốn khôi phục ${count} mục đã chọn?`,
                icon: "fa-undo",
                confirmText: "Khôi phục tất cả",
                confirmIcon: "fa-undo",
                danger: false,
                onConfirm: () => {
                    const promises = $scope.selectedItems.map((item) => {
                        const endpoint = `${BASE_API}/media/trash/${item.type}/${item.id}/restore`;
                        return $http.post(endpoint);
                    });

                    Promise.all(promises)
                        .then(() => {
                            $toastr.show(
                                `Đã khôi phục ${count} mục`,
                                "success"
                            );
                            $scope.clearSelection();
                            $scope.loadTrash();
                            $scope.loadFolderTree();
                            $scope.loadStorageStats();
                            $rootScope.$broadcast("notification:refresh");
                        })
                        .catch(() => {
                            $toastr.show(
                                "Có lỗi xảy ra khi khôi phục",
                                "error"
                            );
                        });
                },
            });
        };

        // Trash functions
        $scope.currentTrashFolderId = null;
        $scope.trashBreadcrumb = [];
        $scope.trashSearchKeyword = "";

        $scope.openTrash = () => {
            $scope.currentView = "trash";
            $scope.selectedItem = null;
            $scope.selectedItems = []; // Clear normal view selection
            $scope.searchKeyword = ""; // Clear search
            $scope.currentTrashFolderId = null;
            $scope.trashSearchKeyword = "";
            $scope.loadTrash();
        };

        $scope.loadTrash = (folderId) => {
            if (folderId !== undefined) {
                $scope.currentTrashFolderId = folderId;
            }
            $scope.loading = true;
            const params = {};
            if ($scope.currentTrashFolderId) {
                params.folder_id = $scope.currentTrashFolderId;
            }
            if ($scope.trashSearchKeyword) {
                params.search = $scope.trashSearchKeyword;
            }
            $http
                .get(`${BASE_API}/media/trash`, { params })
                .then((res) => {
                    const d = res.data;
                    $scope.trashFolders = d.folders || [];
                    $scope.trashFiles = d.files || [];
                    $scope.folderCount = d.folder_count || 0;
                    $scope.fileCount = d.file_count || 0;
                    if (d.breadcrumb) {
                        $scope.trashBreadcrumb = d.breadcrumb;
                    }
                })
                .catch(() => $toastr.show("Tải dữ liệu thất bại", "error"))
                .finally(() => ($scope.loading = false));
        };

        $scope.trashSearch = () => {
            $scope.loadTrash();
        };

        $scope.navigateTrashTo = (folderId) => {
            $scope.trashSearchKeyword = "";
            $scope.selectedItem = null;
            $scope.loadTrash(folderId);
        };

        $scope.trashGoUp = () => {
            if ($scope.trashBreadcrumb.length > 1) {
                const parentIndex = $scope.trashBreadcrumb.length - 2;
                $scope.navigateTrashTo($scope.trashBreadcrumb[parentIndex].id);
            }
        };

        $scope.showTrashContextMenu = (e, item, type) => {
            e.preventDefault();
            e.stopPropagation();
            $scope.hideContextMenus();

            // Auto-select item on right-click (if not already in multi-select)
            const itemWithType = { ...item, type };
            if (!$scope.isItemSelected(item, type)) {
                $scope.selectedItems = [itemWithType];
            }
            $scope.selectedItem = itemWithType;

            $scope.trashItemContextMenu = {
                visible: true,
                x: e.clientX,
                y: e.clientY,
                item: itemWithType,
            };
        };

        $scope.restoreItem = (item) => {
            $scope.hideContextMenus();
            $scope.showConfirm({
                title: "Khôi phục",
                message: `Khôi phục "${item.name}" về vị trí ban đầu?`,
                icon: "fa-undo",
                confirmText: "Khôi phục",
                confirmIcon: "fa-undo",
                danger: false,
                onConfirm: () => {
                    $http
                        .post(
                            `${BASE_API}/media/trash/${item.type}/${item.id}/restore`
                        )
                        .then((res) => {
                            const { status, data } = res.data;
                            $toastr.show(
                                typeof data === "string"
                                    ? data
                                    : "Khôi phục thành công",
                                status ? "success" : "error"
                            );
                            if (status) {
                                $scope.loadTrash();
                                $scope.loadFolderTree();
                                $rootScope.$broadcast("notification:refresh");
                                if ($scope.selectedItem?.id === item.id)
                                    $scope.selectedItem = null;
                            }
                        })
                        .catch((err) => {
                            const msg =
                                err?.data?.message || "Khôi phục thất bại";
                            $toastr.show(msg, "error");
                        });
                },
            });
        };

        $scope.forceDeleteItem = (item) => {
            $scope.hideContextMenus();
            $scope.showConfirm({
                title: "Xóa vĩnh viễn",
                message: `Xóa vĩnh viễn "${item.name}"? Hành động này không thể hoàn tác.`,
                icon: "fa-trash-alt",
                confirmText: "Xóa vĩnh viễn",
                confirmIcon: "fa-trash-alt",
                danger: true,
                onConfirm: () => {
                    $http
                        .delete(
                            `${BASE_API}/media/trash/${item.type}/${item.id}`
                        )
                        .then((res) => {
                            const { status, data } = res.data;
                            $toastr.show(
                                typeof data === "string"
                                    ? data
                                    : "Xóa vĩnh viễn thành công",
                                status ? "success" : "error"
                            );
                            if (status) {
                                $scope.loadTrash();
                                $scope.loadStorageStats();
                                $rootScope.$broadcast("notification:refresh");
                                if ($scope.selectedItem?.id === item.id)
                                    $scope.selectedItem = null;
                            }
                        })
                        .catch((err) => {
                            const msg = err?.data?.message || "Xóa thất bại";
                            $toastr.show(msg, "error");
                        });
                },
            });
        };

        $scope.emptyTrash = () => {
            $scope.hideContextMenus();
            $scope.showConfirm({
                title: "Dọn dẹp thùng rác",
                message:
                    "Dọn dẹp tất cả? Tất cả tệp và thư mục trong thùng rác sẽ bị xóa vĩnh viễn.",
                icon: "fa-broom",
                confirmText: "Dọn dẹp tất cả",
                confirmIcon: "fa-broom",
                danger: true,
                onConfirm: () => {
                    $http
                        .delete(`${BASE_API}/media/trash`)
                        .then((res) => {
                            const { status, data } = res.data;
                            $toastr.show(
                                typeof data === "string"
                                    ? data
                                    : "Dọn dẹp thành công",
                                status ? "success" : "error"
                            );
                            if (status) {
                                $scope.loadTrash();
                                $scope.loadStorageStats();
                                $rootScope.$broadcast("notification:refresh");
                                $scope.selectedItem = null;
                            }
                        })
                        .catch((err) => {
                            const msg =
                                err?.data?.message || "Dọn dẹp thất bại";
                            $toastr.show(msg, "error");
                        });
                },
            });
        };

        // Create folder
        $scope.openCreateFolderModal = () => {
            $scope.hideContextMenus();
            $scope.newFolderName = "";
            $("#createFolderModal").modal("show");
        };

        $scope.createFolder = () => {
            if (!$scope.newFolderName) return;
            $scope.creatingFolder = true;
            $http
                .post(`${BASE_API}/media/folders`, {
                    name: $scope.newFolderName,
                    parent_id: $scope.currentFolderId,
                })
                .then((res) => {
                    const { status, data } = res.data;
                    $toastr.show("Tạo thư mục thành công", "success");
                    $("#createFolderModal").modal("hide");
                    $scope.loadContents();
                    $scope.loadFolderTree();
                    $rootScope.$broadcast("notification:refresh");
                })
                .catch((err) => {
                    const msg = err?.data?.message || "Tạo thư mục thất bại";
                    $toastr.show(msg, "error");
                })
                .finally(() => ($scope.creatingFolder = false));
        };

        // Upload
        $scope.openUploadModal = () => {
            $scope.hideContextMenus();
            $scope.uploadFiles = [];
            $("#uploadModal").modal("show");
        };

        $scope.onFilesSelected = (files) => {
            $scope.$apply(() => {
                for (let i = 0; i < files.length; i++) {
                    $scope.uploadFiles.push(files[i]);
                }
            });
        };

        $scope.removeUploadFile = (index) => {
            $scope.uploadFiles.splice(index, 1);
        };

        $scope.formatBytes = (bytes) => {
            if (bytes === 0) return "0 B";
            const k = 1024;
            const sizes = ["B", "KB", "MB", "GB"];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return (
                parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i]
            );
        };

        $scope.uploadFilesAction = () => {
            if (!$scope.uploadFiles.length) return;
            $scope.uploading = true;

            const formData = new FormData();
            for (let i = 0; i < $scope.uploadFiles.length; i++) {
                formData.append("files[]", $scope.uploadFiles[i]);
            }
            if ($scope.currentFolderId) {
                formData.append("folder_id", $scope.currentFolderId);
            }

            $http
                .post(`${BASE_API}/media/files`, formData, {
                    headers: { "Content-Type": undefined },
                    transformRequest: angular.identity,
                })
                .then((res) => {
                    const { status, data } = res.data;
                    const uploaded = data.uploaded?.length || 0;
                    const errors = data.errors || [];
                    if (uploaded > 0) {
                        $toastr.show(`Đã tải lên ${uploaded} tệp`, "success");
                    }
                    if (errors.length > 0) {
                        errors.forEach((e) => $toastr.show(e, "warning"));
                    }
                    $("#uploadModal").modal("hide");
                    $scope.loadContents();
                    $scope.loadStorageStats();
                    $rootScope.$broadcast("notification:refresh");
                })
                .catch((err) => {
                    const msg = err?.data?.message || "Tải lên thất bại";
                    $toastr.show(msg, "error");
                })
                .finally(() => ($scope.uploading = false));
        };

        // Rename
        $scope.openRenameModal = (item) => {
            $scope.hideContextMenus();
            $scope.renameItem_ = item;
            $scope.renameValue = item.name;
            $("#renameModal").modal("show");
        };

        $scope.renameItem = () => {
            if (!$scope.renameValue || !$scope.renameItem_) return;
            $scope.renaming = true;

            const item = $scope.renameItem_;
            const url =
                item.type === "folder"
                    ? `${BASE_API}/media/folders/${item.id}`
                    : `${BASE_API}/media/files/${item.id}`;

            $http
                .put(url, { name: $scope.renameValue })
                .then((res) => {
                    $toastr.show("Đổi tên thành công", "success");
                    $("#renameModal").modal("hide");
                    $scope.loadContents();
                    if (item.type === "folder") $scope.loadFolderTree();
                    $rootScope.$broadcast("notification:refresh");
                })
                .catch((err) => {
                    const msg = err?.data?.message || "Đổi tên thất bại";
                    $toastr.show(msg, "error");
                })
                .finally(() => ($scope.renaming = false));
        };

        // Delete
        $scope.deleteItem = (item) => {
            $scope.hideContextMenus();
            $scope.showConfirm({
                title: "Xóa " + (item.type === "folder" ? "thư mục" : "tệp"),
                message: `Xóa ${item.type === "folder" ? "thư mục" : "tệp"} "${
                    item.name
                }"?`,
                icon: "fa-trash",
                confirmText: "Xóa",
                confirmIcon: "fa-trash",
                danger: true,
                onConfirm: () => {
                    const url =
                        item.type === "folder"
                            ? `${BASE_API}/media/folders/${item.id}`
                            : `${BASE_API}/media/files/${item.id}`;

                    $http
                        .delete(url)
                        .then((res) => {
                            const { status, data } = res.data;
                            $toastr.show(
                                typeof data === "string"
                                    ? data
                                    : "Xóa thành công",
                                status ? "success" : "error"
                            );
                            if (status) {
                                $scope.loadContents();
                                if (item.type === "folder")
                                    $scope.loadFolderTree();
                                $scope.loadStorageStats();
                                $rootScope.$broadcast("notification:refresh");
                                if ($scope.selectedItem?.id === item.id)
                                    $scope.selectedItem = null;
                            }
                        })
                        .catch((err) => {
                            const msg = err?.data?.message || "Xóa thất bại";
                            $toastr.show(msg, "error");
                        });
                },
            });
        };

        // History
        $scope.openHistoryModal = () => {
            $scope.historyFilter = {};
            $scope.historyLogs = [];
            $("#historyModal").modal("show");
            $scope.loadHistory();
        };

        $scope.loadHistory = () => {
            $scope.loadingHistory = true;
            $http
                .get(`${BASE_API}/media/logs`, { params: $scope.historyFilter })
                .then((res) => {
                    $scope.historyLogs = res.data.data || [];
                })
                .finally(() => ($scope.loadingHistory = false));
        };

        $scope.resetHistoryFilter = () => {
            $scope.historyFilter = {};
            $scope.loadHistory();
        };

        $scope.getActionBadgeClass = (actionType) => {
            return (
                {
                    upload: "badge-info",
                    create_folder: "badge-warning",
                    rename: "badge-primary",
                    move: "badge-secondary",
                    delete: "badge-danger",
                }[actionType] || "badge-secondary"
            );
        };

        // Settings Modal
        $scope.openSettingsModal = () => {
            $("#settingsModal").modal("show");
        };

        $scope.saveSettings = () => {
            $scope.savingSettings = true;
            $http
                .put(`${BASE_API}/media/settings`, {
                    convert_to_webp: $scope.settingsForm.convert_to_webp,
                })
                .then((res) => {
                    const { status, data } = res.data;
                    $toastr.show(
                        typeof data === "string"
                            ? data
                            : "Cập nhật cài đặt thành công",
                        status ? "success" : "error"
                    );
                    if (status) {
                        $("#settingsModal").modal("hide");
                    }
                })
                .catch((err) => {
                    const msg = err?.data?.message || "Cập nhật thất bại";
                    $toastr.show(msg, "error");
                })
                .finally(() => ($scope.savingSettings = false));
        };

        // Click outside to hide context menus
        $(document).on("click", () => {
            $scope.$apply(() => {
                $scope.hideContextMenus();
            });
        });

        // Global context menu handler for media areas
        $(document).on("contextmenu", (e) => {
            const target = e.target;

            // Check if right-click is within mediaContentArea (normal view)
            const mediaArea = document.getElementById("mediaContentArea");
            if (mediaArea && mediaArea.contains(target)) {
                // Skip if clicking on a media item (let item handler manage it)
                if (target.closest(".media-item")) {
                    return;
                }
                e.preventDefault();
                $scope.$apply(() => {
                    $scope.hideContextMenus();
                    $scope.contextMenu = {
                        visible: true,
                        x: e.clientX,
                        y: e.clientY,
                    };
                });
                return;
            }

            // Check if right-click is within trashContentArea (trash view)
            const trashArea = document.getElementById("trashContentArea");
            if (trashArea && trashArea.contains(target)) {
                // Skip if clicking on a table row (let item handler manage it)
                if (target.closest("tr")) {
                    return;
                }
                e.preventDefault();
                $scope.$apply(() => {
                    $scope.hideContextMenus();
                    $scope.trashContextMenu = {
                        visible: true,
                        x: e.clientX,
                        y: e.clientY,
                    };
                });
                return;
            }
        });

        // Wrapper for showItemContextMenu to ensure $apply is called
        const originalShowItemContextMenu = $scope.showItemContextMenu;
        $scope.showItemContextMenu = (e, item, type) => {
            $scope.$apply(() => {
                originalShowItemContextMenu(e, item, type);
            });
        };

        // Wrapper for showTrashContextMenu to ensure $apply is called
        const originalShowTrashContextMenu = $scope.showTrashContextMenu;
        $scope.showTrashContextMenu = (e, item, type) => {
            $scope.$apply(() => {
                originalShowTrashContextMenu(e, item, type);
            });
        };

        // Initialize
        $scope.loadFolderTree();
        $scope.loadContents();
        $scope.loadStorageStats();
    },
]);
