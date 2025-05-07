<?php

namespace Intimation\LaravelBloomFilter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Intimation\LaravelBloomFilter\LaravelBloomFilter
 */
class LaravelBloomFilter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Intimation\LaravelBloomFilter\LaravelBloomFilter::class;
    }
}
