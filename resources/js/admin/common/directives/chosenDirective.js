/**
 * Chosen Directive for AngularJS
 * Usage: <select chosen ng-model="model.categories"
 *                ng-options="cat.id as cat.name for cat in categoriesList"
 *                multiple data-placeholder="Chọn danh mục..."></select>
 */
export function registerChosenDirective(app) {
    app.directive("chosen", [
        "$timeout",
        function ($timeout) {
            return {
                restrict: "A",
                require: "ngModel",
                link: function (scope, element, attrs, ngModel) {
                    const options = {
                        width: "100%",
                        no_results_text: "Không tìm thấy:",
                        placeholder_text_multiple:
                            attrs.dataPlaceholder || "Chọn...",
                        placeholder_text_single:
                            attrs.dataPlaceholder || "Chọn...",
                        search_contains: true,
                    };

                    // Wait for ng-options to render
                    $timeout(function () {
                        $(element).chosen(options);
                    }, 100);

                    // Watch for options source changes (re-init when data loads)
                    if (attrs.ngOptions) {
                        const match = attrs.ngOptions.match(/in\s+(\w+)/);
                        if (match) {
                            const collectionName = match[1];
                            scope.$watchCollection(
                                collectionName,
                                function (newVal) {
                                    if (newVal && newVal.length) {
                                        $timeout(function () {
                                            $(element).trigger(
                                                "chosen:updated"
                                            );
                                        }, 50);
                                    }
                                }
                            );
                        }
                    }

                    // Model -> View
                    ngModel.$render = function () {
                        $timeout(function () {
                            $(element).trigger("chosen:updated");
                        });
                    };

                    // View -> Model (on change)
                    $(element).on("change", function () {
                        scope.$apply(function () {
                            let val = $(element).val();
                            // Check if multiple select
                            const isMultiple =
                                element[0].hasAttribute("multiple");
                            if (isMultiple && val) {
                                val = val.map(function (v) {
                                    const num = parseInt(v, 10);
                                    return isNaN(num) ? v : num;
                                });
                            } else if (val && val !== "") {
                                const num = parseInt(val, 10);
                                val = isNaN(num) ? val : num;
                            }
                            ngModel.$setViewValue(val);
                        });
                    });

                    // Cleanup
                    scope.$on("$destroy", function () {
                        $(element).chosen("destroy");
                    });
                },
            };
        },
    ]);
}
