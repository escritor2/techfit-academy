<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('TenantMiddleware: Processing request for ' . $request->fullUrl());

        // Pega o host da requisição (ex: gym.techfit.com) ou um Header específico (X-Tenant-Domain)
        $domain = $request->header('X-Tenant-Domain') ?? $request->getHost();

        // Encontra o tenant
        $tenant = Tenant::where('domain', $domain)->where('is_active', true)->first();

        if ($tenant) {
            \Log::info('TenantMiddleware: Found tenant ' . $tenant->id);
            $request->attributes->set('tenant_id', $tenant->id);
            session(['tenant_id' => $tenant->id]);
        } else {
            $fallback = Tenant::first();
            if ($fallback) {
                \Log::info('TenantMiddleware: Using fallback tenant ' . $fallback->id);
                $request->attributes->set('tenant_id', $fallback->id);
                session(['tenant_id' => $fallback->id]);
            } else {
                \Log::error('TenantMiddleware: No tenant found!');
                return response()->json(['message' => 'Tenant não encontrado ou inativo.'], 403);
            }
        }

        \Log::info('TenantMiddleware: Proceeding to next middleware');
        return $next($request);
    }
}
