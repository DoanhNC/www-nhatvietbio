@extends('layouts.admin')
@section('title','Quản lý Media')

@section('content')
<div ng-controller="MediaCtrl" class="media-manager" ng-cloak>
    <div class="media-container">
        {{-- Left Sidebar - Folder Tree --}}
        <div class="media-sidebar-left">
            <div class="folder-tree-container">
                {{-- My Files Header --}}
                <div class="folder-tree-header" ng-click="viewMode !== 'trash' && navigateTo(null); currentView = 'files'"
                    ng-class="{active: currentView !== 'trash'}" style="cursor:pointer">
                    <i class="fas fa-folder"></i> Tất cả
                </div>

                {{-- Scrollable folder tree --}}
                <div class="folder-tree-scrollable" ng-if="currentView !== 'trash'">
                    <ul class="folder-tree">
                        <li ng-repeat="folder in folderTree" ng-include="'folderTemplate'"></li>
                    </ul>
                </div>

                {{-- Trash Header --}}
                <div class="folder-tree-header trash-header mt-3" ng-click="openTrash()"
                    ng-class="{active: currentView === 'trash'}" style="cursor:pointer">
                    <i class="fas fa-trash-alt text-danger"></i> Tệp đã xóa
                </div>
            </div>

            {{-- Storage Stats --}}
            <div class="storage-stats" style="background: #f8f9fc; border-radius: 8px; padding: 12px; margin-top: 12px;">
                <div class="text-center mb-2">
                    <div style="position: relative; width: 70px; height: 70px; margin: 0 auto;">
                        <canvas id="storageChart" width="70" height="70"></canvas>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.75rem; font-weight: bold;">@{{ storageStats.used_percent }}%</div>
                    </div>
                </div>
                <div style="font-size: 0.75rem;">
                    <div class="d-flex justify-content-between mb-1">
                        <span><span style="width: 8px; height: 8px; border-radius: 50%; background: #f6c23e; display: inline-block; margin-right: 4px;"></span>Đã dùng:</span>
                        <strong class="text-dark">@{{ storageStats.used_formatted }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span><span style="width: 8px; height: 8px; border-radius: 50%; background: #e0e0e0; display: inline-block; margin-right: 4px;"></span>Còn trống:</span>
                        <strong class="text-dark">@{{ storageStats.free_formatted }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><span style="width: 8px; height: 8px; border-radius: 50%; background: #006545; display: inline-block; margin-right: 4px;"></span>Tổng:</span>
                        <strong class="text-dark">@{{ storageStats.max_formatted }}</strong>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-secondary btn-block mt-2" ng-click="loadStorageStats()" style="font-size: 0.7rem; padding: 4px 8px;">
                    <i class="fas fa-sync-alt"></i> Làm mới
                </button>
            </div>

            <div class="sidebar-actions mt-2 d-flex">
                <button class="btn btn-sm btn-primary flex-fill mr-1" ng-click="openSettingsModal()" style="font-size: 0.75rem;">
                    <i class="fas fa-cog"></i> Cài đặt
                </button>
                <button class="btn btn-sm btn-outline-secondary flex-fill" ng-click="openHistoryModal()" style="font-size: 0.75rem;">
                    <i class="fas fa-history"></i> Lịch sử
                </button>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="media-main">
            {{-- Toolbar --}}
            <div class="media-toolbar">
                <div class="toolbar-left">
                    <button class="btn btn-light border" ng-click="goBack()" ng-disabled="!currentFolderId">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    {{-- Normal view buttons --}}
                    <button class="btn btn-light border" ng-if="currentView !== 'trash'" ng-click="goUp()" ng-disabled="!currentFolderId">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <button class="btn btn-light border" ng-if="currentView !== 'trash'" ng-click="loadContents()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    {{-- Trash view buttons --}}
                    <button class="btn btn-light border" ng-if="currentView === 'trash'" ng-click="trashGoUp()" ng-disabled="!currentTrashFolderId">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <button class="btn btn-light border" ng-if="currentView === 'trash'" ng-click="loadTrash()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    {{-- Add menu (only in normal view) --}}
                    <div class="btn-group" ng-if="currentView !== 'trash'">
                        <button class="btn btn-light border dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-plus"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" ng-click="openUploadModal()">
                                <i class="fas fa-upload text-primary"></i> Tải lên
                            </a>
                            <a class="dropdown-item" href="#" ng-click="openCreateFolderModal()">
                                <i class="fas fa-folder-plus text-warning"></i> Tạo thư mục
                            </a>
                        </div>
                    </div>
                </div>
                <div class="toolbar-center">
                    <div class="input-group search-box">
                        <input type="text" class="form-control"
                            placeholder="@{{ currentView === 'trash' ? 'Tìm trong thùng rác' : 'Nhập tên tệp' }}"
                            ng-model="searchKeyword"
                            ng-keypress="$event.keyCode === 13 && search()">
                        <div class="input-group-append">
                            <button class="btn btn-light border" ng-click="search()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="toolbar-right">
                    <div class="btn-group">
                        <button class="btn btn-light border" ng-class="{active: viewMode === 'grid'}" ng-click="viewMode = 'grid'">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="btn btn-light border" ng-class="{active: viewMode === 'list'}" ng-click="viewMode = 'list'">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Breadcrumb --}}
            <div class="media-breadcrumb">
                {{-- Normal view breadcrumb --}}
                <span ng-if="currentView !== 'trash'" ng-repeat="crumb in breadcrumb" class="breadcrumb-item">
                    <a href="#" ng-click="navigateTo(crumb.id)">@{{ crumb.name }}</a>
                    <i class="fas fa-chevron-right" ng-if="!$last"></i>
                </span>
                {{-- Trash view breadcrumb --}}
                <span ng-if="currentView === 'trash'" ng-repeat="crumb in trashBreadcrumb" class="breadcrumb-item">
                    <a href="#" ng-click="navigateTrashTo(crumb.id)">@{{ crumb.name }}</a>
                    <i class="fas fa-chevron-right" ng-if="!$last"></i>
                </span>
                <span class="breadcrumb-info">(@{{ folderCount }} Thư mục - @{{ fileCount }} Tệp tin)</span>
            </div>

            {{-- Normal Content Grid/List --}}
            <div class="media-content" id="mediaContentArea" ng-if="currentView !== 'trash'"
                ng-class="{'grid-view': viewMode === 'grid', 'list-view': viewMode === 'list'}">

                {{-- Loading --}}
                <div class="text-center py-5" ng-if="loading">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Đang tải...</p>
                </div>

                {{-- Empty State --}}
                <div class="text-center py-5 text-muted" ng-if="!loading && folders.length === 0 && files.length === 0">
                    <i class="fas fa-folder-open fa-3x"></i>
                    <p class="mt-2">Thư mục trống</p>
                    <p class="small">Click chuột phải để tạo thư mục hoặc tải lên tệp</p>
                </div>

                {{-- Folders --}}
                <div class="media-item folder" ng-repeat="folder in folders" ng-if="!loading"
                    ng-click="selectItem($event, folder, 'folder')"
                    ng-dblclick="navigateTo(folder.id)"
                    ng-class="{selected: isItemSelected(folder, 'folder')}"
                    oncontextmenu="angular.element(this).scope().showItemContextMenu(event, angular.element(this).scope().folder, 'folder'); return false;">
                    <div class="item-icon">
                        <i class="fas fa-folder fa-3x text-warning"></i>
                        <span class="item-badge folder-badge" ng-if="folder.item_count > 0">@{{ folder.item_count }}</span>
                        <span class="item-badge folder-badge" ng-if="!folder.item_count">folder</span>
                    </div>
                    <div class="item-name" title="@{{ folder.name }}">@{{ folder.name }}</div>
                </div>

                {{-- Files --}}
                <div class="media-item file" ng-repeat="file in files" ng-if="!loading"
                    ng-click="selectItem($event, file, 'file')"
                    ng-class="{selected: isItemSelected(file, 'file')}"
                    oncontextmenu="angular.element(this).scope().showItemContextMenu(event, angular.element(this).scope().file, 'file'); return false;">
                    <div class="item-icon">
                        <img ng-if="file.file_type === 'image'" ng-src="@{{ file.url }}" class="item-thumbnail">
                        <i ng-if="file.file_type !== 'image'" class="fas fa-file fa-3x text-secondary"></i>
                        <span class="item-badge file-badge">@{{ file.extension }}</span>
                    </div>
                    <div class="item-name" title="@{{ file.name }}">@{{ file.name }}</div>
                </div>
            </div>

            {{-- Trash Content Grid/List --}}
            <div class="media-content" id="trashContentArea" ng-if="currentView === 'trash'"
                ng-class="{'grid-view': viewMode === 'grid', 'list-view': viewMode === 'list'}">

                {{-- Loading --}}
                <div class="text-center py-5" ng-if="loading">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Đang tải...</p>
                </div>

                {{-- Empty Trash --}}
                <div class="text-center py-5 text-muted" ng-if="!loading && trashFolders.length === 0 && trashFiles.length === 0">
                    <i class="fas fa-trash-alt fa-3x"></i>
                    <p class="mt-2">Thùng rác trống</p>
                </div>

                {{-- Trash Folders --}}
                <div class="media-item folder" ng-repeat="folder in trashFolders" ng-if="!loading"
                    ng-click="selectTrashItem($event, folder, 'folder')"
                    ng-dblclick="navigateTrashTo(folder.id)"
                    ng-class="{selected: isItemSelected(folder, 'folder')}"
                    oncontextmenu="angular.element(this).scope().showTrashContextMenu(event, angular.element(this).scope().folder, 'folder'); return false;">
                    <div class="item-icon">
                        <i class="fas fa-folder fa-3x text-warning"></i>
                        <span class="item-badge folder-badge" ng-if="folder.item_count > 0">@{{ folder.item_count }}</span>
                        <span class="item-badge folder-badge" ng-if="!folder.item_count">folder</span>
                    </div>
                    <div class="item-name" title="@{{ folder.name }}">@{{ folder.name }}</div>
                </div>

                {{-- Trash Files --}}
                <div class="media-item file" ng-repeat="file in trashFiles" ng-if="!loading"
                    ng-click="selectTrashItem($event, file, 'file')"
                    ng-class="{selected: isItemSelected(file, 'file')}"
                    oncontextmenu="angular.element(this).scope().showTrashContextMenu(event, angular.element(this).scope().file, 'file'); return false;">
                    <div class="item-icon">
                        <img ng-if="file.file_type === 'image'" ng-src="@{{ file.url }}" class="item-thumbnail">
                        <i ng-if="file.file_type !== 'image'" class="fas fa-file fa-3x text-secondary"></i>
                        <span class="item-badge file-badge">@{{ file.extension }}</span>
                    </div>
                    <div class="item-name" title="@{{ file.name }}">@{{ file.name }}</div>
                </div>
            </div>
        </div>
        {{-- Right Sidebar - Details --}}
        <div class="media-sidebar-right" ng-if="selectedItem">
            <div class="detail-header">
                <span>@{{ selectedItem.name }}</span>
                <div class="detail-actions">
                    <button class="btn btn-sm btn-link" ng-if="selectedItem.type === 'folder' && currentView !== 'trash'" ng-click="navigateTo(selectedItem.id)">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <button class="btn btn-sm btn-link" ng-if="selectedItem.type === 'folder' && currentView === 'trash'" ng-click="navigateTrashTo(selectedItem.id)">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <button class="btn btn-sm btn-link" ng-if="selectedItem.type === 'file' && currentView !== 'trash'" ng-click="selectFile(selectedItem)">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>
            <div class="detail-preview">
                <i ng-if="selectedItem.type === 'folder'" class="fas fa-folder fa-5x text-warning"></i>
                <img ng-if="selectedItem.type === 'file' && selectedItem.file_type === 'image'" ng-src="@{{ selectedItem.url }}" class="preview-image">
                <i ng-if="selectedItem.type === 'file' && selectedItem.file_type !== 'image'" class="fas fa-file fa-5x text-secondary"></i>
            </div>
            <div class="detail-info">
                <div class="detail-row">
                    <span class="detail-label">Đường dẫn:</span>
                    <span class="detail-value text-muted" style="word-break:break-all">@{{ selectedItem.path || '/' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tên tệp:</span>
                    <span class="detail-value">@{{ selectedItem.name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Loại:</span>
                    <span class="detail-value">@{{ selectedItem.type === 'folder' ? 'Thư mục' : selectedItem.type_label || 'Tệp tin' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Kích thước:</span>
                    <span class="detail-value">@{{ selectedItem.formatted_size }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Ngày cập nhật:</span>
                    <span class="detail-value">@{{ selectedItem.updated_at | date:'HH:mm - dd/MM/yyyy' }}</span>
                </div>
                <div class="detail-row" ng-if="currentView === 'trash'">
                    <span class="detail-label">Ngày xóa:</span>
                    <span class="detail-value">@{{ selectedItem.deleted_at | date:'HH:mm - dd/MM/yyyy' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Context Menu (Normal View) --}}
    <div class="context-menu" ng-show="contextMenu.visible" ng-style="{top: contextMenu.y + 'px', left: contextMenu.x + 'px'}">
        <div class="context-menu-item" ng-click="openUploadModal()">
            <i class="fas fa-upload text-primary"></i> Tải lên
        </div>
        <div class="context-menu-item" ng-click="openCreateFolderModal()">
            <i class="fas fa-folder-plus text-warning"></i> Tạo thư mục
        </div>
    </div>

    {{-- Item Context Menu (Normal View) - Single Select --}}
    <div class="context-menu" ng-show="itemContextMenu.visible && selectedItems.length <= 1" ng-style="{top: itemContextMenu.y + 'px', left: itemContextMenu.x + 'px'}">
        <div class="context-menu-item" ng-if="itemContextMenu.item.type === 'folder'" ng-click="navigateTo(itemContextMenu.item.id)">
            <i class="fas fa-folder-open"></i> Mở
        </div>
        <div class="context-menu-item" ng-if="itemContextMenu.item.type === 'file' && itemContextMenu.item.file_type === 'image'" ng-click="openImageInNewTab(itemContextMenu.item)">
            <i class="fas fa-external-link-alt text-info"></i> Mở ảnh
        </div>
        <div class="context-menu-item" ng-click="openRenameModal(itemContextMenu.item)">
            <i class="fas fa-edit"></i> Đổi tên
        </div>
        <div class="context-menu-item text-danger" ng-click="deleteItem(itemContextMenu.item)">
            <i class="fas fa-trash"></i> Xóa
        </div>
    </div>

    {{-- Item Context Menu (Normal View) - Multi Select --}}
    <div class="context-menu" ng-show="itemContextMenu.visible && selectedItems.length > 1" ng-style="{top: itemContextMenu.y + 'px', left: itemContextMenu.x + 'px'}">
        <div class="context-menu-header text-muted px-3 py-1" style="font-size: 12px;">
            <i class="fas fa-check-double"></i> Đã chọn @{{ selectedItems.length }} mục
        </div>
        <div class="dropdown-divider"></div>
        <div class="context-menu-item text-danger" ng-click="deleteSelectedItems()">
            <i class="fas fa-trash"></i> Xóa tất cả
        </div>
    </div>

    {{-- Trash Empty Area Context Menu (only at root level) --}}
    <div class="context-menu" ng-show="trashContextMenu.visible && !currentTrashFolderId" ng-style="{top: trashContextMenu.y + 'px', left: trashContextMenu.x + 'px'}">
        <div class="context-menu-item text-danger" ng-click="emptyTrash()">
            <i class="fas fa-broom"></i> Dọn dẹp tất cả
        </div>
    </div>

    {{-- Trash Item Context Menu - Single Select --}}
    <div class="context-menu" ng-show="trashItemContextMenu.visible && selectedItems.length <= 1" ng-style="{top: trashItemContextMenu.y + 'px', left: trashItemContextMenu.x + 'px'}">
        <div class="context-menu-item text-success" ng-click="restoreItem(trashItemContextMenu.item)">
            <i class="fas fa-undo"></i> Khôi phục
        </div>
        <div class="context-menu-item text-danger" ng-click="forceDeleteItem(trashItemContextMenu.item)">
            <i class="fas fa-trash-alt"></i> Xóa vĩnh viễn
        </div>
    </div>

    {{-- Trash Item Context Menu - Multi Select --}}
    <div class="context-menu" ng-show="trashItemContextMenu.visible && selectedItems.length > 1" ng-style="{top: trashItemContextMenu.y + 'px', left: trashItemContextMenu.x + 'px'}">
        <div class="context-menu-header text-muted px-3 py-1" style="font-size: 12px;">
            <i class="fas fa-check-double"></i> Đã chọn @{{ selectedItems.length }} mục
        </div>
        <div class="dropdown-divider"></div>
        <div class="context-menu-item text-success" ng-click="restoreSelectedItems()">
            <i class="fas fa-undo"></i> Khôi phục tất cả
        </div>
    </div>

    {{-- Create Folder Modal --}}
    <div class="modal fade" id="createFolderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tạo thư mục mới</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tên thư mục</label>
                        <input type="text" class="form-control" ng-model="newFolderName" placeholder="Nhập tên thư mục">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" ng-click="createFolder()" ng-disabled="!newFolderName || creatingFolder">
                        @{{ creatingFolder ? 'Đang tạo...' : 'Tạo' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Upload Modal --}}
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tải lên tệp</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="upload-dropzone" ng-class="{dragover: isDragover}"
                        ng-dragover="onDragover($event)"
                        ng-dragleave="onDragleave($event)"
                        ng-drop="onDrop($event)">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted"></i>
                        <p class="mt-2">Kéo thả tệp vào đây hoặc</p>
                        <label class="btn btn-primary">
                            Chọn tệp
                            <input type="file" id="uploadFileInput" multiple style="display:none" onchange="angular.element(this).scope().onFilesSelected(this.files); this.value='';">
                        </label>
                    </div>
                    <div class="upload-files mt-3" ng-if="uploadFiles.length">
                        <div class="upload-file" ng-repeat="f in uploadFiles">
                            <i class="fas fa-file"></i>
                            <span>@{{ f.name }}</span>
                            <span class="text-muted">(@{{ formatBytes(f.size) }})</span>
                            <button class="btn btn-sm btn-link text-danger" ng-click="removeUploadFile($index)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" ng-click="uploadFilesAction()" ng-disabled="!uploadFiles.length || uploading">
                        @{{ uploading ? 'Đang tải lên...' : 'Tải lên' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Rename Modal --}}
    <div class="modal fade" id="renameModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Đổi tên</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tên mới</label>
                        <input type="text" class="form-control" ng-model="renameValue">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" ng-click="renameItem()" ng-disabled="!renameValue || renaming">
                        @{{ renaming ? 'Đang lưu...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- History Modal --}}
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lịch sử cập nhật</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-row mb-3">
                        <div class="col-md-2">
                            <label class="small">Từ ngày</label>
                            <input type="date" class="form-control" ng-model="historyFilter.from_date">
                        </div>
                        <div class="col-md-2">
                            <label class="small">Đến ngày</label>
                            <input type="date" class="form-control" ng-model="historyFilter.to_date">
                        </div>
                        <div class="col-md-2">
                            <label class="small">Hành động</label>
                            <select class="form-control" ng-model="historyFilter.action_type">
                                <option value="">-- Tất cả --</option>
                                <option value="upload">Tải lên</option>
                                <option value="create_folder">Tạo thư mục</option>
                                <option value="rename">Đổi tên</option>
                                <option value="delete">Xóa</option>
                            </select>
                        </div>
                        <div class="col-md-2 align-self-end">
                            <button class="btn btn-primary" ng-click="loadHistory()">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                        <div class="col-md-2 align-self-end">
                            <button class="btn btn-outline-secondary" ng-click="resetHistoryFilter()">
                                <i class="fas fa-undo"></i> Làm mới
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Loại</th>
                                    <th>Thời gian</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="log in historyLogs">
                                    <td><span class="badge" ng-class="getActionBadgeClass(log.action_type)">@{{ log.action_label }}</span></td>
                                    <td>@{{ log.created_at }}</td>
                                    <td>@{{ log.description }}</td>
                                </tr>
                                <tr ng-if="!historyLogs.length && !loadingHistory">
                                    <td colspan="3" class="text-center text-muted">Không có dữ liệu</td>
                                </tr>
                                <tr ng-if="loadingHistory">
                                    <td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirm Modal --}}
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" ng-class="{'bg-danger text-white': confirmModal.danger}">
                    <h5 class="modal-title">
                        <i class="fas" ng-class="confirmModal.icon || 'fa-question-circle'"></i>
                        @{{ confirmModal.title }}
                    </h5>
                    <button type="button" class="close" ng-class="{'text-white': confirmModal.danger}" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">@{{ confirmModal.message }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="button" class="btn" ng-class="confirmModal.danger ? 'btn-danger' : 'btn-primary'" ng-click="confirmModal.onConfirm()">
                        <i class="fas" ng-class="confirmModal.confirmIcon || 'fa-check'"></i> @{{ confirmModal.confirmText || 'Xác nhận' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Settings Modal --}}
    <div class="modal fade" id="settingsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-cog"></i> Cài đặt Media</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Chuyển đổi ảnh Webp</label>
                        <div class="mt-2">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="webpOff" name="convertToWebp" class="custom-control-input"
                                    ng-model="settingsForm.convert_to_webp" ng-value="false">
                                <label class="custom-control-label" for="webpOff">Không hoạt động</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="webpOn" name="convertToWebp" class="custom-control-input"
                                    ng-model="settingsForm.convert_to_webp" ng-value="true">
                                <label class="custom-control-label" for="webpOn">Hoạt động</label>
                            </div>
                        </div>
                        <small class="form-text text-muted mt-2">
                            Khi upload ảnh hệ thống sẽ tự động xóa ảnh gốc và chuyển định dạng sang Webp
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-block" ng-click="saveSettings()" ng-disabled="savingSettings">
                        <i class="fas" ng-class="savingSettings ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                        @{{ savingSettings ? 'Đang cập nhật...' : 'Cập nhật' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Folder Tree Template --}}
    <script type="text/ng-template" id="folderTemplate">
        <div class="folder-tree-item" ng-class="{active: currentFolderId === folder.id}" ng-click="navigateTo(folder.id)">
            <i class="fas folder-toggle" 
               ng-class="{'fa-chevron-down': folder.expanded, 'fa-chevron-right': !folder.expanded}"
               ng-if="folder.children && folder.children.length" 
               ng-click="folder.expanded = !folder.expanded; $event.stopPropagation()"></i>
            <i class="fas fa-folder text-warning"></i>
            <span>@{{ folder.name }}</span>
        </div>
        <ul ng-if="folder.children.length && folder.expanded" class="folder-tree-children">
            <li ng-repeat="folder in folder.children" ng-include="'folderTemplate'"></li>
        </ul>
    </script>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
@vite('resources/js/admin/pages/media/mediaCtrl.js')
@endpush