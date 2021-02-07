<?php

namespace Kalizi\LaravelSpyhole;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kalizi\LaravelSpyhole\Skeleton\SkeletonClass
 */
class LaravelSpyholeFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-spyhole';
    }
}
