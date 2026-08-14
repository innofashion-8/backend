<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class N8nMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secretKey = config('app.n8n_secret_key', env('N8N_SECRET_KEY'));
        
        if (!$secretKey || $request->header('X-Secret-Key') !== $secretKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid N8N Secret Key.'
            ], 401);
        }

        return $next($request);
    }
}
