// resources/js/web/common/auth.js
export default function (webApp) {
    // ===== Tiny helper to read/write token =====
    const TOKEN_KEY = "ecommerce_token";

    function getToken() {
        try {
            return localStorage.getItem(TOKEN_KEY) || "";
        } catch (e) {
            return "";
        }
    }

    function setToken(token) {
        try {
            localStorage.setItem(TOKEN_KEY, token || "");
        } catch (e) {}
        // để tương thích code cũ
        window.isAuthenticated = !!token;
    }

    // Cho mấy chỗ khác có thể xài lại
    window.getAuthToken = getToken;
    window.setAuthToken = setToken;

    // ===== $http interceptor: đính kèm Bearer token =====
    webApp.config([
        "$httpProvider",
        function ($httpProvider) {
            $httpProvider.interceptors.push([
                "$q",
                "$injector",
                function ($q, $injector) {
                    return {
                        request: function (config) {
                            const t = getToken();
                            if (t) {
                                config.headers = config.headers || {};
                                config.headers.Authorization = "Bearer " + t;
                            }
                            return config;
                        },
                        responseError: function (rejection) {
                            // Nếu sau này API trả 401 thì clear auth luôn
                            if (rejection.status === 401) {
                                try {
                                    localStorage.removeItem(TOKEN_KEY);
                                } catch (e) {}
                                window.isAuthenticated = false;

                                const $rootScope = $injector.get("$rootScope");
                                $rootScope.isAuthenticated = false;
                                $rootScope.currentUser = null;
                            }
                            return $q.reject(rejection);
                        },
                    };
                },
            ]);
        },
    ]);

    // ===== Hàm gọi API kiểm tra trạng thái đăng nhập (ĐÃ TẮT) =====
    // function callAuthCheck($http, $rootScope) {
    //     return $http
    //         .get("/rest/web/auth/check")
    //         .then(function (res) {
    //             // format theo ApiResponse
    //             // success: { status: true, data: {...}, message: "Success" }
    //             const body = res.data || {};

    //             if (body.status && body.data && body.data.isAuthenticated) {
    //                 const user = body.data.user || null;
    //                 // Đã đăng nhập
    //                 localStorage.setItem("isAuthenticated", true);
    //             } else {
    //                 // Chưa đăng nhập
    //                 localStorage.removeItem("isAuthenticated");
    //             }
    //         })
    //         .catch(function () {
    //             // Chưa đăng nhập
    //             localStorage.removeItem("isAuthenticated");
    //         });
    // }

    // ===== run block: chạy 1 lần khi app khởi tạo =====
    webApp.run([
        "$http",
        "$rootScope",
        function ($http, $rootScope) {
            // Giá trị mặc định
            $rootScope.isAuthenticated = false;
            $rootScope.currentUser = null;

            // Cho mấy controller khác có thể gọi lại (ĐÃ TẮT)
            // $rootScope.refreshAuthStatus = function () {
            //     return callAuthCheck($http, $rootScope);
            // };

            // Gọi lần đầu khi load trang (đã bỏ qua)
            // callAuthCheck($http, $rootScope);
        },
    ]);
}
