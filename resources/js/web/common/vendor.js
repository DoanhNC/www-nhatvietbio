// jQuery (Toastr phụ thuộc jQuery)
//import $ from "jquery";
// window.$ = $;
// window.jQuery = $;

const $ = window.jQuery || window.$;
window.$ = window.jQuery = $;
if (!$) {
    console.warn(
        "jQuery global is missing. Please load it via CDN before vendor.js"
    );
}

// AngularJS (nếu bạn dùng ng-app/ng-controller)
import "angular";

// Popper v1 cho Bootstrap 4
import "popper.js/dist/popper.min.js";

// Bootstrap 4 JS (bundle đã kèm Popper)
import "bootstrap/dist/js/bootstrap.bundle.min.js";

// Feather Icons
import feather from "feather-icons";
window.feather = feather;

// Toastr
import toastr from "toastr";
window.toastr = toastr;

// Headroom.js
import Headroom from "headroom.js";
window.Headroom = Headroom;

// ====== Swiper v11+ ======
import Swiper from "swiper";
import {
    Navigation,
    Pagination,
    Autoplay,
    EffectCoverflow,
    EffectFade,
    EffectCube,
    EffectFlip,
    EffectCreative,
    EffectCards,
} from "swiper/modules";

window.Swiper = Swiper;
window.SwiperModules = {
    Navigation,
    Pagination,
    Autoplay,
    EffectCoverflow,
    EffectFade,
    EffectCube,
    EffectFlip,
    EffectCreative,
    EffectCards,
};

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".js-product-swiper").forEach((el) => {
        const effect = el.dataset.effect || "coverflow"; // mặc định coverflow

        // Bổ sung phần tử pagination/progress nếu chưa có trong HTML
        if (!el.querySelector(".swiper-pagination")) {
            el.insertAdjacentHTML(
                "beforeend",
                '<div class="swiper-pagination"></div>'
            );
        }
        if (!el.querySelector(".autoplay-progress")) {
            el.insertAdjacentHTML(
                "beforeend",
                `
        <div class="autoplay-progress" aria-hidden="true">
          <svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="20"></circle></svg>
          <span></span>
        </div>
      `
            );
        }

        const progressCircle = el.querySelector(".autoplay-progress svg");
        const progressContent = el.querySelector(".autoplay-progress span");

        // Base config chung
        const base = {
            modules: [
                window.SwiperModules.Navigation,
                window.SwiperModules.Pagination,
                window.SwiperModules.Autoplay,
            ],
            loop: true,
            speed: 600,
            autoplay: { delay: 10000, disableOnInteraction: false },
            pagination: {
                el: el.querySelector(".swiper-pagination"),
                clickable: true,
            },

            // Chỉ gán navigation nếu hiện diện trong DOM (nếu bạn đã bỏ nút thì sẽ tự bỏ qua)
            ...(el.querySelector(".swiper-button-next") &&
            el.querySelector(".swiper-button-prev")
                ? {
                      navigation: {
                          nextEl: el.querySelector(".swiper-button-next"),
                          prevEl: el.querySelector(".swiper-button-prev"),
                      },
                  }
                : {}),

            // Autoplay progress cho MỌI slider
            on: {
                autoplayTimeLeft(_, time, progress) {
                    // Kẹp progress về [0,1] để vòng tròn không “giật”
                    const safeProgress = Math.min(
                        1,
                        Math.max(0, 1 - (progress ?? 0))
                    );
                    // Không cho hiển thị giây âm
                    const secs = Math.max(0, Math.ceil((time ?? 0) / 1000));

                    progressCircle.style.setProperty(
                        "--progress",
                        safeProgress
                    );
                    progressContent.textContent = `${secs}s`;
                },
            },
        };

        // Tuỳ biến theo hiệu ứng
        if (effect === "coverflow") {
            base.modules.push(window.SwiperModules.EffectCoverflow);
            Object.assign(base, {
                effect: "coverflow",
                centeredSlides: true,
                slidesPerView: "auto",
                spaceBetween: 24,
                lazyPreloadPrevNext: 1,
                coverflowEffect: {
                    rotate: 12,
                    depth: 140,
                    stretch: 0,
                    modifier: 1,
                    slideShadows: false,
                },
            });
            // Gợi ý: cố định width để coverflow nổi bật (đặt trong CSS)
            // .js-product-swiper .swiper-slide { width: 280px; }
        } else if (effect === "fade") {
            base.modules.push(window.SwiperModules.EffectFade);
            Object.assign(base, { effect: "fade" });
        } else if (effect === "cube") {
            base.modules.push(window.SwiperModules.EffectCube);
            Object.assign(base, {
                effect: "cube",
                cubeEffect: { shadow: false },
            });
        } else if (effect === "flip") {
            base.modules.push(window.SwiperModules.EffectFlip);
            Object.assign(base, { effect: "flip" });
        } else if (effect === "creative") {
            base.modules.push(window.SwiperModules.EffectCreative);
            Object.assign(base, {
                effect: "creative",
                creativeEffect: {
                    prev: { translate: ["-20%", 0, -1], opacity: 0.7 },
                    next: { translate: ["20%", 0, -1], opacity: 0.7 },
                },
            });
        } else if (effect === "cards") {
            base.modules.push(window.SwiperModules.EffectCards);
            Object.assign(base, { effect: "cards", grabCursor: true });
        }

        new window.Swiper(el, base);
    });
});

// =============================================
// BMW Website - Common UI Functionality
// =============================================

$(document).ready(function () {
    // ---- Mobile Menu ----
    $(".mobile-menu-btn").on("click", function () {
        $(".mobile-menu-backdrop").addClass("show");
        $(".mobile-menu").addClass("show");
    });

    $(".mobile-menu-close, .mobile-menu-backdrop, .mobile-nav-link").on(
        "click",
        function () {
            $(".mobile-menu").removeClass("show");
            $(".mobile-menu-backdrop").removeClass("show");
        }
    );

    // ---- Header Scroll Effect ----
    $(window).on("scroll", function () {
        var scrollTop = $(this).scrollTop();

        // Header class toggle
        if (scrollTop > 20) {
            $(".header").addClass("scrolled");
        } else {
            $(".header").removeClass("scrolled");
        }

        // Scroll to top button visibility
        if (scrollTop > 400) {
            $(".quick-btn.scroll-top").addClass("show");
        } else {
            $(".quick-btn.scroll-top").removeClass("show");
        }

        // Scroll Spy - Update active navigation
        var sections = [
            "technology",
            "systems",
            "products",
            "applications",
            "news",
            "contact",
        ];
        var scrollPos = scrollTop + 150;
        var currentSection = "";
        var closestDistance = Infinity;

        sections.forEach(function (sectionId) {
            var section = $("#" + sectionId);
            if (section.length) {
                var sectionTop = section.offset().top;
                var distance = scrollPos - sectionTop;

                if (distance >= 0 && distance < closestDistance) {
                    closestDistance = distance;
                    currentSection = sectionId;
                }
            }
        });

        // Update nav links classes
        $(".nav-link, .mobile-nav-link").removeClass("active");
        if (currentSection) {
            // Selector khớp với cả href="#section" và href="/path#section"
            $(".nav-link, .mobile-nav-link").each(function () {
                var href = $(this).attr("href") || "";
                if (href.indexOf("#" + currentSection) !== -1) {
                    $(this).addClass("active");
                }
            });
        }
    });

    // ---- Smooth Scroll for Anchor Links ----
    $('a[href^="#"]').on("click", function (e) {
        e.preventDefault();
        var target = $($(this).attr("href"));
        if (target.length) {
            var offsetTop = target.offset().top - 80;
            window.scrollTo({ top: offsetTop, behavior: "smooth" });
        }
    });

    // ---- Logo Click - Scroll to Top ----
    $(".logo").on("click", function (e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: "smooth" });
    });

    // ---- Scroll to Top Button ----
    $(".quick-btn.scroll-top").on("click", function () {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });

    // ---- Contact Form Handling ----
    $("#contact-form").on("submit", function (e) {
        e.preventDefault();
        var name = $('[data-i18n="formName"]').val().trim();
        var phone = $('[data-i18n="formPhone"]').val().trim();

        if (!name || !phone) return;

        var $btn = $(this).find(".cta-form-btn");
        $btn.prop("disabled", true).html(
            '<div class="cta-form-spinner"></div>'
        );

        setTimeout(function () {
            $(".cta-form-fields").addClass("hide");
            $(".cta-form-success").addClass("show");
            console.log("Form submitted:", { name: name, phone: phone });
        }, 1000);
    });

    // ---- Scroll Animations with Intersection Observer ----
    if ("IntersectionObserver" in window) {
        var observerOptions = {
            threshold: 0.1,
            rootMargin: "0px 0px -50px 0px",
        };
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add("scroll-visible");
                    entry.target.classList.remove("scroll-hidden");
                }
            });
        }, observerOptions);

        $(".scroll-animate").each(function () {
            this.classList.add("scroll-hidden");
            observer.observe(this);
        });
    }
});
