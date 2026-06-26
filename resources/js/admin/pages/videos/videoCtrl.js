/**
 * Video Controller - Manages YouTube videos
 */
adminApp.controller("VideoCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    "$confirm",
    function ($scope, $http, BASE_API, $toastr, $confirm) {
        // State
        $scope.videos = [];
        $scope.loading = true;
        $scope.saving = false;

        // Form data for add modal
        $scope.formData = {
            youtube_url: "",
            title: "",
            is_active: true,
            preview_id: null,
        };

        /**
         * Extract YouTube ID from URL
         */
        function extractYoutubeId(url) {
            if (!url) return null;
            var match = url.match(
                /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i
            );
            return match ? match[1] : null;
        }

        /**
         * Preview video when URL changes
         */
        $scope.previewVideo = function () {
            $scope.formData.preview_id = extractYoutubeId(
                $scope.formData.youtube_url
            );
        };

        /**
         * Load all videos
         */
        $scope.loadVideos = function () {
            $scope.loading = true;
            $http.get(BASE_API + "/videos").then(
                function (response) {
                    $scope.videos = response.data.videos || [];
                    $scope.loading = false;
                },
                function () {
                    $toastr.show("Không thể tải danh sách video", "error");
                    $scope.loading = false;
                }
            );
        };

        /**
         * Open add video modal
         */
        $scope.openAddModal = function () {
            // Reset form
            $scope.formData = {
                youtube_url: "",
                title: "",
                is_active: true,
                preview_id: null,
            };
            $("#videoFormModal").modal("show");
        };

        /**
         * Save new video
         */
        $scope.saveVideo = function () {
            if (!$scope.formData.youtube_url || !$scope.formData.preview_id) {
                $toastr.show("Vui lòng nhập URL YouTube hợp lệ", "error");
                return;
            }

            $scope.saving = true;
            $http
                .post(BASE_API + "/videos", {
                    youtube_url: $scope.formData.youtube_url,
                    title: $scope.formData.title,
                    is_active: $scope.formData.is_active,
                })
                .then(
                    function (response) {
                        if (response.data.success) {
                            $scope.videos.push(response.data.video);
                            $toastr.show("Đã thêm video mới", "success");
                            $("#videoFormModal").modal("hide");
                        }
                        $scope.saving = false;
                    },
                    function (error) {
                        var msg = error.data?.message || "Không thể thêm video";
                        $toastr.show(msg, "error");
                        $scope.saving = false;
                    }
                );
        };

        /**
         * Update video (title, is_active)
         */
        $scope.updateVideo = function (video) {
            $http.put(BASE_API + "/videos/" + video.id, {
                title: video.title,
                is_active: video.is_active,
            });
        };

        /**
         * Delete video with confirmation
         */
        $scope.deleteVideo = function (video) {
            $confirm.show({
                title: "Xóa video",
                message: "Bạn có chắc muốn xóa video này?",
                icon: "fa-youtube",
                confirmText: "Xóa",
                confirmIcon: "fa-trash",
                danger: true,
                onConfirm: function () {
                    $http.delete(BASE_API + "/videos/" + video.id).then(
                        function (response) {
                            if (response.data.success) {
                                var index = $scope.videos.indexOf(video);
                                if (index > -1) {
                                    $scope.videos.splice(index, 1);
                                }
                                $toastr.show("Đã xóa video", "success");
                            }
                        },
                        function () {
                            $toastr.show("Không thể xóa video", "error");
                        }
                    );
                },
            });
        };

        /**
         * Move video up in order
         */
        $scope.moveUp = function (index) {
            if (index <= 0) return;
            var temp = $scope.videos[index];
            $scope.videos[index] = $scope.videos[index - 1];
            $scope.videos[index - 1] = temp;
            $scope.saveOrder();
        };

        /**
         * Move video down in order
         */
        $scope.moveDown = function (index) {
            if (index >= $scope.videos.length - 1) return;
            var temp = $scope.videos[index];
            $scope.videos[index] = $scope.videos[index + 1];
            $scope.videos[index + 1] = temp;
            $scope.saveOrder();
        };

        /**
         * Save video order to server
         */
        $scope.saveOrder = function () {
            var order = $scope.videos.map(function (v) {
                return v.id;
            });
            $http.post(BASE_API + "/videos/reorder", { order: order }).then(
                function () {
                    $toastr.show("Đã cập nhật thứ tự", "success");
                },
                function () {
                    $toastr.show("Lỗi cập nhật thứ tự", "error");
                }
            );
        };

        // Initialize
        $scope.loadVideos();
    },
]);
