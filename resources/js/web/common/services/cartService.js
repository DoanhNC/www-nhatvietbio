export default function (webApp) {
    // Thực hiện init ban đầu để lấy thông tin cartCount
    webApp.run([
        "$rootScope",
        "cartService",
        function ($rootScope, cartService) {
            $rootScope.cartCount = 0;

            cartService.getLocalCart().then(function (cart) {
                $rootScope.cartCount = cart.count || 0;
            });
        },
    ]);

    // Cart Service
    webApp.factory("cartService", [
        "$rootScope",
        "$http",
        function ($rootScope, $http) {
            const CART_KEY = "guest_cart";
            const CART_CHECKED_OUT = "cart_checked_out";

            function getLocalCart() {
                if (localStorage.getItem("isAuthenticated") === "true") {
                    return $http.get("/api/web/cart").then(function (response) {
                        const res = response.data;

                        const items = res.data.items || [];
                        let cart = {
                            items: items,
                            count: res.data.totalQuantity,
                        };
                        return Promise.resolve(cart);
                    });
                } else {
                    try {
                        let cart = JSON.parse(
                            localStorage.getItem(CART_KEY)
                        ) || {
                            items: [],
                            count: 0,
                        };
                        return Promise.resolve(cart); // <-- return promise
                    } catch (e) {
                        return Promise.resolve({ items: [], count: 0 });
                    }
                }
            }

            function saveLocalCart(cart) {
                localStorage.setItem(CART_KEY, JSON.stringify(cart));
            }

            function getCheckoutItems() {
                try {
                    let items = JSON.parse(
                        localStorage.getItem(CART_CHECKED_OUT)
                    );
                    return items || [];
                } catch (e) {
                    return [];
                }
            }

            function saveCheckoutItems(items) {
                localStorage.setItem(CART_CHECKED_OUT, JSON.stringify(items));
            }

            return {
                updateCount(count) {
                    $rootScope.cartCount = count;
                },

                addToLocalCart(item) {
                    if (localStorage.getItem("isAuthenticated") === "true") {
                        // Đã đăng nhập - call API
                        // item payload: { product_id, attribute_id, quantity, price }
                        return $http
                            .post("/api/web/cart/add", item)
                            .then(function (response) {
                                const res = response.data;
                                if (res.status) {
                                    // thực hiện cập nhật số lượng cart
                                    $rootScope.cartCount =
                                        res.data.totalQuantity;
                                }
                                return Promise.resolve(res);
                            });
                    } else {
                        // Chưa đăng nhập - lưu vào localStorage
                        // Cần đảm bảo item có đủ thông tin để hiển thị trong giỏ hàng nếu cần
                        // Tuy nhiên logic hiện tại của getLocalCart đang return items đơn giản
                        // Ta sẽ giữ nguyên logic cũ nhưng wrap lại để trả về promise thống nhất
                        return getLocalCart().then((cart) => {
                            const existingIndex = cart.items.findIndex(
                                (x) =>
                                    x.product_id === item.product_id &&
                                    x.attribute_id === item.attribute_id
                            );
                            if (existingIndex > -1) {
                                cart.items[existingIndex].quantity +=
                                    item.quantity;
                            } else {
                                cart.items.push(item);
                            }
                            cart.count = cart.items.reduce(
                                (sum, it) => sum + it.quantity,
                                0
                            );
                            saveLocalCart(cart);
                            this.updateCount(cart.count);
                            return Promise.resolve({ status: true, data: { totalQuantity: cart.count } });
                        });
                    }
                },

                //
                syncLocalCartToServer() {
                    getLocalCart().then((cart) => {
                        if (cart.items.length) {
                            return $http
                                .post("/api/cart/sync", cart.items)
                                .then((response) => {
                                    const res = response.data;
                                    try {
                                        localStorage.removeItem(CART_KEY);
                                    } catch (error) {}
                                });
                        }
                        return Promise.resolve();
                    });
                },

                updateLocalCartItem(item) {
                    // thực hiện update khi đã đăng nhập
                    if (localStorage.getItem("isAuthenticated") === "true") {
                        $http
                            .put("/api/web/cart/items/" + item.id, {
                                quantity: item.quantity,
                            })
                            .then((response) => {
                                let res = response.data;
                                if (res.status) {
                                    //thực hiện update lại số lượng cart
                                    this.updateCount(res.data.totalQuantity);
                                }
                                return Promise.resolve();
                            });
                    } else {
                        getLocalCart().then((cart) => {
                            const index = cart.items.findIndex(
                                (x) =>
                                    x.product_id === item.product_id &&
                                    x.attribute_id === item.attribute_id
                            );
                            if (index > -1) {
                                cart.items[index] = { ...item };
                                cart.count = cart.items.reduce(
                                    (sum, it) => sum + it.quantity,
                                    0
                                );
                                localStorage.setItem(
                                    CART_KEY,
                                    JSON.stringify(cart)
                                );
                                $rootScope.cartCount = cart.count;
                            }

                            return Promise.resolve();
                        });
                    }
                },

                removeFromLocalCart(item) {
                    if (localStorage.getItem("isAuthenticated") === "true") {
                        $http
                            .delete("/api/web/cart/items/" + item.id)
                            .then((response) => {
                                const res = response.data;
                                return Promise.resolve();
                            });
                    } else {
                        getLocalCart().then((cart) => {
                            cart.items = cart.items.filter(
                                (x) =>
                                    !(
                                        x.product_id === item.product_id &&
                                        x.attribute_id === item.attribute_id
                                    )
                            );
                            cart.count = cart.items.reduce(
                                (sum, it) => sum + it.quantity,
                                0
                            );
                            localStorage.setItem(
                                CART_KEY,
                                JSON.stringify(cart)
                            );
                            $rootScope.cartCount = cart.count;
                            return Promise.resolve();
                        });
                    }

                    return Promise.resolve();
                },

                /**
                 * Xóa tất cả items khỏi giỏ hàng
                 */
                clearLocalCart: function () {
                    if (localStorage.getItem("isAuthenticated") === "true") {
                        // Đã đăng nhập - call API delete all
                        return $http
                            .delete("/api/web/cart/clear")
                            .then((response) => {
                                const res = response.data;
                                this.updateCount(0);
                                return Promise.resolve({ items: [] });
                            })
                            .catch(function (err) {
                                console.error("Error clearing cart:", err);
                                return Promise.reject(err);
                            });
                    } else {
                        // Chưa đăng nhập - xóa localStorage
                        return $q(function (resolve, reject) {
                            try {
                                localStorage.removeItem(CART_KEY);
                                $rootScope.cartCount = 0;
                                resolve({ items: [] });
                            } catch (e) {
                                console.error(
                                    "Error clearing localStorage:",
                                    e
                                );
                                reject(e);
                            }
                        });
                    }
                },

                getLocalCart() {
                    return getLocalCart();
                },

                getCheckoutItems: getCheckoutItems,
                saveCheckoutItems: saveCheckoutItems,
                removeCheckoutItems() {
                    try {
                        localStorage.removeItem(CART_CHECKED_OUT);
                    } catch (error) {}
                },
            };
        },
    ]);
}
