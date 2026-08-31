<?php

namespace App\Core\Enums;

use App\Core\Traits\HasEnumHelpers;

enum DocumentType: string
{
    use HasEnumHelpers;

    case DRIVING_LICENSE = 'driving_license';

    case NIC = 'nic';

    case PASSPORT = 'passport';

    case SELFIE = 'selfie';

    case VEHICLE_REGISTRATION = 'vehicle_registration';

    case INSURANCE = 'insurance';

    case REVENUE_LICENSE = 'revenue_license';

    case BUSINESS_REGISTRATION = 'business_registration';

    case POLICE_CLEARANCE = 'police_clearance';

    case MEDICAL_CERTIFICATE = 'medical_certificate';
}