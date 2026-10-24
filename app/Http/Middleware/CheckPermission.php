<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = Auth::guard('admin')->user();

        //Ktra permission của user
        if (! $user || ! $user->role->permissions->contains('name', $permission)) {
            return response()->view('admin.pages.403', [
                'message' => 'Bạn không có quyền truy cập vào trang này.',
            ], 403);
        }
        return $next($request);
    }
}
