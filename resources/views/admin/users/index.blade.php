@extends('layouts.admin')
@section('title','Quản lý người dùng')

@section('content')
<div ng-controller="UsersCtrl" ng-cloak>
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
                            <input class="form-control" ng-model="filter.keyword" ng-change="load()" placeholder="Tên, username, email…">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="small text-muted mb-1">Trạng thái</label>
                            <select class="form-control" ng-model="filter.status" ng-change="load()">
                                <option value="">Tất cả</option>
                                <option value="1">Hoạt động</option>
                                <option value="0">Vô hiệu hóa</option>
                            </select>
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
                            <th>Username</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Nhóm</th>
                            <th>Trạng thái</th>
                            <th style="width:120px">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="r in rows">
                            <td>@{{ r.id }}</td>
                            <td>
                                <strong>@{{ r.username }}</strong>
                                <span class="badge badge-danger ml-1" ng-if="r.is_root">ROOT</span>
                            </td>
                            <td>@{{ r.name }}</td>
                            <td>@{{ r.email }}</td>
                            <td>
                                <span class="badge badge-info mr-1" ng-repeat="g in r.groups">@{{ g.name }}</span>
                                <span class="text-muted" ng-if="!r.groups || r.groups.length === 0">-</span>
                            </td>
                            <td>
                                <span class="badge" ng-class="r.status ? 'badge-success' : 'badge-secondary'">
                                    @{{ r.status ? 'Hoạt động' : 'Vô hiệu' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" ng-click="openEdit(r)"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-outline-danger" ng-click="remove(r)" ng-if="!r.is_root"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr ng-if="!rows.length && !loading">
                            <td colspan="7" class="text-center">Không có dữ liệu</td>
                        </tr>
                        <tr ng-if="loading">
                            <td colspan="7" class="text-center">Đang tải…</td>
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
@vite('resources/js/admin/pages/usersCtrl.js')
@endpush