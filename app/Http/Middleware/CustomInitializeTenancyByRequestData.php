<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;

class CustomInitializeTenancyByRequestData extends InitializeTenancyByRequestData
{
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
            Log::warning("No tenant found in request", [
                'headers' => $request->headers->all(),
                'query' => $request->query->all()
            ]);
        }
        
        return $tenant;
    }
}
