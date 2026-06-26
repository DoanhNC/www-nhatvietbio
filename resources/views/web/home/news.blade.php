@extends('layouts.web')
@section('title', 'Tin Tức - BMW Technology')
@section('description', 'Tin tức mới nhất về công nghệ BMW và nông nghiệp bền vững')
@section('header_class', 'scrolled')
@section('styles')
<link rel="stylesheet" href="{{ asset('bmw/css/news.css') }}">
@endsection
@section('content')
<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="page-header-content">
            <div class="section-badge green">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2" />
                    <path d="M18 14h-8" />
                    <path d="M15 18h-5" />
                    <path d="M10 6h8v4h-8V6Z" />
                </svg>
                <span data-i18n="newsBadge">Tin Tức</span>
            </div>
            <h1 class="page-title" data-i18n="newsPageTitle">Tất Cả Tin Tức</h1>
            <p class="page-subtitle" data-i18n="newsPageSubtitle">Cập nhật thông tin mới nhất về công nghệ BMW và nông nghiệp bền vững</p>
        </div>
    </div>
</section>

<!-- News List -->
<section class="news-list-section">
    <div class="container">
        <div class="news-list-grid" id="newsListGrid">
            <!-- News items will be rendered here by JavaScript -->
        </div>
    </div>
</section>
@endsection
@section('scripts')
<script src="{{ asset('bmw/js/news.js') }}"></script>
@endsection