@echo off

echo Installing Packages...

composer require laravel/sanctum

php artisan install:api

composer require spatie/laravel-permission

composer require --dev barryvdh/laravel-ide-helper

composer require laravel/pint --dev

composer dump-autoload

echo Packages Installed Successfully!
pause