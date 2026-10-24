<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin') || $request->is('admin/*')) {
            if (!Auth::guard('admin')->check()) {
                toastr()->error('Vui lòng đăng nhập quản trị viên.');
                return redirect()->route('admin.login');
            }
        } else {
            if (!Auth::guard('web')->check()) {
                toastr()->error('Vui lòng đăng nhập để thực hiện chức năng này.');
                return redirect()->route('login');
            }
        }
        return $next($request);
    }
}
