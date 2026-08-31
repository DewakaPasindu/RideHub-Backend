<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */
        $superAdmin = Role::findByName('Super Admin');

        $allPermissions = collect(config('ridehub_permissions'))
            ->flatten()
            ->toArray();

        $superAdmin->syncPermissions($allPermissions);
        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */
        $admin = Role::findByName('Admin');

        $admin->syncPermissions([
            'admin.dashboard',
            'admin.users',
            'admin.roles',
            'admin.permissions',
            'admin.system',

            'driver.review',
            'driver.approve',
            'driver.reject',
            'driver.verify',
            'driver.manage',

            'vehicle.manage',
            'vehicle.verify',

            'booking.manage',

            'payment.manage',

            'wallet.manage',

            'analytics.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Customer
        |--------------------------------------------------------------------------
        */
        $customer = Role::findByName('Customer');

        $customer->syncPermissions([
            'customer.view',
            'customer.update',

            'driver.apply',
            'driver.view',
            'driver.update',

            'booking.create',
            'booking.view',
            'booking.cancel',

            'payment.create',
            'payment.view',

            'wallet.view',
            'wallet.deposit',
            'wallet.withdraw',

            'review.create',
            'review.update',
            'review.delete',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Driver
        |--------------------------------------------------------------------------
        */
        $driver = Role::findByName('Driver');

        $driver->syncPermissions([
            'driver.view',
            'driver.update',
            'booking.view',
            'wallet.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Vehicle Owner
        |--------------------------------------------------------------------------
        */
        $owner = Role::findByName('Vehicle Owner');

        $owner->syncPermissions([
            'owner.apply',
            'owner.view',
            'owner.update',
            'vehicle.create',
            'vehicle.update',
            'vehicle.view',
        ]);
    }
}