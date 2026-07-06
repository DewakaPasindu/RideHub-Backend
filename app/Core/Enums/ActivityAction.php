<?php

namespace App\Enums;

enum ActivityAction: string
{
    // Authentication
    case USER_REGISTERED = 'user.registered';
    case USER_LOGGED_IN = 'user.logged_in';
    case USER_LOGGED_OUT = 'user.logged_out';
    case PASSWORD_CHANGED = 'user.password_changed';

    // Customer Profile
    case PROFILE_CREATED = 'profile.created';
    case PROFILE_UPDATED = 'profile.updated';
    case PROFILE_PHOTO_UPDATED = 'profile.photo_updated';

    // Driver
    case DRIVER_APPLICATION_SUBMITTED = 'driver.application.submitted';
    case DRIVER_APPLICATION_APPROVED = 'driver.application.approved';
    case DRIVER_APPLICATION_REJECTED = 'driver.application.rejected';

    // Vehicle
    case VEHICLE_CREATED = 'vehicle.created';
    case VEHICLE_UPDATED = 'vehicle.updated';
    case VEHICLE_DELETED = 'vehicle.deleted';

    // Booking
    case BOOKING_CREATED = 'booking.created';
    case BOOKING_ACCEPTED = 'booking.accepted';
    case BOOKING_COMPLETED = 'booking.completed';
    case BOOKING_CANCELLED = 'booking.cancelled';

    // Payment
    case PAYMENT_INITIATED = 'payment.initiated';
    case PAYMENT_COMPLETED = 'payment.completed';
    case PAYMENT_FAILED = 'payment.failed';
}