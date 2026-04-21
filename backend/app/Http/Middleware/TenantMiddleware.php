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
        // Pega o host da requisição (ex: gym.techfit.com) ou um Header específico (X-Tenant-Domain)
        $domain = $request->header('X-Tenant-Domain') ?? $request->getHost();

        // Encontra o tenant
        $tenant = Tenant::where('domain', $domain)->where('is_active', true)->first();

        if ($tenant) {
            session(['tenant_id' => $tenant->id]);
        } else {
            // Fallback para ambiente local se não achar o domínio, usa a Matriz
            $fallback = Tenant::first();
            if ($fallback) {
                session(['tenant_id' => $fallback->id]);
            } else {
                return response()->json(['message' => 'Tenant não encontrado ou inativo.'], 403);
            }
        }

        return $next($request);
    }
}
