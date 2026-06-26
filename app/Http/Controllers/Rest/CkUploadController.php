<?php

namespace App\Http\Controllers\Rest;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class CkUploadController extends Controller
{
    // CKEditor5 Simple Upload adapter
    public function ckeditor(Request $r)
    {
        $r->validate(['upload' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120']]);
        $path = $r->file('upload')->store('editor/' . date('Y/m'), 'public');
        return response()->json(['url' => asset('storage/' . $path)]);
    }
}
