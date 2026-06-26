@extends('layouts.web')

@section('title', 'Xử Lý Nước Nhật Việt Biotech - Công Ty Xử Lý Nước Uy Tín')
@section('meta_description', 'Xử Lý Nước Nhật Việt Biotech - Công Ty Xử Lý Nước Uy Tín chuyên cung cấp các dịch vụ tư vấn, thiết kế và thi công hệ thống xử lý nước trên toàn quốc')

@push('styles')
<style>
    /* Services Slider - reuse video slider pattern */
    .services-slider-wrapper {
        position: relative;
        padding: 0 50px;
    }

    .services-swiper {
        overflow: hidden;
    }

    .services-swiper .swiper-slide {
        height: auto;
    }

    .services-swiper .service-card {
        height: 100%;
        margin-bottom: 0;
        /* Reset margin for slider */
        display: flex;
        flex-direction: column;
    }

    .services-swiper-prev,
    .services-swiper-next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        background: var(--color-secondary, #2eb72e);
        color: var(--color-white, #fff);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
    }

    .services-swiper-prev:hover,
    .services-swiper-next:hover {
        background: var(--color-secondary-dark, #259625);
        transform: translateY(-50%) scale(1.1);
    }

    .services-swiper-prev {
        left: 0;
    }

    .services-swiper-next {
        right: 0;
    }

    @media (max-width: 768px) {
        .services-slider-wrapper {
            padding: 0 40px;
        }

        .services-swiper-prev,
        .services-swiper-next {
            width: 32px;
            height: 32px;
        }

        .services-swiper-prev i,
        .services-swiper-next i {
            font-size: 12px;
        }
    }

    /* News Slider - reuse video slider pattern */
    .news-slider-wrapper {
        position: relative;
        padding: 0 50px;
    }

    .news-swiper {
        overflow: hidden;
    }

    .news-swiper .swiper-slide {
        height: auto;
    }

    .news-swiper .news-card-link {
        display: flex;
        height: 100%;
    }

    .news-swiper .news-card {
        width: 100%;
    }

    .news-swiper-prev,
    .news-swiper-next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        background: var(--color-secondary, #2eb72e);
        color: var(--color-white, #fff);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
    }

    .news-swiper-prev:hover,
    .news-swiper-next:hover {
        background: var(--color-secondary-dark, #259625);
        transform: translateY(-50%) scale(1.1);
    }

    .news-swiper-prev {
        left: 0;
    }

    .news-swiper-next {
        right: 0;
    }

    @media (max-width: 768px) {
        .news-slider-wrapper {
            padding: 0 40px;
        }

        .news-swiper-prev,
        .news-swiper-next {
            width: 32px;
            height: 32px;
        }

        .news-swiper-prev i,
        .news-swiper-next i {
            font-size: 12px;
        }
    }
</style>
@endpush

@section('content')
<!-- ========== HERO SLIDER (Swiper) ========== -->
<section class="hero">
    <div class="swiper hero-swiper">
        <div class="swiper-wrapper">
            @forelse($slides as $slide)
            <div class="swiper-slide">
                @if($slide->media)
                <img src="{{ asset($slide->media->url) }}" alt="{{ $slide->title ?? 'Slide' }}">
                @else
                <img src="https://via.placeholder.com/1920x500?text=No+Image" alt="No Image">
                @endif

            </div>
            @empty
            {{-- Fallback nếu không có slide nào --}}
            <div class="swiper-slide">
                <img src="{{ asset('images/web/homes/hero.jpg') }}" alt="Hệ thống lọc nước công nghiệp">

            </div>
            <div class="swiper-slide">
                <img src="{{ asset('images/web/homes/tech-water-treatment.jpg') }}" alt="Xử lý nước thải">

            </div>
            <div class="swiper-slide">
                <img src="{{ asset('images/web/homes/tech-industrial-water.jpg') }}" alt="Máy lọc nước RO công nghiệp">

            </div>
            @endforelse
        </div>
        <!-- Pagination -->
        <div class="swiper-pagination"></div>
        <!-- Progress Bar -->
        <div class="hero-progress">
            <div class="hero-progress-bar"></div>
        </div>
    </div>
</section>

<!-- ========== ABOUT SECTION ========== -->
<section class="section" id="about">
    <div class="container">
        <div class="about">
            <div class="about-video" style="aspect-ratio: 16/9;">
                <iframe
                    width="100%"
                    height="100%"
                    src="https://www.youtube.com/embed/xmG4iGWXBrc"
                    title="Giới thiệu Nhật Việt Biotech - Công nghệ BMW"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    style="border-radius: inherit;"></iframe>
            </div>

            <div class="about-content">
                <h3>{{ __t('about.title', 'Giới Thiệu') }}</h3>
                <h2>{{ __t('about.company_name', 'Công ty TNHH Giải pháp Môi trường Xanh Bền Vững Nhật Việt') }}</h2>
                <p>{{ __t('about.description_1', 'Là doanh nghiệp liên doanh giữa Nhật Bản và Việt Nam, được thành lập nhằm kết hợp công nghệ sinh học tiên tiến từ Nhật Bản với tiềm năng phát triển nông nghiệp xanh tại Việt Nam.') }}</p>
                <p>{{ __t('about.description_2', 'Công ty là thành viên của Hiệp hội Công nghệ BMW Nhật Bản – tổ chức phi lợi nhuận được thành lập từ năm 1990, chuyên nghiên cứu, phát triển và phổ biến công nghệ BMW trong lĩnh vực tái chế bền vững, bảo vệ môi trường và nông nghiệp tuần hoàn.') }}</p>
                <p>{{ __t('about.description_3', 'Mục tiêu của công ty là mang công nghệ BMW Nhật Bản về phổ cập và hướng dẫn để người dân Việt Nam có thể tự áp dụng, góp phần giải quyết các vấn đề về môi trường nông thôn và sản xuất nông nghiệp tại Việt Nam.') }}</p>
                <p>{{ __t('about.description_4', 'Chúng tôi tin tưởng rằng, bằng sự đổi mới không ngừng và trách nhiệm xã hội sâu sắc, Công ty TNHH Giải pháp Môi trường Xanh Bền Vững Nhật Việt sẽ góp phần kiến tạo nền tảng vững chắc cho một tương lai xanh – sạch – thịnh vượng.') }}</p>

                <div class="about-features">
                    <div class="about-feature">
                        <div class="about-feature-icon" style="color: #DAA520;">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4>{{ __t('about.feature_1', 'Liên Doanh Nhật-Việt') }}</h4>
                    </div>
                    <div class="about-feature">
                        <div class="about-feature-icon" style="color: #DC3545;">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h4>{{ __t('about.feature_2', 'Hiệp Hội BMW Nhật Bản') }}</h4>
                    </div>
                    <div class="about-feature">
                        <div class="about-feature-icon" style="color: #2eb72e;">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h4>{{ __t('about.feature_3', 'Công Nghệ Xanh') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== SERVICES SECTION ========== -->
<section class="section section-bg-gray" id="services">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">{{ __t('services.title', 'Ứng Dụng Công Nghệ BMW') }}</h2>
            <p class="section-subtitle">{{ __t('services.subtitle', 'Nước sinh học đa năng BMW được ứng dụng rộng rãi trong nhiều lĩnh vực, mang lại hiệu quả cao và thân thiện môi trường') }}</p>
        </div>

        <div class="services-slider-wrapper">
            <div class="swiper services-swiper">
                <div class="swiper-wrapper">
                    @if(isset($applicationCategories) && $applicationCategories->count() > 0)
                    {{-- Dynamic application categories from database --}}
                    @foreach($applicationCategories as $appCategory)
                    <div class="swiper-slide">
                        <div class="service-card">
                            @php
                            // Get latest post from this category (newest first)
                            $latestPost = \App\Models\EPost::where('main_category_id', $appCategory->id)
                            ->where('status', 1)
                            ->orderBy('created_at', 'desc')
                            ->first();
                            @endphp
                            @if($latestPost && $latestPost->main_image)
                            <div class="service-image">
                                <img src="{{ asset($latestPost->main_image) }}" alt="{{ $appCategory->getName($currentLang ?? 'vi') }}">
                            </div>
                            @else
                            <div class="service-icon"><i class="fas fa-leaf"></i></div>
                            @endif
                            <h3>{{ $appCategory->getName($currentLang ?? 'vi') }}</h3>
                            @if($latestPost)
                            <p>{{ Str::limit(strip_tags(html_entity_decode($latestPost->getShortDescription($currentLang ?? 'vi'))), 100) }}</p>
                            @else
                            <p>{{ Str::limit($appCategory->description ?? '', 100) }}</p>
                            @endif
                            <a href="{{ url('/category/' . $appCategory->slug) }}" class="service-link">{{ __t('services.detail', 'Chi tiết') }} <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    @endforeach
                    @else
                    {{-- Static fallback content - BMW Applications --}}
                    <div class="swiper-slide">
                        <div class="service-card">
                            <div class="service-icon"><i class="fas fa-seedling"></i></div>
                            <h3>{{ __t('services.crop_title', 'Trồng Trọt') }}</h3>
                            <p>{{ __t('services.crop_desc', 'Kích thích nảy mầm, ra rễ, thúc đẩy sinh trưởng, nâng cao năng suất và chất lượng nông sản') }}</p>
                            <a href="/category/trong-trot" class="service-link">{{ __t('services.detail', 'Chi tiết') }} <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="service-card">
                            <div class="service-icon"><i class="fas fa-piggy-bank"></i></div>
                            <h3>{{ __t('services.livestock_title', 'Chăn Nuôi') }}</h3>
                            <p>{{ __t('services.livestock_desc', 'Tăng cường tiêu hóa, miễn dịch, cải thiện FCR, nâng cao chất lượng thịt và năng suất sữa') }}</p>
                            <a href="/category/chan-nuoi" class="service-link">{{ __t('services.detail', 'Chi tiết') }} <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="service-card">
                            <div class="service-icon"><i class="fas fa-fish"></i></div>
                            <h3>{{ __t('services.aquaculture_title', 'Thủy Hải Sản') }}</h3>
                            <p>{{ __t('services.aquaculture_desc', 'Ổn định chất lượng nước, ức chế vi khuẩn gây bệnh, giảm chi phí xử lý môi trường') }}</p>
                            <a href="/category/thuy-hai-san" class="service-link">{{ __t('services.detail', 'Chi tiết') }} <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="service-card">
                            <div class="service-icon"><i class="fas fa-recycle"></i></div>
                            <h3>{{ __t('services.environment_title', 'Xử Lý Môi Trường') }}</h3>
                            <p>{{ __t('services.environment_desc', 'Xử lý bể phốt, ủ phân, rác thải sinh hoạt, cống rãnh và chất đệm lót vật nuôi') }}</p>
                            <a href="/category/xu-ly-moi-truong" class="service-link">{{ __t('services.detail', 'Chi tiết') }} <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="service-card">
                            <div class="service-icon"><i class="fas fa-home"></i></div>
                            <h3>{{ __t('services.lifestyle_title', 'Đời Sống') }}</h3>
                            <p>{{ __t('services.lifestyle_desc', 'Khử mùi thú cưng, làm sạch bể cá, cắm hoa, chăm sóc cây cảnh, khử khuẩn nhà bếp') }}</p>
                            <a href="/category/doi-song" class="service-link">{{ __t('services.detail', 'Chi tiết') }} <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <!-- Navigation arrows -->
            <div class="services-swiper-prev"><i class="fas fa-chevron-left"></i></div>
            <div class="services-swiper-next"><i class="fas fa-chevron-right"></i></div>
        </div>
    </div>
</section>

{{--
<!-- ========== PROJECTS SECTION ========== -->
<section class="section" id="projects">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">{{ __t('projects.title', 'Dự Án Tiêu Biểu') }}</h2>
<p class="section-subtitle">{{ __t('projects.subtitle', 'Các dự án xử lý nước đã được chúng tôi triển khai thành công') }}</p>
</div>

<div class="projects-grid">
    <div class="project-card">
        <img src="{{ asset('images/web/homes/use-case-irrigation.jpg') }}" alt="Dự án 1">
        <div class="project-overlay">
            <h3>Hệ Thống Lọc Nước 50m³/h</h3>
            <p>KCN Trà Nóc, Cần Thơ</p>
        </div>
    </div>
    <div class="project-card">
        <img src="{{ asset('images/web/homes/use-case-greenhouse.jpg') }}" alt="Dự án 2">
        <div class="project-overlay">
            <h3>Xử Lý Nước Thải 200m³/ngày</h3>
            <p>Nhà máy thủy sản, Sóc Trăng</p>
        </div>
    </div>
    <div class="project-card">
        <img src="{{ asset('images/web/homes/use-case-sustainable.jpg') }}" alt="Dự án 3">
        <div class="project-overlay">
            <h3>Hệ Thống RO 10m³/h</h3>
            <p>Bệnh viện đa khoa, Long An</p>
        </div>
    </div>
</div>
</div>
</section>
--}}

<!-- ========== VIDEO YOUTUBE SECTION ========== -->
@if(isset($videos) && $videos->count() > 0)
<section class="section section-bg-gray" id="videos">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">{{ __t('videos.title', 'Thư Viện Video') }}</h2>
            <p class="section-subtitle">{{ __t('videos.subtitle', 'Khám phá các video về hoạt động, sản phẩm và dự án của chúng tôi') }}</p>
        </div>

        <div class="video-slider-wrapper">
            <div class="swiper video-swiper">
                <div class="swiper-wrapper">
                    @foreach($videos as $video)
                    <div class="swiper-slide">
                        <div class="video-card" data-youtube-id="{{ $video->youtube_id }}" data-title="{{ $video->title }}">
                            <div class="video-thumbnail">
                                <img src="{{ $video->thumbnail }}" alt="{{ $video->title ?? 'Video' }}">
                                <div class="video-play-overlay">
                                    <i class="fab fa-youtube"></i>
                                </div>
                            </div>
                            @if($video->title)
                            <div class="video-title">{{ $video->title }}</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <!-- Navigation arrows -->
            <div class="video-swiper-prev"><i class="fas fa-chevron-left"></i></div>
            <div class="video-swiper-next"><i class="fas fa-chevron-right"></i></div>
        </div>
    </div>
</section>

<!-- Video Modal -->
<div class="video-modal" id="videoModal">
    <div class="video-modal-backdrop"></div>
    <div class="video-modal-content">
        <button class="video-modal-close" id="videoModalClose">&times;</button>
        <div class="video-modal-iframe-wrapper">
            <iframe id="videoModalIframe" src="" frameborder="0" allowfullscreen
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
            </iframe>
        </div>
    </div>
</div>
@endif

<!-- ========== NEWS SECTION (Swiper Slider) ========== -->
<section class="section section-bg-gray" id="news">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">{{ __t('news.title', 'Tin Tức & Sự Kiện') }}</h2>
            <p class="section-subtitle">{{ __t('news.subtitle', 'Cập nhật những thông tin mới nhất về lĩnh vực xử lý nước') }}</p>
        </div>

        @if($latestNews->count() > 0)
        <div class="news-slider-wrapper">
            <div class="swiper news-swiper">
                <div class="swiper-wrapper">
                    @foreach($latestNews as $news)
                    <div class="swiper-slide">
                        <a href="{{ url('/news/' . $news->slug) }}" class="news-card-link">
                            <article class="news-card">
                                <div class="news-image">
                                    @if($news->main_image)
                                    <img src="{{ asset($news->main_image) }}" alt="{{ $news->getTitle($currentLang ?? 'vi') }}">
                                    @else
                                    <img src="{{ asset('images/web/homes/app-aquaculture-new.png') }}" alt="{{ $news->getTitle($currentLang ?? 'vi') }}">
                                    @endif
                                    @if($news->mainCategory)
                                    <span class="news-category">{{ $news->mainCategory->getName($currentLang ?? 'vi') }}</span>
                                    @endif
                                </div>
                                <div class="news-content">
                                    <div class="news-date">
                                        <i class="far fa-calendar-alt"></i>
                                        <span>{{ $news->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    <h3>{{ $news->getTitle($currentLang ?? 'vi') }}</h3>
                                    <p class="news-excerpt">{{ Str::limit(strip_tags(html_entity_decode($news->getShortDescription($currentLang ?? 'vi'))), 100) }}</p>
                                </div>
                            </article>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            <!-- Navigation arrows -->
            <div class="news-swiper-prev"><i class="fas fa-chevron-left"></i></div>
            <div class="news-swiper-next"><i class="fas fa-chevron-right"></i></div>
        </div>
        @else
        <div class="text-center" style="padding: 40px 20px; color: #999;">
            <i class="far fa-newspaper" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
            <p style="margin: 0; font-size: 16px;">-</p>
        </div>
        @endif
    </div>
</section>

<!-- ========== PARTNERS SECTION ========== -->
<section class="partners-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">{{ __t('partners.title', 'ĐƠN VỊ HỢP TÁC') }}</h2>
            <p class="section-subtitle">{{ __t('partners.subtitle', 'Chúng tôi tự hào là đối tác tin cậy của các đơn vị uy tín trong ngành') }}</p>
        </div>
    </div>
    
    <div class="partners-track">
        <div class="partner-group">
            <div class="partner-item"><img src="https://anviet-group.vn/wp-content/themes/anvietgroup/images/logo.svg" alt="An Viet Group"></div>
            <div class="partner-item"><img src="https://anviet-group.vn/wp-content/uploads/2025/07/481682570_649873334220743_4454488163653540504_n-1.jpg" alt="Trạm Xanh"></div>
            <div class="partner-item"><img src="https://anviet-group.vn/wp-content/uploads/2021/07/anvita-1-1024x576.jpg" alt="Anvita"></div>
            <!-- Lặp lại để slide dài hơn -->
            <div class="partner-item"><img src="https://anviet-group.vn/wp-content/themes/anvietgroup/images/logo.svg" alt="An Viet Group"></div>
            <div class="partner-item"><img src="https://anviet-group.vn/wp-content/uploads/2025/07/481682570_649873334220743_4454488163653540504_n-1.jpg" alt="Trạm Xanh"></div>
            <div class="partner-item"><img src="https://anviet-group.vn/wp-content/uploads/2021/07/anvita-1-1024x576.jpg" alt="Anvita"></div>
        </div><div class="partner-group">
            <div class="partner-item"><img src="https://anviet-group.vn/wp-content/themes/anvietgroup/images/logo.svg" alt="An Viet Group"></div>
            <div class="partner-item"><img src="https://anviet-group.vn/wp-content/uploads/2025/07/481682570_649873334220743_4454488163653540504_n-1.jpg" alt="Trạm Xanh"></div>
            <div class="partner-item"><img src="https://anviet-group.vn/wp-content/uploads/2021/07/anvita-1-1024x576.jpg" alt="Anvita"></div>
            <!-- Lặp lại để slide dài hơn -->
            <div class="partner-item"><img src="https://anviet-group.vn/wp-content/themes/anvietgroup/images/logo.svg" alt="An Viet Group"></div>
            <div class="partner-item"><img src="https://anviet-group.vn/wp-content/uploads/2025/07/481682570_649873334220743_4454488163653540504_n-1.jpg" alt="Trạm Xanh"></div>
            <div class="partner-item"><img src="https://anviet-group.vn/wp-content/uploads/2021/07/anvita-1-1024x576.jpg" alt="Anvita"></div>
        </div>
    </div>
</section>

<!-- ========== CONTACT SECTION ========== -->
@php
// Lấy thông tin website từ ESetting (sử dụng lại logic từ layout)
$contactInfo = \App\Models\ESetting::getWebsiteInfo();
$contactAddress = $contactInfo['address'] ?? '-';
$contactHotline = $contactInfo['hotline'] ?? '-';
$contactEmail = $contactInfo['email'] ?? '-';
$contactHotlineClean = $contactHotline !== '-' ? preg_replace('/\s+/', '', $contactHotline) : '';
$contactMapEmbed = $contactInfo['map_embed'] ?? '';
@endphp
<section class="section" id="contact">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">{{ __t('contact.title', 'Liên Hệ Với Chúng Tôi') }}</h2>
            <p class="section-subtitle">{{ __t('contact.subtitle', 'Hãy để lại thông tin, chúng tôi sẽ liên hệ tư vấn miễn phí') }}</p>
        </div>

        <div class="about" style="gap: 2rem">
            <div class="about-content">
                <h3>{{ __t('contact.info_title', 'Thông Tin Liên Hệ') }}</h3>
                <div style="margin-top: 1.5rem">
                    <div class="footer-contact-item">
                        <i class="fas fa-map-marker-alt" style="color: var(--color-green)"></i>
                        <div>
                            <strong>{{ __t('contact.address', 'Trụ sở chính') }}:</strong><br>
                            {{ $contactAddress }}
                        </div>
                    </div>
                    <div class="footer-contact-item" style="margin-top: 1rem">
                        <i class="fas fa-phone-alt" style="color: var(--color-green)"></i>
                        <div>
                            <strong>{{ __t('contact.hotline', 'Hotline') }}:</strong><br>
                            <a href="tel:{{ $contactHotlineClean }}" style="color: var(--color-red); font-weight: 600;">{{ $contactHotline }}</a>
                        </div>
                    </div>
                    <div class="footer-contact-item" style="margin-top: 1rem">
                        <i class="fas fa-envelope" style="color: var(--color-green)"></i>
                        <div>
                            <strong>{{ __t('contact.email', 'Email') }}:</strong><br>
                            <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="about-video" style="box-shadow: none">
                @if($contactMapEmbed)
                <iframe src="{{ $contactMapEmbed }}" width="100%" height="300" style="border: 0; border-radius: 12px" allowfullscreen="" loading="lazy"></iframe>
                @else
                <div style="width: 100%; height: 300px; background: #f5f5f5; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #999;">
                    <div class="text-center">
                        <i class="fas fa-map-marker-alt" style="font-size: 48px; margin-bottom: 16px;"></i>
                        <p style="margin: 0;">Chưa cấu hình Google Maps</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Initialize Hero Swiper with Progress Bar
    document.addEventListener('DOMContentLoaded', function() {
        if (window.Swiper) {
            const progressBar = document.querySelector('.hero-progress-bar');
            const heroSection = document.querySelector('.hero');
            const autoplayDelay = 5000;
            let startTime = Date.now();
            let pausedTime = 0;
            let isPaused = false;

            // Function to start/reset progress animation
            function startProgress() {
                if (progressBar) {
                    startTime = Date.now();
                    pausedTime = 0;
                    isPaused = false;
                    progressBar.style.transition = 'none';
                    progressBar.style.width = '0%';
                    // Force reflow
                    progressBar.offsetWidth;
                    progressBar.style.transition = `width ${autoplayDelay}ms linear`;
                    progressBar.style.width = '100%';
                }
            }

            // Function to pause progress
            function pauseProgress() {
                if (progressBar && !isPaused) {
                    isPaused = true;
                    pausedTime = Date.now() - startTime;
                    const currentPercent = (pausedTime / autoplayDelay) * 100;
                    progressBar.style.transition = 'none';
                    progressBar.style.width = currentPercent + '%';
                }
            }

            // Function to resume progress
            function resumeProgress() {
                if (progressBar && isPaused) {
                    isPaused = false;
                    const remainingTime = autoplayDelay - pausedTime;
                    if (remainingTime > 0) {
                        startTime = Date.now() - pausedTime;
                        progressBar.style.transition = `width ${remainingTime}ms linear`;
                        progressBar.style.width = '100%';
                    }
                }
            }

            const heroSwiper = new Swiper('.hero-swiper', {
                modules: [
                    window.SwiperModules.Pagination,
                    window.SwiperModules.Autoplay,
                    window.SwiperModules.EffectFade
                ],
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                loop: true,
                speed: 800,
                autoplay: {
                    delay: autoplayDelay,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true
                },
                pagination: {
                    el: '.hero-swiper .swiper-pagination',
                    clickable: true,
                    dynamicBullets: true
                },
                keyboard: {
                    enabled: true
                },
                grabCursor: true,
                on: {
                    init: function() {
                        startProgress();
                    },
                    slideChange: function() {
                        startProgress();
                    },
                    autoplayPause: function() {
                        pauseProgress();
                    },
                    autoplayResume: function() {
                        resumeProgress();
                    }
                }
            });

            // Additional hover handlers for the entire hero section
            if (heroSection) {
                heroSection.addEventListener('mouseenter', function() {
                    pauseProgress();
                });
                heroSection.addEventListener('mouseleave', function() {
                    resumeProgress();
                });
            }
        }

        // ========== VIDEO YOUTUBE SLIDER ==========
        const videoSwiperEl = document.querySelector('.video-swiper');
        if (videoSwiperEl && window.Swiper) {
            const videoSwiper = new Swiper('.video-swiper', {
                modules: [
                    window.SwiperModules.Navigation,
                    window.SwiperModules.Autoplay
                ],
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 20,
                loop: true,
                speed: 500,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true
                },
                navigation: {
                    nextEl: '.video-swiper-next',
                    prevEl: '.video-swiper-prev',
                },
                breakpoints: {
                    // Mobile: 1 video
                    0: {
                        slidesPerView: 1,
                        spaceBetween: 15
                    },
                    // Tablet: 2 videos
                    576: {
                        slidesPerView: 2,
                        spaceBetween: 20
                    },
                    // Desktop: 3 videos
                    769: {
                        slidesPerView: 3,
                        spaceBetween: 20
                    }
                }
            });
        }

        // ========== SERVICES SLIDER ==========
        const servicesSwiperEl = document.querySelector('.services-swiper');
        if (servicesSwiperEl && window.Swiper) {
            const servicesSwiper = new Swiper('.services-swiper', {
                modules: [
                    window.SwiperModules.Navigation,
                    window.SwiperModules.Autoplay
                ],
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 30,
                loop: true,
                speed: 500,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true
                },
                navigation: {
                    nextEl: '.services-swiper-next',
                    prevEl: '.services-swiper-prev',
                },
                breakpoints: {
                    // Mobile: 1 card
                    0: {
                        slidesPerView: 1,
                        spaceBetween: 15
                    },
                    // Tablet: 2 cards
                    576: {
                        slidesPerView: 2,
                        spaceBetween: 20
                    },
                    // Desktop: 3 cards
                    992: {
                        slidesPerView: 3,
                        spaceBetween: 30
                    }
                }
            });
        }

        // ========== NEWS SLIDER ==========
        const newsSwiperEl = document.querySelector('.news-swiper');
        if (newsSwiperEl && window.Swiper) {
            const newsSwiper = new Swiper('.news-swiper', {
                modules: [
                    window.SwiperModules.Navigation,
                    window.SwiperModules.Autoplay
                ],
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 20,
                loop: true,
                speed: 500,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true
                },
                navigation: {
                    nextEl: '.news-swiper-next',
                    prevEl: '.news-swiper-prev',
                },
                breakpoints: {
                    // Mobile: 1 card
                    0: {
                        slidesPerView: 1,
                        spaceBetween: 15
                    },
                    // Tablet: 2 cards
                    576: {
                        slidesPerView: 2,
                        spaceBetween: 20
                    },
                    // Desktop: 3 cards
                    769: {
                        slidesPerView: 3,
                        spaceBetween: 20
                    }
                }
            });
        }

        // ========== VIDEO MODAL ==========
        const videoModal = document.getElementById('videoModal');
        const videoModalIframe = document.getElementById('videoModalIframe');
        const videoModalClose = document.getElementById('videoModalClose');
        const videoModalBackdrop = videoModal ? videoModal.querySelector('.video-modal-backdrop') : null;

        // Open modal when clicking video card
        document.querySelectorAll('.video-card').forEach(function(card) {
            card.addEventListener('click', function() {
                const youtubeId = this.dataset.youtubeId;
                if (youtubeId && videoModal && videoModalIframe) {
                    videoModalIframe.src = 'https://www.youtube.com/embed/' + youtubeId + '?autoplay=1';
                    videoModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            });
        });

        // Close modal function
        function closeVideoModal() {
            if (videoModal && videoModalIframe) {
                videoModal.classList.remove('active');
                videoModalIframe.src = '';
                document.body.style.overflow = '';
            }
        }

        // Close on X button
        if (videoModalClose) {
            videoModalClose.addEventListener('click', closeVideoModal);
        }

        // Close on backdrop click
        if (videoModalBackdrop) {
            videoModalBackdrop.addEventListener('click', closeVideoModal);
        }

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && videoModal && videoModal.classList.contains('active')) {
                closeVideoModal();
            }
        });
    });
</script>
@endpush