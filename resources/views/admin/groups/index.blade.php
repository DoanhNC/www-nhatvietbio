@extends('layouts.admin')
@section('title','Quản lý nhóm')

@section('content')
<div ng-controller="GroupsCtrl" ng-cloak>
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <button class="btn btn-primary" ng-click="openCreate()"><i class="fas fa-plus"></i> Thêm mới</button>
                <button class="btn btn-light border" id="btnToggleFilter"><i class="fas fa-sliders-h mr-1"></i><span class="btn-text"></span></button>
            </div>

            <div class="filter-content mb-2">
                <form>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="small text-muted mb-1">Từ khoá</label>
                            <input class="form-control" ng-model="filter.keyword" ng-change="load()" placeholder="Tên nhóm…">
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
                            <th role="button" ng-click="toggleSort('id')">ID <i class="fas" ng-class="sortIcon('id')"></i></th>
                            <th>Tên nhóm</th>
                            <th>Mô tả</th>
                            <th>Quyền</th>
                            <th>Thành viên</th>
                            <th style="width:120px">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="r in rows">
                            <td>@{{ r.id }}</td>
                            <td><strong>@{{ r.name }}</strong></td>
                            <td>@{{ r.description || '-' }}</td>
                            <td>
                                <span class="badge badge-primary mr-1" ng-repeat="perm in r.permissions" ng-if="$index < 3">@{{ permissionLabels[perm] || perm }}</span>
                                <span class="badge badge-secondary" ng-if="r.permissions.length > 3">+@{{ r.permissions.length - 3 }}</span>
                                <span class="text-muted" ng-if="!r.permissions || r.permissions.length === 0">Không có quyền</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">@{{ r.users_count }}</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" ng-click="openEdit(r)"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-outline-danger" ng-click="remove(r)"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr ng-if="!rows.length && !loading">
                            <td colspan="6" class="text-center">Không có dữ liệu</td>
                        </tr>
                        <tr ng-if="loading">
                            <td colspan="6" class="text-center">Đang tải…</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav ng-if="meta && meta.last_page > 1" class="d-flex justify-content-end">
                <ul class="pagination">
                    <li class="page-item" ng-class="{disabled: meta.current_page==1}">
                        <a class="page-link" href="" ng-click="goto(meta.current_page-1)">Trước</a>
                    </li>
                    <li class="page-item" ng-repeat="p in [].constructor(meta.last_page) track by $index"
                        ng-class="{active: ($index+1)==meta.current_page}">
                        <a class="page-link" href="" ng-click="goto($index+1)">@{{$index+1}}</a>
                    </li>
                    <li class="page-item" ng-class="{disabled: meta.current_page==meta.last_page}">
                        <a class="page-link" href="" ng-click="goto(meta.current_page+1)">Sau</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/pages/groupsCtrl.js')
@endpush