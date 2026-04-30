<?php

namespace FyrnDly\Matomo\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \FyrnDly\Matomo\MatomoService
 */
class Matomo extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \FyrnDly\Matomo\MatomoService::class;
    }
}