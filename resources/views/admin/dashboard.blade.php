{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')
@section('title','Thống kê')

@section('content')
<div class="container-fluid mt-4" ng-controller="DashboardCtrl">

  <!-- ROW 1: 3 cards đồng đều -->
  <div class="row">

    <!-- Thống kê truy cập (4 cột) -->
    <div class="col-lg-4 col-md-6 mb-4">
      <div class="card shadow h-100">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users mr-2"></i>Thống kê truy cập</h6>
        </div>
        <div class="card-body py-3">
          <div class="row align-items-center">
            <div class="col-5">
              <canvas id="visitorPieChart" width="100" height="100"></canvas>
            </div>
            <div class="col-7" style="font-size: 0.8rem;">
              <div class="d-flex align-items-center mb-1">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: #1cc88a; display: inline-block; margin-right: 6px;"></span>
                <span class="text-muted">Online:</span>
                <strong class="ml-auto text-dark">@{{ visitorStats.online }}</strong>
              </div>
              <div class="d-flex align-items-center mb-1">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: #f6c23e; display: inline-block; margin-right: 6px;"></span>
                <span class="text-muted">Hôm nay:</span>
                <strong class="ml-auto text-dark">@{{ visitorStats.today }}</strong>
              </div>
              <div class="d-flex align-items-center mb-1">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: #4e73df; display: inline-block; margin-right: 6px;"></span>
                <span class="text-muted">Tuần:</span>
                <strong class="ml-auto text-dark">@{{ visitorStats.this_week }}</strong>
              </div>
              <div class="d-flex align-items-center">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: #e74a3b; display: inline-block; margin-right: 6px;"></span>
                <span class="text-muted">Tháng:</span>
                <strong class="ml-auto text-dark">@{{ visitorStats.this_month }}</strong>
              </div>
            </div>
          </div>
          <hr class="my-2">
          <div class="text-center">
            <h5 class="font-weight-bold text-gray-800 mb-0">@{{ visitorStats.total | number }}</h5>
            <small class="text-muted">Tổng lượt truy cập</small>
          </div>
        </div>
        <div class="card-footer bg-light text-center py-2">
          <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-chart-line mr-1"></i> Xem chi tiết
          </a>
        </div>
      </div>
    </div>

    <!-- Dung lượng (4 cột) -->
    <div class="col-lg-4 col-md-6 mb-4">
      <div class="card shadow h-100">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-hdd mr-2"></i>Dung lượng</h6>
        </div>
        <div class="card-body py-3">
          <div class="row align-items-center">
            <div class="col-5 text-center" style="position: relative;">
              <canvas id="storagePieChart" width="100" height="100"></canvas>
              <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.9rem; font-weight: bold; color: #5a5c69;">
                @{{ mediaStats.percentage }}%
              </div>
            </div>
            <div class="col-7" style="font-size: 0.8rem;">
              <div class="d-flex align-items-center mb-2">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: #f6c23e; display: inline-block; margin-right: 6px;"></span>
                <span class="text-muted">Đã dùng:</span>
                <strong class="ml-auto text-dark">@{{ mediaStats.formatted_used }}</strong>
              </div>
              <div class="d-flex align-items-center">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: #e0e0e0; display: inline-block; margin-right: 6px;"></span>
                <span class="text-muted">Còn trống:</span>
                <strong class="ml-auto text-dark">@{{ mediaStats.formatted_free }}</strong>
              </div>
            </div>
          </div>
          <hr class="my-2">
          <div class="text-center">
            <h5 class="font-weight-bold text-gray-800 mb-0">@{{ mediaStats.formatted_max }}</h5>
            <small class="text-muted">Tổng dung lượng</small>
          </div>
        </div>
        <div class="card-footer bg-light d-flex justify-content-between py-2">
          <button class="btn btn-sm btn-outline-secondary" ng-click="loadMediaStats(true)">
            <i class="fas fa-sync-alt"></i> Làm mới
          </button>
          <a href="{{ route('admin.media') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-photo-video"></i> Quản lý
          </a>
        </div>
      </div>
    </div>

    <!-- Thông tin Website (4 cột) -->
    <div class="col-lg-4 col-md-6 mb-4">
      <div class="card shadow h-100">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle mr-2"></i>Thông tin website</h6>
        </div>
        <div class="card-body py-2 px-3" style="font-size: 0.8rem;">
          <div class="mb-2">
            <small class="text-muted"><i class="fas fa-globe text-primary mr-1"></i>Tên:</small>
            <div ng-if="websiteConfig.name" class="font-weight-bold text-dark" style="word-wrap: break-word;">@{{ websiteConfig.name }}</div>
            <div ng-if="!websiteConfig.name" class="text-muted font-italic">-</div>
          </div>
          <div class="mb-2">
            <small class="text-muted"><i class="fas fa-building text-info mr-1"></i>Công ty:</small>
            <div ng-if="websiteConfig.company" class="font-weight-bold text-dark" style="word-wrap: break-word;">@{{ websiteConfig.company }}</div>
            <div ng-if="!websiteConfig.company" class="text-muted font-italic">-</div>
          </div>
          <div class="mb-2">
            <small class="text-muted"><i class="fas fa-phone-alt text-success mr-1"></i>Hotline:</small>
            <div ng-if="websiteConfig.hotline" class="font-weight-bold text-dark">@{{ websiteConfig.hotline }}</div>
            <div ng-if="!websiteConfig.hotline" class="text-muted font-italic">-</div>
          </div>
          <div class="mb-2">
            <small class="text-muted"><i class="fas fa-envelope text-warning mr-1"></i>Email:</small>
            <div ng-if="websiteConfig.email" class="font-weight-bold text-dark" style="word-wrap: break-word;">@{{ websiteConfig.email }}</div>
            <div ng-if="!websiteConfig.email" class="text-muted font-italic">-</div>
          </div>
          <div class="mb-2">
            <small class="text-muted"><i class="fas fa-map-marker-alt text-danger mr-1"></i>Địa chỉ:</small>
            <div ng-if="websiteConfig.address" class="font-weight-bold text-dark" style="word-wrap: break-word;">@{{ websiteConfig.address }}</div>
            <div ng-if="!websiteConfig.address" class="text-muted font-italic">-</div>
          </div>
          <div>
            <small class="text-muted"><i class="fas fa-envelope-open-text mr-1" ng-class="smtpConfig.is_active == '1' ? 'text-success' : 'text-secondary'"></i>SMTP:</small>
            <span class="badge ml-1" ng-class="smtpConfig.is_active == '1' ? 'badge-success' : 'badge-secondary'">
              @{{ smtpConfig.is_active == '1' ? 'Hoạt động' : 'Tắt' }}
            </span>
          </div>
        </div>
        <div class="card-footer bg-light text-center py-2">
          <a href="{{ route('admin.settings.website') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-edit mr-1"></i> Chỉnh sửa
          </a>
        </div>
      </div>
    </div>

  </div>
  <!-- ROW 2: Top bài viết xem nhiều + Thống kê bài viết -->
  <div class="row">
    <!-- Top bài viết xem nhiều - 6 col (LEFT) -->
    <div class="col-lg-6 mb-4">
      <div class="card shadow h-100">
        <div class="card-header py-2 d-flex flex-row align-items-center justify-content-between">
          <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fire-alt mr-2 text-danger"></i>Top bài viết xem nhiều</h6>
          <button class="btn btn-sm btn-outline-secondary" ng-click="loadTopPosts()" ng-disabled="loadingTopPosts">
            <i class="fas" ng-class="loadingTopPosts ? 'fa-spinner fa-spin' : 'fa-sync-alt'"></i>
          </button>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
              <thead class="thead-light">
                <tr>
                  <th width="40" class="text-center">#</th>
                  <th>Tiêu đề</th>
                  <th width="90" class="text-center">Lượt xem</th>
                </tr>
              </thead>
              <tbody>
                <tr ng-if="loadingTopPosts">
                  <td colspan="3" class="text-center py-3">
                    <i class="fas fa-spinner fa-spin text-primary"></i>
                  </td>
                </tr>
                <tr ng-if="!loadingTopPosts && topPosts.length === 0">
                  <td colspan="3" class="text-center py-3 text-muted">
                    Chưa có bài viết nào
                  </td>
                </tr>
                <tr ng-repeat="post in topPosts" ng-if="!loadingTopPosts">
                  <td class="text-center font-weight-bold">@{{ $index + 1 }}</td>
                  <td class="text-truncate" style="max-width: 200px;">
                    <a href="{{ url('/admin/posts') }}/@{{ post.id }}/edit" class="text-primary" title="@{{ post.title }}">
                      @{{ post.title }}
                    </a>
                  </td>
                  <td class="text-center">
                    <span class="badge badge-info"><i class="fas fa-eye mr-1"></i>@{{ post.view_count | number }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Thống kê bài viết - 6 col (RIGHT) -->
    <div class="col-lg-6 mb-4">
      <!-- Posts Filter Card -->
      <div class="card shadow mb-3">
        <div class="card-header py-2 d-flex flex-row align-items-center justify-content-between">
          <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-newspaper mr-2"></i>Thống kê bài viết</h6>
          <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-sm" ng-class="statsActiveRange === 0 ? 'btn-primary' : 'btn-outline-secondary'" ng-click="setStatsRange(0)">Hôm nay</button>
            <button type="button" class="btn btn-sm" ng-class="statsActiveRange === 7 ? 'btn-primary' : 'btn-outline-secondary'" ng-click="setStatsRange(7)">7 ngày</button>
            <button type="button" class="btn btn-sm" ng-class="statsActiveRange === 30 ? 'btn-primary' : 'btn-outline-secondary'" ng-click="setStatsRange(30)">30 ngày</button>
            <button type="button" class="btn btn-sm" ng-class="statsActiveRange === -1 ? 'btn-primary' : 'btn-outline-secondary'" ng-click="setStatsRange(-1)">Tất cả</button>
          </div>
        </div>
      </div>

      <!-- Posts Stats Cards -->
      <div class="row">
        <!-- Total Posts Card -->
        <div class="col-4 mb-3">
          <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body py-2">
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng số</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" ng-class="{'text-muted': loadingStats}">
                @{{ loadingStats ? '...' : postsStats.total }}
              </div>
            </div>
          </div>
        </div>
        <!-- Published Posts Card -->
        <div class="col-4 mb-3">
          <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body py-2">
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Xuất bản</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" ng-class="{'text-muted': loadingStats}">
                @{{ loadingStats ? '...' : postsStats.published }}
              </div>
            </div>
          </div>
        </div>
        <!-- Draft Posts Card -->
        <div class="col-4 mb-3">
          <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body py-2">
              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Nháp</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" ng-class="{'text-muted': loadingStats}">
                @{{ loadingStats ? '...' : postsStats.draft }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@vite('resources/js/admin/pages/dashboardCtrl.js')
@endpush