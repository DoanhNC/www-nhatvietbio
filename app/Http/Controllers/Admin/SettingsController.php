<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    /**
     * Language settings page
     */
    public function languages()
    {
        return view('admin.settings.languages');
    }

    /**
     * System settings page
     */
    public function system()
    {
        return view('admin.settings.system');
    }

    /**
     * Website settings page
     */
    public function website()
    {
        return view('admin.settings.website');
    }
}
