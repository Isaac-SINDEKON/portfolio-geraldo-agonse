<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('admin_token')) {
            return redirect()
                ->route('admin.login')
                ->with('error', 'Veuillez vous connecter pour accéder à l\'administration.');
        }

        return $next($request);
    }
}
