<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RideHub Permissions
    |--------------------------------------------------------------------------
    */

    'booking' => [
        'booking.view',
        'booking.create',
        'booking.update',
        'booking.cancel',
        'booking.manage',
    ],

    'vehicle' => [
        'vehicle.view',
        'vehicle.create',
        'vehicle.update',
        'vehicle.delete',
        'vehicle.verify',
        'vehicle.manage',
    ],

    'driver' => [
        'driver.apply',
        'driver.view',
        'driver.update',
        'driver.verify',
        'driver.manage',
    ],

    'vehicle_owner' => [
        'owner.apply',
        'owner.view',
        'owner.update',
        'owner.verify',
        'owner.manage',
    ],

    'customer' => [
        'customer.view',
        'customer.update',
    ],

    'rental' => [
        'rental.create',
        'rental.view',
        'rental.return',
        'rental.manage',
    ],

    'payment' => [
        'payment.create',
        'payment.view',
        'payment.refund',
        'payment.manage',
    ],

    'wallet' => [
        'wallet.view',
        'wallet.deposit',
        'wallet.withdraw',
        'wallet.manage',
    ],

    'review' => [
        'review.create',
        'review.update',
        'review.delete',
        'review.manage',
    ],

    'notification' => [
        'notification.send',
        'notification.view',
        'notification.manage',
    ],

    'analytics' => [
        'analytics.view',
    ],

    'admin' => [
        'admin.dashboard',
        'admin.users',
        'admin.roles',
        'admin.permissions',
        'admin.system',
    ],
];