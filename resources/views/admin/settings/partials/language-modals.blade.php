{{-- Create/Edit Modal --}}
<div class="modal fade" id="langModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@{{form.id ? 'Sửa ngôn ngữ' : 'Thêm ngôn ngữ mới'}}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Mã ngôn ngữ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" ng-model="form.code" placeholder="vi, en, ja, th..." maxlength="10">
                    <small class="text-muted">Mã ISO 639-1 (VD: vi, en, ja, th, ko)</small>
                </div>
                <div class="form-group">
                    <label>Tên hiển thị <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" ng-model="form.name" placeholder="Tiếng Việt, English...">
                </div>
                <div class="form-group">
                    <label>Icon Flag</label>
                    <div class="d-flex align-items-center">
                        <div ng-if="form.flag_icon" class="mr-3">
                            <img ng-src="@{{form.flag_icon}}" style="height:40px;border:1px solid #ddd;border-radius:4px">
                        </div>
                        <button type="button" class="btn btn-outline-secondary" ng-click="openMediaPicker()">
                            <i class="fas fa-image"></i> Chọn ảnh
                        </button>
                        <button type="button" class="btn btn-sm btn-link text-danger" ng-if="form.flag_icon" ng-click="form.flag_icon=''">
                            <i class="fas fa-times"></i> Xóa
                        </button>
                    </div>
                    <small class="text-muted">Chọn ảnh cờ quốc gia từ Media</small>
                </div>
                {{-- Copy translations from existing language (only when creating) --}}
                <div class="form-group" ng-if="!form.id && rows.length > 0">
                    <label>Copy bản dịch từ</label>
                    <select class="form-control" ng-model="form.copy_from">
                        <option value="">-- Không copy --</option>
                        <option ng-repeat="lang in rows" ng-value="lang.id">
                            @{{lang.name}} (@{{lang.code}}) - @{{lang.translation_count}} keys
                        </option>
                    </select>
                    <small class="text-muted">Sao chép toàn bộ bản dịch từ ngôn ngữ đã có</small>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="isActive" ng-model="form.is_active">
                    <label class="form-check-label" for="isActive">Kích hoạt</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" ng-click="save()" ng-disabled="saving">
                    <i class="fas fa-spinner fa-spin" ng-if="saving"></i>
                    @{{form.id ? 'Cập nhật' : 'Tạo mới'}}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Translations Modal --}}
<div class="modal fade" id="translationsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span style="font-size:1.5rem">@{{currentLang.flag}}</span>
                    Bản dịch - @{{currentLang.name}}
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="max-height:70vh;overflow-y:auto">
                {{-- Tab Navigation --}}
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item">
                        <a class="nav-link" ng-class="{active: transTab !== 'import'}" href="#" ng-click="transTab = 'edit'; $event.preventDefault()">
                            <i class="fas fa-edit"></i> Sửa bản dịch
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" ng-class="{active: transTab === 'import'}" href="#" ng-click="transTab = 'import'; $event.preventDefault()">
                            <i class="fas fa-file-import"></i> Import JSON
                        </a>
                    </li>
                </ul>

                {{-- Edit Tab --}}
                <div ng-if="transTab !== 'import'">
                    {{-- Search and Add Button Row --}}
                    <div class="d-flex mb-3">
                        <input type="text" class="form-control mr-2" ng-model="search.text" ng-change="filterTranslations()" placeholder="Tìm key hoặc giá trị...">
                        <button type="button" class="btn btn-success mr-1" ng-click="openAddKeyModal()" style="white-space: nowrap">
                            <i class="fas fa-plus"></i> Thêm key
                        </button>
                        <button type="button" class="btn btn-info" ng-click="openAddJsonModal()" style="white-space: nowrap">
                            <i class="fas fa-code"></i> Thêm JSON
                        </button>
                    </div>

                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th style="width:35%">Key</th>
                                <th>Giá trị</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="key in displayedKeys track by key"
                                ng-class="{'table-success': newlyAddedKeys.indexOf(key) !== -1}">
                                <td>
                                    <code class="small">@{{key}}</code>
                                    <span ng-if="newlyAddedKeys.indexOf(key) !== -1" class="badge badge-success ml-1">Mới</span>
                                </td>
                                <td><input type="text" class="form-control form-control-sm" ng-model="flatTrans[key]"></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-xs btn-outline-danger"
                                        ng-click="deleteKey(key)" title="Xóa key">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            {{-- Empty state --}}
                            <tr ng-if="displayedKeys.length === 0">
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="fas fa-search fa-2x d-block mb-2"></i>
                                    <span ng-if="search.text">Không tìm thấy kết quả cho "<strong>@{{search.text}}</strong>"</span>
                                    <span ng-if="!search.text">Chưa có bản dịch nào</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Import JSON Tab --}}
                <div ng-if="transTab === 'import'">
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle"></i> Dán nội dung JSON vào textarea bên dưới và nhấn "Lưu bản dịch".
                    </div>
                    <div class="form-group">
                        <label>Nội dung JSON:</label>
                        <textarea class="form-control" rows="15" ng-model="trans.jsonContent" placeholder='{ "key": "value", "nav": { "home": "Trang chủ" } }'></textarea>
                    </div>
                    <div ng-if="trans.jsonError" class="alert alert-danger mt-2">
                        <i class="fas fa-exclamation-triangle"></i> @{{trans.jsonError}}
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-info" ng-click="validateJson()" ng-disabled="!trans.jsonContent">
                            <i class="fas fa-check-circle"></i> Kiểm tra JSON
                        </button>
                    </div>
                    <div ng-if="trans.jsonPreview" class="mt-3">
                        <label>Preview (@{{trans.jsonKeyCount}} keys):</label>
                        <pre class="bg-light p-2 small" style="max-height:200px;overflow:auto">@{{trans.jsonPreview | json}}</pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" ng-click="saveTranslations()" ng-disabled="saving">
                    <i class="fas fa-spinner fa-spin" ng-if="saving"></i> Lưu bản dịch
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Add Key Modal --}}
<div class="modal fade" id="addKeyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Thêm key mới</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Key <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" ng-model="newKey.key"
                        placeholder="header.contact" autofocus>
                    <small class="text-muted">Sử dụng dot notation (VD: header.contact, nav.about, footer.phone)</small>
                </div>
                <div class="form-group">
                    <label>Giá trị <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" ng-model="newKey.value"
                        placeholder="Liên hệ">
                </div>
                <div ng-if="newKey.error" class="alert alert-danger small py-2">
                    <i class="fas fa-exclamation-triangle"></i> @{{newKey.error}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success" ng-click="addNewKey()"
                    ng-disabled="!newKey.key || !newKey.value">
                    <i class="fas fa-plus"></i> Thêm key
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Add JSON Modal --}}
<div class="modal fade" id="addJsonModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-code"></i> Thêm nhiều key từ JSON</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small mb-3">
                    <i class="fas fa-info-circle"></i> Dán nội dung JSON vào đây. Keys mới sẽ được thêm vào danh sách (đánh dấu "Mới"), keys trùng sẽ được cập nhật. Nhấn "Lưu bản dịch" để lưu vào database.
                </div>
                <div class="form-group">
                    <label>Nội dung JSON <span class="text-danger">*</span></label>
                    <textarea class="form-control" rows="10" ng-model="jsonImport.content"
                        placeholder='{ "nav.home": "Trang chủ", "nav.about": "Giới thiệu" }'
                        style="font-family: monospace; font-size: 13px;"></textarea>
                </div>
                <div ng-if="jsonImport.error" class="alert alert-danger small py-2">
                    <i class="fas fa-exclamation-triangle"></i> @{{jsonImport.error}}
                </div>
                <div ng-if="jsonImport.preview" class="alert alert-success small py-2">
                    <i class="fas fa-check-circle"></i> JSON hợp lệ! Sẽ thêm @{{jsonImport.keyCount}} key(s)
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" ng-click="validateJsonImport()" ng-disabled="!jsonImport.content">
                    <i class="fas fa-check"></i> Kiểm tra
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-info" ng-click="applyJsonImport()"
                    ng-disabled="!jsonImport.preview">
                    <i class="fas fa-plus"></i> Thêm vào danh sách
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Media Picker Modal --}}
<div class="modal fade" id="mediaPickerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-image"></i> Chọn ảnh Flag</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <media-picker
                    mode="picker"
                    select-mode="single"
                    accept="image/*"
                    on-select="onMediaSelect(files)">
                </media-picker>
            </div>
        </div>
    </div>
</div>