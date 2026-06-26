@extends('layouts.admin')
@section('title', 'Quản lý Slide')

@section('content')
<div ng-controller="SlideCtrl" ng-cloak>
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <button class="btn btn-primary" ng-click="openAddModal()">
                    <i class="fas fa-plus"></i> Thêm mới
                </button>
                <span class="text-muted small" ng-if="slides.length">
                    Tổng: @{{ slides.length }} slide
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:50px">STT</th>
                            <th style="width:80px">Thứ tự</th>
                            <th style="width:120px">Hình ảnh</th>
                            <th>Tiêu đề</th>
                            <th style="width:100px">Trạng thái</th>
                            <th style="width:80px">Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="slide in slides track by slide.id">
                            <td class="text-center">@{{ $index + 1 }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-light border" ng-click="moveUp($index)" ng-disabled="$first" title="Lên">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                    <button class="btn btn-light border" ng-click="moveDown($index)" ng-disabled="$last" title="Xuống">
                                        <i class="fas fa-arrow-down"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <img ng-if="slide.media" ng-src="@{{ slide.media.url }}"
                                    class="img-thumbnail"
                                    style="width:100px;height:60px;object-fit:cover"
                                    alt="Slide">
                                <span ng-if="!slide.media" class="text-muted">-</span>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm"
                                    ng-model="slide.title"
                                    placeholder="Nhập tiêu đề (tùy chọn)"
                                    ng-blur="updateSlide(slide)">
                            </td>
                            <td class="text-center">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input"
                                        id="active-@{{ slide.id }}"
                                        ng-model="slide.is_active"
                                        ng-change="updateSlide(slide)">
                                    <label class="custom-control-label" for="active-@{{ slide.id }}">
                                        <span class="badge" ng-class="slide.is_active ? 'badge-success' : 'badge-secondary'">
                                            @{{ slide.is_active ? 'Bật' : 'Tắt' }}
                                        </span>
                                    </label>
                                </div>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-danger" ng-click="deleteSlide(slide)" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr ng-if="!slides.length && !loading">
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-images fa-2x mb-2 d-block"></i>
                                Chưa có slide nào. Nhấn "Thêm mới" để bắt đầu.
                            </td>
                        </tr>
                        <tr ng-if="loading">
                            <td colspan="5" class="text-center py-4">
                                <i class="fas fa-spinner fa-spin"></i> Đang tải...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Slide Modal -->
    <div class="modal fade" id="slideFormModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header with gradient -->
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle mr-2"></i>Thêm Slide mới
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 0.8;">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body px-4 py-4">
                    <!-- Image Selection - Main focus -->
                    <div class="mb-4">
                        <label class="font-weight-bold mb-2">
                            <i class="fas fa-image text-primary mr-1"></i> Chọn hình ảnh
                            <span class="text-danger">*</span>
                        </label>
                        <div class="image-picker-box rounded-lg text-center p-4"
                            ng-click="openMediaPicker()"
                            ng-class="{'border-primary': formData.selectedMedia}"
                            style="border: 2px dashed #d1d3e2; background: linear-gradient(180deg, #f8f9fc 0%, #fff 100%); 
                                   cursor: pointer; transition: all 0.3s ease; min-height: 180px;">
                            <!-- Has image -->
                            <div ng-if="formData.selectedMedia" class="py-2">
                                <div class="position-relative d-inline-block">
                                    <img ng-src="@{{ formData.selectedMedia.url }}"
                                        class="rounded shadow-sm"
                                        style="max-height: 130px; max-width: 100%; object-fit: contain;">
                                    <span class="position-absolute bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 24px; height: 24px; top: -8px; right: -8px; font-size: 12px;">
                                        <i class="fas fa-check"></i>
                                    </span>
                                </div>
                                <div class="mt-2 small text-muted text-truncate" style="max-width: 250px; margin: 0 auto;">
                                    @{{ formData.selectedMedia.original_name }}
                                </div>
                                <div class="mt-1">
                                    <span class="badge badge-light border">
                                        <i class="fas fa-sync-alt text-primary mr-1"></i> Click để đổi ảnh
                                    </span>
                                </div>
                            </div>
                            <!-- No image -->
                            <div ng-if="!formData.selectedMedia" class="py-3">
                                <div class="mb-3">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light"
                                        style="width: 70px; height: 70px;">
                                        <i class="fas fa-cloud-upload-alt fa-2x text-primary"></i>
                                    </span>
                                </div>
                                <div class="text-primary font-weight-bold mb-1">Click để chọn ảnh</div>
                                <div class="small text-muted">Chọn từ thư viện Media</div>
                            </div>
                        </div>
                    </div>

                    <!-- Title Input -->
                    <div class="mb-3">
                        <label class="font-weight-bold mb-2">
                            <i class="fas fa-heading text-secondary mr-1"></i> Tiêu đề
                            <span class="text-muted font-weight-normal">(tùy chọn)</span>
                        </label>
                        <input type="text" class="form-control form-control-lg border"
                            ng-model="formData.title"
                            placeholder="Nhập tiêu đề cho slide..."
                            style="border-radius: 8px;">
                    </div>

                    <!-- Status Toggle -->
                    <div class="d-flex align-items-center justify-content-between p-3 rounded" style="background: #f8f9fc;">
                        <div>
                            <i class="fas fa-eye text-secondary mr-2"></i>
                            <span class="font-weight-bold">Hiển thị slide</span>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="formIsActive" ng-model="formData.is_active">
                            <label class="custom-control-label" for="formIsActive">
                                <span class="badge px-3 py-2" ng-class="formData.is_active ? 'badge-success' : 'badge-secondary'">
                                    <i class="fas mr-1" ng-class="formData.is_active ? 'fa-check' : 'fa-times'"></i>
                                    @{{ formData.is_active ? 'Bật' : 'Tắt' }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer border-0 bg-light px-4 py-3">
                    <button type="button" class="btn btn-light border px-4" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Hủy
                    </button>
                    <button type="button" class="btn btn-primary px-4"
                        ng-click="saveSlide()"
                        ng-disabled="saving || !formData.selectedMedia">
                        <i class="fas mr-1" ng-class="saving ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                        @{{ saving ? 'Đang lưu...' : 'Thêm slide' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .image-picker-box:hover {
            border-color: #006545 !important;
            background: linear-gradient(180deg, #eaecf4 0%, #f8f9fc 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 101, 69, 0.15);
        }

        .image-picker-box.border-primary {
            border-color: #006545 !important;
            border-style: solid !important;
        }
    </style>

    <!-- Media Picker Modal -->
    <div class="modal fade" id="mediaPickerModal" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title"><i class="fas fa-images mr-2"></i>Chọn ảnh cho Slide</h6>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <media-picker mode="picker" select-mode="single" accept="image/*" on-select="onMediaSelected(files)"></media-picker>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/pages/slides/slideCtrl.js')
@endpush