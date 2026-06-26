<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class VideoController extends Controller
{
    /**
     * Display video management page
     */
    public function index()
    {
        return view('admin.videos.index');
    }
}
