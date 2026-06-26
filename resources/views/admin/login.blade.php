{{-- resources/views/admin/login.blade.php --}}
@extends('layouts.auth')

@section('title', 'Đăng nhập')
@section('ng-app', 'adminLoginApp')
@section('ng-controller', 'LoginCtrl')
@section('heading', 'Chào mừng trở lại!')
@section('subheading', 'Đăng nhập để tiếp tục quản trị')

@section('content')
<form ng-submit="doLogin()">
  <!-- Username/Email -->
  <div class="form-group">
    <label class="form-label" for="login">Tài khoản</label>
    <div class="input-wrapper">
      <i class="fas fa-user input-icon"></i>
      <input
        type="text"
        class="form-input"
        id="login"
        name="login"
        ng-model="login"
        placeholder="Username hoặc Email"
        autocomplete="username"
        autofocus>
    </div>
  </div>

  <!-- Password -->
  <div class="form-group" ng-init="showPassword = false">
    <label class="form-label" for="password">Mật khẩu</label>
    <div class="input-wrapper password-wrapper">
      <i class="fas fa-lock input-icon"></i>
      <input
        type="@{{ showPassword ? 'text' : 'password' }}"
        class="form-input"
        id="password"
        name="password"
        ng-model="password"
        placeholder="Nhập mật khẩu"
        autocomplete="current-password">
      <button type="button" class="password-toggle" ng-click="showPassword = !showPassword" tabindex="-1">
        <i class="fas" ng-class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
      </button>
    </div>
  </div>

  <!-- Submit Button -->
  <button type="submit" class="btn-submit" ng-disabled="loading">
    <i class="fas fa-spinner fa-spin" ng-if="loading"></i>
    <i class="fas fa-arrow-right-to-bracket" ng-if="!loading"></i>
    <span>@{{ loading ? 'Đang đăng nhập...' : 'Đăng nhập' }}</span>
  </button>
</form>

<!-- Divider & Forgot Password -->
<div class="divider">
  <span>hoặc</span>
</div>

<a href="{{ route('admin.forgot_password') }}" class="auth-link">
  <i class="fas fa-key"></i> Quên mật khẩu?
</a>
@endsection

@push('scripts')
@vite('resources/js/admin/pages/loginCtrl.js')
@endpush