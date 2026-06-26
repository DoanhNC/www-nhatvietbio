// Home Controller - BMW Technology
// Translations are preloaded from PHP via window.pageData
// Supports LocalStorage caching with hash verification

webApp.controller("HomeCtrl", [
    "$scope",
    "$timeout",
    function ($scope, $timeout) {
        // Get data from PHP (passed via window.pageData)
        var pageData = window.pageData || {};
        var STORAGE_KEY = "bmw_lang";
        var CACHE_KEY = "bmw_translations_cache";
        var HASH_KEY = "bmw_translations_hash";

        // Get translations - either from cache or from server-injected data
        var TRANSLATIONS = {};
        var HASHES = pageData.hashes || {};
        var SUPPORTED_LANGS = pageData.supportedLangs || ["vi"];
        var DEFAULT_LANG = pageData.defaultLang || "vi";
        var LANG_META = pageData.langMeta || {};

        // Check cache validity
        var cachedHash = localStorage.getItem(HASH_KEY);
        var serverHash = pageData.combinedHash || "";

        if (cachedHash && cachedHash === serverHash) {
            // Cache is valid, use cached translations
            try {
                var cached = JSON.parse(localStorage.getItem(CACHE_KEY));
                if (cached) {
                    TRANSLATIONS = cached;
                    console.log("[BMW] Using cached translations");
                }
            } catch (e) {
                // Cache corrupted, use server data
                TRANSLATIONS = pageData.translations || {};
            }
        } else {
            // Cache invalid or missing, use server data and update cache
            TRANSLATIONS = pageData.translations || {};
            try {
                localStorage.setItem(CACHE_KEY, JSON.stringify(TRANSLATIONS));
                localStorage.setItem(HASH_KEY, serverHash);
                console.log("[BMW] Translations cached with hash:", serverHash);
            } catch (e) {
                console.warn("[BMW] Cannot cache translations:", e);
            }
        }

        // Current language - lấy từ localStorage
        $scope.currentLang = localStorage.getItem(STORAGE_KEY) || DEFAULT_LANG;

        // Ensure current lang is supported
        if (!SUPPORTED_LANGS.includes($scope.currentLang)) {
            $scope.currentLang = DEFAULT_LANG;
        }

        // Translation object - bound to template
        $scope.t =
            TRANSLATIONS[$scope.currentLang] ||
            TRANSLATIONS[DEFAULT_LANG] ||
            {};

        // Language metadata for display
        $scope.langMeta = LANG_META;
        $scope.supportedLangs = SUPPORTED_LANGS;

        /**
         * Switch language - instant, no API call!
         */
        $scope.switchLang = function (lang) {
            if (!SUPPORTED_LANGS.includes(lang)) return;
            if (lang === $scope.currentLang) return;
            if (!TRANSLATIONS[lang]) return;

            $scope.currentLang = lang;
            $scope.t = TRANSLATIONS[lang]; // Instant switch!
            localStorage.setItem(STORAGE_KEY, lang);

            // Update button active state
            updateLangButtons(lang);
        };

        /**
         * Check if language is active
         */
        $scope.isLangActive = function (lang) {
            return $scope.currentLang === lang;
        };

        /**
         * Update language button active states
         */
        function updateLangButtons(lang) {
            var $ = window.jQuery || window.$;
            if (!$) return;
            $(".lang-btn, .mobile-lang-btn").removeClass("active");
            $(
                '.lang-btn[data-lang="' +
                    lang +
                    '"], .mobile-lang-btn[data-lang="' +
                    lang +
                    '"]'
            ).addClass("active");
        }

        /**
         * Initialize
         */
        $timeout(function () {
            // Set initial button state
            updateLangButtons($scope.currentLang);

            // Bind language button clicks
            var $ = window.jQuery || window.$;
            if ($) {
                $(".lang-btn, .mobile-lang-btn").on("click", function () {
                    var lang = $(this).data("lang");
                    $scope.$apply(function () {
                        $scope.switchLang(lang);
                    });
                });
            }
        }, 100);
    },
]);
