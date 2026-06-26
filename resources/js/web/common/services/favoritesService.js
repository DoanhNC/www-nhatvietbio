/**
 * Favorites Service
 * Quản lý chức năng yêu thích: thêm, xóa, kiểm tra
 */
export default function (webApp) {
    webApp.factory("favoritesService", [
        "$http",
        "$rootScope",
        "$window",
        "$q",
        function ($http, $rootScope, $window, $q) {

            function checkAuth() {
                if (localStorage.getItem("isAuthenticated") !== "true") {
                    $window.location.href = '/web/login';
                    return false;
                }
                return true;
            }

            return {
                /**
                 * Lấy danh sách sản phẩm yêu thích
                 * @returns {Promise}
                 */
                getFavorites: function () {
                    if (!checkAuth()) return $q.reject("Unauthorized");
                    
                    return $http
                        .get("/rest/web/favorites")
                        .then(function (res) {
                            return Promise.resolve(res.data);
                        });
                },

                /**
                 * Thêm sản phẩm vào yêu thích
                 * @param {number} productId
                 */
                addFavorite: function (productId) {
                    if (!checkAuth()) return $q.reject("Unauthorized");

                    return $http
                        .post("/rest/web/favorites", {
                            product_id: productId,
                        })
                        .then(function (res) {
                            $rootScope.$broadcast(
                                "favorite:added",
                                productId
                            );
                            return Promise.resolve(res.data);
                        });
                },

                /**
                 * Xóa sản phẩm khỏi yêu thích
                 * @param {number} productId
                 */
                removeFavorite: function (productId) {
                    if (!checkAuth()) return $q.reject("Unauthorized");

                    return $http
                        .delete("/rest/web/favorites/" + productId)
                        .then(function (res) {
                            $rootScope.$broadcast(
                                "favorite:removed",
                                productId
                            );
                            return Promise.resolve(res.data);
                        });
                },

                /**
                 * Kiểm tra sản phẩm có trong yêu thích hay không
                 * @param {number} productId
                 */
                isFavorite: function (productId) {
                    // Nếu chưa login, trả về false (không redirect để tránh loop khi load trang)
                    if (localStorage.getItem("isAuthenticated") !== "true") {
                        return Promise.resolve(false);
                    }

                    return $http
                        .get("/rest/web/favorites/" + productId + "/check")
                        .then(function (res) {
                            return Promise.resolve(res.data.data.isFavorite);
                        });
                },

                /**
                 * Toggle favorite
                 * @param {object} product - Product object (must have id)
                 */
                toggleFavorite: function (product) {
                    if (!checkAuth()) return $q.reject("Unauthorized");

                    const self = this;
                    // Sử dụng is_favorite có sẵn trong product nếu có, để tránh gọi API check thừa
                    var isFav = product.is_favorite;
                    
                    var promise;
                    if (typeof isFav !== 'undefined') {
                         promise = Promise.resolve(isFav);
                    } else {
                         promise = this.isFavorite(product.id);
                    }

                    return promise.then(function (isFav) {
                        if (isFav) {
                            return self.removeFavorite(product.id).then(function(res) {
                                return { status: true, is_favorite: false, message: "Đã xóa khỏi yêu thích" };
                            });
                        } else {
                            return self.addFavorite(product.id).then(function(res) {
                                return { status: true, is_favorite: true, message: "Đã thêm vào yêu thích" };
                            });
                        }
                    });
                },
            };
        },
    ]);
}
