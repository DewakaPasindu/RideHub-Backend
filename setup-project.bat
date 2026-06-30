@echo off
echo ===============================
echo Creating RideHub Folder Structure
echo ===============================

cd app

mkdir Core
mkdir Core\Base
mkdir Core\Enums
mkdir Core\Exceptions
mkdir Core\Helpers
mkdir Core\Traits

mkdir Services
mkdir Services\Auth
mkdir Services\Booking
mkdir Services\Driver
mkdir Services\Vehicle
mkdir Services\Wallet
mkdir Services\Payment
mkdir Services\Notification
mkdir Services\Location
mkdir Services\Admin
mkdir Services\AI

mkdir Http\Controllers\Api

mkdir Http\Controllers\Api\Auth
mkdir Http\Controllers\Api\User
mkdir Http\Controllers\Api\Driver
mkdir Http\Controllers\Api\VehicleOwner
mkdir Http\Controllers\Api\Vehicle
mkdir Http\Controllers\Api\Booking
mkdir Http\Controllers\Api\Wallet
mkdir Http\Controllers\Api\Payment
mkdir Http\Controllers\Api\Review
mkdir Http\Controllers\Api\Notification
mkdir Http\Controllers\Api\Admin
mkdir Http\Controllers\Api\SuperAdmin

mkdir Http\Requests

mkdir Http\Requests\Auth
mkdir Http\Requests\User
mkdir Http\Requests\Driver
mkdir Http\Requests\Vehicle
mkdir Http\Requests\Booking
mkdir Http\Requests\Wallet

mkdir Http\Resources

mkdir Http\Resources\Auth
mkdir Http\Resources\User
mkdir Http\Resources\Driver
mkdir Http\Resources\Vehicle
mkdir Http\Resources\Booking

mkdir Policies

cd ..

mkdir docs
mkdir docs\Architecture
mkdir docs\Database
mkdir docs\API
mkdir docs\API\Postman
mkdir docs\API\Swagger
mkdir docs\Diagrams
mkdir docs\MeetingNotes
mkdir docs\Screenshots

echo.
echo Project Structure Created Successfully!
pause