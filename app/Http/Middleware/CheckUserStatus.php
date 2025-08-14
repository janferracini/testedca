<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Verifica se o usuário está logado e ativo
        if (Auth::check()) {
            $user = Auth::user();

            // Permite acesso somente se o status for ativo (true)
            if ($user->status == true) {
                return $next($request);
            }
        }

        // Redireciona para a página de login ou exibe erro
        return redirect()->route('site')->withErrors('Entre em contato com o administrador.');
    }
}
