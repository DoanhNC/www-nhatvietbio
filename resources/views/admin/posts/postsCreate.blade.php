@extends('layouts.admin')
@section('title','Thêm bài viết')

@section('content')
<div ng-controller="PostsCreateCtrl" ng-cloak>

    {{-- Loading --}}
    <div class="text-center py-5" ng-if="loading">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
        <p class="mt-2">Đang tải...</p>
    </div>

    <div ng-if="!loading">
        <div class="card shadow mb-3">
            {{-- Unified Tab Navigation --}}
            <div class="card-header bg-light">
                <ul class="nav nav-tabs card-header-tabs">
                    {{-- Tab Thông tin chung --}}
                    <li class="nav-item">
                        <a class="nav-link py-2" href=""
                            ng-class="{'active': activeTab === 'general'}"
                            ng-click="setActiveTab('general')">
                            <i class="fas fa-cog mr-1"></i> Thông tin chung
                        </a>
                    </li>
                    {{-- Tab Ngôn ngữ --}}
                    <li class="nav-item" ng-repeat="lang in languages">
                        <a class="nav-link py-2" href=""
                            ng-class="{'active': activeTab === lang.code, 'text-danger': submitted && !isLangComplete(lang.code)}"
                            ng-click="setActiveTab(lang.code)">
                            <img ng-if="lang.flag_icon" ng-src="@{{ lang.flag_icon }}" style="width: 20px; height: 14px; margin-right: 4px;">
                            @{{ lang.name }}
                            <i class="fas fa-exclamation-circle text-danger" ng-if="submitted && !isLangComplete(lang.code)"></i>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                {{-- TAB: THÔNG TIN CHUNG --}}
                <div ng-show="activeTab === 'general'">
                    <div class="row">
                        {{-- CỘT TRÁI --}}
                        <div class="col-lg-6">
                            {{-- SECTION: Phân loại & Cấu hình --}}
                            <div class="border rounded mb-4">
                                <div class="bg-light px-3 py-2 border-bottom">
                                    <strong><i class="fas fa-folder-open text-primary mr-2"></i>Phân loại & Cấu hình</strong>
                                </div>
                                <div class="p-3">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold text-muted">DANH MỤC LIÊN QUAN</label>
                                                <select class="form-control" multiple chosen
                                                    ng-model="model.categories"
                                                    ng-options="cat.id as cat.name for cat in categoriesList"
                                                    data-placeholder="Chọn danh mục..."></select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold text-muted">DANH MỤC CHÍNH <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" ng-model="model.main_category_id">
                                                    <option value="">-- Chọn --</option>
                                                    <option ng-repeat="cat in categoriesList" ng-value="cat.id">
                                                        @{{ cat.name }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold text-muted">TAGS</label>
                                                <input type="text" class="form-control form-control-sm" ng-model="tagsInput" placeholder="tag1, tag2...">
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="row">
                                        <div class="col-4">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold text-muted">VỊ TRÍ</label>
                                                <input type="number" class="form-control form-control-sm" ng-model="model.position">
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold text-muted">TRẠNG THÁI</label>
                                                <select class="form-control form-control-sm" ng-model="model.status">
                                                    <option ng-value="1">Xuất bản</option>
                                                    <option ng-value="0">Nháp</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold text-muted">TÙY CHỌN</label>
                                                <div class="mt-1">
                                                    <div class="custom-control custom-checkbox custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" id="isFeatured" ng-model="model.is_featured">
                                                        <label class="custom-control-label small" for="isFeatured">Nổi bật</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" id="showToc" ng-model="model.show_toc">
                                                        <label class="custom-control-label small" for="showToc">Mục lục</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION: Hình ảnh & Media --}}
                            <div class="border rounded mb-4">
                                <div class="bg-light px-3 py-2 border-bottom">
                                    <strong><i class="fas fa-images text-success mr-2"></i>Hình ảnh & Media</strong>
                                </div>
                                <div class="p-3">
                                    <div class="row">
                                        {{-- Ảnh chính --}}
                                        <div class="col-5">
                                            <label class="small font-weight-bold text-muted">ẢNH CHÍNH</label>
                                            <div class="border rounded text-center bg-white" style="height: 140px; cursor: pointer; overflow: hidden;"
                                                ng-click="openMediaPicker('main_image')">
                                                <img ng-if="model.main_image" ng-src="@{{ model.main_image }}"
                                                    class="img-fluid h-100" style="object-fit: cover;">
                                                <div ng-if="!model.main_image" class="text-muted d-flex flex-column justify-content-center h-100">
                                                    <i class="fas fa-image fa-2x mb-2"></i>
                                                    <small>Click để chọn</small>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Album ảnh --}}
                                        <div class="col-7">
                                            <label class="small font-weight-bold text-muted">ALBUM ẢNH</label>
                                            <div class="d-flex flex-wrap border rounded p-1 bg-white" style="min-height: 140px; max-height: 140px; overflow-y: auto;">
                                                <div ng-repeat="img in model.album_images track by $index"
                                                    class="position-relative m-1" style="width: 45px; height: 45px;">
                                                    <img ng-src="@{{ img }}" class="img-fluid rounded" style="width: 45px; height: 45px; object-fit: cover;">
                                                    <button type="button" class="btn btn-sm btn-danger position-absolute"
                                                        style="top: -5px; right: -5px; padding: 0 4px; font-size: 9px; line-height: 1.4;"
                                                        ng-click="removeAlbumImage($index)">×</button>
                                                </div>
                                                <div class="m-1 border rounded d-flex align-items-center justify-content-center bg-light"
                                                    style="width: 45px; height: 45px; cursor: pointer;"
                                                    ng-click="openMediaPicker('album')">
                                                    <i class="fas fa-plus text-muted"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-3">
                                    <div class="row">
                                        {{-- Tệp đính kèm --}}
                                        <div class="col-6">
                                            <label class="small font-weight-bold text-muted">TỆP ĐÍNH KÈM</label>
                                            <div class="small border rounded p-2 bg-white" style="min-height: 60px; max-height: 80px; overflow-y: auto;">
                                                <div ng-repeat="file in model.attachments track by $index" class="mb-1" title="@{{ file.path || file.url }}">
                                                    <i class="fas fa-file-alt text-secondary"></i>
                                                    <span class="text-truncate" style="max-width: 120px; display: inline-block; vertical-align: middle;">@{{ file.name || file }}</span>
                                                    <i class="fas fa-times text-danger ml-1" style="cursor:pointer" ng-click="removeAttachment($index)"></i>
                                                </div>
                                                <div ng-if="!model.attachments.length" class="text-muted text-center py-2">
                                                    <small>Chưa có tệp</small>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-block mt-2" ng-click="openMediaPicker('attachments')">
                                                <i class="fas fa-plus"></i> Thêm tệp
                                            </button>
                                        </div>
                                        {{-- Video URLs --}}
                                        <div class="col-6">
                                            <label class="small font-weight-bold text-muted">VIDEO URLs</label>
                                            <div class="border rounded p-2 bg-white" style="min-height: 60px; max-height: 80px; overflow-y: auto;">
                                                <div ng-repeat="video in model.video_urls track by $index" class="input-group input-group-sm mb-1">
                                                    <input type="url" class="form-control form-control-sm" ng-model="video.url" placeholder="https://...">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-danger btn-sm" ng-click="removeVideo($index)">×</button>
                                                    </div>
                                                </div>
                                                <div ng-if="!model.video_urls.length" class="text-muted text-center py-2">
                                                    <small>Chưa có video</small>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-block mt-2" ng-click="addVideo()">
                                                <i class="fas fa-plus"></i> Thêm video
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CỘT PHẢI --}}
                        <div class="col-lg-6">
                            {{-- SECTION: SEO --}}
                            <div class="border rounded mb-4">
                                <div class="bg-light px-3 py-2 border-bottom">
                                    <strong><i class="fas fa-search text-info mr-2"></i>Tối ưu SEO</strong>
                                </div>
                                <div class="p-3">
                                    {{-- Slug --}}
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-muted">ĐƯỜNG DẪN URL <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-link"></i></span>
                                            </div>
                                            <input type="text" class="form-control"
                                                ng-model="model.slug"
                                                ng-class="{'is-invalid': submitted && !model.slug}"
                                                placeholder="duong-dan-url-than-thien">
                                        </div>
                                        <small class="text-muted">Tự động tạo từ tiêu đề tiếng Việt</small>
                                    </div>

                                    {{-- SEO Title --}}
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-muted">TIÊU ĐỀ SEO</label>
                                        <input type="text" class="form-control form-control-sm"
                                            ng-model="model.seo_title"
                                            placeholder="Tiêu đề hiển thị trên Google (50-60 ký tự)">
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">Ký tự: @{{ (model.seo_title || '').length }}</small>
                                            <small ng-class="{'text-success': (model.seo_title || '').length >= 50 && (model.seo_title || '').length <= 60, 'text-warning': (model.seo_title || '').length < 50 || (model.seo_title || '').length > 60}">
                                                Khuyến nghị: 50-60
                                            </small>
                                        </div>
                                    </div>

                                    {{-- SEO Description --}}
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-muted">MÔ TẢ SEO</label>
                                        <textarea class="form-control form-control-sm" rows="3"
                                            ng-model="model.seo_description"
                                            placeholder="Mô tả ngắn hiển thị trên Google (150-160 ký tự)..."></textarea>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">Ký tự: @{{ (model.seo_description || '').length }}</small>
                                            <small ng-class="{'text-success': (model.seo_description || '').length >= 150 && (model.seo_description || '').length <= 160, 'text-warning': (model.seo_description || '').length < 150 || (model.seo_description || '').length > 160}">
                                                Khuyến nghị: 150-160
                                            </small>
                                        </div>
                                    </div>

                                    {{-- SEO Keywords --}}
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-muted">TỪ KHÓA SEO</label>
                                        <input type="text" class="form-control form-control-sm"
                                            ng-model="model.seo_keywords"
                                            placeholder="từ khóa 1, từ khóa 2, từ khóa 3">
                                    </div>

                                    {{-- SEO Analysis Panel --}}
                                    <div class="border-top pt-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="rounded-circle d-inline-block mr-2" style="width: 10px; height: 10px; background: linear-gradient(135deg, #e91e63, #9c27b0);"></span>
                                            <strong class="small">Đánh giá SEO</strong>
                                        </div>

                                        {{-- Issues --}}
                                        <div ng-if="seoAnalysis.issues.length > 0" class="mb-2">
                                            <div class="d-flex align-items-center text-danger small mb-1">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                <span>Cần sửa (@{{ seoAnalysis.issues.length }})</span>
                                            </div>
                                            <div class="pl-3 small text-muted" ng-repeat="issue in seoAnalysis.issues track by $index">
                                                • @{{ issue.label }}
                                            </div>
                                        </div>

                                        {{-- Improvements --}}
                                        <div ng-if="seoAnalysis.improvements.length > 0" class="mb-2">
                                            <div class="d-flex align-items-center text-warning small mb-1">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                <span>Có thể cải tiến (@{{ seoAnalysis.improvements.length }})</span>
                                            </div>
                                            <div class="pl-3 small text-muted" ng-repeat="item in seoAnalysis.improvements track by $index">
                                                • @{{ item.label }}
                                            </div>
                                        </div>

                                        {{-- Good Results --}}
                                        <div ng-if="seoAnalysis.good.length > 0" class="mb-2">
                                            <div class="d-flex align-items-center text-success small mb-1">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                <span>Đạt yêu cầu (@{{ seoAnalysis.good.length }})</span>
                                            </div>
                                            <div class="pl-3 small text-muted" ng-repeat="item in seoAnalysis.good track by $index">
                                                • @{{ item.label }}
                                            </div>
                                        </div>

                                        {{-- All Good Message --}}
                                        <div ng-if="seoAnalysis.issues.length === 0 && seoAnalysis.improvements.length === 0" class="alert alert-success py-2 mb-0">
                                            <i class="fas fa-check-circle mr-1"></i> Tuyệt vời! Bài viết đã được tối ưu SEO.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB: NỘI DUNG NGÔN NGỮ --}}
                <div ng-repeat="lang in languages" ng-show="activeTab === lang.code">
                    {{-- Language Header Banner --}}
                    <div class="alert alert-info py-2 mb-3">
                        <i class="fas fa-language"></i>
                        <strong>Đang nhập nội dung:</strong>
                        <img ng-if="lang.flag_icon" ng-src="@{{ lang.flag_icon }}" style="width: 20px; height: 14px; margin: 0 4px;">
                        <span class="font-weight-bold">@{{ lang.name }}</span>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Tiêu đề <span class="text-danger">*</span></label>
                        <input type="text" class="form-control"
                            ng-model="model.titles[lang.code]"
                            ng-change="lang.code === 'vi' && autoSlugFromVietnamese()"
                            ng-class="{'is-invalid': submitted && !model.titles[lang.code]}"
                            placeholder="Nhập tiêu đề bài viết...">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Mô tả ngắn <span class="text-danger">*</span></label>
                        <textarea mce-editor="simple" mce-height="200"
                            ng-model="model.short_descriptions[lang.code]"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Nội dung <span class="text-danger">*</span></label>
                        <textarea mce-editor mce-height="400"
                            ng-model="model.contents[lang.code]"></textarea>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="border-top pt-3 mt-4 mb-3 mr-3 text-right">
                <a href="{{ route('admin.posts') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <button class="btn btn-primary ml-2" type="button" ng-click="save()" ng-disabled="saving">
                    <i class="fas fa-check"></i> @{{ saving ? 'Đang lưu…' : 'Lưu' }}
                </button>
            </div>
        </div>
    </div>

    {{-- Media Picker Modal --}}
    <div class="modal fade" id="mediaPickerModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">Chọn Media</h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-0">
                    <media-picker mode="picker"
                        select-mode="@{{ mediaPickerMode }}"
                        accept="@{{ mediaPickerAccept }}"
                        on-select="onMediaSelected(files)"></media-picker>
                </div>
            </div>
        </div>
    </div>

    {{-- Media Picker Modal for TinyMCE (z-index higher than TinyMCE dialog) --}}
    <div class="modal fade" id="mediaPickerModalMCE" tabindex="-1" style="z-index: 100000;">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title"><i class="fas fa-images"></i> Chọn ảnh từ Media Library</h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-0">
                    <media-picker mode="picker"
                        select-mode="single"
                        accept="image"
                        on-select="onMceMediaSelected(files)"></media-picker>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @push('scripts')
    @vite('resources/js/admin/pages/posts/postsCreateCtrl.js')
    <script>
        // Listen for TinyMCE Media Picker event
        document.addEventListener('openMediaPickerForMCE', function() {
            $('#mediaPickerModalMCE').modal('show');
        });

        // Set backdrop z-index when modal opens
        $('#mediaPickerModalMCE').on('shown.bs.modal', function() {
            $('.modal-backdrop').last().css('z-index', 99999);
        });
    </script>
    @endpush