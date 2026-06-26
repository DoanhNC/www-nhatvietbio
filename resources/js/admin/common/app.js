import angular from "angular";
import toastr from "toastr";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic"; // CKEditor 5 prebuild
import { registerMediaPickerDirective } from "./directives/mediaPickerDirective";
import { registerMceEditorDirective } from "./directives/mceEditorDirective";
import { registerChosenDirective } from "./directives/chosenDirective";

// TinyMCE is loaded via CDN in layout
export const adminCore = angular
    .module("adminCore", [])
    .constant("BASE_API", "/admin/v1/rest")
    .factory("$toastr", [
        function () {
            return {
                show(msg, type = "info") {
                    (toastr[type] || toastr.info)(msg);
                },
            };
        },
    ])
    /**
     * $confirm Factory - Global Confirmation Modal Service
     *
     * Usage: Inject '$confirm' into any controller, then call $confirm.show(options)
     *
     * Options:
     * - title: string - Modal title (default: "Xác nhận")
     * - message: string - Confirmation message (default: "Bạn có chắc chắn?")
     * - icon: string - FontAwesome icon class without 'fa-' prefix (default: "fa-question-circle")
     * - confirmText: string - Text for confirm button (default: "Xác nhận")
     * - confirmIcon: string - Icon for confirm button (default: "fa-check")
     * - danger: boolean - If true: red header + btn-danger, false: normal header + btn-primary (default: true)
     * - onConfirm: function - Callback when user confirms
     *
     * Examples:
     *
     * // Delete action (danger mode - red button/header)
     * $confirm.show({
     *     title: "Xóa bài viết",
     *     message: "Bạn có chắc muốn xóa bài viết này?",
     *     icon: "fa-trash",
     *     confirmText: "Xóa",
     *     confirmIcon: "fa-trash",
     *     danger: true,
     *     onConfirm: () => { // delete logic }
     * });
     *
     * // Non-delete confirmation (info mode - blue button)
     * $confirm.show({
     *     title: "Xác nhận",
     *     message: "Bạn có chắc muốn thực hiện hành động này?",
     *     icon: "fa-check-circle",
     *     confirmText: "Đồng ý",
     *     confirmIcon: "fa-check",
     *     danger: false,
     *     onConfirm: () => { // action logic }
     * });
     */
    .factory("$confirm", [
        "$rootScope",
        function ($rootScope) {
            // State stored in rootScope for access by modal in admin.blade.php
            $rootScope.confirmModal = {
                title: "",
                message: "",
                icon: "fa-question-circle",
                confirmText: "Xác nhận",
                confirmIcon: "fa-check",
                danger: false,
                onConfirm: null,
            };

            return {
                show(options) {
                    $rootScope.confirmModal = {
                        title: options.title || "Xác nhận",
                        message: options.message || "Bạn có chắc chắn?",
                        icon: options.icon || "fa-question-circle",
                        confirmText: options.confirmText || "Xác nhận",
                        confirmIcon: options.confirmIcon || "fa-check",
                        danger: options.danger !== false,
                        onConfirm: () => {
                            $("#globalConfirmModal").modal("hide");
                            if (options.onConfirm) {
                                $rootScope.$applyAsync(() => {
                                    options.onConfirm();
                                });
                            }
                        },
                    };
                    $("#globalConfirmModal").modal("show");
                },
            };
        },
    ])
    .run([
        "$window",
        "$http",
        "BASE_API",
        function ($window, $http, BASE_API) {
            // Gắn sự kiện Logout vào nút trong layout (nếu có)
            setTimeout(() => {
                const btn = document.getElementById("btnLogout");
                if (btn)
                    btn.onclick = () => {
                        $http.post(`/admin/logout`).finally(() => {
                            localStorage.clear();
                            $window.location.href = "/admin/login";
                        });
                    };

                // Change Password handler
                const btnChangePassword =
                    document.getElementById("btnChangePassword");
                if (btnChangePassword) {
                    btnChangePassword.onclick = () => {
                        const currentPassword =
                            document.getElementById("currentPassword").value;
                        const newPassword =
                            document.getElementById("newPassword").value;
                        const confirmPassword =
                            document.getElementById("confirmPassword").value;
                        const errorDiv =
                            document.getElementById("passwordError");
                        const successDiv =
                            document.getElementById("passwordSuccess");

                        // Reset alerts
                        errorDiv.classList.add("d-none");
                        successDiv.classList.add("d-none");

                        // Validate
                        if (
                            !currentPassword ||
                            !newPassword ||
                            !confirmPassword
                        ) {
                            errorDiv.textContent =
                                "Vui lòng điền đầy đủ thông tin";
                            errorDiv.classList.remove("d-none");
                            return;
                        }
                        if (newPassword.length < 6) {
                            errorDiv.textContent =
                                "Mật khẩu mới phải có ít nhất 6 ký tự";
                            errorDiv.classList.remove("d-none");
                            return;
                        }
                        if (newPassword !== confirmPassword) {
                            errorDiv.textContent =
                                "Xác nhận mật khẩu không khớp";
                            errorDiv.classList.remove("d-none");
                            return;
                        }

                        // Call API
                        btnChangePassword.disabled = true;
                        btnChangePassword.innerHTML =
                            '<i class="fas fa-spinner fa-spin mr-1"></i>Đang lưu...';

                        $http
                            .post("/admin/change-password", {
                                current_password: currentPassword,
                                new_password: newPassword,
                                new_password_confirmation: confirmPassword,
                            })
                            .then((res) => {
                                successDiv.textContent =
                                    res.data?.message ||
                                    "Đổi mật khẩu thành công!";
                                successDiv.classList.remove("d-none");
                                // Clear form
                                document.getElementById(
                                    "currentPassword"
                                ).value = "";
                                document.getElementById("newPassword").value =
                                    "";
                                document.getElementById(
                                    "confirmPassword"
                                ).value = "";
                            })
                            .catch((err) => {
                                errorDiv.textContent =
                                    err.data?.message ||
                                    "Đổi mật khẩu thất bại";
                                errorDiv.classList.remove("d-none");
                            })
                            .finally(() => {
                                btnChangePassword.disabled = false;
                                btnChangePassword.innerHTML =
                                    '<i class="fas fa-save mr-1"></i>Lưu mật khẩu';
                            });
                    };
                }
            }, 0);
        },
    ]);

window.adminApp = angular.module("adminApp", [adminCore.name]);

// Register directives after adminApp is defined
registerMediaPickerDirective(window.adminApp);
registerMceEditorDirective(window.adminApp);
registerChosenDirective(window.adminApp);
/**
 * Directive ck-editor:
 * - Khởi tạo CKEditor 5 trên <textarea ck-editor ng-model="...">
 * - Đồng bộ 2 chiều với ngModel
 * - Tự hủy khi scope bị destroy
 */
adminApp.directive("ckEditor", [
    function () {
        return {
            require: "ngModel",
            link: function (scope, element, attrs, ngModel) {
                let editorInstance = null;

                const csrf =
                    document.querySelector('meta[name="csrf-token"]')
                        ?.content || "";
                const uploadUrl = attrs.uploadUrl || "/api/v1/uploads/ckeditor";

                // --- Custom Upload Adapter (không cần plugin ngoài) ---
                class CKUploadAdapter {
                    constructor(loader, uploadUrl, headers) {
                        this.loader = loader;
                        this.uploadUrl = uploadUrl;
                        this.headers = headers || {};
                    }
                    // CKEditor sẽ gọi upload() -> trả về { default: "URL ẢNH" }
                    async upload() {
                        const file = await this.loader.file;
                        const form = new FormData();
                        // CKEditor 5 simpleUpload mặc định field name là "upload"
                        form.append("upload", file, file.name);

                        const res = await fetch(this.uploadUrl, {
                            method: "POST",
                            headers: this.headers, // KHÔNG set 'Content-Type' khi dùng FormData
                            body: form,
                            credentials: "omit", // đổi thành 'include' nếu cần cookie
                        });

                        if (!res.ok)
                            throw new Error(`Upload failed: ${res.status}`);
                        const json = await res.json();

                        // Hỗ trợ cả {url: "..."} hoặc {urls:{default:"..."}}
                        const url = json.url || json?.urls?.default;
                        if (!url)
                            throw new Error(
                                'Invalid response. Expected { url: "..." }.'
                            );

                        return { default: url };
                    }
                    // Bắt buộc phải có (dù không dùng)
                    abort() {}
                }

                const headers = {
                    ...(csrf ? { "X-CSRF-TOKEN": csrf } : {}),
                };

                const EditorCtor = ClassicEditor; // hoặc CKEDITOR.ClassicEditor nếu dùng super-build CDN
                const config = {
                    placeholder: attrs.placeholder || "Nhập mô tả…",
                    toolbar: [
                        "heading",
                        "|",
                        "bold",
                        "italic",
                        // "underline",
                        // "strikethrough",
                        "|",
                        // "alignment",
                        "outdent",
                        "indent",
                        "|",
                        "bulletedList",
                        "numberedList",
                        "|",
                        "link",
                        "blockQuote",
                        "insertTable",
                        "|",
                        "imageUpload",
                        "mediaEmbed",
                        "|",
                        "undo",
                        "redo",
                    ],
                    image: {
                        toolbar: [
                            "imageTextAlternative",
                            "toggleImageCaption",
                            "|",
                            "imageStyle:inline",
                            "imageStyle:block",
                            "imageStyle:side",
                        ],
                    },
                    table: {
                        contentToolbar: [
                            "tableColumn",
                            "tableRow",
                            "mergeTableCells",
                        ],
                    },
                    pasteFromOffice: { keepZeroWidthSpace: true },
                    // KHÔNG cần simpleUpload ở đây vì ta tự gắn adapter bên dưới
                };

                EditorCtor.create(element[0], config)
                    .then((editor) => {
                        editorInstance = editor;

                        // >>> GẮN UPLOAD ADAPTER VÀO FileRepository
                        editor.plugins.get(
                            "FileRepository"
                        ).createUploadAdapter = (loader) => {
                            return new CKUploadAdapter(
                                loader,
                                uploadUrl,
                                headers
                            );
                        };

                        // model -> editor
                        ngModel.$render = () =>
                            editor.setData(ngModel.$viewValue || "");

                        // ⚠️ QUAN TRỌNG: gọi ngay để load giá trị ban đầu (Edit)
                        ngModel.$render();

                        // editor -> model
                        editor.model.document.on("change:data", () => {
                            scope.$evalAsync(() =>
                                ngModel.$setViewValue(editor.getData())
                            );
                        });
                    })
                    .catch((err) => console.error("CKEditor init error:", err));

                scope.$on("$destroy", () => {
                    if (editorInstance) editorInstance.destroy();
                });
            },
        };
    },
]);

/**
 * Directive: vn-datetime
 * - vn-format:
 *    'date'        -> View: d-m-Y        | Model: YYYY-MM-DD 00:00:00
 *    'datetime'    -> View: d-m-Y H:i    | Model: YYYY-MM-DD HH:mm:00
 *    'datetimesec' -> View: d-m-Y H:i:s  | Model: YYYY-MM-DD HH:mm:ss
 * - picker="true": gắn flatpickr (VN), appendTo body, z-index cao, auto open khi focus/click.
 * 
 *                 <input type="text"
                    class="form-control"
                    ng-model="filterList.fromDate"
                    vn-datetime
                    vn-format="datetime"
                    picker="true">
 */
adminApp.directive("vnDatetime", [
    "$timeout",
    function ($timeout) {
        return {
            require: "ngModel",
            scope: {
                vnFormat: "@?", // 'date' | 'datetime' | 'datetimesec'
                picker: "@?", // 'true' => bật flatpickr
                minuteStep: "@?", // tuỳ chọn
                secondStep: "@?", // tuỳ chọn
            },
            link: function (scope, el, attrs, ngModel) {
                var mode = (scope.vnFormat || "datetime").toLowerCase();
                var isDateOnly = mode === "date";
                var hasSeconds = mode === "datetimesec";

                // Regex view (VN)
                var reDate = /^\d{2}-\d{2}-\d{4}$/;
                var reDateHM = /^\d{2}-\d{2}-\d{4}\s\d{2}:\d{2}$/;
                var reDateHMS = /^\d{2}-\d{2}-\d{4}\s\d{2}:\d{2}:\d{2}$/;

                function toView(modelVal) {
                    if (!modelVal) return "";
                    var s = String(modelVal).trim();
                    var m = s.match(
                        /^(\d{4})-(\d{2})-(\d{2})(?:\s+(\d{2}):(\d{2})(?::(\d{2}))?)?$/
                    );
                    if (!m) return s;
                    var Y = m[1],
                        M = m[2],
                        D = m[3];
                    var H = m[4] || "00",
                        I = m[5] || "00",
                        S = m[6] || "00";
                    var dateVN = D + "-" + M + "-" + Y;
                    if (isDateOnly) return dateVN;
                    if (hasSeconds) return dateVN + " " + H + ":" + I + ":" + S;
                    return dateVN + " " + H + ":" + I;
                }

                function toModel(viewVal) {
                    if (!viewVal) return null;
                    var s = String(viewVal).trim();

                    if (isDateOnly) {
                        if (!reDate.test(s)) return undefined;
                        var dmY = s.split("-"); // d-m-Y
                        var d = dmY[0],
                            m = dmY[1],
                            y = dmY[2];
                        return y + "-" + m + "-" + d + " 00:00:00";
                    }

                    if (hasSeconds) {
                        if (!reDateHMS.test(s)) return undefined;
                        var dmY = s.slice(0, 10).split("-");
                        var t = s.slice(11).split(":");
                        var d = dmY[0],
                            m = dmY[1],
                            y = dmY[2];
                        var H = t[0],
                            I = t[1],
                            S = t[2];
                        return (
                            y + "-" + m + "-" + d + " " + H + ":" + I + ":" + S
                        );
                    } else {
                        if (!reDateHM.test(s)) return undefined;
                        var dmY = s.slice(0, 10).split("-");
                        var t = s.slice(11).split(":");
                        var d = dmY[0],
                            m = dmY[1],
                            y = dmY[2];
                        var H = t[0],
                            I = t[1];
                        return (
                            y + "-" + m + "-" + d + " " + H + ":" + I + ":00"
                        );
                    }
                }

                ngModel.$formatters.push(toView);
                ngModel.$parsers.push(function (v) {
                    var out = toModel(v);
                    var ok =
                        !v ||
                        (isDateOnly
                            ? reDate.test(v)
                            : hasSeconds
                            ? reDateHMS.test(v)
                            : reDateHM.test(v));
                    ngModel.$setValidity("vndatetime", ok);
                    return out;
                });

                if (!attrs.placeholder) {
                    el.attr(
                        "placeholder",
                        isDateOnly
                            ? "dd-mm-yyyy"
                            : hasSeconds
                            ? "dd-mm-yyyy HH:mm:ss"
                            : "dd-mm-yyyy HH:mm"
                    );
                }
                if (!attrs.inputmode) el.attr("inputmode", "numeric");

                // Flatpickr
                var wantPicker =
                    String(scope.picker || "").toLowerCase() === "true";
                var hasFP = typeof window.flatpickr === "function";
                var fp = null;

                function ensurePicker() {
                    if (!(wantPicker && hasFP)) return;

                    var fpFormat = isDateOnly
                        ? "d-m-Y"
                        : hasSeconds
                        ? "d-m-Y H:i:S"
                        : "d-m-Y H:i";
                    var opts = {
                        dateFormat: fpFormat,
                        time_24hr: true,
                        allowInput: true,
                        locale:
                            window.flatpickr &&
                            window.flatpickr.l10ns &&
                            window.flatpickr.l10ns.vn
                                ? window.flatpickr.l10ns.vn
                                : "vn",
                        enableTime: !isDateOnly,
                        enableSeconds: !!hasSeconds,
                        // Quan trọng: ép render ra body để tránh bị ẩn/che
                        appendTo: document.body,
                        // Tránh native picker trên mobile để luôn thấy calendar của flatpickr
                        disableMobile: true,
                        // Đảm bảo định vị đúng theo input
                        positionElement: el[0],
                        // Mở khi focus/nhấn
                        clickOpens: true,
                    };
                    if (scope.minuteStep)
                        opts.minuteIncrement = +scope.minuteStep;
                    if (scope.secondStep)
                        opts.secondIncrement = +scope.secondStep;

                    fp = window.flatpickr(el[0], opts);

                    // Đồng bộ model -> picker khi model đổi bên ngoài
                    scope.$watch(
                        function () {
                            return ngModel.$modelValue;
                        },
                        function (val) {
                            if (!fp) return;
                            var viewVal = toView(val);
                            fp.setDate(viewVal || "", false);
                        }
                    );

                    // Mở lịch khi focus input để user thấy ngay
                    el.on("focus", function () {
                        if (fp) fp.open();
                    });
                    // Nếu muốn mở cả khi click icon thì ở controller có hàm openPicker(...)
                }

                // Khởi tạo flatpickr sau một tick để chắc chắn input đã có trong DOM
                $timeout(ensurePicker, 0);

                // --- Modal support (Bootstrap) ---
                var modalParent = el.closest(".modal");

                if (modalParent.length) {
                    // Nếu modal đang mở sẵn (trường hợp load trong modal show)
                    if (modalParent.hasClass("show")) {
                        $timeout(ensurePicker, 0);
                    }

                    // Mỗi lần modal mở lại -> init flatpickr nếu chưa có
                    $(modalParent).on("shown.bs.modal", function () {
                        if (!fp) {
                            $timeout(ensurePicker, 0);
                        }
                    });

                    // Khi modal ẩn -> destroy flatpickr
                    $(modalParent).on("hidden.bs.modal", function () {
                        if (fp) {
                            fp.destroy();
                            fp = null;
                        }
                    });
                } else {
                    // Không nằm trong modal -> init bình thường
                    $timeout(ensurePicker, 0);
                }
            },
        };
    },
]);

adminApp.directive("asNumber", function () {
    return {
        require: "ngModel",
        link: function (scope, el, attrs, ngModel) {
            ngModel.$parsers.push(function (v) {
                if (v === "" || v === null || v === undefined) return null;
                var n = Number(v);
                return isNaN(n) ? undefined : n;
            });
            ngModel.$formatters.push(function (v) {
                if (v === "" || v === null || v === undefined) return null;
                return typeof v === "number" ? v : Number(v);
            });
        },
    };
});

/** fileModel: bind input[type=file] multiple to $scope.files */
adminApp.directive("fileModel", [
    function () {
        return {
            scope: { fileModel: "=" },
            link: function (scope, element) {
                element.bind("change", function () {
                    scope.$apply(function () {
                        scope.fileModel = element[0].files;
                    });
                });
            },
        };
    },
]);

adminApp.factory("$storage", function ($window) {
    function parse(val, defVal) {
        if (val == null) return defVal;
        try {
            return JSON.parse(val);
        } catch (e) {
            return val;
        }
    }
    return {
        get: function (key, defVal) {
            try {
                return parse($window.localStorage.getItem(key), defVal);
            } catch (e) {
                return defVal;
            }
        },
        set: function (key, val) {
            try {
                var out = typeof val === "string" ? val : JSON.stringify(val);
                $window.localStorage.setItem(key, out);
            } catch (e) {
                /* ignore quota errors */
            }
        },
        remove: function (key) {
            try {
                $window.localStorage.removeItem(key);
            } catch (e) {}
        },
    };
});

window.CONFIG = {
    siteUrl: window.location.origin || "http://localhost:8080",
};

document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("btnToggleFilter");
    const filter = document.querySelector(".filter-content");
    const text = btn ? btn.querySelector(".btn-text") : null;

    if (btn && filter && text) {
        // Đảm bảo lúc load trang luôn ẩn filter và text nút là "Hiển thị tìm kiếm"
        filter.classList.add("d-none");
        text.textContent = "Hiển thị tìm kiếm";

        btn.addEventListener("click", function () {
            filter.classList.toggle("d-none");

            if (filter.classList.contains("d-none")) {
                text.textContent = "Hiển thị tìm kiếm";
            } else {
                text.textContent = "Ẩn tìm kiếm";
            }
        });
    }
});

/**
 * Notification Controller - Xử lý dropdown thông báo trong admin layout
 */
adminApp.controller("notificationCtrl", [
    "$scope",
    "$http",
    "$rootScope",
    "BASE_API",
    function ($scope, $http, $rootScope, BASE_API) {
        $scope.notifications = [];
        $scope.unreadCount = 0;
        $scope.loading = false;
        $scope.loaded = false;
        $scope.showingAll = false;
        $scope.activeTab = "all"; // 'all' or 'unread'

        // Load notifications on init
        loadUnreadCount();

        // Listen for refresh event from other controllers
        $rootScope.$on("notification:refresh", function () {
            loadUnreadCount();
            // Reset loaded flag to force reload next time dropdown is opened
            $scope.loaded = false;
        });

        function loadUnreadCount() {
            $http
                .get(BASE_API + "/notifications/unread-count")
                .then(function (res) {
                    $scope.unreadCount = res.data?.data?.count || 0;
                });
        }

        $scope.setTab = function (tab, $event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.activeTab = tab;
        };

        $scope.getFilteredNotifications = function () {
            if ($scope.activeTab === "unread") {
                return $scope.notifications.filter(function (n) {
                    return !n.is_read;
                });
            }
            return $scope.notifications;
        };

        $scope.loadNotifications = function () {
            if ($scope.loaded) return;
            $scope.loading = true;
            $http
                .get(BASE_API + "/notifications") // Default: 3 days
                .then(function (res) {
                    $scope.notifications = res.data?.data?.notifications || [];
                    $scope.unreadCount = res.data?.data?.unread_count || 0;
                    $scope.showingAll = res.data?.data?.showing_days === null;
                    $scope.loaded = true;
                })
                .finally(function () {
                    $scope.loading = false;
                });
        };

        $scope.loadAllNotifications = function ($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.loading = true;
            $http
                .get(BASE_API + "/notifications?all=1")
                .then(function (res) {
                    $scope.notifications = res.data?.data?.notifications || [];
                    $scope.unreadCount = res.data?.data?.unread_count || 0;
                    $scope.showingAll = true;
                })
                .finally(function () {
                    $scope.loading = false;
                });
        };

        $scope.markAsRead = function (notification, $event) {
            if (notification.is_read) return;
            $event.preventDefault();
            $http
                .post(BASE_API + "/notifications/" + notification.id + "/read")
                .then(function () {
                    notification.is_read = true;
                    $scope.unreadCount = Math.max(0, $scope.unreadCount - 1);
                });
        };

        $scope.markAllAsRead = function ($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $http.post(BASE_API + "/notifications/read-all").then(function () {
                $scope.notifications.forEach(function (n) {
                    n.is_read = true;
                });
                $scope.unreadCount = 0;
            });
        };

        $scope.getIcon = function (type) {
            var icons = {
                category: "fas fa-folder",
                post: "fas fa-newspaper",
                media: "fas fa-photo-video",
                settings: "fas fa-cog",
            };
            return icons[type] || "fas fa-bell";
        };

        $scope.getIconClass = function (action) {
            var classes = {
                created: "bg-success",
                updated: "bg-info",
                deleted: "bg-danger",
            };
            return classes[action] || "bg-primary";
        };

        $scope.formatDate = function (dateString) {
            if (!dateString) return "";
            var date = new Date(dateString);
            var day = String(date.getDate()).padStart(2, "0");
            var month = String(date.getMonth() + 1).padStart(2, "0");
            var year = date.getFullYear();
            var hour = String(date.getHours()).padStart(2, "0");
            var min = String(date.getMinutes()).padStart(2, "0");
            return day + "/" + month + "/" + year + " " + hour + ":" + min;
        };

        $scope.formatTime = function (dateString) {
            if (!dateString) return "";
            var date = new Date(dateString);
            var now = new Date();
            var diff = (now - date) / 1000; // seconds

            if (diff < 60) return "Vừa xong";
            if (diff < 3600) return Math.floor(diff / 60) + " phút trước";
            if (diff < 86400) return Math.floor(diff / 3600) + " giờ trước";
            if (diff < 604800) return Math.floor(diff / 86400) + " ngày trước";

            return ""; // Don't show relative time for old entries
        };
    },
]);

/**
 * Auto-refresh notification count every 30 seconds
 * This runs globally to ensure notifications stay updated
 */
adminApp.run([
    "$interval",
    "$http",
    "$rootScope",
    "BASE_API",
    function ($interval, $http, $rootScope, BASE_API) {
        // Refresh notification count every 30 seconds
        $interval(function () {
            $rootScope.$broadcast("notification:refresh");
        }, 30000);
    },
]);
