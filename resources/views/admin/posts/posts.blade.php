@extends('layouts.admin')
@section('title','Bài viết')

@section('content')
<div ng-controller="PostsCtrl">
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                @if(auth('admin')->user()->is_root || auth('admin')->user()->hasPermission('posts.manage'))
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
                            <input class="form-control" ng-model="filter.keyword" placeholder="Tiêu đề / slug …">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="small text-muted mb-1">Danh mục chính</label>
                            <select class="form-control" ng-model="filter.category_id">
                                <option value="">Tất cả</option>
                                <option ng-repeat="c in categories" value="@{{c.id}}">@{{c.name}}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label class="small text-muted mb-1">Trạng thái</label>
                            <select class="form-control" ng-model="filter.status">
                                <option value="">Tất cả</option>
                                <option value="1">Hoạt động</option>
                                <option value="0">Không</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label class="small text-muted mb-1">Nổi bật</label>
                            <select class="form-control" ng-model="filter.is_featured">
                                <option value="">Tất cả</option>
                                <option value="1">Nổi bật</option>
                                <option value="0">Thường</option>
                            </select>
                        </div>
                        <div class="form-group col-md-1 align-self-end">
                            <button type="button" class="btn btn-outline-secondary" ng-click="resetFilter()"><i class="fas fa-undo mr-1"></i></button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th role="button" ng-click="toggleSort('id')">ID <i class="fas" ng-class="sortIcon('id')"></i></th>
                            <th>Tiêu đề</th>
                            <th>Danh mục chính</th>
                            <th>Danh mục liên quan</th>
                            <th>Nổi bật</th>
                            <th>Trạng thái</th>
                            @if(auth('admin')->user()->is_root || auth('admin')->user()->hasPermission('posts.manage'))
                            <th style="width:120px">Thao tác</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="r in rows">
                            <td>@{{r.id}}</td>
                            <td>@{{ getTitle(r) }}</td>
                            <td>@{{ getMainCategoryName(r) }}</td>
                            <td>@{{ getRelatedCategoriesDisplay(r) }}</td>
                            <td><span class="badge" ng-class="r.is_featured?'badge-warning':'badge-light'">@{{ r.is_featured?'Nổi bật':'-' }}</span></td>
                            <td><span class="badge" ng-class="r.status==1?'badge-success':'badge-secondary'">@{{ r.status==1?'Xuất bản':'Nháp' }}</span></td>
                            @if(auth('admin')->user()->is_root || auth('admin')->user()->hasPermission('posts.manage'))
                            <td>
                                <button class="btn btn-sm btn-outline-primary" ng-click="openEdit(r)"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-outline-danger" ng-click="remove(r)"><i class="fas fa-trash"></i></button>
                            </td>
                            @endif
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

            <nav ng-if="meta && meta.last_page" class="d-flex justify-content-end">
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
@vite('resources/js/admin/pages/posts/postsCtrl.js')
@endpush