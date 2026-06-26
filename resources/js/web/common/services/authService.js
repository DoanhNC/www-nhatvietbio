export default function (webApp) {
    webApp.factory("AuthService", [
        "$http",
        "$window",
        "$rootScope",
        function ($http, $window, $rootScope) {
            const TOKEN_KEY = "ecommerce_token";
            const NEXT_KEY = "ecommerce_next";
            const MERGE_KEY = "ecommerce_cart_merge_payload";

            function setToken(token) {
                try {
                    localStorage.setItem(TOKEN_KEY, token || "");
                } catch (e) {}
                $window.isAuthenticated = !!token;
                $rootScope.$broadcast("auth:status", !!token);
            }
            function getToken() {
                try {
                    return localStorage.getItem(TOKEN_KEY) || "";
                } catch (e) {
                    return "";
                }
            }
            function isAuthenticated() {
                return !!getToken() || !!$window.isAuthenticated;
            }

            function saveNext(u) {
                try {
                    sessionStorage.setItem(NEXT_KEY, u || "/");
                } catch (e) {}
            }
            function popNext() {
                try {
                    const v = sessionStorage.getItem(NEXT_KEY) || "/";
                    sessionStorage.removeItem(NEXT_KEY);
                    return v;
                } catch (e) {
                    return "/";
                }
            }

            function saveMergePayload(p) {
                try {
                    sessionStorage.setItem(MERGE_KEY, JSON.stringify(p || {}));
                } catch (e) {}
            }
            function popMergePayload() {
                try {
                    const r = sessionStorage.getItem(MERGE_KEY);
                    sessionStorage.removeItem(MERGE_KEY);
                    return r ? JSON.parse(r) : null;
                } catch (e) {
                    return null;
                }
            }

            function register(payload) {
                return $http.post("/web/register", payload).then((r) => {
                    if (r.data?.token) {
                        setToken(r.data.token);
                        localStorage.setItem("isAuthenticated", true);
                    }
                    return r;
                });
            }
            function login(email, password) {
                return $http
                    .post("/web/login", { email, password })
                    .then((r) => {
                        if (r.data?.token) {
                            setToken(r.data.token);
                            localStorage.setItem("isAuthenticated", true);
                        }
                        return r;
                    });
            }
            function logout() {
                setToken("");
                try {
                    // thực hiện xóa thông tin localStorage liên quan
                    localStorage.setItem("isAuthenticated", false);
                    localStorage.removeItem("guest_cart");
                    localStorage.removeItem("cart_checked_out");
                } catch (e) {
                    // do nothing
                }

                return $http.post("/web/logout");
            }

            function mergeLocalCartToServer() {
                const payload = popMergePayload();
                if (!payload || !payload.items || !payload.items.length)
                    return Promise.resolve({ data: { merged: false } });
                return $http.post("/api/web/cart/merge-local", payload);
            }

            return {
                setToken,
                getToken,
                isAuthenticated,
                saveNext,
                popNext,
                saveMergePayload,
                popMergePayload,
                register,
                login,
                logout,
                mergeLocalCartToServer,
            };
        },
    ]);
}
