<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SlideController extends Controller
{
    /**
     * Display slide management page
     */
    public function index()
    {
        return view('admin.slides.index');
    }
}
