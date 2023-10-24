<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;

class ACLMiddleware
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userRolePermissions = auth()->user()->role->role_permissions;
        $currentRouteName = $request->route()->getName();
        if (!in_array($currentRouteName, $userRolePermissions)) {
            return $this->sendError("Bạn không có quyền truy cập trang này", Response::HTTP_FORBIDDEN, 403);
        }
        return $next($request);
    }
}
