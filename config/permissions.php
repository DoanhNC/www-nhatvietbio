<?php

/**
 * Permission definitions for the application.
 * 
 * Format: 'permission.key' => 'Description'
 * 
 * To add a new permission:
 * 1. Add the key and description here
 * 2. Use middleware: Route::middleware(['permission:your.key'])
 * 3. Or check in controller: $user->hasPermission('your.key')
 */
return [
    // Posts permissions
    'posts.view' => 'Xem danh sách bài viết',
    'posts.manage' => 'Thêm/Sửa/Xóa bài viết',

    // Categories permissions
    'categories.view' => 'Xem danh mục',
    'categories.manage' => 'Thêm/Sửa/Xóa danh mục',

    // Website Settings permissions
    'settings.view' => 'Xem cấu hình website',
    'settings.manage' => 'Chỉnh sửa cấu hình website',
];
