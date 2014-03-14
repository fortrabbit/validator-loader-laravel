<?php
/**
 * This class is part of BackendApi
 */

namespace Frbit\Tests\ValidatorLoader\Laravel;

/**
 * Class TestCase
 * @package Frbit\Tests\ValidatorLoader\Laravel
 **/
class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        \Mockery::close();
        parent::tearDown(); // TODO: Change the autogenerated stub
    }


}