export default function (webApp) {
    // Service gọi API provinces.open-api.vn
    webApp.factory("AddressApi", [
        "$http",
        function ($http) {
            return {
                getProvinces: function () {
                    return $http.get("https://provinces.open-api.vn/api/p/");
                },

                getDistricts: function (provinceCode) {
                    return $http.get(
                        "https://provinces.open-api.vn/api/p/" +
                            provinceCode +
                            "?depth=2"
                    );
                },

                getWards: function (districtCode) {
                    return $http.get(
                        "https://provinces.open-api.vn/api/d/" +
                            districtCode +
                            "?depth=2"
                    );
                },
            };
        },
    ]);
}
