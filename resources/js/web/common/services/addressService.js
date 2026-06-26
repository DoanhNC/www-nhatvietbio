/**
 * Address Service
 * Quản lý địa chỉ: thêm, xóa, cập nhật, đặt mặc định
 */
export default function (webApp) {
    webApp.factory("addressService", [
        "$http",
        function ($http) {
            return {
                /**
                 * Lấy danh sách địa chỉ
                 */
                getAddresses: function () {
                    return $http.get("/rest/web/addresses");
                },

                /**
                 * Lấy chi tiết một địa chỉ
                 */
                getAddress: function (id) {
                    return $http.get("/rest/web/addresses/" + id);
                },

                /**
                 * Thêm địa chỉ mới
                 */
                createAddress: function (data) {
                    return $http.post("/rest/web/addresses", data);
                },

                /**
                 * Cập nhật địa chỉ
                 */
                updateAddress: function (id, data) {
                    return $http.put("/rest/web/addresses/" + id, data);
                },

                /**
                 * Xóa địa chỉ
                 */
                deleteAddress: function (id) {
                    return $http.delete("/rest/web/addresses/" + id);
                },

                /**
                 * Đặt làm địa chỉ mặc định
                 */
                setDefault: function (id) {
                    return $http.post(
                        "/rest/web/addresses/" + id + "/set-default"
                    );
                },
            };
        },
    ]);
}
