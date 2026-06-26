export default function (webApp) {
    const footerNavMobileTemplate = `
<nav class="bottom-nav d-block d-md-none" id="siteFooter" role="navigation" aria-label="Điều hướng dưới cùng">
  <div class="container">
    <ul class="nav justify-content-between align-items-center">

      <!-- Trang chủ -->
      <li class="nav-item">
        <a class="nav-link text-center"
           ng-class="{ 'active': isActive(['web.home']) }"
           ng-href="{{ homeUrl }}">
          <span class="icon"><i class="fas fa-home"></i></span>
          <span>Trang chủ</span>
        </a>
      </li>

      <!-- Tìm kiếm -->
      <li class="nav-item">
        <a class="nav-link text-center search-trigger" href="#"
           ng-click="$event.preventDefault(); openSearch()">
          <span class="icon"><i class="fas fa-search"></i></span>
          <span>Tìm kiếm</span>
        </a>
      </li>

      <!-- Giỏ hàng -->
      <li class="nav-item">
        <a class="nav-link text-center"
           ng-class="{ 'active': isActive(['web.cart']) }"
           ng-href="{{ cartUrl }}">
          <span class="icon position-relative">
            <i class="fas fa-shopping-cart"></i>
            <span class="badge badge-pill badge-primary position-absolute"
                  style="top:-6px; right:-8px;"
                  ng-if="$root.cartCount>0"
                  ng-bind="$root.cartCount"></span>
          </span>
          <span>Giỏ hàng</span>
        </a>
      </li>

      <!-- Tài khoản -->
      <li class="nav-item">
        <a class="nav-link text-center"
           ng-class="{ 'active': isActive(['web.account.info', 'web.account.orders', 'web.account.favorites', 'web.account.address', 'web.account.notifications', 'web.login']) }"
           ng-href="{{ accountUrl }}">
          <span class="icon"><i class="fas fa-user"></i></span>
          <span>Tài khoản</span>
        </a>
      </li>

    </ul>
  </div>
</nav>
`;

    webApp.directive("footerNavMobile", [
        "$rootScope",
        function ($rootScope) {
            return {
                restrict: "E",
                replace: true,
                template: footerNavMobileTemplate,
                scope: {
                    homeUrl: "@?homeUrl",
                    cartUrl: "@?cartUrl",
                    accountUrl: "@?accountUrl",
                    currentRoute: "@?currentRoute",
                },
                link: function (scope) {
                    // Default
                    scope.homeUrl = scope.homeUrl || "/";
                    scope.cartUrl = scope.cartUrl || "/cart";
                    scope.accountUrl = scope.accountUrl || "/account";

                    // ============================
                    // ⭐ NEW: Hỗ trợ dạng MẢNG ROUTE
                    // ============================
                    scope.isActive = function (patterns) {
                        if (!scope.currentRoute || !patterns) return false;

                        // Nếu chỉ truyền string → convert thành mảng
                        if (!Array.isArray(patterns)) {
                            patterns = [patterns];
                        }

                        // Duyệt từng pattern trong mảng
                        for (let pattern of patterns) {
                            if (!pattern) continue;

                            // wildcard: web.account.*
                            if (pattern.indexOf(".*") > -1) {
                                let prefix = pattern.replace(".*", "");
                                // startsWith
                                if (
                                    scope.currentRoute.lastIndexOf(
                                        prefix,
                                        0
                                    ) === 0
                                ) {
                                    return true;
                                }
                            } else {
                                // so sánh chính xác
                                if (scope.currentRoute === pattern) {
                                    return true;
                                }
                            }
                        }

                        return false;
                    };

                    // Mở popup tìm kiếm
                    scope.openSearch = function () {
                        if (!$rootScope.searchState) {
                            $rootScope.searchState = {
                                isOpen: false,
                                query: "",
                                suggestions: [],
                                recent: [],
                            };
                        }
                        $rootScope.searchState.isOpen = true;
                    };

                    // Headroom
                    const footer = document.getElementById("siteFooter");
                    if (footer && window.Headroom) {
                        const footerRoom = new Headroom(footer, {
                            offset: 20,
                            tolerance: { up: 0, down: 0 },
                            classes: {
                                pinned: "headroom--pinned",
                                unpinned: "headroom--unpinned",
                            },
                            onPin: function () {
                                // footer đang HIỆN
                                document.body.classList.add("footer-visible");
                            },
                            onUnpin: function () {
                                // footer đang ẨN
                                document.body.classList.remove(
                                    "footer-visible"
                                );
                            },
                        });

                        footerRoom.init();
                    }
                },
            };
        },
    ]);
}
