<?php

namespace Horlerdipo\Pretend;

use Horlerdipo\Pretend\Contracts\HasImpersonationStorage;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Application;
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
            ->hasMigration('create_impersonations_table');
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(HasImpersonationStorage::class, function (Application $app) {

            $storage = app(config()->string('pretend.impersonation_storage'));
            if (! $storage instanceof HasImpersonationStorage) {
                throw new BindingResolutionException('Impersonation storage is not configured.');
            }

            return $storage;
        });
    }
}
