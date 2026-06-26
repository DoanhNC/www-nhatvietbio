@extends('layouts.admin')
@section('title','Danh mục bài viết')

@section('content')
<div ng-controller="PostCategoriesCtrl" ng-cloak>
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                @if(auth('admin')->user()->is_root || auth('admin')->user()->hasPermission('categories.manage'))
                <button class="btn btn-primary" ng-click="openCreate()"><i class="fas fa-plus"></i> Thêm mới</button>
                @else
                <div></div>
                @endif
                <button class="btn btn-light border" id="btnToggleFilter"><i class="fas fa-sliders-h mr-1"></i><span class="btn-text"></span></button>
            </div>

            <div class="filter-content mb-2">
                <form>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="small text-muted mb-1">Từ khoá</label>
                            <input class="form-control" ng-model="filter.keyword" placeholder="Tên danh mục …">
                        </div>
                        <div class="form-group col-md-2 align-self-end">
                            <button type="button" class="btn btn-outline-secondary" ng-click="resetFilter()"><i class="fas fa-undo mr-1"></i> Đặt lại</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:50px">STT</th>
                            <th style="width:80px">Thứ tự</th>
                            <th ng-repeat="lang in languages">Tên (@{{ lang.code.toUpperCase() }})</th>
                            <th class="text-center">Menu</th>
                            <th class="text-center">BV cùng DM</th>
                            <th>Trạng thái</th>
                            @if(auth('admin')->user()->is_root || auth('admin')->user()->hasPermission('categories.manage'))
                            <th style="width:100px">Thao tác</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="r in rows" ng-class="{'table-light': !r.parent_id}">
                            <td class="text-center">
                                <span ng-if="!r.parent_id">@{{ $index + 1 }}</span>
                                <span ng-if="r.parent_id" class="text-muted">-</span>
                            </td>
                            <td class="text-center">
                                <div ng-if="!r.parent_id" class="btn-group btn-group-sm">
                                    <button class="btn btn-light border" ng-click="moveUp(r)" ng-disabled="$first" title="Lên">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                    <button class="btn btn-light border" ng-click="moveDown(r)" ng-disabled="$last" title="Xuống">
                                        <i class="fas fa-arrow-down"></i>
                                    </button>
                                </div>
                                <span ng-if="r.parent_id" class="text-muted">-</span>
                            </td>
                            <td ng-repeat="lang in languages">
                                <i ng-if="r.parent_id" class="fas fa-level-up-alt fa-rotate-90 text-info mr-2" style="font-size:12px;"></i>
                                <span ng-class="{'font-weight-bold': !r.parent_id}">@{{ r.names[lang.code] || '-' }}</span>
                            </td>
                            <td class="text-center">
                                <i class="fas" ng-class="r.show_in_menu ? 'fa-check-circle text-success' : 'fa-times-circle text-secondary'"></i>
                            </td>
                            <td class="text-center">
                                <i class="fas" ng-class="r.show_related_posts ? 'fa-check-circle text-success' : 'fa-times-circle text-secondary'"></i>
                            </td>
                            <td>
                                <span class="badge" ng-class="r.is_active ? 'badge-success' : 'badge-secondary'">
                                    @{{ r.is_active ? 'Hoạt động' : 'Ẩn' }}
                                </span>
                            </td>
                            @if(auth('admin')->user()->is_root || auth('admin')->user()->hasPermission('categories.manage'))
                            <td>
                                <button class="btn btn-sm btn-outline-primary" ng-click="openEdit(r)"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-outline-danger" ng-click="remove(r)"><i class="fas fa-trash"></i></button>
                            </td>
                            @endif
                        </tr>
                        <tr ng-if="!rows.length && !loading">
                            <td colspan="@{{ 6 + languages.length }}" class="text-center">Không có dữ liệu</td>
                        </tr>
                        <tr ng-if="loading">
                            <td colspan="@{{ 6 + languages.length }}" class="text-center">Đang tải…</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/pages/post_categories/postCategoriesCtrl.js')
@endpush