<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRootAdmin
{
    /**
     * Handle an incoming request - only allow root admin users.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Chưa đăng nhập.'], 401);
            }
            return redirect()->route('admin.login');
        }

        // Only root users can access
        if (!$user->isRoot()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Chỉ admin root mới có quyền truy cập.'], 403);
            }
            abort(403, 'Chỉ admin root mới có quyền truy cập.');
        }

        return $next($request);
    }
}
