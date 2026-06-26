/**
 * Media Picker Directive
 *
 * Usage:
 * <media-picker
 *     mode="picker|manager"           // 'manager' = full admin, 'picker' = modal selection
 *     select-mode="single|multiple"   // Selection mode
 *     accept="image/*"                // File type filter (optional)
 *     on-select="callback(files)"     // Callback when files selected
 * ></media-picker>
 */

export function registerMediaPickerDirective(app) {
    app.directive("mediaPicker", [
        "$http",
        "BASE_API",
        "$toastr",
        "$timeout",
        function ($http, BASE_API, $toastr, $timeout) {
            return {
                restrict: "E",
                scope: {
                    mode: "@", // 'picker' or 'manager'
                    selectMode: "@", // 'single' or 'multiple'
                    accept: "@", // file type filter
                    onSelect: "&", // callback
                },
                templateUrl: "/admin/partials/media-picker",
                link: function ($scope, element, attrs) {
                    const API = `${BASE_API}/media`;

                    // State
                    $scope.currentFolderId = null;
                    $scope.folders = [];
                    $scope.files = [];
                    $scope.folderTree = [];
                    $scope.breadcrumb = [];
                    $scope.loading = false;
                    $scope.viewMode = "grid"; // 'grid' or 'list'
                    $scope.searchKeyword = "";
                    $scope.selectedFiles = [];

                    // Picker mode defaults
                    $scope.isPicker = $scope.mode === "picker";
                    $scope.isMultiple = $scope.selectMode === "multiple";

                    // Context menu state
                    $scope.contextMenu = {
                        visible: false,
                        x: 0,
                        y: 0,
                    };

                    // Load folder tree
                    $scope.loadFolderTree = () => {
                        $http.get(`${API}/folders/tree`).then((res) => {
                            $scope.folderTree = res.data.tree || [];
                        });
                    };

                    // Load contents (same as admin - uses /folders?parent_id)
                    $scope.loadContents = (folderId) => {
                        $scope.loading = true;
                        const id =
                            folderId !== undefined
                                ? folderId
                                : $scope.currentFolderId;

                        // Use same API as admin: /folders?parent_id
                        $http
                            .get(`${API}/folders`, {
                                params: { parent_id: id },
                            })
                            .then((res) => {
                                const d = res.data;
                                $scope.folders = d.folders || [];
                                // Filter files by accept type if needed
                                let files = d.files || [];
                                if (
                                    $scope.accept &&
                                    $scope.accept.startsWith("image")
                                ) {
                                    files = files.filter(
                                        (f) => f.file_type === "image"
                                    );
                                }
                                $scope.files = files;
                                $scope.breadcrumb = d.breadcrumb || [
                                    { id: null, name: "Tất cả" },
                                ];
                            })
                            .finally(() => {
                                $scope.loading = false;
                            });
                    };

                    // Navigate to folder
                    $scope.navigateTo = (folderId) => {
                        $scope.currentFolderId = folderId;
                        $scope.isSearching = false;
                        $scope.searchKeyword = "";
                        $scope.loadContents(folderId);
                    };

                    // Go up
                    $scope.goUp = () => {
                        if ($scope.breadcrumb.length > 1) {
                            const parent =
                                $scope.breadcrumb[$scope.breadcrumb.length - 2];
                            $scope.navigateTo(parent.id);
                        }
                    };

                    // Expand folder tree path to show a specific folder
                    $scope.expandFolderPath = (folderId) => {
                        const expandRecursive = (folders, targetId) => {
                            for (let folder of folders) {
                                if (folder.id == targetId) {
                                    return true;
                                }
                                if (folder.children && folder.children.length) {
                                    if (
                                        expandRecursive(
                                            folder.children,
                                            targetId
                                        )
                                    ) {
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
                            $scope.loadContents();
                            return;
                        }
                        $scope.loading = true;
                        $scope.isSearching = true;
                        $http
                            .get(`${API}/files`, {
                                params: { search: $scope.searchKeyword },
                            })
                            .then((res) => {
                                let files = res.data.files || [];
                                if (
                                    $scope.accept &&
                                    $scope.accept.startsWith("image")
                                ) {
                                    files = files.filter(
                                        (f) => f.file_type === "image"
                                    );
                                }
                                $scope.files = files;
                                $scope.folders = res.data.folders || [];
                                $scope.folderCount =
                                    res.data.folder_count ||
                                    $scope.folders.length;
                                $scope.fileCount =
                                    res.data.file_count || $scope.files.length;
                                // Show search result breadcrumb with counts
                                $scope.breadcrumb = [
                                    {
                                        id: null,
                                        name: `Kết quả tìm kiếm: ${$scope.searchKeyword} (${$scope.folderCount} Thư mục - ${$scope.fileCount} Tệp tin)`,
                                    },
                                ];
                            })
                            .finally(() => {
                                $scope.loading = false;
                            });
                    };

                    // Select file (picker mode)
                    $scope.selectFile = (file) => {
                        if (!$scope.isPicker) return;

                        // If in search mode, highlight parent folder in sidebar
                        if ($scope.isSearching && file.folder_id) {
                            $scope.expandFolderPath(file.folder_id);
                            $scope.currentFolderId = file.folder_id;
                        } else if ($scope.isSearching && !file.folder_id) {
                            $scope.currentFolderId = null;
                        }

                        if ($scope.isMultiple) {
                            // Toggle selection
                            const idx = $scope.selectedFiles.findIndex(
                                (f) => f.id === file.id
                            );
                            if (idx > -1) {
                                $scope.selectedFiles.splice(idx, 1);
                            } else {
                                $scope.selectedFiles.push(file);
                            }
                        } else {
                            // Single selection
                            $scope.selectedFiles = [file];
                        }
                    };

                    // Check if file is selected
                    $scope.isSelected = (file) => {
                        return $scope.selectedFiles.some(
                            (f) => f.id === file.id
                        );
                    };

                    // Confirm selection (picker mode)
                    $scope.confirmSelection = () => {
                        if ($scope.selectedFiles.length === 0) {
                            $toastr.show(
                                "Vui lòng chọn ít nhất 1 file",
                                "warning"
                            );
                            return;
                        }
                        // Call parent callback
                        if ($scope.onSelect) {
                            const result = $scope.isMultiple
                                ? $scope.selectedFiles
                                : $scope.selectedFiles[0];
                            $scope.onSelect({ files: result });
                        }
                    };

                    // Double click to select (single mode)
                    $scope.onFileDblClick = (file) => {
                        if ($scope.isPicker && !$scope.isMultiple) {
                            $scope.selectedFiles = [file];
                            $scope.confirmSelection();
                        }
                    };

                    // ======== Context Menu ========
                    $scope.showContextMenu = (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        $scope.$apply(() => {
                            $scope.contextMenu.visible = true;
                            $scope.contextMenu.x = e.clientX;
                            $scope.contextMenu.y = e.clientY;
                        });
                    };

                    $scope.hideContextMenu = () => {
                        $scope.contextMenu.visible = false;
                    };

                    // Hide context menu on click anywhere
                    $(document).on("click", () => {
                        $scope.$apply(() => {
                            $scope.hideContextMenu();
                        });
                    });

                    // ======== Upload Modal ========
                    $scope.uploadFiles = [];
                    $scope.uploading = false;
                    $scope.isDragover = false;

                    $scope.openUploadModal = () => {
                        $scope.hideContextMenu();
                        $scope.uploadFiles = [];
                        element.find(".picker-upload-modal").modal("show");
                    };

                    $scope.onFilesSelected = (files) => {
                        $scope.$apply(() => {
                            $scope.uploadFiles = Array.from(files);
                        });
                    };

                    $scope.removeUploadFile = (index) => {
                        $scope.uploadFiles.splice(index, 1);
                    };

                    $scope.uploadFilesAction = () => {
                        if (!$scope.uploadFiles.length) return;
                        $scope.uploading = true;

                        const formData = new FormData();
                        formData.append(
                            "folder_id",
                            $scope.currentFolderId || ""
                        );
                        $scope.uploadFiles.forEach((f, i) => {
                            formData.append(`files[${i}]`, f);
                        });

                        $http
                            .post(`${API}/files`, formData, {
                                headers: { "Content-Type": undefined },
                                transformRequest: angular.identity,
                            })
                            .then(() => {
                                $toastr.show("Tải lên thành công", "success");
                                $scope.uploadFiles = [];
                                element
                                    .find(".picker-upload-modal")
                                    .modal("hide");
                                $scope.loadContents();
                            })
                            .catch((err) => {
                                $toastr.show(
                                    err.data?.message || "Lỗi tải lên",
                                    "error"
                                );
                            })
                            .finally(() => {
                                $scope.uploading = false;
                            });
                    };

                    // ======== Create Folder Modal ========
                    $scope.newFolderName = "";
                    $scope.creatingFolder = false;

                    $scope.openCreateFolderModal = () => {
                        $scope.hideContextMenu();
                        $scope.newFolderName = "";
                        element
                            .find(".picker-create-folder-modal")
                            .modal("show");
                    };

                    $scope.createFolder = () => {
                        if (!$scope.newFolderName) return;
                        $scope.creatingFolder = true;

                        $http
                            .post(`${API}/folders`, {
                                name: $scope.newFolderName,
                                parent_id: $scope.currentFolderId,
                            })
                            .then(() => {
                                $toastr.show(
                                    "Tạo thư mục thành công",
                                    "success"
                                );
                                $scope.newFolderName = "";
                                element
                                    .find(".picker-create-folder-modal")
                                    .modal("hide");
                                $scope.loadFolderTree();
                                $scope.loadContents();
                            })
                            .catch((err) => {
                                $toastr.show(
                                    err.data?.message || "Lỗi tạo thư mục",
                                    "error"
                                );
                            })
                            .finally(() => {
                                $scope.creatingFolder = false;
                            });
                    };

                    // Format bytes
                    $scope.formatBytes = (bytes) => {
                        if (!bytes) return "0 B";
                        const k = 1024;
                        const sizes = ["B", "KB", "MB", "GB"];
                        const i = Math.floor(Math.log(bytes) / Math.log(k));
                        return (
                            parseFloat((bytes / Math.pow(k, i)).toFixed(2)) +
                            " " +
                            sizes[i]
                        );
                    };

                    // Cleanup
                    $scope.$on("$destroy", () => {
                        $(document).off("click");
                    });

                    // Init
                    $scope.loadFolderTree();
                    $scope.loadContents();
                },
            };
        },
    ]);
}
