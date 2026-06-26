export default function (webApp) {
    webApp.factory("$toastr", [
        "toastr",
        function (toastr) {
            return {
                /**
                 * Hiển thị toastr
                 * @param {string} message - nội dung thông báo
                 * @param {string} type - success | error | warning | info
                 * @param {number} time - thời gian hiển thị (ms)
                 */
                show(message, type = "success", time = null) {
                    // thực hiển ẩn thông báo trước đó đi
                    toastr.clear();
                    const options = {};
                    if (time) options.timeOut = time; // ghi đè thời gian mặc định

                    switch (type) {
                        case "error":
                            return toastr.error(message, null, options);
                        case "warning":
                            return toastr.warning(message, null, options);
                        case "info":
                            return toastr.info(message, null, options);
                        default:
                            return toastr.success(message, null, options);
                    }
                },
            };
        },
    ]);

    //
    webApp.config([
        "toastrConfig",
        function (toastrConfig) {
            angular.extend(toastrConfig, {
                positionClass: "toast-top-right",
                timeOut: 3000, // thời gian mặc định 3 giây
                progressBar: true,
                closeButton: true,
                newestOnTop: true,
            });
        },
    ]);
}
