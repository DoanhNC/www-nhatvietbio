{{-- Media Picker Partial Template --}}
{{-- Uses same CSS classes as media admin for consistent styling --}}

<div class="media-picker-wrapper" ng-class="{'picker-mode': isPicker}">
    <div class="media-container" style="height:500px">
        {{-- Left Sidebar - Folder Tree --}}
        <div class="media-sidebar-left" style="width:180px;min-width:180px">
            <div class="folder-tree-container">
                <div class="folder-tree-header" ng-click="navigateTo(null)" ng-class="{active: !currentFolderId}" style="cursor:pointer">
                    <i class="fas fa-home"></i> Tất cả
                </div>
                <div class="folder-tree-scrollable">
                    <ul class="folder-tree">
                        <li ng-repeat="folder in folderTree">
                            <div class="folder-tree-item" ng-class="{active: currentFolderId == folder.id}" ng-click="navigateTo(folder.id)">
                                <i class="fas folder-toggle"
                                    ng-class="{'fa-chevron-down': folder.expanded, 'fa-chevron-right': !folder.expanded}"
                                    ng-if="folder.children && folder.children.length"
                                    ng-click="folder.expanded = !folder.expanded; $event.stopPropagation()"></i>
                                <i class="fas fa-folder text-warning"></i>
                                <span>@{{ folder.name }}</span>
                            </div>
                            <ul ng-if="folder.children.length && folder.expanded" class="folder-tree-children">
                                <li ng-repeat="child in folder.children">
                                    <div class="folder-tree-item" ng-class="{active: currentFolderId == child.id}" ng-click="navigateTo(child.id)">
                                        <i class="fas folder-toggle"
                                            ng-class="{'fa-chevron-down': child.expanded, 'fa-chevron-right': !child.expanded}"
                                            ng-if="child.children && child.children.length"
                                            ng-click="child.expanded = !child.expanded; $event.stopPropagation()"></i>
                                        <i class="fas fa-folder text-warning"></i>
                                        <span>@{{ child.name }}</span>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="media-main">
            {{-- Toolbar --}}
            <div class="media-toolbar">
                <div class="toolbar-left">
                    <button class="btn btn-light btn-sm border" ng-click="goUp()" ng-disabled="!currentFolderId">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <button class="btn btn-light btn-sm border" ng-click="loadContents()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <div class="btn-group">
                        <button class="btn btn-light btn-sm border dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-plus"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" ng-click="openUploadModal(); $event.preventDefault()">
                                <i class="fas fa-upload text-primary"></i> Tải lên
                            </a>
                            <a class="dropdown-item" href="#" ng-click="openCreateFolderModal(); $event.preventDefault()">
                                <i class="fas fa-folder-plus text-warning"></i> Tạo thư mục
                            </a>
                        </div>
                    </div>
                </div>
                <div class="toolbar-center">
                    <div class="input-group input-group-sm search-box">
                        <input type="text" class="form-control" placeholder="Tìm kiếm..."
                            ng-model="searchKeyword" ng-keypress="$event.keyCode === 13 && search()">
                        <div class="input-group-append">
                            <button class="btn btn-light border" ng-click="search()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="toolbar-right">
                    <div class="btn-group btn-group-sm">
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
                <span ng-repeat="crumb in breadcrumb" class="breadcrumb-item">
                    <a href="#" ng-click="navigateTo(crumb.id); $event.preventDefault()">@{{ crumb.name }}</a>
                    <i class="fas fa-chevron-right" ng-if="!$last"></i>
                </span>
            </div>

            {{-- Content Grid/List --}}
            <div class="media-content" ng-class="{'grid-view': viewMode === 'grid', 'list-view': viewMode === 'list'}"
                oncontextmenu="angular.element(this).scope().showContextMenu(event); return false;">
                {{-- Loading --}}
                <div class="text-center py-5" ng-if="loading">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Đang tải...</p>
                </div>

                {{-- Empty --}}
                <div class="text-center py-5 text-muted" ng-if="!loading && !folders.length && !files.length">
                    <i class="fas fa-folder-open fa-3x"></i>
                    <p class="mt-2">Thư mục trống</p>
                    <p class="small">Click chuột phải để tạo thư mục hoặc tải lên tệp</p>
                </div>

                {{-- Folders --}}
                <div class="media-item folder" ng-repeat="folder in folders" ng-if="!loading"
                    ng-dblclick="navigateTo(folder.id)">
                    <div class="item-icon">
                        <i class="fas fa-folder fa-3x text-warning"></i>
                        <span class="item-badge folder-badge" ng-if="folder.item_count > 0">@{{ folder.item_count }}</span>
                        <span class="item-badge folder-badge" ng-if="!folder.item_count">folder</span>
                    </div>
                    <div class="item-name" title="@{{ folder.name }}">@{{ folder.name }}</div>
                </div>

                {{-- Files --}}
                <div class="media-item file" ng-repeat="file in files" ng-if="!loading"
                    ng-click="selectFile(file)"
                    ng-dblclick="onFileDblClick(file)"
                    ng-class="{selected: isSelected(file)}">
                    <div class="item-checkbox" ng-if="isPicker && isMultiple" style="position:absolute;top:4px;left:4px;z-index:1">
                        <i class="fas" ng-class="isSelected(file) ? 'fa-check-square text-primary' : 'fa-square text-muted'"></i>
                    </div>
                    <div class="item-icon">
                        <img ng-if="file.file_type === 'image'" ng-src="@{{ file.url }}" class="item-thumbnail">
                        <i ng-if="file.file_type !== 'image'" class="fas fa-file fa-3x text-secondary"></i>
                        <span class="item-badge file-badge">@{{ file.extension }}</span>
                    </div>
                    <div class="item-name" title="@{{ file.name }}">@{{ file.name }}</div>
                </div>
            </div>

            {{-- Selection Footer (Picker Mode) --}}
            <div class="picker-footer px-3 py-2 border-top bg-light" ng-if="isPicker">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <span ng-if="selectedFiles.length">Đã chọn @{{ selectedFiles.length }} file</span>
                        <span ng-if="!selectedFiles.length">Click để chọn, double-click để chọn nhanh</span>
                    </span>
                    <button class="btn btn-primary btn-sm" ng-click="confirmSelection()" ng-disabled="!selectedFiles.length">
                        <i class="fas fa-check"></i> Chọn
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Context Menu --}}
    <div class="context-menu" ng-show="contextMenu.visible" ng-style="{top: contextMenu.y + 'px', left: contextMenu.x + 'px'}">
        <div class="context-menu-item" ng-click="openUploadModal()">
            <i class="fas fa-upload text-primary"></i> Tải lên
        </div>
        <div class="context-menu-item" ng-click="openCreateFolderModal()">
            <i class="fas fa-folder-plus text-warning"></i> Tạo thư mục
        </div>
    </div>

    {{-- Create Folder Modal --}}
    <div class="modal fade picker-create-folder-modal" tabindex="-1">
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
    <div class="modal fade picker-upload-modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tải lên tệp</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="upload-dropzone" ng-class="{dragover: isDragover}">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted"></i>
                        <p class="mt-2">Kéo thả tệp vào đây hoặc</p>
                        <label class="btn btn-primary">
                            Chọn tệp
                            <input type="file" class="picker-upload-input" multiple style="display:none"
                                onchange="angular.element(this).scope().onFilesSelected(this.files); this.value='';">
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
</div>

<style>
    /* Only picker-specific styles, rest inherits from custom.css */
    .media-picker-wrapper .media-container {
        border: none;
        border-radius: 0;
    }

    .media-picker-wrapper.picker-mode .media-content {
        flex: 1;
    }

    .media-picker-wrapper .picker-footer {
        flex-shrink: 0;
    }

    .media-picker-wrapper .media-item {
        position: relative;
    }

    .media-picker-wrapper .grid-view .media-item.selected {
        background: #cce8ff;
        outline: 2px solid #4e73df;
    }

    /* Context menu positioning for picker */
    .media-picker-wrapper .context-menu {
        position: fixed;
    }
</style>