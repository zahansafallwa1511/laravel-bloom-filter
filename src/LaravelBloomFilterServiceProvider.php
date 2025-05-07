<?php

namespace Intimation\LaravelBloomFilter;

use Intimation\LaravelBloomFilter\Commands\LaravelBloomFilterCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelBloomFilterServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-bloom-filter')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_bloom_filter_table')
            ->hasCommand(LaravelBloomFilterCommand::class);
    }
}
