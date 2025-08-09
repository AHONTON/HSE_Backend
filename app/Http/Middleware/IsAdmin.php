<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Accès refusé, administrateur requis'], 403);
        }
        return $next($request);
    }
}
