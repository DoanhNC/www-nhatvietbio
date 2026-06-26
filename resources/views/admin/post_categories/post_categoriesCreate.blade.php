@extends('layouts.admin')
@section('title','Thêm danh mục bài viết')

@section('content')
<div ng-controller="PostCategoriesCreateCtrl" ng-cloak>
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="text-center py-3" ng-if="loadingLanguages">
                <i class="fas fa-spinner fa-spin"></i> Đang tải...
            </div>
            <form name="categoryForm" novalidate ng-if="!loadingLanguages">
                {{-- Dynamic language name fields --}}
                <div class="form-group" ng-repeat="lang in languages">
                    <label>Tên (@{{ lang.name }}) <span class="text-danger" ng-if="lang.is_default">*</span></label>
                    <input class="form-control" ng-model="model.names[lang.code]"
                        ng-class="{'is-invalid': submitted && lang.is_default && !model.names[lang.code]}"
                        ng-required="lang.is_default">
                    <div class="invalid-feedback" ng-show="submitted && lang.is_default && !model.names[lang.code]">
                        Vui lòng nhập tên @{{ lang.name }}
                    </div>
                </div>

                {{-- Parent category --}}
                <div class="form-group">
                    <label>Danh mục cha</label>
                    <select class="form-control" ng-model="model.parent_id">
                        <option value="">-- Không có --</option>
                        <option ng-repeat="cat in parentCategories" ng-value="cat.id">@{{ cat.name }}</option>
                    </select>
                </div>

                {{-- Status --}}
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="isActive" ng-model="model.is_active">
                        <label class="custom-control-label" for="isActive">Hoạt động</label>
                    </div>
                </div>

                {{-- Show in menu --}}
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="showInMenu" ng-model="model.show_in_menu">
                        <label class="custom-control-label" for="showInMenu">Hiển thị trong menu</label>
                    </div>
                    <small class="form-text text-muted">Bật để hiển thị danh mục này trong menu website</small>
                </div>

                {{-- Show related posts --}}
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="showRelatedPosts" ng-model="model.show_related_posts">
                        <label class="custom-control-label" for="showRelatedPosts">Hiển thị bài viết cùng danh mục</label>
                    </div>
                    <small class="form-text text-muted">Bật để hiển thị bài viết cùng danh mục và các danh mục con ở sidebar</small>
                </div>

                <div class="text-right">
                    <a href="{{ route('admin.post_categories') }}" class="btn btn-secondary">Quay lại</a>
                    <button class="btn btn-primary" type="button" ng-click="save()" ng-disabled="saving">@{{ saving?'Đang lưu…':'Lưu' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/pages/post_categories/postCategoriesCreateCtrl.js')
@endpush