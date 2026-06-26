<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::guard('admin')->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Chưa đăng nhập.'], 401);
            }
            return redirect()->route('admin.login');
        }

        // Root users bypass all permission checks
        if ($user->isRoot()) {
            return $next($request);
        }

        // Check if user has the required permission
        if (!$user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Không có quyền thực hiện thao tác này.'], 403);
            }
            abort(403, 'Không có quyền truy cập.');
        }

        return $next($request);
    }
}
