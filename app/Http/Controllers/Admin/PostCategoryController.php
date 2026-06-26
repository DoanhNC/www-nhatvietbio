<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EPostCategory;

class PostCategoryController extends Controller
{
    private $directories = [
        'admin.post_categories'        => 'Danh mục bài viết',
        'admin.post_categories.create' => 'Thêm mới',
        'admin.post_categories.edit'   => 'Chỉnh sửa',
    ];

    public function index()
    {
        return view('admin.post_categories.post_categories', ['directories' => $this->directories]);
    }

    public function create()
    {
        return view('admin.post_categories.post_categoriesCreate', ['directories' => $this->directories]);
    }

    public function edit($id)
    {
        $row = EPostCategory::findOrFail($id);
        return view('admin.post_categories.post_categoriesEdit', [
            'directories' => $this->directories,
            'id'          => $id,
        ]);
    }
}
