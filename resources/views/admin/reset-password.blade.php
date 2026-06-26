{{-- resources/views/admin/reset-password.blade.php --}}
@extends('layouts.auth')

@section('title', 'Đặt lại mật khẩu')
@section('ng-app', 'resetPasswordApp')
@section('ng-controller', 'ResetPasswordCtrl')
@section('heading', 'Đặt lại mật khẩu')
@section('subheading', 'Nhập mật khẩu mới cho tài khoản của bạn')

@section('content')
<!-- Success Message -->
<div ng-if="success" class="success-message">
    <div class="success-icon">
        <i class="fas fa-check"></i>
    </div>
    <h3>Mật khẩu đã được thay đổi!</h3>
    <p>Bạn có thể đăng nhập bằng mật khẩu mới.</p>
    <a href="{{ route('admin.login') }}" class="btn-action">
        <i class="fas fa-arrow-right-to-bracket"></i>
        <span>Đăng nhập ngay</span>
    </a>
</div>

<!-- Form -->
<form ng-submit="resetPassword()" ng-if="!success">
    <input type="hidden" ng-model="formData.token" ng-init="formData.token='{{ $token }}'">
    <input type="hidden" ng-model="formData.email" ng-init="formData.email='{{ $email }}'">

    <!-- Email (disabled) -->
    <div class="form-group">
        <label class="form-label">Tài khoản Email</label>
        <div class="input-wrapper">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" class="form-input" value="{{ $email }}" disabled>
        </div>
    </div>

    <!-- New Password -->
    <div class="form-group" ng-init="showPassword = false">
        <label class="form-label" for="password">Mật khẩu mới</label>
        <div class="input-wrapper password-wrapper">
            <i class="fas fa-lock input-icon"></i>
            <input
                type="@{{ showPassword ? 'text' : 'password' }}"
                class="form-input"
                id="password"
                name="password"
                ng-model="formData.password"
                placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)"
                autocomplete="new-password"
                autofocus
                required
                minlength="6">
            <button type="button" class="password-toggle" ng-click="showPassword = !showPassword" tabindex="-1">
                <i class="fas" ng-class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
        </div>
    </div>

    <!-- Confirm Password -->
    <div class="form-group" ng-init="showConfirmPassword = false">
        <label class="form-label" for="password_confirmation">Xác nhận mật khẩu</label>
        <div class="input-wrapper password-wrapper">
            <i class="fas fa-lock input-icon"></i>
            <input
                type="@{{ showConfirmPassword ? 'text' : 'password' }}"
                class="form-input"
                id="password_confirmation"
                name="password_confirmation"
                ng-model="formData.password_confirmation"
                placeholder="Nhập lại mật khẩu mới"
                autocomplete="new-password"
                required>
            <button type="button" class="password-toggle" ng-click="showConfirmPassword = !showConfirmPassword" tabindex="-1">
                <i class="fas" ng-class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn-submit" ng-disabled="loading">
        <i class="fas fa-spinner fa-spin" ng-if="loading"></i>
        <i class="fas fa-key" ng-if="!loading"></i>
        <span>@{{ loading ? 'Đang xử lý...' : 'Đặt lại mật khẩu' }}</span>
    </button>
</form>

<!-- Divider & Back to Login -->
<div class="divider" ng-if="!success">
    <span>hoặc</span>
</div>

<a href="{{ route('admin.login') }}" class="auth-link" ng-if="!success">
    <i class="fas fa-arrow-left"></i>
    <span>Quay lại đăng nhập</span>
</a>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/angular@1.8.3/angular.min.js"></script>
<script>
    angular.module('resetPasswordApp', [])
        .controller('ResetPasswordCtrl', ['$scope', '$http', function($scope, $http) {
            $scope.formData = {
                token: '',
                email: '',
                password: '',
                password_confirmation: ''
            };
            $scope.loading = false;
            $scope.success = false;

            $scope.resetPassword = function() {
                if (!$scope.formData.password || !$scope.formData.password_confirmation) {
                    toastr.error('Vui lòng nhập đầy đủ thông tin');
                    return;
                }

                if ($scope.formData.password !== $scope.formData.password_confirmation) {
                    toastr.error('Mật khẩu xác nhận không khớp');
                    return;
                }

                if ($scope.formData.password.length < 6) {
                    toastr.error('Mật khẩu phải có ít nhất 6 ký tự');
                    return;
                }

                $scope.loading = true;
                $http.post('/admin/reset-password', {
                        token: $scope.formData.token,
                        email: $scope.formData.email,
                        password: $scope.formData.password,
                        password_confirmation: $scope.formData.password_confirmation
                    })
                    .then(function(res) {
                        $scope.success = true;
                        toastr.success('Mật khẩu đã được thay đổi thành công!');
                    })
                    .catch(function(err) {
                        var msg = err.data?.message || err.data?.errors?.password?.[0] || 'Có lỗi xảy ra';
                        toastr.error(msg);
                    })
                    .finally(function() {
                        $scope.loading = false;
                    });
            };
        }]);
</script>
@endpush