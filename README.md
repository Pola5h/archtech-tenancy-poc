# Multi-Tenancy Implementation with stancl/tenancy

This repository demonstrates a multi-tenant application built with Laravel using the `stancl/tenancy` package. The implementation addresses specific requirements for a robust, scalable, and flexible multi-tenant system.

## Key Features

### 1. Request-Based Tenant Identification via X-TENANT-ID

- Implemented custom middleware `CustomInitializeTenancyByRequestData` that identifies tenants using:
  - `X-TENANT-ID` HTTP header (primary method)
  - `?tenant=` query parameter (fallback method)
- Gracefully handles invalid tenant identifiers with proper error responses instead of exceptions
- Automatically logs tenant identification attempts for debugging and audit purposes

```php
// Example API request with X-TENANT-ID header
curl -X GET \
  https://yourdomain.com/api/v1/resources \
  -H 'X-TENANT-ID: tenant1'
```

### 2. Tenant Model Storage

- `App\Tenant` model extends `Stancl\Tenancy\Database\Models\Tenant` and implements `TenantWithDatabase`
- Tenant identifiers and database connection details are stored in the central database
- Uses the `data` attribute (JSON column) to store tenant-specific configuration
- Proper type casting and serialization are handled automatically

```php
// Example of accessing tenant data
$tenant = \App\Tenant::find('tenant1');
$databaseName = $tenant->database()->getName();
$tenantData = $tenant->data; // Array of tenant-specific config
```

### 3. Persistent Central Database Connection & Caching

- Maintains a persistent connection to the central database via `AppServiceProvider`
- Implements connection caching to reduce database queries:
  - Configured with `tenant_connection_cache_ttl` (default: 3600 seconds)
  - Automatically invalidates cache when tenants are updated or deleted
- Efficient connection management to minimize overhead during tenant switching

```php
// Configuration in tenancy.php
'database' => [
    'central_connection' => env('DB_CONNECTION', 'central'),
    'tenant_connection_cache_ttl' => 3600, // Cache tenant connections for 1 hour
    // ...
],
```

### 4. Model-Specific Database Connections

- Models can explicitly define their database connection
- Central data models use the `central` connection
- Tenant models automatically use the tenant-specific connection
- Flexible configuration for mixed-mode applications

```php
// Example of a model using central connection
class Tenant extends BaseTenant implements TenantWithDatabase
{
    protected $connection = 'central';
    // ...
}

// Example of a tenant-specific model (uses tenant connection automatically)
class User extends Authenticatable
{
    // No connection specified - uses tenant connection within tenant context
    // ...
}
```

### 5. Unified API Routes

- Universal routes are enabled via the `UniversalRoutes` feature
- All tenant-aware API routes are defined in standard `routes/api.php` file
- Routes can be conditionally applied based on tenant context
- No duplication of route definitions necessary

```php
// Example of tenant-aware routes in api.php
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
        ];
    });
});
```

### 6. Tenant-Aware Job Dispatching

- Jobs maintain tenant context when dispatched
- The `QueueTenancyBootstrapper` ensures jobs run in the correct tenant context
- Works with various queue drivers (database, Redis, etc.)
- Tenant ID is automatically attached to jobs to ensure they execute in the correct context

## Getting Started

### Installation

1. Clone the repository
2. Install dependencies:
   ```
   composer install
   ```
3. Set up your environment variables in `.env`
4. Run migrations:
   ```
   php artisan migrate
   ```
5. Seed initial tenants (optional):
   ```
   php artisan db:seed --class=TenantSeeder
   ```

### Creating a Tenant

```php
$tenant = \App\Tenant::create([
    'id' => 'tenant1', // Will be used in X-TENANT-ID header
    'data' => [
        'name' => 'Tenant Organization Name',
        // Add any other tenant-specific data
    ],
]);

// This will automatically create the tenant database
// and run migrations for the tenant
```

### Using the API

Make requests with the `X-TENANT-ID` header:

```bash
curl -X GET \
  https://yourdomain.com/api/v1/resources \
  -H 'X-TENANT-ID: tenant1'
```

## Configuration

The primary configuration file is `config/tenancy.php`, which defines:

- Database connections
- Caching strategies
- File storage settings
- Queue configuration
- Feature flags
