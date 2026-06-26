{{-- resources/views/admin/settings/website.blade.php --}}
@extends('layouts.admin')
@section('title', 'Cấu hình Website')

@section('content')
<div class="container-fluid mt-4" ng-controller="WebsiteSettingsCtrl">

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-cog mr-2"></i>Cấu hình Website
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Vertical Nav tabs -->
                <div class="col-md-3">
                    <ul class="nav nav-pills flex-column settings-nav-pills" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" ng-class="{active: activeTab === 'website'}" ng-click="setTab('website')" href="javascript:void(0)">
                                <i class="fas fa-globe mr-2"></i> Thông tin website
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" ng-class="{active: activeTab === 'smtp'}" ng-click="setTab('smtp')" href="javascript:void(0)">
                                <i class="fas fa-cogs mr-2"></i> Cấu hình email
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" ng-class="{active: activeTab === 'template_config'}" ng-click="setTab('template_config')" href="javascript:void(0)">
                                <i class="fas fa-envelope mr-2"></i> Cấu hình mẫu email
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" ng-class="{active: activeTab === 'template_edit'}" ng-click="setTab('template_edit')" href="javascript:void(0)">
                                <i class="fas fa-code mr-2"></i> Chỉnh sửa mẫu email
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" ng-class="{active: activeTab === 'analytics'}" ng-click="setTab('analytics')" href="javascript:void(0)">
                                <i class="fas fa-chart-line mr-2"></i> Thống kê & Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" ng-class="{active: activeTab === 'theme_colors'}" ng-click="setTab('theme_colors')" href="javascript:void(0)">
                                <i class="fas fa-palette mr-2"></i> Tông màu website
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" ng-class="{active: activeTab === 'test'}" ng-click="setTab('test')" href="javascript:void(0)">
                                <i class="fas fa-paper-plane mr-2"></i> Gửi thử
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Tab content -->
                <div class="col-md-9">
                    <div class="tab-content">
                        <!-- Tab 1: Thông tin website -->
                        <div class="tab-pane" ng-class="{active: activeTab === 'website'}">
                            <form ng-submit="saveWebsiteInfo()">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>URL Website</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-link"></i></span>
                                                </div>
                                                <input type="url" class="form-control" ng-model="website.url" placeholder="https://example.com">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Tên website</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                                </div>
                                                <input type="text" class="form-control" ng-model="website.name" placeholder="Tên website">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Tên công ty</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                                </div>
                                                <input type="text" class="form-control" ng-model="website.company" placeholder="Tên công ty">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Hotline</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                                                </div>
                                                <input type="text" class="form-control" ng-model="website.hotline" placeholder="Hotline">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Số điện thoại</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                </div>
                                                <input type="text" class="form-control" ng-model="website.phone" placeholder="Số điện thoại">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Email</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                </div>
                                                <input type="email" class="form-control" ng-model="website.email" placeholder="Email">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Địa chỉ</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                                </div>
                                                <input type="text" class="form-control" ng-model="website.address" placeholder="Địa chỉ">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Giờ làm việc</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="far fa-clock"></i></span>
                                                </div>
                                                <input type="text" class="form-control" ng-model="website.working_hours" placeholder="VD: Thứ 2 - Thứ 7: 07:30 - 17:30">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Mô tả ngắn (Slogan)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-quote-left"></i></span>
                                                </div>
                                                <input type="text" class="form-control" ng-model="website.description" placeholder="VD: Công Ty Xử Lý Nước Uy Tín">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label><i class="fas fa-map-marked-alt mr-1"></i> Google Maps Embed URL</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-map"></i></span>
                                                </div>
                                                <input type="text" class="form-control" ng-model="website.map_embed" placeholder="https://www.google.com/maps/embed?pb=...">
                                            </div>
                                            <small class="text-muted">
                                                Lấy link embed từ Google Maps: Mở Google Maps → Chọn địa điểm → Chia sẻ → Nhúng bản đồ → Sao chép URL trong src=""
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Logo Section --}}
                                <hr>
                                <div class="form-group">
                                    <label><i class="fas fa-image mr-1"></i> Logo Website</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="logo-preview-box border rounded p-3 text-center" style="min-height: 120px; background: #f8f9fc;">
                                                <img ng-if="logo" ng-src="@{{ logo }}" alt="Logo" class="img-fluid" style="max-height: 100px;">
                                                <div ng-if="!logo" class="text-muted d-flex align-items-center justify-content-center" style="height: 80px;">
                                                    <span><i class="fas fa-image fa-2x mb-2 d-block"></i> Chưa có logo</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="text-muted small mb-2">Chọn logo từ thư viện Media. Logo sẽ hiển thị ở header website và trang đăng nhập admin.</p>
                                            <button type="button" class="btn btn-outline-primary btn-sm" ng-click="openLogoMediaPicker()">
                                                <i class="fas fa-images mr-1"></i> Chọn từ Media
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm ml-2" ng-if="logo" ng-click="removeLogo()">
                                                <i class="fas fa-trash mr-1"></i> Xóa logo
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Favicon Section --}}
                                <hr>
                                <div class="form-group">
                                    <label><i class="fas fa-globe mr-1"></i> Favicon (Icon Tab)</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="favicon-preview-box border rounded p-3 text-center" style="min-height: 100px; background: #f8f9fc;">
                                                <img ng-if="favicon" ng-src="@{{ favicon }}" alt="Favicon" class="img-fluid" style="max-height: 64px;">
                                                <div ng-if="!favicon" class="text-muted d-flex align-items-center justify-content-center" style="height: 64px;">
                                                    <span><i class="fas fa-globe fa-2x mb-2 d-block"></i> Chưa có</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="text-muted small mb-2">Chọn ảnh favicon (ico, png) để hiển thị ở tab trình duyệt.</p>
                                            <button type="button" class="btn btn-outline-primary btn-sm" ng-click="openFaviconMediaPicker()">
                                                <i class="fas fa-images mr-1"></i> Chọn từ Media
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm ml-2" ng-if="favicon" ng-click="removeFavicon()">
                                                <i class="fas fa-trash mr-1"></i> Xóa favicon
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary" ng-disabled="saving">
                                    <i class="fas fa-save mr-1"></i> Lưu thông tin
                                </button>
                            </form>
                        </div>

                        <!-- Tab 2: Cấu hình email SMTP -->
                        <div class="tab-pane" ng-class="{active: activeTab === 'smtp'}">
                            <form ng-submit="saveSmtpConfig()">
                                <div class="form-group">
                                    <label>Trạng thái</label>
                                    <div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="smtp_active" name="smtp_status" class="custom-control-input" ng-model="smtp.is_active" value="1">
                                            <label class="custom-control-label" for="smtp_active">
                                                Hoạt động
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="smtp_inactive" name="smtp_status" class="custom-control-input" ng-model="smtp.is_active" value="0">
                                            <label class="custom-control-label" for="smtp_inactive">
                                                Không hoạt động
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email ứng dụng <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                </div>
                                                <input type="email" class="form-control" ng-model="smtp.username" placeholder="email@gmail.com">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Mật khẩu ứng dụng <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                </div>
                                                <input type="text" class="form-control" ng-model="smtp.password"
                                                    placeholder="jcjz vtfz rbuy hmiv">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email Host</label>
                                            <select class="form-control" ng-model="smtp.host">
                                                <option value="smtp.gmail.com">Gmail</option>
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label>Port</label>
                                                    <input type="number" class="form-control" ng-model="smtp.port" placeholder="587">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label>Encryption</label>
                                                    <select class="form-control" ng-model="smtp.encryption">
                                                        <option value="tls">TLS</option>
                                                        <option value="ssl">SSL</option>
                                                        <option value="">Không</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tên người gửi</label>
                                            <input type="text" class="form-control" ng-model="smtp.from_name" placeholder="Tên hiển thị khi gửi email">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email người gửi</label>
                                            <input type="email" class="form-control" ng-model="smtp.from_email" placeholder="Để trống sẽ dùng email ứng dụng">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary" ng-disabled="saving">
                                    <i class="fas fa-save mr-1"></i> Lưu cấu hình
                                </button>
                            </form>
                        </div>

                        <!-- Tab 3: Cấu hình mẫu email -->
                        <div class="tab-pane" ng-class="{active: activeTab === 'template_config'}">
                            <form ng-submit="saveTemplateConfig()">
                                <div class="form-group">
                                    <label>Tên email <span class="text-danger">*</span></label>
                                    <select class="form-control" ng-model="selectedTemplateId" ng-change="loadTemplate()">
                                        <option value="">-- Chọn mẫu email --</option>
                                        <option ng-repeat="t in templates" ng-value="t.id">@{{ t.label }}</option>
                                    </select>
                                </div>

                                <div ng-if="currentTemplate">
                                    <div class="form-group">
                                        <label>Tiêu đề email gửi <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                            </div>
                                            <input type="text" class="form-control" ng-model="currentTemplate.subject">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Email gửi kèm (CC)</label>
                                        <div ng-repeat="email in currentTemplate.cc_emails track by $index" class="input-group mb-2">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            </div>
                                            <input type="email" class="form-control" ng-model="currentTemplate.cc_emails[$index]" placeholder="email@example.com">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-danger" ng-click="removeCcEmail($index)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" ng-click="addCcEmail()">
                                            <i class="fas fa-plus mr-1"></i> Thêm email CC
                                        </button>
                                    </div>

                                    <div class="form-group">
                                        <label>Email gửi kèm (ẩn danh - BCC)</label>
                                        <div ng-repeat="email in currentTemplate.bcc_emails track by $index" class="input-group mb-2">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-user-secret"></i></span>
                                            </div>
                                            <input type="email" class="form-control" ng-model="currentTemplate.bcc_emails[$index]" placeholder="email@example.com">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-danger" ng-click="removeBccEmail($index)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" ng-click="addBccEmail()">
                                            <i class="fas fa-plus mr-1"></i> Thêm email BCC
                                        </button>
                                    </div>

                                    <button type="submit" class="btn btn-primary" ng-disabled="saving">
                                        <i class="fas fa-save mr-1"></i> Lưu mẫu email
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 4: Chỉnh sửa mẫu email -->
                        <div class="tab-pane" ng-class="{active: activeTab === 'template_edit'}">
                            <div class="form-group">
                                <label>Mẫu email <span class="text-danger">*</span></label>
                                <select class="form-control" ng-model="editTemplateId" ng-change="loadTemplateForEdit()">
                                    <option value="">-- Chọn mẫu email --</option>
                                    <option ng-repeat="t in templates" ng-value="t.id">@{{ t.label }}</option>
                                </select>
                            </div>

                            <div ng-if="editTemplate">
                                <div class="form-group">
                                    <label>Nội dung mẫu (HTML/Blade)</label>
                                    <div class="border rounded" style="background: #1e1e1e;">
                                        <textarea id="templateEditor" class="form-control"
                                            ng-model="editTemplate.content"
                                            rows="20"
                                            style="font-family: 'Consolas', 'Monaco', monospace; font-size: 13px; background: #1e1e1e; color: #d4d4d4; border: none;"></textarea>
                                    </div>
                                    <small class="text-muted">
                                        Biến có sẵn: @{{ '$name' }}, @{{ '$email' }}, @{{ '$phone' }}, @{{ '$message' }}, @{{ '$reset_link' }}
                                    </small>
                                </div>

                                <button type="button" class="btn btn-primary" ng-click="saveTemplateContent()" ng-disabled="saving">
                                    <i class="fas fa-save mr-1"></i> Cập nhật
                                </button>
                            </div>
                        </div>

                        <!-- Tab 5: Thống kê & Analytics -->
                        <div class="tab-pane" ng-class="{active: activeTab === 'analytics'}">
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle mr-2"></i>
                                Cấu hình các dịch vụ thống kê và tracking để theo dõi lượng truy cập website.
                            </div>

                            <form ng-submit="saveAnalytics()">
                                <!-- Analytics Services List -->
                                <div ng-repeat="service in analytics.services track by $index" class="card mb-3 border-left-primary">
                                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                        <span>
                                            <i ng-class="getServiceIcon(service.type)" class="mr-2"></i>
                                            <strong>@{{ getServiceLabel(service.type) }}</strong>
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-danger" ng-click="removeAnalyticsService($index)" title="Xóa">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <label class="small text-muted">Loại dịch vụ</label>
                                                    <select class="form-control form-control-sm" ng-model="service.type" ng-change="onServiceTypeChange(service)">
                                                        <option ng-repeat="type in analyticsTypes" ng-value="type.value">@{{ type.label }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="form-group mb-2" ng-if="service.type !== 'custom'">
                                                    <label class="small text-muted">Mã tracking / ID</label>
                                                    <input type="text" class="form-control form-control-sm"
                                                        ng-model="service.code"
                                                        placeholder="@{{ getServicePlaceholder(service.type) }}">
                                                </div>
                                                <div class="form-group mb-2" ng-if="service.type === 'custom'">
                                                    <label class="small text-muted">Vị trí nhúng</label>
                                                    <select class="form-control form-control-sm" ng-model="service.position">
                                                        <option value="head">Head (trước &lt;/head&gt;)</option>
                                                        <option value="body">Body (trước &lt;/body&gt;)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-2">
                                                    <label class="small text-muted">Trạng thái</label>
                                                    <div>
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="analytics_active_@{{$index}}"
                                                                ng-model="service.is_active"
                                                                ng-true-value="'1'"
                                                                ng-false-value="'0'">
                                                            <label class="custom-control-label" for="analytics_active_@{{$index}}">
                                                                <span ng-if="service.is_active === '1'" class="text-success">Bật</span>
                                                                <span ng-if="service.is_active !== '1'" class="text-muted">Tắt</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Custom Script textarea -->
                                        <div ng-if="service.type === 'custom'" class="form-group mb-0">
                                            <label class="small text-muted">Script code</label>
                                            <textarea class="form-control form-control-sm" ng-model="service.code" rows="4"
                                                placeholder="<script>...</script>"
                                                style="font-family: monospace; font-size: 12px;"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Empty state -->
                                <div ng-if="!analytics.services || analytics.services.length === 0" class="text-center py-4 text-muted">
                                    <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                    <p>Chưa có dịch vụ thống kê nào được cấu hình.</p>
                                </div>

                                <!-- Add Service Button -->
                                <div class="mb-3">
                                    <button type="button" class="btn btn-outline-primary" ng-click="addAnalyticsService()">
                                        <i class="fas fa-plus mr-1"></i> Thêm dịch vụ thống kê
                                    </button>
                                </div>

                                <hr>

                                <!-- Save Button -->
                                <button type="submit" class="btn btn-primary" ng-disabled="saving">
                                    <i class="fas fa-save mr-1"></i> Lưu cấu hình Analytics
                                </button>
                            </form>
                        </div>

                        <!-- Tab 6: Tông màu website -->
                        <div class="tab-pane" ng-class="{active: activeTab === 'theme_colors'}">
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle mr-2"></i>
                                Cấu hình tông màu cho giao diện website phía người dùng. Thay đổi sẽ áp dụng ngay sau khi lưu.
                            </div>

                            <form ng-submit="saveThemeColors()">
                                <!-- Nhóm 1: Màu chính -->
                                <div class="card mb-4 border-left-primary shadow-sm">
                                    <div class="card-header py-3 bg-light">
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <i class="fas fa-palette mr-2"></i>Nhóm Màu Chính
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Màu chính -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        <span class="color-preview-dot mr-2" ng-style="{'background-color': themeColors.color_primary}"></span>
                                                        Màu chính
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="color" class="form-control form-control-color"
                                                            ng-model="themeColors.color_primary"
                                                            style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                                        <input type="text" class="form-control"
                                                            ng-model="themeColors.color_primary"
                                                            placeholder="#000000">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                ng-click="resetSingleColor('color_primary')"
                                                                title="Khôi phục mặc định">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">Navbar, tiêu đề section, breadcrumb</small>
                                                </div>
                                            </div>

                                            <!-- Màu chính tối -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        <span class="color-preview-dot mr-2" ng-style="{'background-color': themeColors.color_primary_dark}"></span>
                                                        Màu chính tối
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="color" class="form-control form-control-color"
                                                            ng-model="themeColors.color_primary_dark"
                                                            style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                                        <input type="text" class="form-control"
                                                            ng-model="themeColors.color_primary_dark"
                                                            placeholder="#000000">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                ng-click="resetSingleColor('color_primary_dark')"
                                                                title="Khôi phục mặc định">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">Topbar, footer background, hover states</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nhóm 2: Màu phụ -->
                                <div class="card mb-4 border-left-success shadow-sm">
                                    <div class="card-header py-3 bg-light">
                                        <h6 class="m-0 font-weight-bold text-success">
                                            <i class="fas fa-star mr-2"></i>Nhóm Màu Phụ (Accent)
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Màu phụ -->
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        <span class="color-preview-dot mr-2" ng-style="{'background-color': themeColors.color_secondary}"></span>
                                                        Màu phụ
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="color" class="form-control form-control-color"
                                                            ng-model="themeColors.color_secondary"
                                                            style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                                        <input type="text" class="form-control"
                                                            ng-model="themeColors.color_secondary"
                                                            placeholder="#000000">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                ng-click="resetSingleColor('color_secondary')"
                                                                title="Khôi phục mặc định">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">Nút CTA, icon, hotline</small>
                                                </div>
                                            </div>

                                            <!-- Màu phụ tối -->
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        <span class="color-preview-dot mr-2" ng-style="{'background-color': themeColors.color_secondary_dark}"></span>
                                                        Màu phụ tối
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="color" class="form-control form-control-color"
                                                            ng-model="themeColors.color_secondary_dark"
                                                            style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                                        <input type="text" class="form-control"
                                                            ng-model="themeColors.color_secondary_dark"
                                                            placeholder="#000000">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                ng-click="resetSingleColor('color_secondary_dark')"
                                                                title="Khôi phục mặc định">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">Hover nút, active states</small>
                                                </div>
                                            </div>

                                            <!-- Màu hover menu -->
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        <span class="color-preview-dot mr-2" ng-style="{'background-color': themeColors.color_menu_hover}"></span>
                                                        Màu hover menu
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="color" class="form-control form-control-color"
                                                            ng-model="themeColors.color_menu_hover"
                                                            style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                                        <input type="text" class="form-control"
                                                            ng-model="themeColors.color_menu_hover"
                                                            placeholder="#000000">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                ng-click="resetSingleColor('color_menu_hover')"
                                                                title="Khôi phục mặc định">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">Di chuột qua menu</small>
                                                </div>
                                            </div>

                                            <!-- Màu icon -->
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        <span class="color-preview-dot mr-2" ng-style="{'background-color': themeColors.color_icon}"></span>
                                                        Màu icon
                                                        <span class="badge badge-success ml-1">MỚI</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="color" class="form-control form-control-color"
                                                            ng-model="themeColors.color_icon"
                                                            style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                                        <input type="text" class="form-control"
                                                            ng-model="themeColors.color_icon"
                                                            placeholder="#000000">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                ng-click="resetSingleColor('color_icon')"
                                                                title="Khôi phục mặc định">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">Icon trên giao diện</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nhóm 3: Màu chữ & Nền -->
                                <div class="card mb-4 border-left-info shadow-sm">
                                    <div class="card-header py-3 bg-light">
                                        <h6 class="m-0 font-weight-bold text-info">
                                            <i class="fas fa-font mr-2"></i>Nhóm Màu Chữ & Nền
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Màu chữ chính -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        <span class="color-preview-dot mr-2" ng-style="{'background-color': themeColors.color_text}"></span>
                                                        Màu chữ chính
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="color" class="form-control form-control-color"
                                                            ng-model="themeColors.color_text"
                                                            style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                                        <input type="text" class="form-control"
                                                            ng-model="themeColors.color_text"
                                                            placeholder="#000000">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                ng-click="resetSingleColor('color_text')"
                                                                title="Khôi phục mặc định">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">Nội dung văn bản, tiêu đề bài viết</small>
                                                </div>
                                            </div>

                                            <!-- Màu chữ phụ -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        <span class="color-preview-dot mr-2" ng-style="{'background-color': themeColors.color_text_secondary}"></span>
                                                        Màu chữ phụ
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="color" class="form-control form-control-color"
                                                            ng-model="themeColors.color_text_secondary"
                                                            style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                                        <input type="text" class="form-control"
                                                            ng-model="themeColors.color_text_secondary"
                                                            placeholder="#000000">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                ng-click="resetSingleColor('color_text_secondary')"
                                                                title="Khôi phục mặc định">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">Mô tả, caption, meta, thời gian</small>
                                                </div>
                                            </div>

                                            <!-- Màu chữ thứ 3 (MỚI) -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        <span class="color-preview-dot mr-2" ng-style="{'background-color': themeColors.color_text_tertiary}"></span>
                                                        Màu chữ thứ 3
                                                        <span class="badge badge-success ml-1">MỚI</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="color" class="form-control form-control-color"
                                                            ng-model="themeColors.color_text_tertiary"
                                                            style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                                        <input type="text" class="form-control"
                                                            ng-model="themeColors.color_text_tertiary"
                                                            placeholder="#000000">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                ng-click="resetSingleColor('color_text_tertiary')"
                                                                title="Khôi phục mặc định">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">Footer, text trên nền tối</small>
                                                </div>
                                            </div>

                                            <!-- Màu nền -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        <span class="color-preview-dot mr-2" ng-style="{'background-color': themeColors.color_background, 'border': '1px solid #ddd'}"></span>
                                                        Màu nền
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="color" class="form-control form-control-color"
                                                            ng-model="themeColors.color_background"
                                                            style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                                        <input type="text" class="form-control"
                                                            ng-model="themeColors.color_background"
                                                            placeholder="#000000">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                ng-click="resetSingleColor('color_background')"
                                                                title="Khôi phục mặc định">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">Background chính của trang web</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <button type="submit" class="btn btn-primary" ng-disabled="saving">
                                            <i class="fas fa-save mr-1"></i> Lưu cấu hình màu
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary ml-2" ng-click="resetAllColors()">
                                            <i class="fas fa-undo mr-1"></i> Khôi phục tất cả mặc định
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 7: Gửi thử -->
                        <div class="tab-pane" ng-class="{active: activeTab === 'test'}">
                            <form ng-submit="sendTestEmail()">
                                <div class="form-group">
                                    <label>Email nhận <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        </div>
                                        <input type="email" class="form-control" ng-model="testEmail.recipient" required placeholder="email@example.com">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Chọn loại</label>
                                    <div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="test_content" name="test_type" class="custom-control-input" ng-model="testEmail.type" value="content">
                                            <label class="custom-control-label" for="test_content">
                                                Gửi thử nội dung
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="test_template" name="test_type" class="custom-control-input" ng-model="testEmail.type" value="template">
                                            <label class="custom-control-label" for="test_template">
                                                Gửi thử mẫu email
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group" ng-if="testEmail.type === 'content'">
                                    <label>Nội dung gửi thử <span class="text-danger">*</span></label>
                                    <textarea class="form-control" ng-model="testEmail.content" rows="4" placeholder="Nhập nội dung email thử nghiệm"></textarea>
                                </div>

                                <div class="form-group" ng-if="testEmail.type === 'template'">
                                    <label>Chọn mẫu email</label>
                                    <select class="form-control" ng-model="testEmail.templateId">
                                        <option value="">-- Chọn mẫu --</option>
                                        <option ng-repeat="t in templates" ng-value="t.id">@{{ t.label }}</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-success" ng-disabled="sending">
                                    <i class="fas fa-spinner fa-spin mr-1" ng-if="sending"></i>
                                    <i class="fas fa-paper-plane mr-1" ng-if="!sending"></i>
                                    <span ng-if="!sending">Gửi email</span>
                                    <span ng-if="sending">Đang gửi...</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Logo Media Picker Modal - MUST be inside controller scope --}}
    <div class="modal fade" id="mediaPickerModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title"><i class="fas fa-images mr-2"></i>Chọn Logo từ Media</h6>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <media-picker mode="picker" select-mode="single" accept="image/*" on-select="onMediaSelect(files)"></media-picker>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
@vite('resources/js/admin/pages/settings/websiteSettingsCtrl.js')
@endpush