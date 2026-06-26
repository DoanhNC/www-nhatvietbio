import angular from "angular";
import "angular-sanitize";
import "angular-toastr";
import "angular-toastr/dist/angular-toastr.css";
// ===== Import factory dành cho các API =====
import ApiResponseHandler from "./services/apiResponseHandler.js";
// ===== Import services =====
import favoritesService from "./services/favoritesService.js";
import addressService from "./services/addressService.js";
import notificationService from "./services/notificationService.js";
import cartService from "./services/cartService.js";
import addressApi from "./services/addressApi.js";
import authService from "./services/authService.js";
import httpConfig from "./services/httpConfig.js";
// ===== Import directives dành cho giao diện=====
import mobileHeader from "./directives/mobileHeaderDirective.js";
import confirmModal from "./directives/confirmModalDirective.js";
import toastr from "./directives/toastr.js";
import select2 from "./directives/select2.js";
import authButton from "./directives/authButton.js";
import searchPopupServiceSetting from "./directives/searchPopupMobileSetting.js";
import footerNavMobile from "./directives/footerNavMobile.js";
import searchPopupMobile from "./directives/searchPopupMobile.js";

window.angular = angular;
const webApp = angular.module("webApp", ["ngSanitize", "toastr"]);

// Setup services
ApiResponseHandler(webApp);
favoritesService(webApp);
addressService(webApp);
notificationService(webApp);
mobileHeader(webApp);
confirmModal(webApp);
cartService(webApp);
toastr(webApp);
select2(webApp);
authService(webApp);
addressApi(webApp);
authButton(webApp);
httpConfig(webApp);
// khởi tạo service search popup
searchPopupServiceSetting(webApp);
footerNavMobile(webApp);
searchPopupMobile(webApp);

//export ra window để dùng chung
window.webApp = webApp;

if (import.meta.hot) {
    // Để HMR không double-register, có thể chặn cảnh báo log
    import.meta.hot.accept(() => {});
}

// ---- App code ----
$(document).ready(function () {
    //#Headroom thực hiện ẩn hiện header khi cuộn trang
    if (window.feather) {
        feather.replace();
    }

    // // hiệu ứng ẩn hiện header
    // const header = document.getElementById("siteHeader");
    // const headroom = new Headroom(header, {
    //     offset: 80,
    //     tolerance: {
    //         up: 0,
    //         down: 0,
    //     },
    //     classes: {
    //         pinned: "headroom--pinned",
    //         unpinned: "headroom--unpinned",
    //     },
    //     onUnpin: function () {
    //         // Đóng dropdown user nếu đang mở khi header ẩn đi (mượt hơn)
    //         var userDropdown = document.getElementById("userDropdown");
    //         var dropdownMenu = userDropdown && userDropdown.nextElementSibling;
    //         if (
    //             userDropdown &&
    //             userDropdown.getAttribute("aria-expanded") === "true" &&
    //             dropdownMenu &&
    //             dropdownMenu.classList.contains("show")
    //         ) {
    //             dropdownMenu.classList.remove("show");
    //             userDropdown.setAttribute("aria-expanded", "false");
    //             // Nếu muốn mượt hơn nữa, có thể delay ẩn bằng setTimeout nếu cần
    //         }
    //     },
    // });
    // headroom.init();

    (function () {
        // hiệu ứng ẩn hiện header
        const header = document.getElementById("siteHeader");
        if (header && window.Headroom) {
            const headroom = new Headroom(header, {
                offset: 80,
                tolerance: { up: 0, down: 0 },
                classes: {
                    pinned: "headroom--pinned",
                    unpinned: "headroom--unpinned",
                },
                onUnpin: function () {
                    var userDropdown = document.getElementById("userDropdown");
                    var dropdownMenu =
                        userDropdown && userDropdown.nextElementSibling;
                    if (
                        userDropdown &&
                        userDropdown.getAttribute("aria-expanded") === "true" &&
                        dropdownMenu &&
                        dropdownMenu.classList.contains("show")
                    ) {
                        dropdownMenu.classList.remove("show");
                        userDropdown.setAttribute("aria-expanded", "false");
                    }
                },
            });
            headroom.init();
        }
    })();
});
