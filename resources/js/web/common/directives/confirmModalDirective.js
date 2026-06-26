/**
 * Confirm Modal Directive
 * Modal xác nhận đa năng thay thế window.confirm()
 */

export default function (webApp) {
    webApp.directive("confirmModal", [
        "$timeout",
        "$rootScope",
        function ($timeout, $rootScope) {
            return {
                restrict: "E",
                replace: true,
                template: `
                    <div class="confirm-modal" ng-class="{ active: isOpen }">
                        <div class="confirm-modal-backdrop" ng-click="dismiss()"></div>
                        <div class="confirm-modal-container">
                            <div class="confirm-modal-content" ng-class="'confirm-' + type">
                                <!-- Modal Header -->
                                <div class="confirm-modal-header">
                                    <div class="confirm-modal-icon">
                                        <i ng-class="getIconClass()"></i>
                                    </div>
                                    <h2 class="confirm-modal-title">{{ title }}</h2>
                                    <button class="confirm-modal-close" ng-click="dismiss()" aria-label="Đóng">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <!-- Modal Body -->
                                <div class="confirm-modal-body">
                                    <p class="confirm-modal-message">{{ message }}</p>
                                </div>

                                <!-- Modal Footer -->
                                <div class="confirm-modal-footer">
                                    <button class="confirm-modal-btn confirm-modal-btn-cancel"
                                        ng-click="dismiss()"
                                        ng-disabled="loading">
                                        {{ cancelText }}
                                    </button>
                                    <button class="confirm-modal-btn"
                                        ng-class="'confirm-modal-btn-' + type"
                                        ng-click="confirm()"
                                        ng-disabled="loading">
                                        <i class="fas fa-spinner fa-spin color-primary" ng-if="loading"></i>
                                        {{ confirmText }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                link: function (scope, element, attrs) {
                    // ===== State =====
                    scope.isOpen = false;
                    scope.loading = false;
                    scope.title = "";
                    scope.message = "";
                    scope.confirmText = "Xác nhận";
                    scope.cancelText = "Hủy";
                    scope.type = "info";
                    let resolveCallback = null;

                    /**
                     * Get icon class theo type
                     */
                    scope.getIconClass = function () {
                        const iconMap = {
                            danger: "fas fa-exclamation-triangle",
                            warning: "fas fa-exclamation-circle",
                            info: "fas fa-info-circle",
                            success: "fas fa-check-circle",
                        };
                        return iconMap[scope.type] || iconMap.info;
                    };

                    /**
                     * Open modal
                     */
                    scope.open = function (options) {
                        return new Promise(function (resolve) {
                            scope.title = options.title || "Xác nhận";
                            scope.message = options.message || "";
                            scope.confirmText =
                                options.confirmText || "Xác nhận";
                            scope.cancelText = options.cancelText || "Hủy";
                            scope.type = options.type || "info";
                            scope.loading = false;
                            resolveCallback = resolve;

                            scope.isOpen = true;

                            // Focus vào button cancel khi mở modal
                            $timeout(function () {
                                const cancelBtn = element[0].querySelector(
                                    ".confirm-modal-btn-cancel"
                                );
                                if (cancelBtn) {
                                    cancelBtn.focus();
                                }
                            }, 100);

                            // Thêm event listener cho keyboard
                            attachKeyboardListener();
                        });
                    };

                    /**
                     * Confirm action
                     */
                    scope.confirm = function () {
                        scope.loading = true;

                        // Delay 300ms để user thấy loading state
                        $timeout(function () {
                            scope.isOpen = false;
                            scope.loading = false;
                            detachKeyboardListener();

                            if (resolveCallback) {
                                resolveCallback(true);
                                resolveCallback = null;
                            }
                        }, 300);
                    };

                    /**
                     * Dismiss modal
                     */
                    scope.dismiss = function () {
                        scope.isOpen = false;
                        scope.loading = false;
                        detachKeyboardListener();

                        if (resolveCallback) {
                            resolveCallback(false);
                            resolveCallback = null;
                        }
                    };

                    /**
                     * Keyboard event listener
                     */
                    let keyboardListener = null;

                    function attachKeyboardListener() {
                        keyboardListener = function (e) {
                            if (e.key === "Escape") {
                                scope.$apply(function () {
                                    scope.dismiss();
                                });
                            } else if (e.key === "Enter") {
                                scope.$apply(function () {
                                    scope.confirm();
                                });
                            }
                        };
                        document.addEventListener("keydown", keyboardListener);
                    }

                    function detachKeyboardListener() {
                        if (keyboardListener) {
                            document.removeEventListener(
                                "keydown",
                                keyboardListener
                            );
                            keyboardListener = null;
                        }
                    }

                    /**
                     * Cleanup khi directive destroy
                     */
                    scope.$on("$destroy", function () {
                        detachKeyboardListener();
                    });

                    // ===== Register directive scope vào $rootScope =====
                    $rootScope.confirmModalScope = scope;
                },
            };
        },
    ]);

    /**
     * Confirm Service - wrapper để sử dụng directive dễ hơn
     */
    webApp.factory("confirmService", [
        "$rootScope",
        function ($rootScope) {
            return {
                /**
                 * Show confirm modal
                 * @param {Object} options - { title, message, confirmText, cancelText, type }
                 * @returns {Promise} - resolve(true) nếu confirm, resolve(false) nếu cancel
                 */
                show: function (options) {
                    if ($rootScope.confirmModalScope) {
                        return $rootScope.confirmModalScope.open(options);
                    } else {
                        console.error(
                            "confirmModal directive not found. Make sure <confirm-modal></confirm-modal> is in your HTML."
                        );
                        // Fallback to window.confirm
                        return Promise.resolve(
                            window.confirm(options.message || "Xác nhận?")
                        );
                    }
                },
            };
        },
    ]);
}
