adminApp.controller("PostsEditCtrl", [
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

        $scope.mediaPickerTarget = null;
        $scope.mediaPickerMode = "single";
        $scope.mediaPickerAccept = "image/*";

        $scope.tagsInput = "";

        // ========================================
        // Model
        // ========================================
        $scope.model = {
            id: null,
            titles: {},
            categories: [],
            main_category_id: null,
            position: 0,
            view_count: 0,
            is_featured: false,
            show_toc: false,
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
                    $scope.languages = responses[0].data.languages || [];
                    $scope.languages.forEach((lang) => {
                        lang.flag_icon = $sce.trustAsHtml(lang.flag_icon || "");
                    });

                    const defaultLang = $scope.languages.find(
                        (l) => l.is_default
                    );
                    if (defaultLang) $scope.activeLang = defaultLang.code;

                    $scope.categoriesList = responses[1].data || [];

                    if (pageData && pageData.data)
                        $scope.loadPostData(pageData.data);

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

        $scope.loadPostData = (data) => {
            // Extract category IDs from categories array
            // (handle both array of IDs and array of objects with id property)
            let categoryIds = [];
            if (Array.isArray(data.categories)) {
                categoryIds = data.categories.map((cat) => {
                    return typeof cat === "object" ? cat.id : cat;
                });
            }

            $scope.model = {
                id: data.id,
                titles: data.titles || {},
                categories: categoryIds,
                main_category_id: data.main_category_id,
                position: data.position || 0,
                view_count: data.view_count || 0,
                is_featured: !!data.is_featured,
                show_toc: !!data.show_toc,
                status: data.status,
                main_image: data.main_image || "",
                album_images: data.album_images || [],
                short_descriptions: data.short_descriptions || {},
                contents: data.contents || {},
                tags: data.tags || [],
                // Process attachments to ensure each has name (extract from URL if not present)
                attachments: (data.attachments || []).map((att) => {
                    if (typeof att === "string") {
                        return {
                            name: att.split("/").pop().split("?")[0],
                            url: att,
                        };
                    }
                    if (!att.name && att.url) {
                        return {
                            ...att,
                            name: att.url.split("/").pop().split("?")[0],
                        };
                    }
                    return att;
                }),
                video_urls: data.video_urls || [],
                // Global SEO fields
                slug: data.slug || "",
                seo_title: data.seo_title || "",
                seo_description: data.seo_description || "",
                seo_keywords: data.seo_keywords || "",
            };

            if (Array.isArray($scope.model.tags)) {
                $scope.tagsInput = $scope.model.tags.join(", ");
            }

            // Refresh chosen selects after model is loaded (for categories)
            setTimeout(() => {
                $("select[chosen]").trigger("chosen:updated");
            }, 300);
        };

        // ========================================
        // Helpers
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
            if (tab !== "general") {
                $scope.activeLang = tab;
            }
        };

        $scope.setActiveLang = (langCode) => {
            $scope.activeLang = langCode;
            $scope.activeTab = langCode;
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

            // Check content
            const langs = $scope.languages.map((l) => l.code);
            const hasAnyContent = langs.some(
                (code) => $scope.model.contents[code]
            );
            const hasAnyTitle = langs.some((code) => $scope.model.titles[code]);

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
            if (!hasAnyContent) {
                issues.push({
                    label: "Nội dung bài viết",
                    message: "Hãy nhập nội dung bài viết",
                });
            } else {
                const content = $scope.model.contents[langCode] || "";
                const text = content.replace(/<[^>]*>/g, "");
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
            const content = $scope.model.contents[langCode] || "";
            if (content && !content.includes('href="')) {
                improvements.push({
                    label: "Đường dẫn nội bộ",
                    message: "Nên có đường dẫn trong nội dung bài viết",
                });
            }

            // Good results
            if (hasAnyTitle) {
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
            if (hasAnyContent) {
                const content = $scope.model.contents[langCode] || "";
                const text = content.replace(/<[^>]*>/g, "");
                const wordCount = text.split(/\s+/).filter((w) => w).length;
                if (wordCount >= 300) {
                    good.push({
                        label: "Độ dài nội dung",
                        message: `Nội dung đủ dài (${wordCount} từ)`,
                    });
                }
            }

            $scope.seoAnalysis.issues = issues;
            $scope.seoAnalysis.improvements = improvements;
            $scope.seoAnalysis.good = good;
        };

        $scope.$watch("model", updateSeoAnalysis, true);
        $scope.$watch("activeLang", updateSeoAnalysis);

        // Media Picker
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
                        if (f.url && !$scope.model.album_images.includes(f.url))
                            $scope.model.album_images.push(f.url);
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

        // Validation
        $scope.validate = () => {
            // Validate global slug
            if (!$scope.model.slug?.trim()) {
                $toastr.show("Vui lòng nhập đường dẫn SEO", "error");
                return false;
            }

            for (const lang of $scope.languages) {
                if (!$scope.model.titles[lang.code]?.trim()) {
                    $toastr.show(`Nhập tiêu đề ${lang.name}`, "error");
                    $scope.activeLang = lang.code;
                    return false;
                }
                if (!$scope.model.short_descriptions[lang.code]?.trim()) {
                    $toastr.show(`Nhập mô tả ${lang.name}`, "error");
                    $scope.activeLang = lang.code;
                    return false;
                }
                if (!$scope.model.contents[lang.code]?.trim()) {
                    $toastr.show(`Nhập nội dung ${lang.name}`, "error");
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

        // Save
        function prepareData() {
            const data = { ...$scope.model };

            // Convert categories to integers
            if (data.categories && Array.isArray(data.categories)) {
                data.categories = data.categories.map((c) => {
                    const num = parseInt(c, 10);
                    return isNaN(num) ? c : num;
                });
            }

            if ($scope.tagsInput)
                data.tags = $scope.tagsInput
                    .split(",")
                    .map((t) => t.trim())
                    .filter((t) => t)
                    .slice(0, 10);
            // seo_keywords is already a string (comma-separated), no conversion needed
            data.video_urls = data.video_urls.filter((v) => v.url);
            return data;
        }

        $scope.save = () => {
            $scope.submitted = true;
            if (!$scope.validate()) return;
            $scope.saving = true;
            const data = prepareData();
            $http
                .put(`${BASE_API}/posts/${data.id}`, data)
                .then(() => {
                    $toastr.show("Cập nhật thành công", "success");
                    window.location.href = pageData.listUrl;
                })
                .catch((err) => {
                    $toastr.show(
                        err?.data?.message || "Cập nhật thất bại",
                        "error"
                    );
                })
                .finally(() => ($scope.saving = false));
        };

        $scope.init();
    },
]);
