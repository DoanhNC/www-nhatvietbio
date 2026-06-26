/**
 * TinyMCE Editor Directive with Media Picker Integration
 *
 * Usage:
 * <textarea mce-editor ng-model="model.content"></textarea>
 * <textarea mce-editor="simple" ng-model="model.shortDesc"></textarea>
 *
 * Options:
 * - mce-editor: "" (full) or "simple" (minimal toolbar)
 * - mce-height: editor height in px (default: 300)
 *
 * IMPORTANT NOTES:
 * 1. Use ng-show (NOT ng-if) for tabs containing this directive to avoid reinit on tab switch
 * 2. In Blade templates, all inline scripts must be inside @push('scripts') or before @endsection
 *    Scripts placed outside will cause "not in standards mode" error
 * 3. Directive checks element.isConnected and offsetParent before init to ensure DOM is ready
 */
export function registerMceEditorDirective(app) {
    app.directive("mceEditor", [
        "$timeout",
        function ($timeout) {
            return {
                require: "ngModel",
                restrict: "A",
                scope: {
                    mceHeight: "@",
                },
                link: function (scope, element, attrs, ngModel) {
                    let editorInstance = null;

                    // Generate a unique ID for this editor instance
                    const editorId =
                        "mce_" + Math.random().toString(36).substr(2, 9);
                    element.attr("id", editorId);

                    const isSimple = attrs.mceEditor === "simple";
                    const height =
                        parseInt(scope.mceHeight) || (isSimple ? 200 : 400);

                    // Store pending content to set after init
                    let pendingContent = null;

                    // Initialize TinyMCE
                    const initEditor = () => {
                        if (typeof tinymce === "undefined") {
                            console.error("TinyMCE not loaded");
                            return;
                        }
                        if (editorInstance) return; // Already initialized

                        // Ensure element is connected to DOM and visible
                        const el = element[0];
                        if (!el.isConnected || el.offsetParent === null) {
                            // Element not ready, retry later
                            $timeout(initEditor, 50);
                            return;
                        }

                        const toolbar = isSimple
                            ? "bold italic underline strikethrough | bullist numlist | link image | undo redo"
                            : "undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | outdent indent | bullist numlist | link image media table | forecolor backcolor removeformat | code fullscreen";

                        const plugins = isSimple
                            ? "lists link image autoresize"
                            : "advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount autoresize";

                        tinymce.init({
                            selector: "#" + editorId,
                            height: height,
                            menubar: !isSimple,
                            plugins: plugins,
                            toolbar: toolbar,
                            branding: false,
                            promotion: false,
                            relative_urls: false,
                            remove_script_host: false,
                            convert_urls: true,
                            min_height: isSimple ? 150 : 300,
                            max_height: isSimple ? 400 : 800,
                            autoresize_bottom_margin: 20,
                            automatic_uploads: false,
                            image_uploadtab: false,
                            file_picker_types: "image",
                            file_picker_callback: function (
                                callback,
                                value,
                                meta
                            ) {
                                if (meta.filetype === "image") {
                                    window._mceMediaPickerCallback = callback;
                                    const event = new CustomEvent(
                                        "openMediaPickerForMCE",
                                        {
                                            detail: { callback: callback },
                                        }
                                    );
                                    document.dispatchEvent(event);
                                    if (
                                        typeof $ !== "undefined" &&
                                        $("#mediaPickerModalMCE").length
                                    ) {
                                        $("#mediaPickerModalMCE").modal("show");
                                    }
                                }
                            },
                            setup: function (editor) {
                                editorInstance = editor;

                                editor.on("init", function () {
                                    // Set pending content if available, otherwise use model value
                                    if (pendingContent !== null) {
                                        editor.setContent(pendingContent);
                                        pendingContent = null;
                                    } else if (ngModel.$viewValue) {
                                        editor.setContent(ngModel.$viewValue);
                                    }
                                });

                                editor.on("change keyup", function () {
                                    scope.$evalAsync(function () {
                                        ngModel.$setViewValue(
                                            editor.getContent()
                                        );
                                    });
                                });
                            },
                        });
                    };

                    // ngModel render - set content when data is available
                    ngModel.$render = function () {
                        const content = ngModel.$viewValue || "";
                        if (editorInstance && editorInstance.initialized) {
                            editorInstance.setContent(content);
                        } else {
                            // Editor not ready yet, store content for later
                            pendingContent = content;
                        }
                    };

                    // Initialize after a short delay to ensure DOM is ready
                    // With ng-if, element is only created when visible, so no visibility check needed
                    $timeout(initEditor, 100);

                    // Cleanup
                    scope.$on("$destroy", function () {
                        if (editorInstance) {
                            tinymce.remove("#" + editorId);
                        }
                    });
                },
            };
        },
    ]);

    // Listen for media selection from parent controller
    app.run([
        "$rootScope",
        function ($rootScope) {
            // Method to insert selected media URL into TinyMCE
            $rootScope.insertMediaToMCE = function (url) {
                if (window._mceMediaPickerCallback) {
                    window._mceMediaPickerCallback(url, { alt: "" });
                    window._mceMediaPickerCallback = null;
                }
            };
        },
    ]);
}
