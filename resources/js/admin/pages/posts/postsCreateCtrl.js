adminApp.controller("PostsCreateCtrl", [
    "$scope",
    "$rootScope",
    "$http",
    "$sce",
    "BASE_API",
    "$toastr",
    "$confirm",
    function ($scope, $rootScope, $http, $sce, BASE_API, $toastr, $confirm) {
        // ========================================
        // State
        // ========================================
        $scope.loading = true;
        $scope.saving = false;
        $scope.submitted = false;
        $scope.activeTab = "general"; // 'general' or language code
        $scope.activeLang = "vi";

        $scope.languages = [];
        $scope.categoriesList = [];

        // Media Picker state
        $scope.mediaPickerTarget = null;
        $scope.mediaPickerMode = "single";
        $scope.mediaPickerAccept = "image/*";

        // Tags input (comma-separated string)
        $scope.tagsInput = "";

        // ========================================
        // Model
        // ========================================
        function defaultModel() {
            return {
                titles: {},
                categories: [],
                main_category_id: null,
                position: 0,
                is_featured: true,
                show_toc: true,
                status: 1,
                main_image: "",
                album_images: [],
                short_descriptions: {},
                contents: {},
                tags: [],
                attachments: [],
                video_urls: [],
                // Global SEO fields
                slug: "",
                seo_title: "",
                seo_description: "",
                seo_keywords: "",
            };
        }

        $scope.model = defaultModel();

        // ========================================
        // Init
        // ========================================
        $scope.init = () => {
            const promises = [
                $http.get(`${BASE_API}/languages`, {
                    params: { active_only: "true" },
                }),
                $http.get(`${BASE_API}/post-categories/dropdown`),
            ];

            Promise.all(promises)
                .then((responses) => {
                    // Languages
                    $scope.languages = responses[0].data.languages || [];
                    $scope.languages.forEach((lang) => {
                        lang.flag_icon = $sce.trustAsHtml(lang.flag_icon || "");
                        $scope.model.titles[lang.code] = "";
                        $scope.model.short_descriptions[lang.code] = "";
                        $scope.model.contents[lang.code] = "";
                    });

                    // Set default lang
                    const defaultLang = $scope.languages.find(
                        (l) => l.is_default
                    );
                    if (defaultLang) {
                        $scope.activeLang = defaultLang.code;
                    }

                    // Categories
                    $scope.categoriesList = responses[1].data || [];

                    $scope.$apply(() => {
                        $scope.loading = false;
                    });
                })
                .catch((err) => {
                    console.error("Init error:", err);
                    $toastr.show("Không thể tải dữ liệu", "error");
                    $scope.$apply(() => {
                        $scope.loading = false;
                    });
                });
        };

        // ========================================
        // Slug generation (auto from Vietnamese title)
        // ========================================
        function toSlug(str) {
            return str
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .replace(/đ/g, "d")
                .replace(/Đ/g, "d")
                .replace(/[^a-z0-9\s-]/g, "")
                .replace(/\s+/g, "-")
                .replace(/-+/g, "-")
                .replace(/^-+|-+$/g, "");
        }

        // Auto-generate slug from Vietnamese title
        $scope.autoSlugFromVietnamese = () => {
            const viTitle = $scope.model.titles["vi"] || "";
            if (viTitle) {
                $scope.model.slug = toSlug(viTitle);
            }
        };

        // ========================================
        // Category helpers
        // ========================================
        $scope.getCategoryName = (catId) => {
            const cat = $scope.categoriesList.find((c) => c.id === catId);
            return cat ? cat.name : "";
        };

        $scope.toggleCategory = (catId) => {
            const idx = $scope.model.categories.indexOf(catId);
            if (idx > -1) {
                $scope.model.categories.splice(idx, 1);
            } else {
                $scope.model.categories.push(catId);
            }
        };

        // ========================================
        // Language completeness check
        // ========================================
        $scope.isLangComplete = (langCode) => {
            return (
                $scope.model.titles[langCode]?.trim() &&
                $scope.model.short_descriptions[langCode]?.trim() &&
                $scope.model.contents[langCode]?.trim()
            );
        };

        // Tab Navigation
        $scope.setActiveTab = (tab) => {
            $scope.activeTab = tab;
            // If selecting a language tab, also update activeLang for SEO analysis
            if (tab !== "general") {
                $scope.activeLang = tab;
            }
        };

        $scope.setActiveLang = (langCode) => {
            $scope.activeLang = langCode;
            $scope.activeTab = langCode; // Keep in sync
        };

        // Handle media selection for TinyMCE
        $scope.onMceMediaSelected = (files) => {
            // files can be single object or array depending on select-mode
            const file = Array.isArray(files) ? files[0] : files;
            if (file) {
                const url = file.url || file.path;
                // Use $rootScope.insertMediaToMCE to pass URL back to TinyMCE
                $rootScope.insertMediaToMCE(url);
                // Close the modal
                $("#mediaPickerModalMCE").modal("hide");
            }
        };
        // ========================================
        // SEO Analysis (global fields)
        // ========================================
        $scope.seoAnalysis = {
            issues: [],
            improvements: [],
            good: [],
        };

        const updateSeoAnalysis = () => {
            const langCode = $scope.activeLang;
            const issues = [];
            const improvements = [];
            const good = [];

            // Issues (critical)
            if (!$scope.model.seo_description) {
                issues.push({
                    label: "Mô tả SEO",
                    message: "Hãy nhập mô tả SEO",
                });
            }
            if (!$scope.model.slug) {
                issues.push({
                    label: "Đường dẫn bài viết",
                    message: "Hãy nhập đường dẫn bài viết",
                });
            }
            if (!$scope.model.contents[langCode]) {
                issues.push({
                    label: "Nội dung bài viết",
                    message: "Hãy nhập nội dung bài viết",
                });
            } else {
                const text = $scope.model.contents[langCode].replace(
                    /<[^>]*>/g,
                    ""
                );
                const wordCount = text.split(/\s+/).filter((w) => w).length;
                if (wordCount < 300) {
                    issues.push({
                        label: "Độ dài văn bản",
                        message: `Nội dung quá ngắn (${wordCount}/300 từ)`,
                    });
                }
            }

            // Improvements (recommended)
            if (!$scope.model.seo_title) {
                improvements.push({
                    label: "Tiêu đề SEO",
                    message: "Hãy nhập thông tin tiêu đề SEO",
                });
            }
            if (!$scope.model.seo_keywords) {
                improvements.push({
                    label: "Từ khóa SEO",
                    message: "Hãy nhập một từ khóa SEO",
                });
            }
            if (!$scope.model.main_image) {
                improvements.push({
                    label: "Ảnh bài viết",
                    message: "Nên có ảnh đại diện cho bài viết",
                });
            }
            // Check for internal links
            const content = $scope.model.contents[langCode] || "";
            if (content && !content.includes('href="')) {
                improvements.push({
                    label: "Đường dẫn nội bộ",
                    message: "Nên có đường dẫn trong nội dung bài viết",
                });
            }

            // Good results
            if ($scope.model.titles[langCode]) {
                good.push({
                    label: "Tiêu đề bài viết",
                    message: "Đã nhập tiêu đề bài viết",
                });
            }
            if ($scope.model.slug) {
                good.push({
                    label: "Đường dẫn",
                    message: "Đã nhập đường dẫn SEO",
                });
            }
            if ($scope.model.seo_title) {
                good.push({
                    label: "Tiêu đề SEO",
                    message: "Đã nhập tiêu đề SEO",
                });
            }
            if ($scope.model.seo_description) {
                good.push({ label: "Mô tả SEO", message: "Đã nhập mô tả SEO" });
            }
            if ($scope.model.seo_keywords) {
                good.push({
                    label: "Từ khóa SEO",
                    message: "Đã nhập từ khóa SEO",
                });
            }
            if ($scope.model.main_image) {
                good.push({
                    label: "Ảnh đại diện",
                    message: "Có ảnh đại diện cho bài viết",
                });
            }
            if ($scope.model.contents[langCode]) {
                const text = $scope.model.contents[langCode].replace(
                    /<[^>]*>/g,
                    ""
                );
                const wordCount = text.split(/\s+/).filter((w) => w).length;
                if (wordCount >= 300) {
                    good.push({
                        label: "Độ dài nội dung",
                        message: `Nội dung đủ dài (${wordCount} từ)`,
                    });
                }
            }

            // Update scope
            $scope.seoAnalysis.issues = issues;
            $scope.seoAnalysis.improvements = improvements;
            $scope.seoAnalysis.good = good;
        };

        // Watch for changes and update SEO analysis
        $scope.$watch("model", updateSeoAnalysis, true);
        $scope.$watch("activeLang", updateSeoAnalysis);

        // ========================================
        // Media Picker
        // ========================================
        $scope.openMediaPicker = (target) => {
            $scope.mediaPickerTarget = target;
            switch (target) {
                case "main_image":
                    $scope.mediaPickerMode = "single";
                    $scope.mediaPickerAccept = "image/*";
                    break;
                case "album":
                    $scope.mediaPickerMode = "multiple";
                    $scope.mediaPickerAccept = "image/*";
                    break;
                case "attachments":
                    $scope.mediaPickerMode = "multiple";
                    $scope.mediaPickerAccept = "*/*";
                    break;
            }
            $("#mediaPickerModal").modal("show");
        };

        $scope.onMediaSelected = (files) => {
            $("#mediaPickerModal").modal("hide");
            if (!files) return;
            const fileList = Array.isArray(files) ? files : [files];

            switch ($scope.mediaPickerTarget) {
                case "main_image":
                    if (fileList[0]) $scope.model.main_image = fileList[0].url;
                    break;
                case "album":
                    fileList.forEach((f) => {
                        if (
                            f.url &&
                            !$scope.model.album_images.includes(f.url)
                        ) {
                            $scope.model.album_images.push(f.url);
                        }
                    });
                    break;
                case "attachments":
                    fileList.forEach((f) => {
                        // Get filename from properties (API returns 'name' from original_name)
                        let fileName = f.name || f.original_name || f.file_name;
                        if (!fileName && f.url) {
                            // Extract filename from URL path as fallback
                            fileName = f.url.split("/").pop().split("?")[0];
                        }
                        $scope.model.attachments.push({
                            name: fileName || "Tệp đính kèm",
                            path: f.path || f.url,
                            url: f.url,
                        });
                    });
                    break;
            }
        };

        $scope.removeAlbumImage = (index) => {
            $confirm.show({
                title: "Xóa ảnh",
                message: "Bạn có chắc muốn xóa ảnh này khỏi album?",
                icon: "fa-image",
                confirmText: "Xóa ảnh",
                confirmIcon: "fa-trash",
                danger: true,
                onConfirm: () => $scope.model.album_images.splice(index, 1),
            });
        };

        $scope.removeAttachment = (index) => {
            const file = $scope.model.attachments[index];
            $confirm.show({
                title: "Xóa tệp đính kèm",
                message: `Bạn có chắc muốn xóa "${file?.name || "tệp này"}"?`,
                icon: "fa-file-alt",
                confirmText: "Xóa tệp",
                confirmIcon: "fa-trash",
                danger: true,
                onConfirm: () => $scope.model.attachments.splice(index, 1),
            });
        };

        // ========================================
        // Video URLs
        // ========================================
        $scope.addVideo = () =>
            $scope.model.video_urls.push({ type: "youtube", url: "" });

        $scope.removeVideo = (index) => {
            $confirm.show({
                title: "Xóa video",
                message: "Bạn có chắc muốn xóa video này?",
                icon: "fa-video",
                confirmText: "Xóa video",
                confirmIcon: "fa-trash",
                danger: true,
                onConfirm: () => $scope.model.video_urls.splice(index, 1),
            });
        };

        // ========================================
        // Validation
        // ========================================
        $scope.validate = () => {
            // Validate global slug
            if (!$scope.model.slug?.trim()) {
                $toastr.show("Vui lòng nhập đường dẫn SEO", "error");
                return false;
            }

            for (const lang of $scope.languages) {
                if (!$scope.model.titles[lang.code]?.trim()) {
                    $toastr.show(`Vui lòng nhập tiêu đề ${lang.name}`, "error");
                    $scope.activeLang = lang.code;
                    return false;
                }
                if (!$scope.model.short_descriptions[lang.code]?.trim()) {
                    $toastr.show(
                        `Vui lòng nhập mô tả ngắn ${lang.name}`,
                        "error"
                    );
                    $scope.activeLang = lang.code;
                    return false;
                }
                if (!$scope.model.contents[lang.code]?.trim()) {
                    $toastr.show(
                        `Vui lòng nhập nội dung ${lang.name}`,
                        "error"
                    );
                    $scope.activeLang = lang.code;
                    return false;
                }
            }

            // Validate main category (required)
            if (!$scope.model.main_category_id) {
                $toastr.show("Vui lòng chọn danh mục chính", "error");
                return false;
            }

            return true;
        };

        // ========================================
        // Save
        // ========================================
        function prepareData() {
            const data = { ...$scope.model };

            // Convert categories to integers
            if (data.categories && Array.isArray(data.categories)) {
                data.categories = data.categories.map((c) => {
                    const num = parseInt(c, 10);
                    return isNaN(num) ? c : num;
                });
            }

            // Convert tags input to array
            if ($scope.tagsInput) {
                data.tags = $scope.tagsInput
                    .split(",")
                    .map((t) => t.trim())
                    .filter((t) => t)
                    .slice(0, 10);
            }

            // seo_keywords is already a string (comma-separated), no conversion needed

            // Filter empty video URLs
            data.video_urls = data.video_urls.filter((v) => v.url);

            return data;
        }

        $scope.save = () => {
            $scope.submitted = true;
            if (!$scope.validate()) return;

            $scope.saving = true;
            const data = prepareData();
            data.status = 1;

            $http
                .post(`${BASE_API}/posts`, data)
                .then(() => {
                    $toastr.show("Đã thêm bài viết thành công", "success");
                    window.location.href = "/admin/posts";
                })
                .catch((err) => {
                    const msg =
                        err?.data?.message || err?.data?.data || "Lưu thất bại";
                    $toastr.show(msg, "error");
                })
                .finally(() => ($scope.saving = false));
        };

        $scope.saveDraft = () => {
            $scope.saving = true;
            const data = prepareData();
            data.status = 0;

            $http
                .post(`${BASE_API}/posts`, data)
                .then(() => {
                    $toastr.show("Đã lưu nháp thành công", "success");
                    window.location.href = "/admin/posts";
                })
                .catch((err) => {
                    const msg =
                        err?.data?.message || err?.data?.data || "Lưu thất bại";
                    $toastr.show(msg, "error");
                })
                .finally(() => ($scope.saving = false));
        };

        // Init
        $scope.init();
    },
]);
