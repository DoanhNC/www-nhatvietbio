{{-- resources/views/layouts/admin.blade.php --}}
@php
use App\Models\ESetting;
$siteFavicon = ESetting::getFavicon();
$siteLogo = ESetting::getLogo();
$websiteInfo = ESetting::getWebsiteInfo();
$siteName = $websiteInfo['name'] ?? 'Admin Panel';
@endphp
<!doctype html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Admin') - {{ $siteName }}</title>

  <!-- Favicon -->
  @if($siteFavicon)
  <link rel="icon" type="image/x-icon" href="{{ $siteFavicon }}">
  <link rel="shortcut icon" href="{{ $siteFavicon }}">
  @endif

  <!-- Google Fonts - Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
  <!-- Flatpickr CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <!-- Chosen CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chosen-js@1.8.7/chosen.min.css">
  @vite('resources/css/admin/custom.css')
</head>

<body id="page-top" ng-app="adminApp" ng-cloak>
  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-default sidebar sidebar-dark accordion" id="accordionSidebar">

      <!-- Sidebar - Brand -->
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
          <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Admin <sup>2</sup></div>
      </a>

      <!-- Divider -->
      <hr class="sidebar-divider my-0">

      <!-- Nav Item - Dashboard -->
      <li class="nav-item {{ Route::currentRouteName() === 'admin.dashboard' ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Thống kê</span></a>
      </li>

      <!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Heading -->
      <div class="sidebar-heading">
        Tổng quan
      </div>

      <!-- Nav Item - Posts Collapse Menu -->
      @if(auth('admin')->user() && (auth('admin')->user()->is_root || auth('admin')->user()->hasPermission('posts.view') || auth('admin')->user()->hasPermission('categories.view')))
      @php
      $postsActive = in_array(Route::currentRouteName(), ['admin.posts', 'admin.posts.create', 'admin.posts.edit', 'admin.post_categories', 'admin.post_categories.create', 'admin.post_categories.edit']);
      @endphp
      <li class="nav-item">
        <a class="nav-link {{ $postsActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapsePosts"
          aria-expanded="{{ $postsActive ? 'true' : 'false' }}" aria-controls="collapsePosts">
          <i class="fas fa-newspaper"></i>
          <span>Bài viết</span>
        </a>
        <div id="collapsePosts" class="collapse {{ $postsActive ? 'show' : '' }}" aria-labelledby="headingPosts"
          data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Tùy chỉnh</h6>
            @if(auth('admin')->user()->is_root || auth('admin')->user()->hasPermission('posts.view'))
            <a class="collapse-item {{ in_array(Route::currentRouteName(), ['admin.posts', 'admin.posts.create', 'admin.posts.edit']) ? 'active' : ''}}" href="{{route('admin.posts')}}">Quản lý bài viết</a>
            @endif
            @if(auth('admin')->user()->is_root || auth('admin')->user()->hasPermission('categories.view'))
            <a class="collapse-item {{ in_array(Route::currentRouteName(), ['admin.post_categories', 'admin.post_categories.create', 'admin.post_categories.edit']) ? 'active' : ''}}" href="{{route('admin.post_categories')}}">Danh mục</a>
            @endif
          </div>
        </div>
      </li>
      @endif

      <!-- Nav Item - Media -->
      <li class="nav-item {{ Route::currentRouteName() === 'admin.media' ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.media') }}">
          <i class="fas fa-photo-video"></i>
          <span>Media</span></a>
      </li>

      <!-- Nav Item - Slides -->
      <li class="nav-item {{ Route::currentRouteName() === 'admin.slides' ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.slides') }}">
          <i class="fas fa-images"></i>
          <span>Slide</span></a>
      </li>

      <!-- Nav Item - Videos -->
      <li class="nav-item {{ Route::currentRouteName() === 'admin.videos' ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.videos') }}">
          <i class="fab fa-youtube"></i>
          <span>Video YouTube</span></a>
      </li>

      <!-- Nav Item - Configuration Collapse Menu (PERMISSION BASED) -->
      @if(auth('admin')->user() && (auth('admin')->user()->is_root || auth('admin')->user()->hasPermission('settings.view') || auth('admin')->user()->hasPermission('settings.manage')))
      @php
      $configActive = Route::currentRouteName() === 'admin.settings.website';
      @endphp
      <li class="nav-item">
        <a class="nav-link {{ $configActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseConfig"
          aria-expanded="{{ $configActive ? 'true' : 'false' }}" aria-controls="collapseConfig">
          <i class="fas fa-sliders-h"></i>
          <span>Cấu hình</span>
        </a>
        <div id="collapseConfig" class="collapse {{ $configActive ? 'show' : '' }}" aria-labelledby="headingConfig"
          data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item {{ Route::currentRouteName() === 'admin.settings.website' ? 'active' : ''}}" href="{{ route('admin.settings.website') }}">
              <i class="fas fa-globe mr-2"></i>Cấu hình Website
            </a>
          </div>
        </div>
      </li>
      @endif

      <!-- Nav Item - Settings Collapse Menu (ROOT ADMIN ONLY) -->
      @if(auth('admin')->user() && auth('admin')->user()->is_root)
      @php
      $settingsActive = in_array(Route::currentRouteName(), ['admin.settings', 'admin.settings.system', 'admin.users', 'admin.groups']);
      @endphp
      <li class="nav-item">
        <a class="nav-link {{ $settingsActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseSettings"
          aria-expanded="{{ $settingsActive ? 'true' : 'false' }}" aria-controls="collapseSettings">
          <i class="fas fa-user-shield"></i>
          <span>Quản trị hệ thống</span>
        </a>
        <div id="collapseSettings" class="collapse {{ $settingsActive ? 'show' : '' }}" aria-labelledby="headingSettings"
          data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Tài khoản & Phân quyền</h6>
            <a class="collapse-item {{ Route::currentRouteName() === 'admin.users' ? 'active' : ''}}" href="{{ route('admin.users') }}">
              <i class="fas fa-users mr-2"></i>Người dùng
            </a>
            <a class="collapse-item {{ Route::currentRouteName() === 'admin.groups' ? 'active' : ''}}" href="{{ route('admin.groups') }}">
              <i class="fas fa-users-cog mr-2"></i>Nhóm & Quyền
            </a>
            <h6 class="collapse-header">Hệ thống</h6>
            <a class="collapse-item {{ in_array(Route::currentRouteName(), ['admin.settings', 'admin.settings.system']) ? 'active' : ''}}" href="{{ route('admin.settings') }}">
              <i class="fas fa-cogs mr-2"></i>Cấu hình chung
            </a>
          </div>
        </div>
      </li>
      @endif

      <!-- Divider -->
      <hr class="sidebar-divider d-none d-md-block">

      <!-- Sidebar Toggler (Sidebar) -->
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>

    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar static-top shadow">

          <!-- Sidebar Toggle (Topbar) -->
          <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>

          <!-- Current DateTime -->
          <div class="d-none d-md-flex align-items-center text-gray-600" id="headerDateTime">
            <i class="fas fa-calendar-alt mr-2"></i>
            <span id="currentDateTime"></span>
          </div>
          <script>
            (function updateDateTime() {
              var now = new Date();
              var days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
              var day = String(now.getDate()).padStart(2, '0');
              var month = String(now.getMonth() + 1).padStart(2, '0');
              var year = now.getFullYear();
              var hour = String(now.getHours()).padStart(2, '0');
              var min = String(now.getMinutes()).padStart(2, '0');
              var sec = String(now.getSeconds()).padStart(2, '0');
              var text = days[now.getDay()] + ', ' + day + '/' + month + '/' + year + ' | ' + hour + ':' + min + ':' + sec;
              var el = document.getElementById('currentDateTime');
              if (el) el.textContent = text;
              setTimeout(updateDateTime, 1000); // Update every second
            })();
          </script>

          <!-- Topbar Navbar -->
          <ul class="navbar-nav ml-auto">

            <!-- Nav Item - Notifications -->
            <li class="nav-item dropdown no-arrow mx-1" ng-controller="notificationCtrl">
              <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" ng-click="loadNotifications()">
                <i class="fas fa-bell fa-fw"></i>
                <!-- Counter - Notifications -->
                <span class="badge badge-danger badge-counter" ng-if="unreadCount > 0">
                  @{{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
              </a>
              <!-- Dropdown - Notifications -->
              <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in p-0 notification-dropdown"
                aria-labelledby="alertsDropdown">
                <!-- Fixed Header -->
                <div class="dropdown-header d-flex justify-content-between align-items-center" style="flex-shrink: 0;">
                  <span>Thông báo</span>
                  <a href="#" class="text-white small" ng-if="unreadCount > 0" ng-click="markAllAsRead($event)">
                    <i class="fas fa-check-double"></i> Đọc tất cả
                  </a>
                </div>
                <!-- Fixed Tabs -->
                <div class="d-flex border-bottom bg-light" style="flex-shrink: 0;">
                  <a href="#" class="flex-fill text-center py-2 small"
                    ng-class="{'text-primary border-primary border-bottom-2 font-weight-bold': activeTab === 'all', 'text-muted': activeTab !== 'all'}"
                    ng-click="setTab('all', $event)" style="border-bottom: 2px solid transparent;">
                    <i class="fas fa-list mr-1"></i>Tất cả
                  </a>
                  <a href="#" class="flex-fill text-center py-2 small"
                    ng-class="{'text-primary border-primary border-bottom-2 font-weight-bold': activeTab === 'unread', 'text-muted': activeTab !== 'unread'}"
                    ng-click="setTab('unread', $event)" style="border-bottom: 2px solid transparent;">
                    <i class="fas fa-envelope mr-1"></i>Chưa đọc
                    <span class="badge badge-danger badge-pill ml-1" ng-if="unreadCount > 0">@{{ unreadCount }}</span>
                  </a>
                </div>
                <!-- Scrollable Content -->
                <div style="flex: 1; overflow-y: auto; max-height: 320px;">
                  <!-- Loading -->
                  <div class="text-center py-4" ng-if="loading">
                    <i class="fas fa-spinner fa-spin text-primary fa-2x"></i>
                  </div>
                  <!-- Empty state -->
                  <div class="text-center text-muted py-4" ng-if="!loading && getFilteredNotifications().length === 0">
                    <i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>
                    <span ng-if="activeTab === 'all'">Không có thông báo</span>
                    <span ng-if="activeTab === 'unread'">Không có thông báo chưa đọc</span>
                  </div>
                  <!-- Notification items -->
                  <a class="dropdown-item d-flex align-items-center py-2 border-bottom" href="#"
                    ng-repeat="n in getFilteredNotifications()" ng-click="markAsRead(n, $event)"
                    ng-class="{'bg-light': !n.is_read}">
                    <div class="mr-3">
                      <div class="icon-circle" ng-class="getIconClass(n.action)">
                        <i class="text-white" ng-class="getIcon(n.type)"></i>
                      </div>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                      <div class="small text-gray-500">
                        <i class="fas fa-clock mr-1"></i>@{{ formatDate(n.created_at) }} · @{{ formatTime(n.created_at) }}
                      </div>
                      <span class="text-truncate d-block" ng-class="{'font-weight-bold': !n.is_read}">@{{ n.title }}</span>
                      <small class="text-muted text-truncate d-block" ng-if="n.message">@{{ n.message }}</small>
                    </div>
                  </a>
                </div>
                <!-- Fixed Footer - View All -->
                <div class="border-top bg-light text-center py-2" style="flex-shrink: 0;" ng-if="!showingAll && loaded">
                  <a href="#" class="small text-primary" ng-click="loadAllNotifications($event)">
                    <i class="fas fa-history mr-1"></i>Xem thông báo cũ hơn
                  </a>
                </div>
              </div>
            </li>

            <div class="topbar-divider d-none d-sm-block"></div>

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ auth('admin')->user()->name ?? 'Admin' }}</span>
                @if(auth('admin')->user()->avatar)
                <img class="img-profile rounded-circle" src="{{ auth('admin')->user()->avatar }}">
                @else
                <i class="fas fa-user-circle fa-2x text-gray-400"></i>
                @endif
              </a>
              <!-- Dropdown - User Information -->
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#profileModal">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                  Thông tin tài khoản
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Đăng xuất
                </a>
              </div>
            </li>

          </ul>

        </nav>
        <!-- End of Topbar -->

        <!-- directory -->
        @isset($directories)
        @include('layouts.partials.directory')
        @endisset

        <!-- Begin Page Content -->
        <div>
          @yield('content')
        </div>

      </div>
      <!-- End of Main Content -->

    </div>
    <!-- End of Content Wrapper -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Xác nhận đăng xuất?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Chọn "Đăng xuất" để kết thúc phiên làm việc.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy</button>
          <button class="btn btn-danger" type="button" id="btnLogout">Đăng xuất</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Profile Modal -->
  <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-user-circle"></i>Thông tin tài khoản</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Profile Section - Centered Avatar -->
          <div class="profile-section">
            <div class="profile-avatar-wrapper">
              <div class="profile-avatar">
                @if(auth('admin')->user()->avatar)
                <img src="{{ auth('admin')->user()->avatar }}" class="profile-avatar-inner" alt="Avatar">
                @else
                <i class="fas fa-user-circle profile-avatar-icon"></i>
                @endif
              </div>
              <span class="profile-status"></span>
            </div>
            <h5 class="profile-name">{{ auth('admin')->user()->name ?? 'Admin' }}</h5>
            <p class="profile-email">
              <i class="fas fa-envelope"></i>
              {{ auth('admin')->user()->email ?? '' }}
            </p>
          </div>

          <!-- Change Password Form -->
          <div class="password-section">
            <div class="password-section-header">
              <i class="fas fa-key"></i>
              <h6>Đổi mật khẩu</h6>
            </div>
            <form id="changePasswordForm">
              <div class="form-group">
                <label>Mật khẩu hiện tại</label>
                <div class="input-wrapper">
                  <input type="password" class="form-control" id="currentPassword" placeholder="Nhập mật khẩu hiện tại" required>
                  <i class="fas fa-lock"></i>
                </div>
              </div>
              <div class="form-group">
                <label>Mật khẩu mới</label>
                <div class="input-wrapper">
                  <input type="password" class="form-control" id="newPassword" placeholder="Nhập mật khẩu mới" required minlength="6">
                  <i class="fas fa-lock"></i>
                </div>
              </div>
              <div class="form-group">
                <label>Xác nhận mật khẩu mới</label>
                <div class="input-wrapper">
                  <input type="password" class="form-control" id="confirmPassword" placeholder="Nhập lại mật khẩu mới" required>
                  <i class="fas fa-check-double"></i>
                </div>
              </div>
              <div id="passwordError" class="alert alert-danger d-none">
                <i class="fas fa-exclamation-circle"></i>
                <span></span>
              </div>
              <div id="passwordSuccess" class="alert alert-success d-none">
                <i class="fas fa-check-circle"></i>
                <span></span>
              </div>
            </form>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">
            <i class="fas fa-times"></i>Đóng
          </button>
          <button class="btn btn-primary" type="button" id="btnChangePassword">
            <i class="fas fa-save"></i>Lưu mật khẩu
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Global Confirm Modal (for all pages) -->
  <div class="modal fade" id="globalConfirmModal" tabindex="-1">
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

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery.easing@1.4.1/jquery.easing.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/js/sb-admin-2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
  <!-- Chosen -->
  <script src="https://cdn.jsdelivr.net/npm/chosen-js@1.8.7/chosen.jquery.min.js"></script>
  <!-- TinyMCE Editor -->
  <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
  @vite('resources/js/admin/common/app.js')
  @stack('scripts')
</body>

</html>