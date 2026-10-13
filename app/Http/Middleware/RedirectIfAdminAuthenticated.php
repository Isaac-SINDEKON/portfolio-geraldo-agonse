<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('admin_token')) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
