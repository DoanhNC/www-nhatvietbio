@extends('layouts.admin')
@section('title','Quản lý Ngôn ngữ')

@section('content')
<div ng-controller="LanguagesCtrl">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap bg-directory-default">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-globe mr-2"></i>Quản lý Ngôn ngữ</h6>
            <button class="btn btn-primary btn-sm" ng-click="openCreate()"><i class="fas fa-plus"></i> Thêm ngôn ngữ</button>
        </div>
        <div class="card-body">
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
        </div>
    </div>

    {{-- Include all modals from partials --}}
    @include('admin.settings.partials.language-modals')
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/pages/settings/languagesCtrl.js')
@endpush