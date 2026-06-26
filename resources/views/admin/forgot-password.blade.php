{{-- resources/views/admin/forgot-password.blade.php --}}
@extends('layouts.auth')

@section('title', 'Quên mật khẩu')
@section('ng-app', 'forgotPasswordApp')
@section('ng-controller', 'ForgotPasswordCtrl')
@section('heading', 'Quên mật khẩu?')
@section('subheading', 'Nhập email để nhận link khôi phục mật khẩu')

@section('content')
<!-- Success Message -->
<div ng-if="success" class="success-message">
    <div class="success-icon">
        <i class="fas fa-check"></i>
    </div>
    <h3>Email đã được gửi!</h3>
    <p>@{{ successMessage }}</p>
</div>

<!-- Form -->
<form ng-submit="sendResetLink()" ng-if="!success">
    <!-- Email -->
    <div class="form-group">
        <label class="form-label" for="email">Địa chỉ Email</label>
        <div class="input-wrapper">
            <i class="fas fa-envelope input-icon"></i>
            <input
                type="email"
                class="form-input"
                id="email"
                name="email"
                ng-model="formData.email"
                placeholder="Nhập email đăng ký"
                autocomplete="email"
                autofocus
                required>
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn-submit" ng-disabled="loading">
        <i class="fas fa-spinner fa-spin" ng-if="loading"></i>
        <i class="fas fa-paper-plane" ng-if="!loading"></i>
        <span>@{{ loading ? 'Đang gửi...' : 'Gửi link khôi phục' }}</span>
    </button>
</form>

<!-- Divider & Back to Login -->
<div class="divider">
    <span>hoặc</span>
</div>

<a href="{{ route('admin.login') }}" class="auth-link">
    <i class="fas fa-arrow-left"></i>
    <span>Quay lại đăng nhập</span>
</a>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/angular@1.8.3/angular.min.js"></script>
<script>
    angular.module('forgotPasswordApp', [])
        .controller('ForgotPasswordCtrl', ['$scope', '$http', function($scope, $http) {
            $scope.formData = {
                email: ''
            };
            $scope.loading = false;
            $scope.success = false;
            $scope.successMessage = '';

            $scope.sendResetLink = function() {
                if (!$scope.formData.email) {
                    toastr.error('Vui lòng nhập email');
                    return;
                }

                $scope.loading = true;
                $http.post('/admin/forgot-password', {
                        email: $scope.formData.email
                    })
                    .then(function(res) {
                        $scope.success = true;
                        $scope.successMessage = res.data.message || 'Vui lòng kiểm tra hộp thư email để lấy link khôi phục.';
                        toastr.success('Email đã được gửi thành công!');
                    })
                    .catch(function(err) {
                        var msg = err.data?.message || err.data?.errors?.email?.[0] || 'Có lỗi xảy ra';
                        toastr.error(msg);
                    })
                    .finally(function() {
                        $scope.loading = false;
                    });
            };
        }]);
</script>
@endpush