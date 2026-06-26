@extends('layouts.web')
@section('title', $post->getSeoTitle() ?: $post->getTitle($currentLang) . ' - BMW Technology')
@section('description', $post->getSeoDescription() ?: $post->getShortDescription($currentLang))
@section('header_class', 'scrolled')
@push('styles')
<link rel="stylesheet" href="{{ asset('bmw/css/news.css') }}">
<style>
    /* ========== Page Layout ========== */
    .news-detail {
        background: #f8f9fa;
        padding: 0;
    }

    /* Grid Layout 9-3 */
    .post-detail-wrapper {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 30px;
        padding: 30px 0 50px;
        align-items: flex-start;
    }

    .post-detail-main {
        min-width: 0;
    }

    .post-detail-sidebar {
        position: sticky;
        top: 160px;
        align-self: start;
    }

    .post-detail-wrapper.no-sidebar {
        grid-template-columns: 1fr;
    }

    .post-detail-wrapper.no-sidebar .post-detail-main {
        max-width: 900px;
        margin: 0 auto;
    }

    .post-detail-wrapper.no-sidebar .post-detail-sidebar {
        display: none !important;
    }

    /* ========== Breadcrumb ========== */
    .breadcrumb-section {
        background-color: var(--color-primary-dark);
        padding: 20px 0;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0;
        padding: 0;
        background: transparent;
    }

    .breadcrumb a,
    .breadcrumb span {
        color: rgba(255, 255, 255, 0.85);
        font-size: 14px;
    }

    .breadcrumb a:hover {
        color: #fff;
    }

    .breadcrumb i {
        color: rgba(255, 255, 255, 0.5);
        flex-shrink: 0;
        font-size: 12px;
    }

    .breadcrumb i.fa-house {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.85);
    }

    .breadcrumb span:last-child {
        color: #fff;
        font-weight: 500;
    }

    /* Mobile breadcrumb adjustments */
    @media (max-width: 768px) {
        .breadcrumb-section {
            padding: 15px 0;
        }

        .breadcrumb {
            flex-wrap: nowrap;
            overflow: hidden;
        }

        .breadcrumb a,
        .breadcrumb span {
            font-size: 13px;
        }

        /* Ưu tiên không gian cho Home và Category */
        .breadcrumb a {
            flex-shrink: 0;
            white-space: nowrap;
        }

        .breadcrumb i {
            flex-shrink: 0;
        }

        /* Tiêu đề bài viết co lại và hiển thị ... */
        .breadcrumb span:last-child {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    }

    /* ========== Article Card ========== */
    .article-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        overflow: visible;
        margin-bottom: 24px;
    }

    .article-card-body {
        padding: 30px;
    }

    /* ========== Article Header ========== */
    .article-header {
        margin-bottom: 24px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }

    .article-category-badge {
        display: inline-block;
        background: linear-gradient(135deg, var(--color-green) 0%, var(--color-green-dark) 100%);
        color: #fff;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }

    .article-title {
        font-size: 28px;
        font-weight: 700;
        color: var(--color-primary);
        line-height: 1.3;
        margin: 0 0 16px 0;
    }

    .article-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        color: #666;
        font-size: 14px;
    }

    .article-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .article-meta-item svg {
        color: var(--color-green);
    }

    /* ========== Article Excerpt ========== */
    .article-excerpt {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        border-left: 4px solid var(--color-green);
        padding: 20px 24px;
        border-radius: 0 8px 8px 0;
        margin-bottom: 24px;
    }

    .article-excerpt p {
        margin: 0;
        font-size: 16px;
        line-height: 1.7;
        color: #2e7d32;
        font-style: italic;
    }

    /* ========== Featured Image ========== */
    .article-featured-image {
        margin: 0 -30px 24px;
        overflow: hidden;
    }

    .article-featured-image img {
        width: 100%;
        height: auto;
        display: block;
    }

    /* ========== Album Images ========== */
    .article-album {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .article-album img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .article-album img:hover {
        transform: scale(1.02);
    }

    /* ========== Table of Contents ========== */
    .toc-card {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border: none;
        border-left: 4px solid #1976d2;
        border-radius: 0 12px 12px 0;
        margin-bottom: 24px;
    }

    .toc-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 20px;
        border-bottom: 1px solid rgba(25, 118, 210, 0.2);
    }

    .toc-header i {
        font-size: 18px;
        color: #1976d2;
    }

    .toc-header h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #1565c0;
    }

    .toc-body {
        padding: 16px 20px;
    }

    .toc-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .toc-list li {
        margin: 8px 0;
    }

    .toc-list a {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #1565c0;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .toc-list a:before {
        content: '•';
        color: #1976d2;
    }

    .toc-list a:hover {
        color: #0d47a1;
        padding-left: 8px;
    }

    .toc-level-3 {
        padding-left: 20px;
    }

    .toc-level-4 {
        padding-left: 40px;
    }

    /* ========== Article Content ========== */
    .article-content {
        font-size: 16px;
        line-height: 1.8;
        color: #333;
    }

    .article-content h2 {
        font-size: 24px;
        font-weight: 700;
        color: var(--color-primary);
        margin: 32px 0 16px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--color-green);
    }

    .article-content h3 {
        font-size: 20px;
        font-weight: 600;
        color: var(--color-primary);
        margin: 24px 0 12px;
    }

    .article-content p {
        margin-bottom: 16px;
    }

    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 16px 0;
    }

    .article-content ul,
    .article-content ol {
        padding-left: 24px;
        margin-bottom: 16px;
    }

    .article-content li {
        margin-bottom: 8px;
    }

    .article-content blockquote {
        background: #f5f5f5;
        border-left: 4px solid var(--color-green);
        padding: 16px 20px;
        margin: 20px 0;
        border-radius: 0 8px 8px 0;
        font-style: italic;
        color: #555;
    }

    /* ========== Article Footer ========== */
    .article-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 24px;
        margin-top: 24px;
        border-top: 1px solid #eee;
    }

    .share-section {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .share-section span {
        font-weight: 500;
        color: #666;
    }

    .share-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: #fff;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .share-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .share-btn.facebook {
        background: #1877f2;
    }

    .share-btn.twitter {
        background: #1da1f2;
    }

    .share-btn.zalo {
        background: #0068ff;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #f5f5f5;
        color: #666;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .back-btn:hover {
        background: var(--color-primary);
        color: #fff;
    }

    /* ========== Sidebar - Bài Viết Mới Nhất ========== */
    .sidebar-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 20px;
        padding: 20px;
    }

    .sidebar-card-header {
        background: transparent;
        color: var(--color-primary);
        padding: 0 0 16px 0;
        margin-bottom: 8px;
        border-bottom: 2px solid var(--color-green);
    }

    .sidebar-card-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--color-primary);
    }

    /* Sidebar Related Posts List */
    .sidebar-posts-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .sidebar-posts-list li {
        margin-bottom: 12px;
    }

    .sidebar-posts-list li:last-child {
        margin-bottom: 0;
    }

    .sidebar-post-item {
        display: flex;
        gap: 14px;
        padding: 0;
        border-radius: 8px;
        border-bottom: none;
        margin-bottom: 14px;
        transition: all 0.25s ease;
    }

    .sidebar-post-item:last-child {
        margin-bottom: 0;
    }

    .sidebar-post-item:hover {
        background: linear-gradient(135deg, #f8f9fa 0%, #e8f5e9 100%);
    }

    .sidebar-post-thumb {
        width: 75px;
        height: 55px;
        flex-shrink: 0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .sidebar-post-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }



    .sidebar-post-content {
        flex: 1;
        min-width: 0;
        display: flex;
        align-items: center;
    }

    .sidebar-post-title {
        font-size: 14px;
        font-weight: 500;
        color: #444;
        line-height: 1.5;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color 0.2s ease;
    }

    .sidebar-post-item:hover .sidebar-post-title {
        color: var(--color-primary) !important;
    }

    /* ========== Empty State ========== */
    .sidebar-empty-state {
        padding: 24px 16px;
        text-align: center;
        background: linear-gradient(135deg, #fafbfc 0%, #f0f7ff 100%);
        border-radius: 10px;
        margin: 12px;
        border: 1px dashed #d0e3f5;
    }

    .sidebar-empty-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        box-shadow: 0 4px 12px rgba(0, 101, 69, 0.2);
    }

    @keyframes pulse-icon {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }
    }

    .sidebar-empty-text {
        font-size: 14px;
        color: #666;
        margin: 0 0 20px;
        line-height: 1.6;
    }

    .sidebar-explore-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        border-radius: 25px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 101, 69, 0.3);
    }

    .sidebar-explore-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 101, 69, 0.4);
        color: #fff;
    }

    .sidebar-explore-btn i {
        font-size: 16px;
        animation: spin-slow 4s linear infinite;
    }

    @keyframes spin-slow {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    /* ========== Responsive ========== */
    @media (max-width: 991px) {
        .post-detail-wrapper {
            grid-template-columns: 1fr;
        }

        .post-detail-main {
            max-width: 100%;
            width: 100%;
        }

        .post-detail-sidebar {
            order: 2;
            position: relative;
            top: 0;
            margin-top: 30px;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .article-title {
            font-size: 24px;
        }
    }

    @media (max-width: 576px) {
        .article-card-body {
            padding: 20px;
        }

        .article-featured-image {
            margin: 0 -20px 20px;
        }

        .article-title {
            font-size: 20px;
        }

        .article-meta-row {
            gap: 12px;
        }

        .article-actions {
            flex-direction: column;
            gap: 16px;
        }

        .sidebar-post-thumb {
            width: 70px;
            height: 50px;
        }
    }

    /* ========== Post Contact Section ========== */
    .post-contact-section {
        padding: 60px 0;
        background: #fff;
        border-top: 1px solid #eee;
    }

    .post-contact-title {
        text-align: center;
        margin-bottom: 40px;
    }

    .post-contact-title h2 {
        font-size: 32px;
        font-weight: 700;
        color: var(--color-primary);
        text-transform: uppercase;
        margin: 0;
        position: relative;
        display: inline-block;
        padding-bottom: 10px;
    }

    .post-contact-title h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: var(--color-secondary);
    }

    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        align-items: stretch;
    }

    .contact-map {
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        height: 100%;
        min-height: 400px;
    }

    .contact-map iframe {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
    }

    .contact-form-card {
        background: #fff;
        padding: 0;
        border-radius: 12px;
    }

    .contact-form .form-group {
        margin-bottom: 20px;
    }

    .contact-form .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .contact-form input,
    .contact-form textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #e0e0e0;
        border-radius: 2px;
        font-size: 15px;
        color: #333;
        transition: all 0.3s;
        background: #fff;
    }

    .contact-form input:focus,
    .contact-form textarea:focus {
        border-color: var(--color-secondary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(46, 183, 46, 0.1);
    }

    .contact-form input::placeholder,
    .contact-form textarea::placeholder {
        color: #999;
        font-style: italic;
    }

    .contact-form-submit {
        text-align: center;
        margin-top: 30px;
    }

    .contact-form-submit button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: transparent;
        color: var(--color-secondary);
        border: 1px solid #cfcfcf;
        padding: 12px 60px;
        font-size: 16px;
        font-weight: 600;
        text-transform: uppercase;
        border-radius: 0;
        transition: all 0.3s;
        cursor: pointer;
        position: relative;
        min-width: 200px;
    }

    .contact-form-submit button:hover {
        background: var(--color-secondary);
        color: #fff;
        border-color: var(--color-secondary);
    }

    .contact-form-submit button i {
        font-size: 18px;
    }

    @media (max-width: 991px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }

        .contact-map {
            height: 350px;
            min-height: 350px;
            order: 2;
        }

        .contact-form-card {
            order: 1;
        }
    }

    @media (max-width: 576px) {
        .contact-form .form-row {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }

    /* ========== Contact Alerts ========== */
    .contact-alert {
        padding: 12px 16px;
        border-radius: 4px;
        margin-bottom: 20px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .contact-alert i {
        font-size: 18px;
        flex-shrink: 0;
    }

    .contact-alert-success {
        background: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
    }

    .contact-alert-error {
        background: #fbe9e7;
        color: #c62828;
        border: 1px solid #ffcdd2;
    }

    /* ========== Video Section ========== */
    .video-section {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    }

    .video-section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .video-section-header i {
        font-size: 24px;
        color: #ff6b6b;
        animation: pulse-video 2s infinite;
    }

    @keyframes pulse-video {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    .video-section-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .video-main-wrapper {
        position: relative;
        width: 100%;
        padding-bottom: 56.25%;
        /* 16:9 aspect ratio */
        border-radius: 12px;
        overflow: hidden;
        background: #000;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
    }

    .video-main-wrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }

    .video-thumbnails {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .video-thumb-item {
        position: relative;
        cursor: pointer;
        border-radius: 8px;
        overflow: hidden;
        aspect-ratio: 16/9;
        background: #2a2a4a;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .video-thumb-item:hover {
        transform: translateY(-4px);
        border-color: #ff6b6b;
        box-shadow: 0 8px 24px rgba(255, 107, 107, 0.3);
    }

    .video-thumb-item.active {
        border-color: #ff6b6b;
        box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.3);
    }

    .video-thumb-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .video-thumb-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.3s ease;
    }

    .video-thumb-item:hover .video-thumb-overlay {
        background: rgba(0, 0, 0, 0.2);
    }

    .video-thumb-overlay i {
        font-size: 32px;
        color: #fff;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
    }

    .video-thumb-number {
        position: absolute;
        top: 8px;
        left: 8px;
        background: rgba(255, 107, 107, 0.9);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 4px;
    }

    /* Single video - no thumbnails needed */
    .video-section.single-video .video-thumbnails {
        display: none;
    }

    @media (max-width: 576px) {
        .video-section {
            padding: 16px;
            margin: 0 -20px 24px;
            border-radius: 0;
        }

        .video-section-header h3 {
            font-size: 16px;
        }

        .video-thumbnails {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
    }

    /* ========== Attachments Section ========== */
    .attachments-section {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        border: 1px solid #e2e8f0;
    }

    .attachments-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--color-green);
    }

    .attachments-header i {
        font-size: 22px;
        color: var(--color-green);
    }

    .attachments-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: var(--color-primary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .attachments-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .attachment-item {
        display: flex;
        align-items: center;
        gap: 14px;
        background: #fff;
        padding: 14px 18px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }

    .attachment-item:hover {
        border-color: var(--color-green);
        box-shadow: 0 4px 16px rgba(0, 101, 69, 0.15);
        transform: translateY(-2px);
    }

    .attachment-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .attachment-icon.excel {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        color: #fff;
    }

    .attachment-icon.pdf {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: #fff;
    }

    .attachment-icon.word {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: #fff;
    }

    .attachment-icon.image {
        background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);
        color: #fff;
    }

    .attachment-icon.default {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        color: #fff;
    }

    .attachment-info {
        flex: 1;
        min-width: 0;
    }

    .attachment-name {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 4px 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .attachment-meta {
        font-size: 12px;
        color: #64748b;
    }

    .attachment-download {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: var(--color-green);
        color: #fff;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .attachment-item:hover .attachment-download {
        background: var(--color-green-dark);
    }

    @media (max-width: 576px) {
        .attachments-section {
            padding: 16px;
            margin: 0 -20px 24px;
            border-radius: 0;
            border-left: none;
            border-right: none;
        }

        .attachment-item {
            flex-wrap: wrap;
            gap: 12px;
        }

        .attachment-info {
            flex: 1 1 calc(100% - 62px);
        }

        .attachment-download {
            width: 100%;
            justify-content: center;
        }
    }

    /* ========== Sidebar Pagination ========== */
    .sidebar-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        margin-top: 18px;
        padding-top: 15px;
        border-top: 1px dashed #eee;
    }

    .sidebar-page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #4a5568;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 0;
    }

    .sidebar-page-btn:hover:not(.disabled):not(.active) {
        border-color: var(--color-green);
        color: var(--color-green);
        background: #f8fafc;
        transform: translateY(-1px);
    }

    .sidebar-page-btn.active {
        background: var(--color-green);
        border-color: var(--color-green);
        color: #fff;
        cursor: default;
        box-shadow: 0 2px 6px rgba(46, 183, 46, 0.3);
    }

    .sidebar-page-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f1f5f9;
        color: #94a3b8;
    }

    /* ng-cloak to prevent blinking */
    [ng\:cloak],
    [ng-cloak],
    [data-ng-cloak],
    [x-ng-cloak],
    .ng-cloak,
    .x-ng-cloak {
        display: none !important;
    }
</style>
@endpush

@section('content')
<!-- Breadcrumb Section -->
<div class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ route('bmw.home') }}">
                <i class="fas fa-house"></i>
                {{ __t('nav.home', 'Trang chủ') }}
            </a>
            <i class="fas fa-chevron-right"></i>
            @if($post->mainCategory)
            <a href="{{ route('bmw.category', $post->mainCategory->slug ?? $post->mainCategory->id) }}">
                {{ $post->mainCategory->getName($currentLang) }}
            </a>
            <i class="fas fa-chevron-right"></i>
            @endif
            <span>{{ Str::limit($post->getTitle($currentLang), 50) }}</span>
        </nav>
    </div>
</div>

<!-- Main Content -->
<article class="news-detail">
    <div class="container">
        <div class="post-detail-wrapper">
            <!-- Main Content Column -->
            <div class="post-detail-main">
                <!-- Article Card -->
                <div class="article-card">
                    <!-- Featured Image -->
                    @if($post->main_image)
                    <div class="article-featured-image">
                        <img src="{{ $post->main_image }}" alt="{{ $post->getTitle($currentLang) }}">
                    </div>
                    @endif

                    <div class="article-card-body">
                        <!-- Article Header -->
                        <header class="article-header">
                            @if($post->mainCategory)
                            <span class="article-category-badge">{{ $post->mainCategory->getName($currentLang) }}</span>
                            @endif

                            <h1 class="article-title">{{ $post->getTitle($currentLang) }}</h1>

                            <div class="article-meta-row">
                                <div class="article-meta-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                    {{ $post->created_at->format('d/m/Y') }}
                                </div>
                                <div class="article-meta-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    {{ number_format($post->view_count) }} {{ __t('news.views', 'lượt xem') }}
                                </div>
                                @if($post->author)
                                <div class="article-meta-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                    {{ $post->author->name ?? 'Admin' }}
                                </div>
                                @endif
                            </div>
                        </header>

                        <!-- Excerpt/Summary -->
                        @if($post->getShortDescription($currentLang))
                        <div class="article-excerpt">
                            {!! $post->getShortDescription($currentLang) !!}
                        </div>
                        @endif

                        <!-- Video Section -->
                        @php
                        // Xử lý video_urls an toàn - hỗ trợ cả format string và object {type, url}
                        $rawVideoUrls = $post->video_urls ?? [];
                        $videoUrls = [];

                        if (is_array($rawVideoUrls)) {
                        foreach ($rawVideoUrls as $item) {
                        $urlString = null;

                        // Nếu item là string
                        if (is_string($item)) {
                        $urlString = trim($item);
                        }
                        // Nếu item là object/array với key 'url'
                        elseif (is_array($item) && isset($item['url'])) {
                        $urlString = trim($item['url']);
                        }

                        if ($urlString && !empty($urlString)) {
                        // Convert YouTube URLs sang embed format
                        if (strpos($urlString, 'youtube.com/embed/') === false) {
                        // YouTube watch URL
                        if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $urlString, $matches)) {
                        $urlString = 'https://www.youtube.com/embed/' . $matches[1];
                        }
                        // YouTube short URL
                        elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $urlString, $matches)) {
                        $urlString = 'https://www.youtube.com/embed/' . $matches[1];
                        }
                        }

                        $videoUrls[] = $urlString;
                        }
                        }
                        }
                        $videoCount = count($videoUrls);
                        @endphp
                        @if($videoCount > 0)
                        <div class="video-section {{ $videoCount === 1 ? 'single-video' : '' }}">
                            <div class="video-section-header">
                                <i class="fab fa-youtube"></i>
                                <h3>{{ __t('news.video_title', 'Video') }}</h3>
                            </div>
                            <div class="video-main-wrapper" id="videoMainWrapper">
                                <iframe id="videoMainIframe" src="{{ $videoUrls[0] }}" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                            </div>
                            @if($videoCount > 1)
                            <div class="video-thumbnails">
                                @foreach($videoUrls as $index => $videoUrl)
                                <div class="video-thumb-item {{ $index === 0 ? 'active' : '' }}" data-video-url="{{ $videoUrl }}" data-index="{{ $index }}">
                                    <div class="video-thumb-overlay">
                                        <i class="fas fa-play-circle"></i>
                                    </div>
                                    <span class="video-thumb-number">{{ $index + 1 }}</span>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endif

                        <!-- Attachments Section -->
                        @php
                        $rawAttachments = $post->attachments ?? [];
                        $attachments = [];
                        if (is_array($rawAttachments)) {
                        foreach ($rawAttachments as $item) {
                        if (is_array($item) && isset($item['url']) && isset($item['name'])) {
                        $attachments[] = $item;
                        }
                        }
                        }
                        $attachmentCount = count($attachments);

                        // Helper function to get file type class
                        function getFileTypeClass($filename) {
                        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        if (in_array($ext, ['xls', 'xlsx', 'csv'])) return 'excel';
                        if (in_array($ext, ['pdf'])) return 'pdf';
                        if (in_array($ext, ['doc', 'docx'])) return 'word';
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) return 'image';
                        return 'default';
                        }

                        // Helper function to get file icon
                        function getFileIcon($filename) {
                        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        if (in_array($ext, ['xls', 'xlsx', 'csv'])) return 'fas fa-file-excel';
                        if (in_array($ext, ['pdf'])) return 'fas fa-file-pdf';
                        if (in_array($ext, ['doc', 'docx'])) return 'fas fa-file-word';
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) return 'fas fa-file-image';
                        if (in_array($ext, ['zip', 'rar', '7z'])) return 'fas fa-file-archive';
                        return 'fas fa-file';
                        }
                        @endphp
                        @if($attachmentCount > 0)
                        <div class="attachments-section">
                            <div class="attachments-header">
                                <i class="fas fa-paperclip"></i>
                                <h3>{{ __t('news.attachments', 'Tệp đính kèm') }}</h3>
                            </div>
                            <div class="attachments-list">
                                @foreach($attachments as $attachment)
                                <a href="{{ $attachment['url'] }}" class="attachment-item" download="{{ $attachment['name'] }}" target="_blank">
                                    <div class="attachment-icon {{ getFileTypeClass($attachment['name']) }}">
                                        <i class="{{ getFileIcon($attachment['name']) }}"></i>
                                    </div>
                                    <div class="attachment-info">
                                        <p class="attachment-name">{{ $attachment['name'] }}</p>
                                        <span class="attachment-meta">{{ __t('news.click_to_download', 'Nhấn để tải xuống') }}</span>
                                    </div>
                                    <span class="attachment-download">
                                        <i class="fas fa-download"></i>
                                        {{ __t('news.download', 'Tải xuống') }}
                                    </span>
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Table of Contents -->
                        @if($post->show_toc && count($toc) > 0)
                        <div class="toc-card">
                            <div class="toc-header">
                                <i class="fas fa-list-ul"></i>
                                <h4>{{ __t('news.toc_title', 'Mục lục bài viết') }}</h4>
                            </div>
                            <div class="toc-body">
                                <ul class="toc-list">
                                    @foreach($toc as $item)
                                    <li class="toc-level-{{ $item['level'] }}">
                                        <a href="#{{ $item['id'] }}">{{ $item['text'] }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif

                        <!-- Album Images -->
                        @if($post->album_images && count($post->album_images) > 0)
                        <div class="article-album">
                            @foreach($post->album_images as $image)
                            <img src="{{ $image }}" alt="{{ $post->getTitle($currentLang) }}">
                            @endforeach
                        </div>
                        @endif

                        <!-- Article Content -->
                        <div class="article-content">
                            {!! $post->getContentWithTocAnchors($currentLang) !!}
                        </div>

                        <!-- Article Actions -->
                        <div class="article-actions">
                            <div class="share-section">
                                <span>{{ __t('news.share', 'Chia sẻ') }}:</span>
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                                    target="_blank" class="share-btn facebook" title="Chia sẻ lên Facebook">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                                    </svg>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->getTitle($currentLang)) }}"
                                    target="_blank" class="share-btn twitter" title="Chia sẻ lên Twitter">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z" />
                                    </svg>
                                </a>
                            </div>

                            <a href="javascript:void(0)" onclick="history.back()" class="back-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="19" y1="12" x2="5" y2="12" />
                                    <polyline points="12 19 5 12 12 5" />
                                </svg>
                                {{ __t('news.back', 'Quay lại') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <aside class="post-detail-sidebar">
                <!-- Bài Viết Cùng Danh Mục (từ danh mục cha và danh mục con) -->
                <div class="sidebar-card" id="sameCategoryPostsCard" ng-controller="SameCategoryPostsCtrl" ng-cloak>
                    <div class="sidebar-card-header">
                        <h3>{{ __t('news.same_category_posts', 'Bài Viết Cùng Danh Mục') }}</h3>
                    </div>

                    <ul class="sidebar-posts-list" ng-if="posts.length > 0">
                        <li ng-repeat="p in posts">
                            <a ng-href="@{{ p.url }}" class="sidebar-post-item">
                                <div class="sidebar-post-thumb">
                                    <img ng-if="p.main_image" ng-src="@{{ p.main_image }}" alt="@{{ p.title }}">
                                    <div ng-if="!p.main_image" style="display:flex;align-items:center;justify-content:center;height:100%;background:#f5f5f5;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                            <polyline points="21 15 16 10 5 21" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="sidebar-post-content">
                                    <h4 class="sidebar-post-title">@{{ p.title }}</h4>
                                </div>
                            </a>
                        </li>
                    </ul>

                    <!-- Empty State UI -->
                    <div class="sidebar-empty-state" ng-if="posts.length === 0 && !loading">
                        <div class="sidebar-empty-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                <polyline points="17 21 17 13 7 13 7 21" />
                                <polyline points="7 3 7 8 15 8" />
                            </svg>
                        </div>
                        <p class="sidebar-empty-text">{{ __t('news.no_posts_yet', 'Chưa có bài viết trong danh mục này') }}</p>
                        <a href="{{ route('bmw.home') }}" class="sidebar-explore-btn">
                            <i class="fas fa-compass"></i>
                            {{ __t('news.explore_more', 'Khám phá thêm') }}
                        </a>
                    </div>

                    <!-- Loader Spinner -->
                    <div class="text-center py-3" ng-if="loading">
                        <div class="spinner-border text-success" role="status" style="width: 1.5rem; height: 1.5rem;">
                            <span class="sr-only">Đang tải...</span>
                        </div>
                    </div>

                    <!-- Phân trang (Luôn hiển thị nút tiến/lùi, disable khi không dùng được) -->
                    <div class="sidebar-pagination" ng-if="meta && meta.last_page >= 1">
                        <button class="sidebar-page-btn" ng-class="{disabled: meta.current_page <= 1}" ng-click="goto(meta.current_page - 1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="sidebar-page-btn" ng-repeat="p in pages track by $index"
                            ng-class="{active: p == meta.current_page, disabled: p === '...'}" ng-click="goto(p)">
                            @{{ p }}
                        </button>
                        <button class="sidebar-page-btn" ng-class="{disabled: meta.current_page >= meta.last_page}" ng-click="goto(meta.current_page + 1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Bài Viết Liên Quan (từ danh mục liên quan) -->
                @if($relatedPosts->count() > 0)
                <div class="sidebar-card">
                    <div class="sidebar-card-header" style="border-bottom-color: var(--color-blue, #1976d2);">
                        <h3 style="color: var(--color-blue, #1976d2);">{{ __t('news.related_posts', 'Bài Viết Liên Quan') }}</h3>
                    </div>
                    <ul class="sidebar-posts-list">
                        @foreach($relatedPosts->take(6) as $related)
                        <li>
                            <a href="{{ route('bmw.news.detail', $related->slug) }}" class="sidebar-post-item">
                                <div class="sidebar-post-thumb">
                                    @if($related->main_image)
                                    <img src="{{ $related->main_image }}" alt="{{ $related->getTitle($currentLang) }}">
                                    @else
                                    <div style="display:flex;align-items:center;justify-content:center;height:100%;background:#f5f5f5;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                            <polyline points="21 15 16 10 5 21" />
                                        </svg>
                                    </div>
                                    @endif
                                </div>
                                <div class="sidebar-post-content">
                                    <h4 class="sidebar-post-title">{{ $related->getTitle($currentLang) }}</h4>
                                </div>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </aside>
        </div>
    </div>
</article>

<!-- ========== Post Contact Section ========== -->
<section id="contact" class="post-contact-section">
    <div class="container">
        <div class="post-contact-title">
            <h2>{{ __t('contact.title', 'LIÊN HỆ VỚI CHÚNG TÔI') }}</h2>
        </div>
        <div class="contact-grid">
            <!-- Map Column -->
            <div class="contact-map">
                @php
                $contactInfo = \App\Models\ESetting::getWebsiteInfo();
                $contactMapEmbed = $contactInfo['map_embed'] ?? '';
                @endphp
                @if($contactMapEmbed)
                <iframe src="{{ $contactMapEmbed }}" width="100%" height="100%" style="border: 0; border-radius: 4px;" allowfullscreen="" loading="lazy"></iframe>
                @else
                <div style="width: 100%; height: 100%; min-height: 300px; background: #f5f5f5; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #999;">
                    <div class="text-center">
                        <i class="fas fa-map-marker-alt" style="font-size: 48px; margin-bottom: 16px;"></i>
                        <p style="margin: 0;">Chưa cấu hình Google Maps</p>
                    </div>
                </div>
                @endif
            </div>


            <!-- Form Column -->
            <div class="contact-form-card">
                @if(session('contact_success'))
                <div class="contact-alert contact-alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('contact_success') }}
                </div>
                @endif
                @if(session('contact_error') && is_string(session('contact_error')))
                <div class="contact-alert contact-alert-error">
                    <i class="fas fa-exclamation-circle"></i> {{ session('contact_error') }}
                </div>
                @endif
                @if($errors->any())
                <div class="contact-alert contact-alert-error">
                    <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
                </div>
                @endif

                <form action="{{ route('bmw.contact.send') }}" method="POST" class="contact-form">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __t('contact.name_placeholder', 'Họ & tên...') }}" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="{{ __t('contact.phone_placeholder', 'Số điện thoại...') }}" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __t('contact.email_placeholder', 'Email...') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <textarea name="message" placeholder="{{ __t('contact.message_placeholder', 'Lời nhắn...') }}" rows="6">{{ old('message') }}</textarea>
                    </div>
                    <div class="contact-form-submit">
                        <button type="submit">
                            <i class="fas fa-paper-plane"></i> {{ __t('contact.submit_btn', 'GỬI') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    // Cấu hình tham số cho AngularJS controller
    window.sidebarConfig = {
        categoryId: {{ $post->main_category_id ?? 0 }},
        excludeId: {{ $post->id ?? 0 }}
    };
</script>
@vite('resources/js/web/common/webApp.js')
@vite('resources/js/web/pages/sameCategoryPostsCtrl.js')
<script src="{{ asset('bmw/js/news.js') }}"></script>
<script>
    // Smooth scroll for TOC links
    document.querySelectorAll('.toc-list a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const target = document.getElementById(targetId);
            if (target) {
                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
                history.pushState(null, null, '#' + targetId);
            }
        });
    });

    // Video thumbnail click handler
    document.querySelectorAll('.video-thumb-item').forEach(thumb => {
        thumb.addEventListener('click', function() {
            const videoUrl = this.dataset.videoUrl;
            const iframe = document.getElementById('videoMainIframe');
            const wrapper = document.getElementById('videoMainWrapper');

            if (iframe && videoUrl) {
                // Add fade effect
                wrapper.style.opacity = '0.5';
                wrapper.style.transition = 'opacity 0.3s ease';

                // Update iframe src
                iframe.src = videoUrl;

                // Remove active class from all thumbnails
                document.querySelectorAll('.video-thumb-item').forEach(t => {
                    t.classList.remove('active');
                });

                // Add active class to clicked thumbnail
                this.classList.add('active');

                // Restore opacity after load
                setTimeout(() => {
                    wrapper.style.opacity = '1';
                }, 300);

                // Scroll video into view
                wrapper.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        });
    });
</script>
@endpush