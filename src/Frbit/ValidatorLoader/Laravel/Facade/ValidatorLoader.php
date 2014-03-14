<?php
/**
 * This class is part of BackendApi
 */

namespace Frbit\ValidatorLoader\Laravel\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for loader
 *
 * @see \Frbit\ValidatorLoader\Loader
 *
 * @package Frbit\ValidatorLoader\Laravel\Facade
 * @codeCoverageIgnore
 **/
class ValidatorLoader extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'validator-loader';
    }
}