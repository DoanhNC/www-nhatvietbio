export default function (webApp) {
    const searchPopupTemplate = `
<div class="search-popup" ng-class="{ 'active': searchState.isOpen }" ng-click="closeSearch()">
  <div class="search-popup-content" ng-click="$event.stopPropagation()">
    <div class="search-popup-header">
      <h5 class="mb-0">
        <i class="fas fa-search color-primary mr-2"></i>Tìm kiếm
      </h5>
      <button type="button" class="btn-close" ng-click="closeSearch()" aria-label="Đóng">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div class="search-popup-body">
      <form class="search-form" role="search" ng-submit="performSearch($event)">
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text bg-white border-right-0">
              <i class="fas fa-search text-muted"></i>
            </span>
          </div>

          <!-- IMPORTANT: name="filter" để param là filter -->
          <input name="filter" type="search" class="form-control border-left-0"
                 placeholder="Tìm kiếm sản phẩm..." minlength="2" required
                 ng-model="searchState.query" ng-focus="onSearchFocus()">

          <div class="input-group-append">
            <button class="btn btn-primary background-primary text-white" type="submit">Tìm</button>
          </div>
        </div>
      </form>

      <div class="search-suggestions mt-3" ng-if="searchState.suggestions && searchState.suggestions.length > 0">
        <h6 class="text-muted mb-2">Gợi ý tìm kiếm</h6>
        <div class="suggestion-tags">
          <span class="badge badge-light mr-2 mb-2 suggestion-tag"
                ng-repeat="suggestion in searchState.suggestions"
                ng-click="selectSuggestion(suggestion)">
            {{ suggestion }}
          </span>
        </div>
      </div>

      <div class="recent-searches mt-3" ng-if="searchState.recent && searchState.recent.length > 0">
        <h6 class="text-muted mb-2">Tìm kiếm gần đây</h6>
        <div class="recent-tags">
          <span class="badge badge-outline-secondary mr-2 mb-2 recent-tag"
                ng-repeat="recent in searchState.recent"
                ng-click="selectSuggestion(recent)">
            {{ recent }}
          </span>
        </div>
      </div>
    </div>
  </div>
</div>
`;

    webApp.directive("searchPopupMobile", [
        "$rootScope",
        "$window",
        function ($rootScope, $window) {
            return {
                restrict: "E",
                replace: true,
                template: searchPopupTemplate,
                scope: {},
                link: function (scope, element) {
                    // khởi tạo nếu chưa có
                    scope.searchState = $rootScope.searchState || {
                        isOpen: false,
                        query: "",
                        suggestions: [],
                        recent: [],
                    };

                    scope.closeSearch = function () {
                        scope.searchState.isOpen = false;
                        scope.searchState.query = "";
                    };

                    scope.onSearchFocus = function () {};

                    // Khi chọn suggestion/recent -> redirect với đúng nội dung suggestion
                    scope.selectSuggestion = function (suggestion) {
                        const q = (suggestion || "").trim();
                        if (!q) return;

                        // cập nhật recent (giữ tối đa 5)
                        const list = scope.searchState.recent;
                        if (list.indexOf(q) === -1) {
                            list.unshift(q);
                            if (list.length > 5) list.pop();
                        }

                        scope.searchState.isOpen = false;

                        // redirect: encodeURIComponent đảm bảo spaces & dấu tiếng việt được mã hoá chính xác
                        $window.location.href =
                            "/web/search?name=" + encodeURIComponent(q);
                    };

                    // Khi submit form hoặc bấm nút Tìm
                    scope.performSearch = function (event) {
                        // ng-submit gọi trước submit thực của browser -> preventDefault để tránh submit HTML mặc định
                        if (event && event.preventDefault)
                            event.preventDefault();

                        const q = (scope.searchState.query || "").trim();

                        if (q.length < 2) {
                            // bạn có thể show lỗi ở đây nếu muốn
                            return;
                        }

                        // cập nhật recent
                        const list = scope.searchState.recent;
                        if (list.indexOf(q) === -1) {
                            list.unshift(q);
                            if (list.length > 5) list.pop();
                        }

                        scope.searchState.isOpen = false;

                        // redirect — chính xác nội dung user nhập (URL-encoded)
                        $window.location.href =
                            "/web/search?name=" + encodeURIComponent(q);
                    };

                    // focus input khi mở popup
                    scope.$watch("searchState.isOpen", function (val) {
                        if (val) {
                            setTimeout(() => {
                                const input = element[0].querySelector(
                                    'input[name="filter"]'
                                );
                                if (input) input.focus();
                            }, 200);
                        }
                    });
                },
            };
        },
    ]);
}
