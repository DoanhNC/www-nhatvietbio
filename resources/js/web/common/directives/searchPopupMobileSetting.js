export default function (webApp) {
    // khởi tạo state dùng chung
    webApp.run([
        "$rootScope",
        function ($rootScope) {
            $rootScope.searchState = {
                isOpen: false,
                query: "",
                suggestions: [
                    "điện thoại",
                    "laptop",
                    "tai nghe",
                    "đồng hồ",
                    "giày",
                ],
                recent: ["iphone", "samsung", "macbook"],
            };
        },
    ]);
}
