<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Tạo sẵn admin root user và nhóm mặc định.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo nhóm mặc định với tất cả quyền
        $group = Group::firstOrCreate(
            ['slug' => 'quan-tri-don-vi'],
            [
                'name' => 'Quản trị đơn vị',
                'description' => 'Nhóm quản trị đơn vị có đầy đủ quyền quản lý bài viết và danh mục',
                'permissions' => array_keys(config('permissions', [])),
            ]
        );

        // Tạo admin root user
        User::firstOrCreate(
            ['username' => 'hdux'],
            [
                'name' => 'Admin Root',
                'email' => 'hdux@localhost',
                'password' => Hash::make('Hdux@123'),
                'is_root' => true,
                'status' => true,
            ]
        );
    }
}
