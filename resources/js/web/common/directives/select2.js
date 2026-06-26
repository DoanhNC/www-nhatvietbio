export default function (webApp) {
    // Directive gắn Select2 với ngModel + re-init khi options đổi
    webApp.directive("select2", [
        "$timeout",
        function ($timeout) {
            return {
                restrict: "A",
                require: "ngModel",
                scope: { select2Options: "<?" },
                link: function (scope, element, attrs, ngModel) {
                    var $el = window.jQuery ? window.jQuery(element) : null;
                    var base = { width: "100%" };

                    function isInited() {
                        return $el && $el.hasClass("select2-hidden-accessible");
                    }

                    function init() {
                        if (!$el || typeof $el.select2 !== "function") return; // guard
                        if (isInited()) $el.select2("destroy");
                        $el.select2(
                            Object.assign({}, base, scope.select2Options || {})
                        );
                        $el.off("change.select2").on(
                            "change.select2",
                            function () {
                                scope.$applyAsync(function () {
                                    ngModel.$setViewValue($el.val());
                                });
                            }
                        );
                    }

                    // Chờ Select2 sẵn sàng (do load từ CDN)
                    (function waitForSelect2(attempt = 0) {
                        var maxTry = 40; // ~2s nếu delay 50ms
                        if (
                            window.jQuery &&
                            window.jQuery.fn &&
                            typeof window.jQuery.fn.select2 === "function"
                        ) {
                            // có select2 rồi -> init
                            $el = window.jQuery(element);
                            $timeout(init, 0);
                        } else if (attempt < maxTry) {
                            $timeout(function () {
                                waitForSelect2(attempt + 1);
                            }, 50);
                        } else {
                            // Hết lần thử -> bỏ qua, tránh crash app
                            // (nếu cần có thể console.warn để debug)
                        }
                    })();

                    ngModel.$render = function () {
                        $timeout(function () {
                            if (!$el) return;
                            var val =
                                ngModel.$viewValue == null
                                    ? ""
                                    : ngModel.$viewValue;
                            $el.val(val).trigger("change.select2");
                        });
                    };

                    // Khi array options đổi -> reinit
                    var off = scope.$on("select2:reinit", function () {
                        $timeout(function () {
                            init();
                            if ($el) {
                                var val =
                                    ngModel.$viewValue == null
                                        ? ""
                                        : ngModel.$viewValue;
                                $el.val(val).trigger("change.select2");
                            }
                        });
                    });

                    scope.$on("$destroy", function () {
                        off && off();
                        if ($el) {
                            $el.off(".select2");
                            if (isInited()) $el.select2("destroy");
                        }
                    });
                },
            };
        },
    ]);
}
