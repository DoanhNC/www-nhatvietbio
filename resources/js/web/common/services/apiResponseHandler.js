/**
 * API Response Handler Service
 * Xử lý response từ Laravel ApiResponse class
 * Format: { status: boolean, data: any, message: string }
 */
export default function (webApp) {
    webApp.factory("ApiResponseHandler", [
        "$q",
        function ($q) {
            return {
                handleSuccess(res) {
                    return {
                        status: res.data && res.data.status === true,
                        data: res.data && res.data.data ? res.data.data : {},
                        message:
                            res.data && res.data.message
                                ? res.data.message
                                : "Thành công",
                        code: res.status || 200,
                    };
                },

                handleError(err) {
                    let errorResponse = {
                        status: false,
                        message: "Có lỗi xảy ra, vui lòng thử lại",
                        code: err.status || 500,
                        errors: {},
                        data: {},
                    };

                    if (err.data) {
                        errorResponse.status = err.data.status === true;
                        errorResponse.message =
                            err.data.message || errorResponse.message;
                        errorResponse.data = err.data.data || {};

                        if (err.status === 422 && err.data.data) {
                            errorResponse.errors = err.data.data;
                        }
                    }

                    return errorResponse;
                },

                getFirstError(errors) {
                    if (!errors || typeof errors !== "object") {
                        return null;
                    }

                    const firstKey = Object.keys(errors)[0];
                    if (firstKey && Array.isArray(errors[firstKey])) {
                        return errors[firstKey][0] || null;
                    }

                    return null;
                },

                getAllErrors(errors) {
                    if (!errors || typeof errors !== "object") {
                        return [];
                    }

                    let allErrors = [];
                    Object.keys(errors).forEach((key) => {
                        if (Array.isArray(errors[key])) {
                            allErrors = allErrors.concat(errors[key]);
                        }
                    });

                    return allErrors;
                },

                isSuccess(response) {
                    return response && response.status === true;
                },

                isError(response) {
                    return !this.isSuccess(response);
                },
            };
        },
    ]);
}
