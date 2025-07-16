<?php

namespace App;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Contracts\SyncMaster;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;

    protected $fillable = ['id', 'data'];
    
    /**
     * The connection name to use for the Tenant model itself.
     * This ensures tenant operations always use the central database.
     *
     * @var string|null
     */
    protected $connection = 'central';
    
    /**
     * The attributes that should be casted to native types.
     * This is built into the package - we don't need custom caching.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];
}
