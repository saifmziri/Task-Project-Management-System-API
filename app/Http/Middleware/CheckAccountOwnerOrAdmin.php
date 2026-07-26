<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAccountOwnerOrAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentUser = Auth::user();
        
        $userIdFromRoute = $request->route('user');

        if (!$currentUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($currentUser->role?->role_name !== 'Admin' && (string)$currentUser->id !== (string)$userIdFromRoute) {
            return response()->json([
                'message' => 'You are not authorized to perform this action.'
            ], 403);
        }

        return $next($request);
    }
}