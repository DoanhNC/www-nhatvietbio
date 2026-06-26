<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class MediaController extends Controller
{
    private $directories = [
        'admin.media' => 'Quản lý Media',
    ];

    public function index()
    {
        return view('admin.media.media', ['directories' => $this->directories]);
    }
}
