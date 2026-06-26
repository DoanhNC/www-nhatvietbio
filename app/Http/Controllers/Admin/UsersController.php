<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    private $directories = [
        'admin.users'        => 'Người dùng',
        'admin.users.create' => 'Thêm mới',
        'admin.users.edit'   => 'Chỉnh sửa',
    ];

    /**
     * Display users management page.
     */
    public function index()
    {
        return view('admin.users.index', ['directories' => $this->directories]);
    }

    /**
     * Display create user form.
     */
    public function create()
    {
        return view('admin.users.create', ['directories' => $this->directories]);
    }

    /**
     * Display edit user form.
     */
    public function edit($id)
    {
        return view('admin.users.edit', [
            'directories' => $this->directories,
            'id' => $id,
        ]);
    }
}
