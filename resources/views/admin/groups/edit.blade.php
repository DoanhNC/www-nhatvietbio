@extends('layouts.admin')
@section('title','Sửa nhóm')

@section('content')
<div ng-controller="GroupsEditCtrl" ng-cloak>
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="text-center py-3" ng-if="loadingGroup">
                <i class="fas fa-spinner fa-spin"></i> Đang tải...
            </div>
            <form name="groupForm" novalidate ng-if="!loadingGroup">
                <div class="form-group">
                    <label>Tên nhóm <span class="text-danger">*</span></label>
                    <input class="form-control" ng-model="model.name" required
                        ng-class="{'is-invalid': submitted && !model.name}">
                    <div class="invalid-feedback" ng-show="submitted && !model.name">Vui lòng nhập tên nhóm</div>
                </div>
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea class="form-control" ng-model="model.description" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Phân quyền</label>
                    <div class="border rounded p-3">
                        <div class="row">
                            <div class="col-md-6" ng-repeat="(key, label) in availablePermissions">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="perm_@{{ key }}"
                                        ng-checked="model.permissions.indexOf(key) > -1"
                                        ng-click="togglePermission(key)">
                                    <label class="custom-control-label" for="perm_@{{ key }}">@{{ label }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <a href="{{ route('admin.groups') }}" class="btn btn-secondary">Quay lại</a>
                    <button class="btn btn-primary" type="button" ng-click="save()" ng-disabled="saving">@{{ saving?'Đang lưu…':'Lưu' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

<script>
    var pageData = {
        'id': <?php echo $id ?>,
        'listUrl': '<?php echo route('admin.groups') ?>'
    };
</script>
@push('scripts')
@vite('resources/js/admin/pages/groupsEditCtrl.js')
@endpush