@extends('layouts.admin')
@section('title','Cấu hình hệ thống')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header card-header-gradient">
        <h6 class="m-0 font-weight-bold"><i class="fas fa-cogs mr-2"></i>Cấu hình hệ thống</h6>
    </div>
    <div class="card-body">
        {{-- Tabs Navigation --}}
        <ul class="nav nav-tabs" id="settingsTabs">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#tabLanguages">
                    <i class="fas fa-globe"></i> Ngôn ngữ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tabMedia">
                    <i class="fas fa-hdd"></i> Lưu trữ Media
                </a>
            </li>
        </ul>

        {{-- Tab Content --}}
        <div class="tab-content mt-3">
            {{-- Languages Tab --}}
            <div class="tab-pane fade show active" id="tabLanguages" ng-controller="LanguagesCtrl">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Quản lý Ngôn ngữ</h5>
                    <button class="btn btn-primary btn-sm" ng-click="openCreate()"><i class="fas fa-plus"></i> Thêm ngôn ngữ</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Flag</th>
                                <th>Mã</th>
                                <th>Tên</th>
                                <th>Trạng thái</th>
                                <th>Mặc định</th>
                                <th>Số key dịch</th>
                                <th style="width:150px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="r in rows">
                                <td>@{{r.position + 1}}</td>
                                <td>
                                    <img ng-if="r.flag_icon" ng-src="@{{r.flag_icon}}" style="max-height:24px;border-radius:2px">
                                    <span ng-if="!r.flag_icon" style="font-size:1.5rem">@{{r.flag}}</span>
                                </td>
                                <td><code>@{{r.code}}</code></td>
                                <td>@{{r.name}}</td>
                                <td>
                                    <span class="badge" ng-class="r.is_active ? 'badge-success' : 'badge-secondary'">
                                        @{{r.is_active ? 'Hoạt động' : 'Ẩn'}}
                                    </span>
                                </td>
                                <td>
                                    <span ng-if="r.is_default" class="badge badge-primary">Mặc định</span>
                                    <button ng-if="!r.is_default && r.is_active" class="btn btn-xs btn-outline-primary"
                                        ng-click="setDefault(r)">Đặt mặc định</button>
                                </td>
                                <td>@{{r.translation_count}} keys</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" ng-click="openTranslations(r)" title="Bản dịch">
                                        <i class="fas fa-language"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" ng-click="openEdit(r)" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" ng-click="remove(r)" ng-if="!r.is_default" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr ng-if="!rows.length && !loading">
                                <td colspan="8" class="text-center">Không có dữ liệu</td>
                            </tr>
                            <tr ng-if="loading">
                                <td colspan="8" class="text-center">Đang tải…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Language Modals --}}
                @include('admin.settings.partials.language-modals')
            </div>

            {{-- Media Storage Tab --}}
            <div class="tab-pane fade" id="tabMedia" ng-controller="SystemSettingsCtrl">
                <div class="row">
                    {{-- Storage Settings --}}
                    <div class="col-lg-6 mb-4">
                        <h5 class="mb-3">Cài đặt lưu trữ</h5>

                        {{-- Loading --}}
                        <div ng-if="loading" class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                        </div>

                        <div ng-if="!loading">
                            {{-- Storage Progress Bar --}}
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Đã sử dụng: <strong>@{{storage.used_formatted}}</strong></span>
                                    <span>Tối đa: <strong>@{{storage.max_formatted}}</strong></span>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar"
                                        ng-class="{'bg-success': storage.used_percent < 70, 'bg-warning': storage.used_percent >= 70 && storage.used_percent < 90, 'bg-danger': storage.used_percent >= 90}"
                                        role="progressbar"
                                        ng-style="{width: storage.used_percent + '%'}">
                                        @{{storage.used_percent}}%
                                    </div>
                                </div>
                                <small class="text-muted">Còn trống: @{{storage.free_formatted}}</small>
                            </div>

                            {{-- Settings Form --}}
                            <div class="form-group">
                                <label>Tổng dung lượng tối đa (GB)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" ng-model="settings.max_storage_gb" min="1" step="1">
                                    <div class="input-group-append">
                                        <span class="input-group-text">GB</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Dung lượng tối đa mỗi file (MB)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" ng-model="settings.max_file_size_mb" min="1" step="1">
                                    <div class="input-group-append">
                                        <span class="input-group-text">MB</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Định dạng file cho phép</label>
                                <input type="text" class="form-control" ng-model="settings.allowed_extensions_str"
                                    placeholder="jpg, jpeg, png, gif, pdf, webp">
                                <small class="text-muted">Các định dạng cách nhau bởi dấu phẩy</small>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="convertWebp" ng-model="settings.convert_to_webp">
                                <label class="form-check-label" for="convertWebp">Tự động chuyển đổi ảnh sang WebP</label>
                            </div>

                            <button class="btn btn-primary" ng-click="saveSettings()" ng-disabled="saving">
                                <i class="fas fa-spinner fa-spin" ng-if="saving"></i>
                                <i class="fas fa-save" ng-if="!saving"></i> Lưu cài đặt
                            </button>
                        </div>
                    </div>

                    {{-- Storage Stats --}}
                    <div class="col-lg-6 mb-4">
                        <h5 class="mb-3">Thống kê lưu trữ</h5>
                        <div ng-if="!loading">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td><i class="fas fa-database text-primary mr-2"></i>Tổng dung lượng</td>
                                        <td class="text-right"><strong>@{{storage.max_formatted}}</strong></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-file text-success mr-2"></i>Đã sử dụng</td>
                                        <td class="text-right"><strong>@{{storage.used_formatted}}</strong></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-folder-open text-warning mr-2"></i>Còn trống</td>
                                        <td class="text-right"><strong>@{{storage.free_formatted}}</strong></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-percentage text-info mr-2"></i>Tỷ lệ sử dụng</td>
                                        <td class="text-right">
                                            <span class="badge" ng-class="{'badge-success': storage.used_percent < 70, 'badge-warning': storage.used_percent >= 70 && storage.used_percent < 90, 'badge-danger': storage.used_percent >= 90}">
                                                @{{storage.used_percent}}%
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="alert" ng-class="{'alert-success': storage.used_percent < 70, 'alert-warning': storage.used_percent >= 70 && storage.used_percent < 90, 'alert-danger': storage.used_percent >= 90}">
                                <i class="fas" ng-class="{'fa-check-circle': storage.used_percent < 70, 'fa-exclamation-triangle': storage.used_percent >= 70}"></i>
                                <span ng-if="storage.used_percent < 70">Dung lượng lưu trữ còn dư dả</span>
                                <span ng-if="storage.used_percent >= 70 && storage.used_percent < 90">Dung lượng lưu trữ sắp đầy</span>
                                <span ng-if="storage.used_percent >= 90">Dung lượng lưu trữ gần hết!</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/pages/settings/languagesCtrl.js')
@vite('resources/js/admin/pages/settings/systemSettingsCtrl.js')
@endpush