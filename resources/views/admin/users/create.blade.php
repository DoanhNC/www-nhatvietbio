@extends('layouts.admin')
@section('title','Thêm người dùng')

@section('content')
<div ng-controller="UsersCreateCtrl" ng-cloak>
    <div class="card shadow mb-4">
        <div class="card-body">
            <form name="userForm" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Username <span class="text-danger">*</span></label>
                            <input class="form-control" ng-model="model.username" required pattern="^[a-zA-Z0-9_]+$"
                                ng-class="{'is-invalid': submitted && !model.username}">
                            <small class="text-muted">Chỉ chữ cái, số và dấu _</small>
                            <div class="invalid-feedback" ng-show="submitted && !model.username">Vui lòng nhập username</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Họ tên <span class="text-danger">*</span></label>
                            <input class="form-control" ng-model="model.name" required
                                ng-class="{'is-invalid': submitted && !model.name}">
                            <div class="invalid-feedback" ng-show="submitted && !model.name">Vui lòng nhập họ tên</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" ng-model="model.email" required
                                ng-class="{'is-invalid': submitted && !model.email}">
                            <div class="invalid-feedback" ng-show="submitted && !model.email">Vui lòng nhập email</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" ng-model="model.password" required
                                ng-class="{'is-invalid': submitted && !model.password}">
                            <div class="invalid-feedback" ng-show="submitted && !model.password">Vui lòng nhập mật khẩu</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nhóm</label>
                            <select id="groupSelect" class="form-control chosen-select" ng-model="model.group_ids" multiple data-placeholder="Chọn nhóm...">
                                <option ng-repeat="g in groups" value="@{{ g.id }}">@{{ g.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-switch mt-4">
                                <input type="checkbox" class="custom-control-input" id="isActive" ng-model="model.status">
                                <label class="custom-control-label" for="isActive">Hoạt động</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <a href="{{ route('admin.users') }}" class="btn btn-secondary">Quay lại</a>
                    <button class="btn btn-primary" type="button" ng-click="save()" ng-disabled="saving">@{{ saving?'Đang lưu…':'Lưu' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/pages/usersCreateCtrl.js')
@endpush