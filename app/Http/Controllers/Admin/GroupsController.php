<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class GroupsController extends Controller
{
    private $directories = [
        'admin.groups'        => 'Nhóm & Quyền',
        'admin.groups.create' => 'Thêm mới',
        'admin.groups.edit'   => 'Chỉnh sửa',
    ];

    /**
     * Display groups management page.
     */
    public function index()
    {
        return view('admin.groups.index', ['directories' => $this->directories]);
    }

    /**
     * Display create group form.
     */
    public function create()
    {
        return view('admin.groups.create', ['directories' => $this->directories]);
    }

    /**
     * Display edit group form.
     */
    public function edit($id)
    {
        return view('admin.groups.edit', [
            'directories' => $this->directories,
            'id' => $id,
        ]);
    }
}
