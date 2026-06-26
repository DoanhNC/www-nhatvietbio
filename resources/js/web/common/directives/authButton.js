export default function (webApp) {
    webApp.directive("authButton", [
        "AuthService",
        "$rootScope",
        "$window",
        "$timeout",
        "$document",
        function (AuthService, $rootScope, $window, $timeout, $document) {
            return {
                restrict: "E",
                replace: true,
                template:
                    '<div class="dropdown auth-button-component">' +
                    '  <button class="btn btn-ghost" type="button" aria-haspopup="true" aria-expanded="false">' +
                    '    <i class="fas fa-user"></i>' +
                    "  </button>" +
                    '  <div class="dropdown-menu dropdown-menu-right" role="menu">' +
                    '    <a class="dropdown-item" ng-if="!isLoggedIn" ng-click="goLogin($event)">' +
                    '      <i class="fas fa-sign-in-alt mr-2"></i>Đăng nhập | Đăng ký' +
                    "    </a>" +
                    '    <div ng-if="isLoggedIn">' +
                    '      <a class="dropdown-item" href="/web/account"><i class="fas fa-user-cog mr-2"></i>{{ accountName }}</a>' +
                    '      <a class="dropdown-item" href="/web/account/orders"><i class="fas fa-list-alt mr-2"></i>Đơn hàng</a>' +
                    '      <a class="dropdown-item" href="" ng-click="logout($event)"><i class="fas fa-sign-out-alt mr-2"></i>Đăng xuất</a>' +
                    "    </div>" +
                    "  </div>" +
                    "</div>",
                link: function (scope, element) {
                    $timeout(function () {
                        // initial state
                        scope.isLoggedIn = !!window.isLoggedIn;
                        scope.accountName = window.accountLogin
                            ? window.accountLogin.full_name ||
                              window.accountLogin.email
                            : "";
                    }, 1000);

                    var btn = element[0].querySelector("button");
                    var menu = element[0].querySelector(".dropdown-menu");

                    function showMenu() {
                        menu && menu.classList.add("show");
                        btn && btn.setAttribute("aria-expanded", "true");
                    }
                    function hideMenu() {
                        menu && menu.classList.remove("show");
                        btn && btn.setAttribute("aria-expanded", "false");
                    }
                    function toggleMenu(e) {
                        e && e.preventDefault();
                        e && e.stopPropagation();
                        if (!menu) return;
                        if (menu.classList.contains("show")) hideMenu();
                        else showMenu();
                        scope.$applyAsync();
                    }

                    // click on button toggles menu
                    if (btn) {
                        btn.addEventListener("click", toggleMenu);
                    }

                    // click outside closes menu
                    $document.on("click.authButton", function (ev) {
                        if (!element[0].contains(ev.target)) {
                            if (menu && menu.classList.contains("show")) {
                                scope.$apply(function () {
                                    hideMenu();
                                });
                            }
                        }
                    });

                    // close on escape
                    function onKeydown(e) {
                        if (e.key === "Escape") {
                            hideMenu();
                            scope.$applyAsync();
                        }
                    }
                    document.addEventListener("keydown", onKeydown);

                    scope.goLogin = function (e) {
                        e && e.preventDefault();
                        // thông tin giỏ hàng hiện tại
                        let carts = JSON.parse(
                            localStorage.getItem("guest_cart")
                        ) || {
                            items: [],
                            count: 0,
                        };

                        let cartItems = [];
                        carts.items.forEach((item) => {
                            let cartItem = {
                                product_id: item.product_id,
                                attribute_id: item.attribute_id,
                                quantity: item.quantity,
                                unit_price: item.price,
                            };
                            cartItems.push(cartItem);
                        });
                        AuthService.saveMergePayload({ items: cartItems });
                        AuthService.saveNext($window.location.pathname || "/");
                        $window.location.href = "/web/login";
                    };

                    scope.logout = function (e) {
                        e && e.preventDefault();
                        AuthService.logout().finally(function () {
                            $rootScope.isLoggedIn = false;
                            hideMenu();
                            $window.location.reload();
                        });
                    };
                },
            };
        },
    ]);
}
