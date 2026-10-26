<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof Admin) {
            return response()->json([
                'status' => false,
                'code' => 401,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        Auth::guard('admin')->setUser($user);

        return $next($request);
    }
}
