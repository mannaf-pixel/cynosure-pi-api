<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CompanyScope
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('api')->user();

        if ($user && !$user->is_super_admin && $user->company_id) {
            // Company ID ko request mein set karo
            $request->merge(['_company_id' => $user->company_id]);
            
            // Global scope set karo
            app()->instance('company_id', $user->company_id);
        } elseif ($user && $user->is_super_admin) {
            // Super admin — requested company_id use karo
            $companyId = $request->header('X-Company-Id') ?? $request->query('company_id');
            app()->instance('company_id', $companyId);
        }

        return $next($request);
    }
}