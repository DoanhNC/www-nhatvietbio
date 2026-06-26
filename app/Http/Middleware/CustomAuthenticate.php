<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class CustomAuthenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            // trả về trang login cho admin
            if ($request->is('admin/*')) {
                return route('admin.login');
            }

            // trả về trang login cho web
            return route('web.login');
        }

        return null;
    }
}
