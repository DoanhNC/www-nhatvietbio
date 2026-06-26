// ---- Directive: mediaPicker (giữ cơ chế modal/inline, UI mới)
adminApp.directive(
    "mediaPicker",
    function ($templateRequest, $compile, $timeout) {
        function link(scope, elm) {
            scope._modalId = "mediaPicker_" + scope.$id;
            scope.contentTplPath =
                CONFIG.siteUrl + "/themes/components/mediaPicker/_content.html";

            // ----- State
            scope.state = {
                multiple: true,
                search: "",
                quota: {
                    usedPct: 14.9,
                    usedText: "305.83 MB",
                    freeText: "1.70 GB",
                    totalText: "2 GB",
                },
                selected: {
                    name: "c",
                    type: "folder",
                    size: 0,
                    updatedText: "18:04 – 7/10/2025",
                },
            };
            if (!Array.isArray(scope.ngModel)) scope.ngModel = [];
            if (!Array.isArray(scope.items)) scope.items = [];

            // ----- Helpers
            scope.isFolder = function (it) {
                return it.type === "folder";
            };
            scope.filtered = function () {
                var q = (scope.state.search || "").toLowerCase();
                if (!q) return scope.items;
                return scope.items.filter(function (x) {
                    return (x.name || "").toLowerCase().indexOf(q) !== -1;
                });
            };

            scope.isSelected = function (it) {
                return scope.ngModel.some(function (x) {
                    return x.id === it.id;
                });
            };

            scope.togglePick = function (it) {
                if (!scope.state.multiple) {
                    scope.ngModel = [it];
                } else {
                    var idx = scope.ngModel.findIndex(function (x) {
                        return x.id === it.id;
                    });
                    if (idx >= 0) scope.ngModel.splice(idx, 1);
                    else scope.ngModel.push(it);
                }
                // inline: notify tức thì nếu có
                if (!scope.useModal && typeof scope.onChange === "function") {
                    scope.onChange({ files: scope.ngModel });
                }
            };

            scope.selectInspect = function (it) {
                scope.state.selected = {
                    name: it.name,
                    type: it.type,
                    size: it.type === "folder" ? 0 : null,
                    updatedText: "18:04 – 7/10/2025",
                };
            };

            // Upload / New folder actions (đưa vào Angular)
            scope.addFiles = function (fileList) {
                Array.from(fileList || []).forEach(function (f) {
                    // tạo id giả tạm
                    var id = Date.now() + Math.random();
                    scope.items.unshift({ id: id, name: f.name, type: "file" });
                });
            };
            scope.addFolder = function (name) {
                if (!name) return;
                var id = Date.now() + Math.random();
                scope.items.unshift({ id: id, name: name, type: "folder" });
            };

            // ----- Modal shell controls (mở content vào modal shell)
            scope.open = function () {
                if (!scope.useModal) return;
                $templateRequest(
                    CONFIG.siteUrl +
                        "/themes/components/mediaPicker/_modal-shell.html"
                ).then(function (html) {
                    var modalEl = angular.element(html);
                    elm.append(modalEl);
                    $compile(modalEl)(scope);
                    $("#" + scope._modalId).modal("show");

                    // khởi tạo chart sau khi modal mở
                    $timeout(initQuotaChart, 200);
                });
            };
            scope.close = function () {
                if (!scope.useModal) return;
                var $modal = $("#" + scope._modalId);
                $modal.on("hidden.bs.modal", function () {
                    $modal.remove();
                });
                $modal.modal("hide");
            };
            scope.confirm = function () {
                if (typeof scope.onConfirm === "function")
                    scope.onConfirm({ files: scope.ngModel });
                scope.close();
            };

            // ----- Context menu (center-only)
            function showMenu(x, y) {
                var menu = elm[0].querySelector("#ctxMenu");
                if (!menu) return;
                var w = menu.offsetWidth || 200,
                    h = menu.offsetHeight || 96;
                var vw = window.innerWidth,
                    vh = window.innerHeight;
                var nx = Math.min(x, vw - w - 8),
                    ny = Math.min(y, vh - h - 8);
                angular
                    .element(menu)
                    .css({ left: nx + "px", top: ny + "px", display: "block" });
            }
            function hideMenu() {
                var menu = elm[0].querySelector("#ctxMenu");
                if (menu) angular.element(menu).css("display", "none");
            }

            // bind khu vực trung tâm khi template đã render
            $timeout(function () {
                var center = elm[0].querySelector("#centerArea");
                if (center) {
                    angular.element(center).on("contextmenu", function (e) {
                        e.preventDefault();
                        scope.$applyAsync(function () {
                            showMenu(e.clientX, e.clientY);
                        });
                    });
                }
                angular.element(document).on("click", hideMenu);
                angular.element(window).on("resize", hideMenu);
                angular.element(document).on("scroll", hideMenu, true);
                angular.element(document).on("keydown", function (e) {
                    if (e.key === "Escape") hideMenu();
                });

                // các nút topbar/quota
                bindTopbarAndQuota();
                initQuotaChart();
            }, 0);

            function bindTopbarAndQuota() {
                // upload
                var uploadBtn = elm[0].querySelector("#btnUpload");
                var uploadInput = elm[0].querySelector("#uploadInput");
                if (uploadBtn && uploadInput) {
                    angular.element(uploadBtn).on("click", function () {
                        uploadInput.click();
                    });
                    angular.element(uploadInput).on("change", function (evt) {
                        var files = evt.target.files;
                        scope.$applyAsync(function () {
                            scope.addFiles(files);
                        });
                        evt.target.value = "";
                    });
                }
                // New folder (topbar hoặc context menu)
                var newBtn = elm[0].querySelector("#btnNewFolder");
                if (newBtn) {
                    angular.element(newBtn).on("click", function () {
                        $("#newFolderModal").modal("show");
                        $timeout(function () {
                            $("#folderName").trigger("focus");
                        }, 150);
                    });
                }
                // Context menu items
                var ctx = elm[0].querySelector("#ctxMenu");
                if (ctx) {
                    angular.element(ctx).on("click", function (e) {
                        var item = e.target.closest(".item");
                        if (!item) return;
                        var act = item.getAttribute("data-action");
                        hideMenu();
                        if (act === "upload" && uploadInput)
                            uploadInput.click();
                        if (act === "new-folder") {
                            $("#newFolderModal").modal("show");
                            $timeout(function () {
                                $("#folderName").trigger("focus");
                            }, 150);
                        }
                    });
                }
                // Modal create folder
                var addBtn = elm[0].querySelector("#addFolderBtn");
                if (addBtn) {
                    angular.element(addBtn).on("click", function () {
                        var name = ($("#folderName").val() || "").trim();
                        if (!name) return;
                        scope.$applyAsync(function () {
                            scope.addFolder(name);
                        });
                        $("#newFolderModal").modal("hide");
                    });
                    angular
                        .element(elm[0].querySelector("#folderName"))
                        .on("keydown", function (e) {
                            if (e.key === "Enter")
                                angular.element(addBtn).triggerHandler("click");
                        });
                    $("#newFolderModal").on("hidden.bs.modal", function () {
                        $("#folderName").val("");
                    });
                }

                // Topbar actions demo
                var btnRefresh = elm[0].querySelector("#btnTopRefresh");
                var btnHistory = elm[0].querySelector("#btnHistory");
                if (btnRefresh)
                    angular.element(btnRefresh).on("click", function () {
                        console.log("Topbar: refresh list");
                    });
                if (btnHistory)
                    angular.element(btnHistory).on("click", function () {
                        alert("Hiển thị lịch sử thao tác (demo)");
                    });

                // Quota actions demo
                var btnRQ = elm[0].querySelector("#btnRefreshQuota");
                var btnSet = elm[0].querySelector("#btnSettings");
                var btnNote = elm[0].querySelector("#btnNotes");
                if (btnRQ)
                    angular.element(btnRQ).on("click", function () {
                        console.log("Refresh quota");
                    });
                if (btnSet)
                    angular.element(btnSet).on("click", function () {
                        alert("Mở cài đặt (demo)");
                    });
                if (btnNote)
                    angular.element(btnNote).on("click", function () {
                        alert("Mở lưu ý (demo)");
                    });
            }

            function initQuotaChart() {
                var canvas = elm[0].querySelector("#quotaChart");
                if (!canvas || !Chart) return;
                var used = scope.state.quota.usedPct,
                    free = 100 - used;
                // eslint-disable-next-line no-new
                new Chart(canvas, {
                    type: "doughnut",
                    data: {
                        labels: ["Đã dùng", "Còn trống"],
                        datasets: [
                            {
                                data: [used, free],
                                backgroundColor: ["#f59e0b", "#e5e7eb"],
                                hoverBackgroundColor: ["#d97706", "#d1d5db"],
                                hoverBorderColor: "rgba(234,236,244,1)",
                            },
                        ],
                    },
                    options: {
                        maintainAspectRatio: false,
                        tooltips: {
                            backgroundColor: "#fff",
                            bodyFontColor: "#858796",
                            borderColor: "#dddfeb",
                            borderWidth: 1,
                            xPadding: 15,
                            yPadding: 15,
                            displayColors: false,
                            caretPadding: 10,
                        },
                        legend: { display: false },
                        cutoutPercentage: 72,
                    },
                });
            }

            // cleanup
            scope.$on("$destroy", function () {
                angular.element(document).off("click", hideMenu);
                angular.element(window).off("resize", hideMenu);
                angular.element(document).off("scroll", hideMenu, true);
                angular.element(document).off("keydown");
            });
        }

        return {
            restrict: "E",
            link: link,
            // shell nhỏ: khi inline → include content; khi modal → chỉ nút mở
            template:
                '<div class="media-picker">' +
                '<div ng-if="useModal">' +
                '<button class="btn btn-primary btn-sm" ng-click="open()"><i class="fa fa-images"></i> Mở bộ chọn</button>' +
                "</div>" +
                '<div ng-if="!useModal" ng-include="contentTplPath"></div>' +
                "</div>",
            scope: {
                useModal: "<?",
                items: "<?",
                ngModel: "=",
                onConfirm: "&?",
                onChange: "&?",
            },
        };
    }
);
