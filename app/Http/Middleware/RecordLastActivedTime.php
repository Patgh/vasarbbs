<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class RecordLastActivedTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 如果是登录用户的话
        if (Auth::check()) {
            // 记录登录最后时间
            Auth::user()->recordLastActivedAt();
        }

        return $next($request);
    }
}
