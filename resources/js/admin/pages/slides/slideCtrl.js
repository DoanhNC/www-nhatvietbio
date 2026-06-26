/**
 * Slide Controller - Manages homepage slides
 */
adminApp.controller("SlideCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    "$confirm",
    function ($scope, $http, BASE_API, $toastr, $confirm) {
        // State
        $scope.slides = [];
        $scope.loading = true;
        $scope.saving = false;

        // Form data for add/edit modal
        $scope.formData = {
            selectedMedia: null,
            title: "",
            is_active: true,
        };

        /**
         * Load all slides
         */
        $scope.loadSlides = function () {
            $scope.loading = true;
            $http.get(BASE_API + "/slides").then(
                function (response) {
                    $scope.slides = response.data.slides || [];
                    $scope.loading = false;
                },
                function () {
                    $toastr.show("Không thể tải danh sách slide", "error");
                    $scope.loading = false;
                }
            );
        };

        /**
         * Open add slide modal
         */
        $scope.openAddModal = function () {
            // Reset form
            $scope.formData = {
                selectedMedia: null,
                title: "",
                is_active: true,
            };
            $("#slideFormModal").modal("show");
        };

        /**
         * Open media picker modal
         */
        $scope.openMediaPicker = function () {
            $("#mediaPickerModal").modal("show");
        };

        /**
         * Handle file selected from media picker directive
         */
        $scope.onMediaSelected = function (files) {
            if (!files) return;

            // Media picker returns object with storage_path or url
            $scope.formData.selectedMedia = {
                id: files.id,
                url: files.storage_path || files.url,
                original_name: files.original_name || files.name,
            };

            // Close media picker modal
            $("#mediaPickerModal").modal("hide");
        };

        /**
         * Save new slide
         */
        $scope.saveSlide = function () {
            if (!$scope.formData.selectedMedia) {
                $toastr.show("Vui lòng chọn ảnh", "error");
                return;
            }

            $scope.saving = true;
            $http
                .post(BASE_API + "/slides", {
                    media_id: $scope.formData.selectedMedia.id,
                    title: $scope.formData.title,
                    is_active: $scope.formData.is_active,
                })
                .then(
                    function (response) {
                        if (response.data.success) {
                            $scope.slides.push(response.data.slide);
                            $toastr.show("Đã thêm slide mới", "success");
                            $("#slideFormModal").modal("hide");
                        }
                        $scope.saving = false;
                    },
                    function () {
                        $toastr.show("Không thể thêm slide", "error");
                        $scope.saving = false;
                    }
                );
        };

        /**
         * Update slide (title, is_active)
         */
        $scope.updateSlide = function (slide) {
            $http.put(BASE_API + "/slides/" + slide.id, {
                title: slide.title,
                is_active: slide.is_active,
            });
        };

        /**
         * Delete slide with confirmation
         */
        $scope.deleteSlide = function (slide) {
            $confirm.show({
                title: "Xóa slide",
                message: "Bạn có chắc muốn xóa slide này?",
                icon: "fa-image",
                confirmText: "Xóa",
                confirmIcon: "fa-trash",
                danger: true,
                onConfirm: function () {
                    $http.delete(BASE_API + "/slides/" + slide.id).then(
                        function (response) {
                            if (response.data.success) {
                                var index = $scope.slides.indexOf(slide);
                                if (index > -1) {
                                    $scope.slides.splice(index, 1);
                                }
                                $toastr.show("Đã xóa slide", "success");
                            }
                        },
                        function () {
                            $toastr.show("Không thể xóa slide", "error");
                        }
                    );
                },
            });
        };

        /**
         * Move slide up in order
         */
        $scope.moveUp = function (index) {
            if (index <= 0) return;
            var temp = $scope.slides[index];
            $scope.slides[index] = $scope.slides[index - 1];
            $scope.slides[index - 1] = temp;
            $scope.saveOrder();
        };

        /**
         * Move slide down in order
         */
        $scope.moveDown = function (index) {
            if (index >= $scope.slides.length - 1) return;
            var temp = $scope.slides[index];
            $scope.slides[index] = $scope.slides[index + 1];
            $scope.slides[index + 1] = temp;
            $scope.saveOrder();
        };

        /**
         * Save slide order to server
         */
        $scope.saveOrder = function () {
            var order = $scope.slides.map(function (s) {
                return s.id;
            });
            $http.post(BASE_API + "/slides/reorder", { order: order }).then(
                function () {
                    $toastr.show("Đã cập nhật thứ tự", "success");
                },
                function () {
                    $toastr.show("Lỗi cập nhật thứ tự", "error");
                }
            );
        };

        // Initialize
        $scope.loadSlides();
    },
]);
