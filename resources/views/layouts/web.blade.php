<!DOCTYPE html>
<html lang="vi">
@php
$siteLogo = \App\Models\ESetting::getLogo();
$siteFavicon = \App\Models\ESetting::getFavicon();
$websiteInfo = \App\Models\ESetting::getWebsiteInfo();

// Website Info với fallback values (rỗng nếu không có trong DB)
$siteName = $websiteInfo['name'] ?? '';
$siteCompany = $websiteInfo['company'] ?? '';
$siteHotline = $websiteInfo['hotline'] ?? '';
$sitePhone = $websiteInfo['phone'] ?? '';
$siteEmail = $websiteInfo['email'] ?? '';
$siteAddress = $websiteInfo['address'] ?? '';
$siteWorkingHours = $websiteInfo['working_hours'] ?? '';
$siteDescription = $websiteInfo['description'] ?? '';

// Format phone for tel: links (remove spaces)
$siteHotlineClean = preg_replace('/\s+/', '', $siteHotline);
$sitePhoneClean = preg_replace('/\s+/', '', $sitePhone);

// Languages for language switcher (available globally)
$languages = $languages ?? \App\Models\ELanguage::getActiveLanguages();
$currentLang = $currentLang ?? session('locale', 'vi');
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', $siteName . ' - ' . $siteDescription)">
    <title>@yield('title', $siteName)</title>

    <!-- Favicon -->
    @if($siteFavicon)
    <link rel="icon" type="image/x-icon" href="{{ $siteFavicon }}">
    <link rel="shortcut icon" href="{{ $siteFavicon }}">
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&subset=vietnamese&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Vendor CSS (Swiper, etc.) -->
    @vite('resources/css/web/vendor.css')

    <!-- Main Stylesheet -->
    @vite('resources/css/web/style.css')

    {{-- Theme Colors --}}
    @php
    $css = \App\Models\ESetting::getThemeColorsCss();
    if ($css) {
    echo '<style id="theme-colors">
        :root {
            ' . $css . '
        }
    </style>';
    }
    @endphp

    @stack('styles')
</head>

<body>
    <!-- ========== TOP BAR ========== -->
    <div class="topbar">
        <div class="container topbar-content">
            <div class="topbar-left">
                <div class="topbar-item">
                    <i class="fas fa-water"></i>
                    <span>{{ __t('topbar.slogan', 'Hệ Thống Lọc và Xử Lý Nước Cấp, Xử Lý Nước Thải') }}</span>
                </div>
            </div>
            <div class="topbar-right">
                @if($siteWorkingHours)
                <div class="topbar-item">
                    <i class="far fa-clock"></i>
                    <span>{{ $siteWorkingHours }}</span>
                </div>
                @endif
                <a href="tel:{{ $siteHotlineClean }}" class="topbar-hotline">
                    <i class="fas fa-phone-alt"></i>
                    <span>{{ $siteHotline }}</span>
                </a>
                <!-- Language Switcher -->
                @if(isset($languages) && $languages->count() > 1)
                <div class="lang-switcher">
                    <button class="lang-current" id="langToggle">
                        @php
                        $currentLangData = $languages->firstWhere('code', $currentLang ?? 'vi') ?? $languages->first();
                        @endphp
                        @if($currentLangData && $currentLangData->flag_icon)
                        <img src="{{ $currentLangData->flag_icon }}" alt="{{ $currentLangData->name }}" class="lang-flag">
                        @else
                        <i class="fas fa-globe"></i>
                        @endif
                        <span class="lang-code">{{ strtoupper($currentLangData->code ?? 'VI') }}</span>
                        <i class="fas fa-chevron-down lang-arrow"></i>
                    </button>
                    <ul class="lang-dropdown" id="langDropdown">
                        @foreach($languages as $lang)
                        <li>
                            <a href="{{ url('/change-language/' . $lang->code) }}" class="lang-option {{ ($currentLang ?? 'vi') == $lang->code ? 'active' : '' }}">
                                @if($lang->flag_icon)
                                <img src="{{ $lang->flag_icon }}" alt="{{ $lang->name }}" class="lang-flag">
                                @else
                                <i class="fas fa-globe"></i>
                                @endif
                                <span>{{ $lang->name }}</span>
                                @if(($currentLang ?? 'vi') == $lang->code)
                                <i class="fas fa-check lang-check"></i>
                                @endif
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ========== STICKY HEADER WRAPPER (Desktop) ========== -->
    <div class="sticky-header-wrapper" id="stickyHeaderWrapper">
        <!-- ========== HEADER ========== -->
        <header class="header">
            <div class="container header-content">
                <a href="{{ route('bmw.home') }}" class="logo">
                    @if($siteLogo)
                    <img src="{{ $siteLogo }}" alt="Logo">
                    @else
                    <img src="https://xulynuocvietphat.com/wp-content/uploads/2022/01/logo-xu-ly-nuoc-viet-phat.png" alt="Xử Lý Nước Nhật Việt Biotech">
                    @endif
                </a>

                <div class="header-info">
                    <div class="info-box">
                        <div class="info-box-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <div class="info-box-content">
                            <h4>{{ __t('header.quality_title', 'Chất Lượng Sản Phẩm') }}</h4>
                            <p>{{ __t('header.quality_desc', 'Tiêu chuẩn ISO 9001:2015') }}</p>
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-box-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="info-box-content">
                            <h4>{{ __t('header.warranty_title', 'Bảo Hành Bảo Trì') }}</h4>
                            <p>{{ __t('header.warranty_desc', 'Hỗ trợ 24/7') }}</p>
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-box-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-box-content">
                            <h4>{{ __t('header.coverage_title', 'Phạm Vi Thi Công') }}</h4>
                            <p>{{ __t('header.coverage_desc', 'Toàn quốc') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- ========== NAVIGATION ========== -->
        <nav class="navbar">
            <div class="container navbar-content">
                <button class="nav-toggle" id="navToggle" aria-label="Toggle Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <!-- Mobile Logo -->
                <a href="{{ route('bmw.home') }}" class="navbar-logo">
                    @if($siteLogo)
                    <img src="{{ $siteLogo }}" alt="Logo">
                    @else
                    <img src="https://xulynuocvietphat.com/wp-content/uploads/2022/01/logo-xu-ly-nuoc-viet-phat.png" alt="Xử Lý Nước Nhật Việt Biotech">
                    @endif
                </a>

                <ul class="nav-menu" id="navMenu">
                    <!-- Mobile Search Box -->
                    <li class="nav-search">
                        <input type="text" placeholder="{{ __t('nav.search_placeholder', 'Tìm kiếm...') }}" class="nav-search-input">
                        <button class="nav-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </li>
                    <!-- Trang chủ - Static -->
                    <li class="nav-item">
                        <a href="/" class="nav-link {{ request()->is('/') ? 'active' : '' }}">{{ mb_strtoupper(__t('nav.home', 'Trang Chủ'), 'UTF-8') }}</a>
                    </li>
                    <!-- Dynamic menu from categories -->
                    @isset($menuCategories)
                    @foreach($menuCategories as $category)
                    @if(count($category['children']) > 0)
                    <li class="nav-item has-dropdown">
                        <a href="/category/{{ $category['slug'] }}" class="nav-link">{{ mb_strtoupper($category['name'], 'UTF-8') }}</a>
                        <ul class="nav-dropdown">
                            @foreach($category['children'] as $child)
                            <a href="/category/{{ $child['slug'] }}" class="nav-dropdown-link">{{ $child['name'] }}</a>
                            @endforeach
                        </ul>
                    </li>
                    @else
                    <li class="nav-item">
                        <a href="/category/{{ $category['slug'] }}" class="nav-link">{{ mb_strtoupper($category['name'], 'UTF-8') }}</a>
                    </li>
                    @endif
                    @endforeach
                    @endisset
                </ul>
            </div>
        </nav>
    </div>
    <!-- End Sticky Header Wrapper -->

    <!-- Mobile Menu Overlay -->
    <div class="nav-overlay" id="navOverlay"></div>

    <!-- Main Content -->
    @yield('content')

    <!-- ========== FOOTER ========== -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <!-- Column 1: About -->
                <div class="footer-col">
                    <h4>{{ __t('footer.about_title', 'Về Nhật Việt Biotech') }}</h4>
                    <p>{{ __t('footer.about_desc', 'Công ty TNHH Giải pháp Môi trường Xanh Bền Vững Nhật Việt - Liên doanh Nhật Bản và Việt Nam, chuyên về công nghệ BMW trong lĩnh vực nông nghiệp tuần hoàn và bảo vệ môi trường.') }}</p>
                    <p>{{ __t('footer.about_member', 'Thành viên Hiệp hội Công nghệ BMW Nhật Bản (1990)') }}</p>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="footer-col">
                    <h4>{{ __t('footer.quick_links', 'Liên Kết Nhanh') }}</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('bmw.home') }}"><i class="fas fa-angle-right"></i> {{ __t('nav.home', 'Trang Chủ') }}</a></li>
                        @isset($menuCategories)
                        @foreach($menuCategories as $category)
                        <li><a href="/category/{{ $category['slug'] }}"><i class="fas fa-angle-right"></i> {{ $category['name'] }}</a></li>
                        @endforeach
                        @endisset
                    </ul>
                </div>

                <!-- Column 3: Contact -->
                <div class="footer-col">
                    <h4>{{ __t('footer.contact_info', 'Thông Tin Liên Hệ') }}</h4>
                    <div class="footer-contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>{{ $siteAddress }}</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-phone-alt"></i>
                        <span>{{ $sitePhone }}</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>{{ $siteEmail }}</span>
                    </div>
                    @if($siteWorkingHours)
                    <div class="footer-contact-item">
                        <i class="far fa-clock"></i>
                        <span>{{ $siteWorkingHours }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ $siteCompany }}. {{ __t('footer.copyright', 'Tất cả quyền được bảo lưu.') }}</p>
            </div>
        </div>
    </footer>

    <!-- ========== FLOATING BUTTONS ========== -->
    <div class="floating-buttons">
        <a href="tel:{{ $siteHotlineClean }}" class="floating-btn phone" title="Gọi ngay">
            <span class="phone-number">{{ $siteHotline }}</span>
            <i class="fas fa-phone-alt"></i>
        </a>
        <a href="https://zalo.me/{{ $siteHotlineClean }}" target="_blank" class="floating-btn zalo" title="Chat Zalo">
            <img src="{{ asset('images/web/zalo-icon.svg') }}" alt="Zalo" style="width: 28px; height: 28px">
        </a>
        <a href="#contact" class="floating-btn map" title="Xem bản đồ">
            <i class="fas fa-map-marker-alt"></i>
        </a>
    </div>

    <!-- ========== BACK TO TOP ========== -->
    <button type="button" class="back-to-top" id="backToTop" title="Lên đầu trang">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- jQuery CDN (required before vendor.js) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

    <!-- Vendor JS (Swiper, etc.) -->
    @vite('resources/js/web/common/vendor.js')

    @stack('scripts')

    <!-- ========== JAVASCRIPT ========== -->
    <script>
        // Sticky Header Scroll Handler (Desktop)
        const stickyHeaderWrapper = document.getElementById("stickyHeaderWrapper");
        const navbar = document.querySelector(".navbar");

        function handleStickyHeader() {
            if (window.innerWidth > 768) {
                // Desktop behavior
                if (window.scrollY > 50) {
                    stickyHeaderWrapper.classList.add("scrolled");
                } else {
                    stickyHeaderWrapper.classList.remove("scrolled");
                }
                // Remove mobile classes on desktop
                navbar.classList.remove("mobile-scrolled");
                document.body.classList.remove("mobile-scrolled");
            } else {
                // Mobile behavior
                stickyHeaderWrapper.classList.remove("scrolled");

                if (window.scrollY > 10) {
                    // Scrolled down - fix navbar
                    navbar.classList.add("mobile-scrolled");
                    document.body.classList.add("mobile-scrolled");
                } else {
                    // At top - show topbar, navbar normal
                    navbar.classList.remove("mobile-scrolled");
                    document.body.classList.remove("mobile-scrolled");
                }
            }
        }

        window.addEventListener("scroll", handleStickyHeader);
        window.addEventListener("resize", handleStickyHeader);
        handleStickyHeader(); // Initial check

        // Mobile Menu Toggle
        const navToggle = document.getElementById("navToggle");
        const navMenu = document.getElementById("navMenu");
        const navOverlay = document.getElementById("navOverlay");

        function toggleMenu() {
            navMenu.classList.toggle("active");
            navOverlay.classList.toggle("active");
            document.body.style.overflow = navMenu.classList.contains("active") ? "hidden" : "";
        }

        if (navToggle) {
            navToggle.addEventListener("click", toggleMenu);
        }
        if (navOverlay) {
            navOverlay.addEventListener("click", toggleMenu);
        }

        // Handle dropdown toggle on mobile
        navMenu.querySelectorAll(".nav-item.has-dropdown > .nav-link").forEach((link) => {
            link.addEventListener("click", (e) => {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    e.stopPropagation();
                    const parentItem = link.parentElement;
                    // Close other dropdowns
                    navMenu.querySelectorAll(".nav-item.has-dropdown.open").forEach((item) => {
                        if (item !== parentItem) {
                            item.classList.remove("open");
                        }
                    });
                    parentItem.classList.toggle("open");
                }
            });
        });

        // Back to Top Button
        const backToTopBtn = document.getElementById("backToTop");
        const floatingButtons = document.querySelector(".floating-buttons");

        window.addEventListener("scroll", () => {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add("visible");
                // Đẩy floating-buttons lên khi back-to-top xuất hiện
                if (floatingButtons) {
                    floatingButtons.classList.add("pushed-up");
                }
            } else {
                backToTopBtn.classList.remove("visible");
                // Đưa floating-buttons về vị trí dưới cùng
                if (floatingButtons) {
                    floatingButtons.classList.remove("pushed-up");
                }
            }
        });

        backToTopBtn.addEventListener("click", () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]:not(.nav-item.has-dropdown > .nav-link)').forEach((anchor) => {
            anchor.addEventListener("click", function(e) {
                const href = this.getAttribute("href");
                if (href !== "#") {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: "smooth"
                        });
                        // Close mobile menu if open
                        if (navMenu.classList.contains("active")) {
                            navMenu.classList.remove("active");
                            navOverlay.classList.remove("active");
                            document.body.style.overflow = "";
                        }
                    }
                }
            });
        });

        // Language Switcher Toggle
        const langToggle = document.getElementById("langToggle");
        const langSwitcher = langToggle ? langToggle.closest(".lang-switcher") : null;

        if (langToggle && langSwitcher) {
            langToggle.addEventListener("click", (e) => {
                e.stopPropagation();
                langSwitcher.classList.toggle("open");
            });

            // Close when clicking outside
            document.addEventListener("click", (e) => {
                if (!langSwitcher.contains(e.target)) {
                    langSwitcher.classList.remove("open");
                }
            });
        }

        // ========== SCROLL REVEAL ANIMATIONS ==========
        // Intersection Observer for scroll reveal
        const scrollRevealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");
                    // Optional: stop observing after animation (uncomment if needed)
                    // scrollRevealObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1, // Trigger when 10% visible
            rootMargin: "0px 0px -50px 0px" // Trigger slightly before fully in view
        });

        // Auto-apply scroll reveal to common elements
        document.querySelectorAll(`
            .section-header,
            .section-title,
            .info-box,
            .footer-col,
            .service-card,
            .project-card,
            .news-card,
            .about-video,
            .about-content,
            .about-feature,
            .contact-form,
            .contact-info
        `).forEach((el, index) => {
            // Add base class if not already present
            if (!el.classList.contains("scroll-reveal")) {
                el.classList.add("scroll-reveal", "fade-up");
            }
            // Add staggered delay based on position within parent
            const siblings = el.parentElement ? Array.from(el.parentElement.children) : [];
            const siblingIndex = siblings.indexOf(el);
            if (siblingIndex > 0 && siblingIndex <= 5) {
                el.classList.add(`delay-${siblingIndex}`);
            }
        });

        // Start observing all scroll reveal elements
        document.querySelectorAll(".scroll-reveal").forEach(el => {
            scrollRevealObserver.observe(el);
        });
    </script>
</body>

</html>