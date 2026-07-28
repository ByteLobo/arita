<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUsuarioActivo
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (! Auth::user()->activo) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu usuario está inactivo. Contacta al administrador.',
            ]);
        }

        return $next($request);
    }
}
