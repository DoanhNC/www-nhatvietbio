@extends('layouts.admin')
@section('title', 'Quản lý Video YouTube')

@section('content')
<div ng-controller="VideoCtrl" ng-cloak>
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <button class="btn btn-primary" ng-click="openAddModal()">
                    <i class="fab fa-youtube"></i> Thêm Video
                </button>
                <span class="text-muted small" ng-if="videos.length">
                    Tổng: @{{ videos.length }} video
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:50px">STT</th>
                            <th style="width:80px">Thứ tự</th>
                            <th style="width:160px">Thumbnail</th>
                            <th>Tiêu đề</th>
                            <th style="width:100px">Trạng thái</th>
                            <th style="width:80px">Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="video in videos track by video.id">
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
                                <a ng-href="@{{ video.youtube_url }}" target="_blank" class="d-block position-relative">
                                    <img ng-if="video.thumbnail" ng-src="@{{ video.thumbnail }}"
                                        class="img-thumbnail"
                                        style="width:140px;height:80px;object-fit:cover"
                                        alt="Thumbnail">
                                    <div class="position-absolute d-flex align-items-center justify-content-center"
                                        style="top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.3)">
                                        <i class="fab fa-youtube text-danger" style="font-size:28px"></i>
                                    </div>
                                </a>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm"
                                    ng-model="video.title"
                                    placeholder="Nhập tiêu đề (tùy chọn)"
                                    ng-blur="updateVideo(video)">
                                <small class="text-muted d-block mt-1 text-truncate" style="max-width:200px">
                                    @{{ video.youtube_url }}
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input"
                                        id="active-@{{ video.id }}"
                                        ng-model="video.is_active"
                                        ng-change="updateVideo(video)">
                                    <label class="custom-control-label" for="active-@{{ video.id }}">
                                        <span class="badge" ng-class="video.is_active ? 'badge-success' : 'badge-secondary'">
                                            @{{ video.is_active ? 'Bật' : 'Tắt' }}
                                        </span>
                                    </label>
                                </div>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-danger" ng-click="deleteVideo(video)" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr ng-if="!videos.length && !loading">
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fab fa-youtube fa-2x mb-2 d-block"></i>
                                Chưa có video nào. Nhấn "Thêm Video" để bắt đầu.
                            </td>
                        </tr>
                        <tr ng-if="loading">
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-spinner fa-spin"></i> Đang tải...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Video Modal -->
    <div class="modal fade" id="videoFormModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header -->
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <i class="fab fa-youtube text-danger mr-2"></i>Thêm Video YouTube
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" style="opacity: 0.8;">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body px-4 py-4">
                    <!-- YouTube URL Input -->
                    <div class="mb-4">
                        <label class="font-weight-bold mb-2">
                            <i class="fas fa-link text-primary mr-1"></i> URL YouTube
                            <span class="text-danger">*</span>
                        </label>
                        <input type="url" class="form-control form-control-lg border"
                            ng-model="formData.youtube_url"
                            placeholder="https://www.youtube.com/watch?v=..."
                            ng-change="previewVideo()"
                            style="border-radius: 8px;">
                        <small class="text-muted mt-1 d-block">
                            Dán link video YouTube vào đây
                        </small>
                    </div>

                    <!-- Preview -->
                    <div class="mb-4" ng-if="formData.preview_id">
                        <label class="font-weight-bold mb-2">
                            <i class="fas fa-eye text-secondary mr-1"></i> Xem trước
                        </label>
                        <div class="position-relative rounded overflow-hidden" style="background:#000">
                            <img ng-src="https://img.youtube.com/vi/@{{ formData.preview_id }}/mqdefault.jpg"
                                class="w-100"
                                style="object-fit:contain">
                            <div class="position-absolute d-flex align-items-center justify-content-center"
                                style="top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.3)">
                                <i class="fab fa-youtube text-danger" style="font-size:48px"></i>
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
                            placeholder="Nhập tiêu đề cho video..."
                            style="border-radius: 8px;">
                    </div>

                    <!-- Status Toggle -->
                    <div class="d-flex align-items-center justify-content-between p-3 rounded" style="background: #f8f9fc;">
                        <div>
                            <i class="fas fa-eye text-secondary mr-2"></i>
                            <span class="font-weight-bold">Hiển thị video</span>
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
                        ng-click="saveVideo()"
                        ng-disabled="saving || !formData.youtube_url || !formData.preview_id">
                        <i class="fas mr-1" ng-class="saving ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                        @{{ saving ? 'Đang lưu...' : 'Thêm video' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/pages/videos/videoCtrl.js')
@endpush