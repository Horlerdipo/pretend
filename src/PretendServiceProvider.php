<?php

namespace Horlerdipo\Pretend;

use Horlerdipo\Pretend\Commands\PretendCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PretendServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('pretend')
            ->hasConfigFile()
            ->hasMigration('create_impersonations_table')
            ->hasCommand(PretendCommand::class);
    }
}
