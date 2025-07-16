<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
use Closure;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedByRequestDataException;

class CustomInitializeTenancyByRequestData extends InitializeTenancyByRequestData
{
    /**
     * Handle the incoming request and catch tenant identification exceptions.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TenantCouldNotBeIdentifiedByRequestDataException $e) {
            // Return a JSON error response
            return response()->json([
                'error' => 'tenant_not_found',
                'message' => 'Tenant could not be identified',
                'attempted_tenant' => $this->getPayload($request) ?? 'none'
            ], 404);
        }
    }

    /**
     * Override the getPayload method to only accept X-Tenant-ID header and query parameter
     *
     * @param Request $request
     * @return string|null
     */
    protected function getPayload(Request $request): ?string
    {
        $tenant = null;
        $source = null;
        
        // Only check X-Tenant-ID header (case insensitive)
        if ($request->hasHeader('X-Tenant-ID')) {
            $tenant = $request->header('X-Tenant-ID');
            $source = "header:X-Tenant-ID";
        }
        // Fallback to query parameter
        elseif (static::$queryParameter && $request->has(static::$queryParameter)) {
            $tenant = $request->get(static::$queryParameter);
            $source = "query:" . static::$queryParameter;
        }
        
        // Log what we found for debugging
        if ($tenant) {
            Log::info("Tenant identified from $source: $tenant");
        } else {
            Log::debug("No tenant found in request", [
                'headers' => $request->headers->all(),
                'query' => $request->query->all()
            ]);
        }
        
        return $tenant;
    }
}
