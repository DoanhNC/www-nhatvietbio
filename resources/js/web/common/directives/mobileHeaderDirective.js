/**
 * Mobile Header Directive
 * Hiển thị header mobile với menu quay lại và more menu
 * Tích hợp đầy đủ giao diện và chức năng
 *
 * Usage:
 * <div mobile-header title="Quản lý đơn hàng"></div>
 */

export default function (webApp) {
    webApp.directive("mobileHeader", [
        "$timeout",
        "$rootScope",
        "$window",
        function ($timeout, $rootScope, $window) {
            return {
                restrict: "A",
                scope: {
                    title: "@",
                },
                template: `
                    <div class="mobile-order-header d-flex d-md-none">
                        <!--<a href="#"
                           class="back-button"
                           aria-label="Quay lại"
                           ng-click="goBack($event)">
                            <i class="fas fa-arrow-left"></i>
                        </a>-->
                        <a href="javascript:history.back()" class="back-button" aria-label="Quay lại"> <i class="fas fa-arrow-left"></i> </a>
                        <div class="mobile-order-title">{{ title }}</div>
                        <div class="more-menu-wrapper" ng-class="{ open: menuOpen }">
                            <button class="more-button"
                                ng-click="toggleMenu()"
                                aria-haspopup="true"
                                aria-expanded="{{ menuOpen }}">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="more-menu" role="menu" ng-if="menuOpen">
                                <a href="/" class="more-menu-item" ng-click="closeMenu()">
                                    <i class="fas fa-home"></i>
                                    <span>Quay lại trang chủ</span>
                                </a>
                                <a href="/recently-viewed" class="more-menu-item" ng-click="closeMenu()">
                                    <i class="far fa-clock"></i>
                                    <span>Sản phẩm vừa xem</span>
                                </a>
                                <a href="" class="more-menu-item" ng-click="searchPopup()">
                                    <i class="fas fa-search"></i>
                                    <span>Tìm kiếm</span>
                                </a>
                            </div>
                        </div>
                    </div>
                `,
                link: function (scope, element, attrs) {
                    scope.menuOpen = false;
                    let clickOutsideListener = null;

                    // 👉 Hàm back
                    scope.goBack = function (e) {
                        if (e) e.preventDefault();

                        // Nếu history > 1 (có trang trước trong session này)
                        if ($window.history.length > 1 && document.referrer) {
                            $window.location.href = document.referrer;
                        } else {
                            // Không có trang trước → fallback về trang chủ
                            $window.location.href = "/";
                        }
                    };

                    scope.toggleMenu = function () {
                        scope.menuOpen = !scope.menuOpen;

                        if (scope.menuOpen) {
                            attachClickOutsideListener();
                        } else {
                            detachClickOutsideListener();
                        }
                    };

                    scope.closeMenu = function () {
                        scope.menuOpen = false;
                        detachClickOutsideListener();
                    };

                    scope.searchPopup = function () {
                        // nhớ check tồn tại searchState trước khi dùng
                        if ($rootScope.searchState) {
                            $rootScope.$applyAsync(function () {
                                $rootScope.searchState.isOpen = true;
                            });
                        }
                        scope.closeMenu();
                    };

                    function attachClickOutsideListener() {
                        $timeout(function () {
                            clickOutsideListener = function (e) {
                                const moreMenuWrapper =
                                    e.target.closest(".more-menu-wrapper");

                                if (!moreMenuWrapper && scope.menuOpen) {
                                    scope.$apply(function () {
                                        scope.closeMenu();
                                    });
                                }
                            };

                            document.addEventListener(
                                "click",
                                clickOutsideListener
                            );
                        }, 0);
                    }

                    function detachClickOutsideListener() {
                        if (clickOutsideListener) {
                            document.removeEventListener(
                                "click",
                                clickOutsideListener
                            );
                            clickOutsideListener = null;
                        }
                    }

                    scope.$watch("menuOpen", function (newVal) {
                        if (newVal) {
                            attachClickOutsideListener();
                        } else {
                            detachClickOutsideListener();
                        }
                    });

                    scope.$on("$destroy", function () {
                        detachClickOutsideListener();
                    });

                    document.addEventListener("keydown", function (e) {
                        if (e.keyCode === 27 && scope.menuOpen) {
                            scope.$apply(function () {
                                scope.closeMenu();
                            });
                        }
                    });
                },
            };
        },
    ]);
}
