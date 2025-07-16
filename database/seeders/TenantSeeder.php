<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Tenant;

class TenantSeeder extends Seeder
{
    public function run()
    {
        $tenants = [
            [
                'id' => 'tenant1',
                'data' => [
                    'database' => 'tenant1_db',
                    'db_host' => '127.0.0.1',
                    'db_username' => 'dev',
                    'db_password' => 'password',
                ],
            ],
            [
                'id' => 'tenant2',
                'data' => [
                    'database' => 'tenant2_db',
                    'db_host' => '127.0.0.1',
                    'db_username' => 'dev',
                    'db_password' => 'password',
                ],
            ],
            [
                'id' => 'tenant3',
                'data' => [
                    'database' => 'tenant3_db',
                    'db_host' => '127.0.0.1',
                    'db_username' => 'dev',
                    'db_password' => 'password',
                ],
            ],
        ];

        foreach ($tenants as $tenant) {
            $t = new Tenant(['id' => $tenant['id']]);
            
            // Set data attributes directly on the model
            foreach ($tenant['data'] as $key => $value) {
                $t->$key = $value;
            }
            
            $t->save();
        }
    }
}
