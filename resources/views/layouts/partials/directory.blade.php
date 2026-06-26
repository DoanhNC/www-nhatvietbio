<?php

use Illuminate\Support\Facades\Route;

/** Mảng cấu hình từ controller */
$directories = is_array($directories ?? null) ? $directories : [];

/** Phần tử đầu tiên = module chính */
$baseKey = array_key_first($directories); // ví dụ: admin.brands
$baseLabel = $directories[$baseKey] ?? 'Module';

/** Route hiện tại */
$currentRoute = Route::currentRouteName();

/** URL của module chính */
$baseUrl = ($baseKey && Route::has($baseKey)) ? route($baseKey) : '#';

/** Nhãn phụ cho action */
$actionLabel = $directories[$currentRoute] ?? null;

/** Trạng thái active */
$isBaseActive = ($currentRoute === $baseKey);
$isActionActive = (!$isBaseActive && $actionLabel);
?>

<div class="card-header d-flex justify-content-between align-items-center flex-wrap bg-directory-default">
    <div class="d-flex align-items-center">
        <h6 class="m-0 font-weight-bold text-default mr-2">
            <i class="fas fa-folder-open mr-2"></i>
        </h6>

        {{-- Module chính --}}
        @if($isBaseActive)
        <span class="font-weight-bold text-dark">{{ $baseLabel }}</span>
        @else
        <a href="{{ $baseUrl }}" class="text-dark">
            {{ $baseLabel }}
        </a>
        @endif

        {{-- Action phụ --}}
        @if($isActionActive)
        <span class="mx-2">›</span>
        <span class="font-weight-bold text-dark">{{ $actionLabel }}</span>
        @endif
    </div>
</div>