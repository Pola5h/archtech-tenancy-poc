<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Tenancy;
use Stancl\Tenancy\Contracts\TenantDatabaseManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // The stancl/tenancy package already handles caching internally
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Initialize the central database connection
        if (!app()->runningInConsole()) {
            $centralConnection = config('tenancy.database.central_connection');
            
            // Create a persistent connection to central DB
            DB::connection($centralConnection);
            
            // Use app()->terminating to properly clean up connections
            app()->terminating(function () {
                // Let Laravel handle connection cleanup
            });
        }
    }
}
