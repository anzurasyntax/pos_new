<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Forbidden');
        }

        $role = $user->role ?? 'sales_user';
        $role = strtolower((string) $role);

        // Super admin bypasses all role checks.
        if ($role === 'super_admin') {
            return $next($request);
        }

        $permission = strtolower($permission);

        $allowed = match ($permission) {
            // Manager + sales_user can access sales.
            'sales' => in_array($role, ['manager', 'sales_user'], true),

            // Manager can access purchase (sales_user can't).
            'purchase' => $role === 'manager',

            // Inventory can be viewed by manager + sales_user.
            'inventory.view', 'inventory' => in_array($role, ['manager', 'sales_user'], true),

            // Inventory modifications are manager-only (super_admin bypasses above).
            'inventory.manage' => $role === 'manager',

            default => false,
        };

        if (! $allowed) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
