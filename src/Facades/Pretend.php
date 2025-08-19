<?php

namespace Horlerdipo\Pretend\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Horlerdipo\Pretend\Pretend
 */
class Pretend extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Horlerdipo\Pretend\Pretend::class;
    }
}
