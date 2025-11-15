<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthWebOrAdmin
{
    public function handle($request, Closure $next)
    {
        // ① 管理者は無条件で通す
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        // ② 一般ユーザーの場合
        if (Auth::guard('web')->check()) {
            // verified しているか
            if (! $request->user()->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }
            return $next($request);
        }

        // 未ログインの場合はログインへ
        return redirect()->route('login');
    }
}
