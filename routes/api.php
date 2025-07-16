<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CustomInitializeTenancyByRequestData;
use Stancl\Tenancy\Features\UniversalRoutes;

// Non-tenant routes (available without tenant identification)
Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return [
            'status' => 'ok',
            'message' => 'API is running',
            'timestamp' => now()->toISOString(),
        ];
    });
    
    Route::get('/tenants', function () {
        return [
            'available_tenants' => \App\Tenant::all()->pluck('id'),
            'usage' => [
                'query_parameter' => 'Add ?tenant=TENANT_ID to your request',
                'header' => 'Add X-TENANT-ID: TENANT_ID header to your request',
            ],
        ];
    });
});

// Tenant-aware API routes using the CustomInitializeTenancyByRequestData middleware
// with X-Tenant-ID header or tenant query parameter
Route::middleware([
    CustomInitializeTenancyByRequestData::class,
    'api',
])
->prefix('v1')
->group(function () {
    Route::get('/tenant-info', function () {
        return [
            'tenant_id' => tenant('id'),
            'db' => tenant('database'),
            'message' => 'Successfully identified tenant',
        ];
    });
    
    // ...add more tenant-aware API routes here...
});
