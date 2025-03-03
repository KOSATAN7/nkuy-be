<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminVenue
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Periksa apakah pengguna memiliki peran super_admin
        if (!$user || $user->role !== 'admin_venue') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya admin_venue yang dapat melakukan aksi ini.',
            ], 403);
        }
        

        return $next($request);
    }
}
