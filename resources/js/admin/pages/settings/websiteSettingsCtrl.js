adminApp.controller("WebsiteSettingsCtrl", [
    "$scope",
    "$http",
    "$rootScope",
    "BASE_API",
    "$toastr",
    function ($scope, $http, $rootScope, BASE_API, $toastr) {
        // Tab state
        $scope.activeTab = "website";

        // Data models
        $scope.website = {};
        $scope.smtp = {
            is_active: "0",
            host: "smtp.gmail.com",
            port: 587,
            encryption: "tls",
        };
        $scope.templates = [];
        $scope.selectedTemplateId = "";
        $scope.currentTemplate = null;
        $scope.editTemplateId = "";
        $scope.editTemplate = null;
        $scope.testEmail = {
            recipient: "",
            type: "content",
            content: "",
            templateId: "",
        };

        // CC/BCC input
        $scope.ccEmailInput = "";
        $scope.bccEmailInput = "";

        // Loading states
        $scope.saving = false;
        $scope.sending = false;

        // Tab navigation
        $scope.setTab = (tab) => {
            $scope.activeTab = tab;
            if (tab === "template_config" || tab === "template_edit") {
                $scope.loadTemplates();
            }
        };

        // Load settings on init
        $scope.loadSettings = () => {
            $http
                .get(`${BASE_API}/website-settings`)
                .then((res) => {
                    if (res.data.status && res.data.data) {
                        $scope.website = res.data.data.website || {};
                        $scope.logo = res.data.data.logo || null;
                        $scope.favicon = res.data.data.favicon || null;
                        const smtp = res.data.data.smtp || {};
                        $scope.smtp = {
                            is_active: smtp.is_active || "0",
                            host: smtp.host || "smtp.gmail.com",
                            port: parseInt(smtp.port) || 587,
                            username: smtp.username || "",
                            password: smtp.password || "",
                            encryption: smtp.encryption || "tls",
                            from_name: smtp.from_name || "",
                            from_email: smtp.from_email || "",
                        };
                    }
                })
                .catch(() => {});
        };

        // ======== Logo Functions ========
        $scope.logo = null;

        // Open media picker modal for logo
        $scope.openLogoMediaPicker = () => {
            $scope.faviconPickerMode = false;
            $("#mediaPickerModal").modal("show");
        };

        // Handle media selection from picker (for both logo and favicon)
        $scope.onMediaSelect = (files) => {
            if (files && (files.storage_path || files.url)) {
                const imagePath = files.storage_path || files.url;

                if ($scope.faviconPickerMode) {
                    // Save favicon
                    $scope.favicon = imagePath;
                    $http
                        .post(`${BASE_API}/website-settings/favicon`, {
                            favicon_url: imagePath,
                        })
                        .then((res) => {
                            if (res.data.data?.favicon) {
                                $scope.favicon = res.data.data.favicon;
                            }
                            $toastr.show(
                                res.data.message ||
                                    "Cập nhật favicon thành công",
                                "success"
                            );
                            $rootScope.$broadcast("notification:refresh");
                        })
                        .catch((err) => {
                            $scope.favicon = null;
                            $toastr.show(
                                err.data?.message || "Lỗi cập nhật favicon",
                                "error"
                            );
                        });
                } else {
                    // Save logo
                    $scope.logo = imagePath;
                    $http
                        .post(`${BASE_API}/website-settings/logo`, {
                            logo_url: imagePath,
                        })
                        .then((res) => {
                            if (res.data.data?.logo) {
                                $scope.logo = res.data.data.logo;
                            }
                            $toastr.show(
                                res.data.message || "Cập nhật logo thành công",
                                "success"
                            );
                            $rootScope.$broadcast("notification:refresh");
                        })
                        .catch((err) => {
                            $scope.logo = null;
                            $toastr.show(
                                err.data?.message || "Lỗi cập nhật logo",
                                "error"
                            );
                        });
                }
            }
            $scope.faviconPickerMode = false;
            $("#mediaPickerModal").modal("hide");
        };

        // Remove logo
        $scope.removeLogo = () => {
            $http
                .post(`${BASE_API}/website-settings/logo`, {
                    remove_logo: true,
                })
                .then((res) => {
                    $scope.logo = null;
                    $toastr.show(res.data.message || "Đã xóa logo", "success");
                })
                .catch((err) => {
                    $toastr.show(err.data?.message || "Lỗi xóa logo", "error");
                });
        };

        // ======== Favicon Functions ========
        $scope.favicon = null;
        $scope.faviconPickerMode = false;

        // Open favicon media picker modal
        $scope.openFaviconMediaPicker = () => {
            $scope.faviconPickerMode = true;
            $("#mediaPickerModal").modal("show");
        };

        // Remove favicon
        $scope.removeFavicon = () => {
            $http
                .post(`${BASE_API}/website-settings/favicon`, {
                    remove_favicon: true,
                })
                .then((res) => {
                    $scope.favicon = null;
                    $toastr.show(
                        res.data.message || "Đã xóa favicon",
                        "success"
                    );
                })
                .catch((err) => {
                    $toastr.show(
                        err.data?.message || "Lỗi xóa favicon",
                        "error"
                    );
                });
        };

        // Load email templates
        $scope.loadTemplates = () => {
            $http
                .get(`${BASE_API}/email-templates`)
                .then((res) => {
                    if (res.data.status && res.data.data) {
                        $scope.templates = res.data.data;
                    }
                })
                .catch(() => {});
        };

        // Save website info
        $scope.saveWebsiteInfo = () => {
            $scope.saving = true;
            $http
                .put(`${BASE_API}/website-settings`, $scope.website)
                .then((res) => {
                    $toastr.show(
                        res.data.message || "Lưu thông tin website thành công",
                        "success"
                    );
                    $rootScope.$broadcast("notification:refresh");
                })
                .catch((err) => {
                    $toastr.show(err.data?.message || "Lưu thất bại", "error");
                })
                .finally(() => ($scope.saving = false));
        };

        // Save SMTP config
        $scope.saveSmtpConfig = () => {
            $scope.saving = true;
            const data = { ...$scope.smtp };

            // Handle custom host
            if (data.host === "custom" && data.custom_host) {
                data.host = data.custom_host;
            }
            delete data.custom_host;

            $http
                .put(`${BASE_API}/website-settings/smtp`, data)
                .then((res) => {
                    $toastr.show(
                        res.data.message || "Cấu hình email đã được lưu",
                        "success"
                    );
                    $rootScope.$broadcast("notification:refresh");
                })
                .catch((err) => {
                    $toastr.show(err.data?.message || "Lưu thất bại", "error");
                })
                .finally(() => ($scope.saving = false));
        };

        // Load template for config tab
        $scope.loadTemplate = () => {
            if (!$scope.selectedTemplateId) {
                $scope.currentTemplate = null;
                return;
            }

            $http
                .get(`${BASE_API}/email-templates/${$scope.selectedTemplateId}`)
                .then((res) => {
                    if (res.data.status && res.data.data) {
                        $scope.currentTemplate = res.data.data;
                        $scope.currentTemplate.cc_emails =
                            $scope.currentTemplate.cc_emails || [];
                        $scope.currentTemplate.bcc_emails =
                            $scope.currentTemplate.bcc_emails || [];
                    }
                })
                .catch(() => {});
        };

        // Load template for edit tab
        $scope.loadTemplateForEdit = () => {
            if (!$scope.editTemplateId) {
                $scope.editTemplate = null;
                return;
            }

            $http
                .get(`${BASE_API}/email-templates/${$scope.editTemplateId}`)
                .then((res) => {
                    if (res.data.status && res.data.data) {
                        $scope.editTemplate = res.data.data;
                    }
                })
                .catch(() => {});
        };

        // Add CC email input
        $scope.addCcEmail = () => {
            if (!$scope.currentTemplate.cc_emails) {
                $scope.currentTemplate.cc_emails = [];
            }
            $scope.currentTemplate.cc_emails.push("");
        };

        // Remove CC email
        $scope.removeCcEmail = (index) => {
            $scope.currentTemplate.cc_emails.splice(index, 1);
        };

        // Add BCC email input
        $scope.addBccEmail = () => {
            if (!$scope.currentTemplate.bcc_emails) {
                $scope.currentTemplate.bcc_emails = [];
            }
            $scope.currentTemplate.bcc_emails.push("");
        };

        // Remove BCC email
        $scope.removeBccEmail = (index) => {
            $scope.currentTemplate.bcc_emails.splice(index, 1);
        };

        // Save template config
        $scope.saveTemplateConfig = () => {
            if (!$scope.currentTemplate) return;

            $scope.saving = true;
            $http
                .put(
                    `${BASE_API}/email-templates/${$scope.currentTemplate.id}`,
                    {
                        subject: $scope.currentTemplate.subject,
                        cc_emails: $scope.currentTemplate.cc_emails,
                        bcc_emails: $scope.currentTemplate.bcc_emails,
                    }
                )
                .then((res) => {
                    $toastr.show(
                        res.data.message || "Lưu cấu hình mẫu email thành công",
                        "success"
                    );
                })
                .catch((err) => {
                    $toastr.show(err.data?.message || "Lưu thất bại", "error");
                })
                .finally(() => ($scope.saving = false));
        };

        // Save template content
        $scope.saveTemplateContent = () => {
            if (!$scope.editTemplate) return;

            $scope.saving = true;
            $http
                .put(`${BASE_API}/email-templates/${$scope.editTemplate.id}`, {
                    content: $scope.editTemplate.content,
                })
                .then((res) => {
                    $toastr.show(
                        res.data.message ||
                            "Cập nhật nội dung mẫu email thành công",
                        "success"
                    );
                })
                .catch((err) => {
                    $toastr.show(
                        err.data?.message || "Cập nhật thất bại",
                        "error"
                    );
                })
                .finally(() => ($scope.saving = false));
        };

        // Send test email
        $scope.sendTestEmail = () => {
            if (!$scope.testEmail.recipient) {
                $toastr.show("Vui lòng nhập email nhận", "error");
                return;
            }

            $scope.sending = true;

            if ($scope.testEmail.type === "content") {
                // Send plain content
                $http
                    .post(`${BASE_API}/website-settings/test-email`, {
                        email: $scope.testEmail.recipient,
                        content: $scope.testEmail.content,
                    })
                    .then((res) => {
                        $toastr.show(
                            res.data.message ||
                                "Gửi email thử nghiệm thành công",
                            "success"
                        );
                    })
                    .catch((err) => {
                        $toastr.show(
                            err.data?.message || "Gửi thất bại",
                            "error"
                        );
                    })
                    .finally(() => ($scope.sending = false));
            } else {
                // Send using template
                if (!$scope.testEmail.templateId) {
                    $toastr.show("Vui lòng chọn mẫu email", "error");
                    $scope.sending = false;
                    return;
                }

                $http
                    .post(
                        `${BASE_API}/email-templates/${$scope.testEmail.templateId}/test`,
                        {
                            email: $scope.testEmail.recipient,
                        }
                    )
                    .then((res) => {
                        $toastr.show(
                            res.data.message ||
                                "Gửi email thử theo mẫu thành công",
                            "success"
                        );
                    })
                    .catch((err) => {
                        $toastr.show(
                            err.data?.message || "Gửi thất bại",
                            "error"
                        );
                    })
                    .finally(() => ($scope.sending = false));
            }
        };

        // Email validation helper
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // ======== Analytics Functions ========
        $scope.analytics = {
            services: [],
        };
        $scope.analyticsTypes = [];

        // Load analytics configuration
        $scope.loadAnalytics = () => {
            $http
                .get(`${BASE_API}/website-settings/analytics`)
                .then((res) => {
                    if (res.data.status && res.data.data) {
                        $scope.analytics.services =
                            res.data.data.services || [];
                        $scope.analyticsTypes = res.data.data.types || [];
                    }
                })
                .catch(() => {});
        };

        // Save analytics configuration
        $scope.saveAnalytics = () => {
            $scope.saving = true;
            $http
                .put(`${BASE_API}/website-settings/analytics`, {
                    services: $scope.analytics.services,
                })
                .then((res) => {
                    if (res.data.data?.services) {
                        $scope.analytics.services = res.data.data.services;
                    }
                    $toastr.show(
                        res.data.message || "Lưu cấu hình analytics thành công",
                        "success"
                    );
                    $rootScope.$broadcast("notification:refresh");
                })
                .catch((err) => {
                    $toastr.show(err.data?.message || "Lưu thất bại", "error");
                })
                .finally(() => ($scope.saving = false));
        };

        // Add new analytics service
        $scope.addAnalyticsService = () => {
            $scope.analytics.services.push({
                type: "google_analytics",
                code: "",
                is_active: "1",
                position: "head",
            });
        };

        // Remove analytics service
        $scope.removeAnalyticsService = (index) => {
            $scope.analytics.services.splice(index, 1);
        };

        // Get service label by type
        $scope.getServiceLabel = (type) => {
            const found = $scope.analyticsTypes.find((t) => t.value === type);
            return found ? found.label : type;
        };

        // Get service icon by type
        $scope.getServiceIcon = (type) => {
            const found = $scope.analyticsTypes.find((t) => t.value === type);
            return found ? found.icon : "fas fa-code";
        };

        // Get service placeholder by type
        $scope.getServicePlaceholder = (type) => {
            const found = $scope.analyticsTypes.find((t) => t.value === type);
            return found ? found.placeholder : "";
        };

        // Handle service type change
        $scope.onServiceTypeChange = (service) => {
            // Reset code when changing type
            service.code = "";
            if (service.type === "custom") {
                service.position = "head";
            }
        };

        // ======== Theme Colors Functions ========
        $scope.themeColors = {};
        $scope.themeColorsDefaults = {};
        $scope.themeColorsMetadata = {};

        // Load theme colors configuration
        $scope.loadThemeColors = () => {
            $http
                .get(`${BASE_API}/website-settings/theme-colors`)
                .then((res) => {
                    if (res.data.status && res.data.data) {
                        $scope.themeColors = res.data.data.colors || {};
                        $scope.themeColorsDefaults =
                            res.data.data.defaults || {};
                        $scope.themeColorsMetadata =
                            res.data.data.metadata || {};
                    }
                })
                .catch(() => {});
        };

        // Save theme colors
        $scope.saveThemeColors = () => {
            $scope.saving = true;
            $http
                .put(
                    `${BASE_API}/website-settings/theme-colors`,
                    $scope.themeColors
                )
                .then((res) => {
                    if (res.data.data?.colors) {
                        $scope.themeColors = res.data.data.colors;
                    }
                    $toastr.show(
                        res.data.message || "Lưu cấu hình màu thành công",
                        "success"
                    );
                    $rootScope.$broadcast("notification:refresh");
                })
                .catch((err) => {
                    $toastr.show(err.data?.message || "Lưu thất bại", "error");
                })
                .finally(() => ($scope.saving = false));
        };

        // Reset single color to default
        $scope.resetSingleColor = (key) => {
            if ($scope.themeColorsDefaults[key]) {
                $scope.themeColors[key] = $scope.themeColorsDefaults[key];
            }
        };

        // Reset all colors to default
        $scope.resetAllColors = () => {
            angular.forEach($scope.themeColorsDefaults, (value, key) => {
                $scope.themeColors[key] = value;
            });
        };

        // Init
        $scope.loadSettings();
        $scope.loadTemplates();
        $scope.loadAnalytics();
        $scope.loadThemeColors();
    },
]);
