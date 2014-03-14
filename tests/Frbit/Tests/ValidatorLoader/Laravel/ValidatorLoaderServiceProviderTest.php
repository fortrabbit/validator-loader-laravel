<?php
/**
 * This class is part of BackendApi
 */

namespace Frbit\Tests\ValidatorLoader\Laravel;

use Frbit\ValidatorLoader\Laravel\ValidatorLoaderServiceProvider;

/**
 * @covers  \Frbit\ValidatorLoader\Laravel\ValidatorLoaderServiceProvider
 * @package Frbit\Tests\ValidatorLoader\Laravel
 **/
class ValidatorLoaderServiceProviderTest extends TestCase
{


    /**
     * @var \Mockery\MockInterface
     */
    protected $application;

    /**
     * @var \Mockery\MockInterface
     */
    protected $cache;

    /**
     * @var \Mockery\MockInterface
     */
    protected $config;

    /**
     * @var \Mockery\MockInterface
     */
    protected $files;

    /**
     * @var \Mockery\MockInterface
     */
    protected $loader;

    /**
     * @var \Mockery\MockInterface
     */
    protected $validator;

    public function setUp()
    {
        parent::setUp();
        $this->application = \Mockery::mock('Illuminate\Foundation\Application');
        $this->cache       = \Mockery::mock('Illuminate\Cache\Repository');
        $this->config      = \Mockery::mock('Illuminate\Config\Repository');
        $this->files       = \Mockery::mock('Illuminate\Filesystem\Filesystem');
        $this->loader      = \Mockery::mock('Frbit\ValidatorLoader\Loader');
        $this->validator   = \Mockery::mock('Illuminate\Validation\Factory');
    }


    public function testConditionalFactoryBindingsAreRegistered()
    {
        $provider = $this->generateProvider();
        $self     = $this;

        $expected = array(
            'validator-loader.array-factory'     => true,
            'validator-loader.directory-factory' => true,
            'validator-loader.file-factory'      => true,
        );

        // bind source specific conditionally..
        $this->application->shouldReceive('bindIf')
            ->times(3)
            ->andReturnUsing(function ($binding, $closure, $shared) use ($self, &$expected) {
                $self->assertArrayHasKey($binding, $expected, "Unexpected conditional binding \"$binding\"");
                unset($expected[$binding]);
                $self->assertInstanceOf('\Closure', $closure, "\"$binding\" should be closure");
                $self->assertTrue($shared, "\"$binding\" supposed to be shared");
            });

        // bind loader
        $this->application->shouldReceive('bind')
            ->once()
            ->andReturnUsing(function ($binding, $closure, $shared) use ($self, &$expected) {
                $self->assertSame('validator-loader', $binding, "Unexpected binding \"$binding\"");
                $self->assertInstanceOf('\Closure', $closure, "\"$binding\" should be closure");
                $self->assertTrue($shared, "\"$binding\" supposed to be shared");
            });

        $provider->register();

        $this->assertEmpty($expected, "Not all expected bindings made");
    }

    public function testBuildValidLoaderFromConfiguration()
    {
        $provider = $this->generateProvider();

        // need config
        $this->application->shouldReceive('make')
            ->once()
            ->with('config')
            ->andReturn($this->config);

        // need validator
        $this->application->shouldReceive('make')
            ->once()
            ->with('validator')
            ->andReturn($this->validator);

        // assert cache enabled but empty
        $this->assertCacheUsage(true, null);

        // need filesystem
        $this->application->shouldReceive('make')
            ->once()
            ->with('files')
            ->andReturn($this->files);

        // get config and it's setup
        $this->config->shouldReceive('get')
            ->once()
            ->with('validator-loader-laravel::source')
            ->andReturn('array');
        $this->config->shouldReceive('get')
            ->once()
            ->with('validator-loader-laravel::sources.array')
            ->andReturn(array('the-validator'));

        // make the loader
        $this->application->shouldReceive('make')
            ->once()
            ->with("validator-loader.array-factory", array(array('the-validator'), $this->validator, $this->files))
            ->andReturn($this->loader);

        // cache the loader
        $this->loader->shouldReceive('toArray')
            ->once()
            ->andReturn(array('loader' => 'data'));
        $this->cache->shouldReceive('put')
            ->once()
            ->with('the-cache-key', array('loader' => 'data'), 123);

        $loader = $provider->buildLoader();
        $this->assertSame($this->loader, $loader);
    }

    public function testReturnCachedLoaderIfFound()
    {
        $provider = $this->generateProvider();

        // need config
        $this->application->shouldReceive('make')
            ->once()
            ->with('config')
            ->andReturn($this->config);

        // need validator
        $this->application->shouldReceive('make')
            ->once()
            ->with('validator')
            ->andReturn($this->validator);

        // assert cache enabled but empty
        $this->assertCacheUsage(true, array(
            'validators' => array(),
            'methods'    => array(
                'foo' => 'bar'
            )
        ));

        $loader = $provider->buildLoader();
        $this->assertInstanceOf('\Frbit\ValidatorLoader\Loader', $loader);
    }

    public function testNotCachedIfCacheDisabled()
    {
        $provider = $this->generateProvider();

        // need config
        $this->application->shouldReceive('make')
            ->once()
            ->with('config')
            ->andReturn($this->config);

        // need validator
        $this->application->shouldReceive('make')
            ->once()
            ->with('validator')
            ->andReturn($this->validator);

        // assert cache enabled but empty
        $this->assertCacheUsage(false);

        // need filesystem
        $this->application->shouldReceive('make')
            ->once()
            ->with('files')
            ->andReturn($this->files);

        // get config and it's setup
        $this->config->shouldReceive('get')
            ->once()
            ->with('validator-loader-laravel::source')
            ->andReturn('array');
        $this->config->shouldReceive('get')
            ->once()
            ->with('validator-loader-laravel::sources.array')
            ->andReturn(array('the-validator'));

        // make the loader
        $this->application->shouldReceive('make')
            ->once()
            ->with("validator-loader.array-factory", array(array('the-validator'), $this->validator, $this->files))
            ->andReturn($this->loader);

        // assert cached
        $this->cache->shouldReceive('put')
            ->never();

        $loader = $provider->buildLoader();
        $this->assertSame($this->loader, $loader);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Source "foo" not defined in "config.sources.foo"
     */
    public function testFailOnBuildingInvalidLoader()
    {
        $provider = $this->generateProvider();

        // need config
        $this->application->shouldReceive('make')
            ->once()
            ->with('config')
            ->andReturn($this->config);

        // need validator
        $this->application->shouldReceive('make')
            ->once()
            ->with('validator')
            ->andReturn($this->validator);

        // assert cache enabled but empty
        $this->assertCacheUsage(false);

        // need filesystem
        $this->application->shouldReceive('make')
            ->once()
            ->with('files')
            ->andReturn($this->files);

        // get config and it's setup
        $this->config->shouldReceive('get')
            ->once()
            ->with('validator-loader-laravel::source')
            ->andReturn('foo');
        $this->config->shouldReceive('get')
            ->once()
            ->with('validator-loader-laravel::sources.foo')
            ->andReturn(array());

        $provider->buildLoader();
    }

    public function testModifiedPackagePathGuessIsCalculatedCorrectly()
    {
        $provider = $this->generateProvider();
        $this->assertStringEndsWith('/ValidatorLoaderLaravel/src', $provider->guessPackagePath());
    }

    public function testAllProvidedAreListed()
    {
        $provider = new ValidatorLoaderServiceProvider($this->application);
        $this->assertEquals(array(
            'validator-loader',
            'validator-loader.array-factory',
            'validator-loader.directory-factory',
            'validator-loader.file-factory',
        ), $provider->provides());
    }

    /**
     * @return ValidatorLoaderServiceProvider
     */
    protected function generateProvider()
    {
        $provider = new ValidatorLoaderServiceProvider($this->application);

        return $provider;
    }

    /**
     * @param bool  $inUse
     * @param mixed $cacheResult
     *
     * @return mixed
     */
    protected function assertCacheUsage($inUse = true, $cacheResult = null)
    {
        // need cache
        $this->application->shouldReceive('make')
            ->once()
            ->with('cache')
            ->andReturn($this->cache);

        if ($inUse) {

            // read cache config
            $this->config->shouldReceive('get')
                ->once()
                ->with('validator-loader-laravel::cache')
                ->andReturn(123);
            $this->config->shouldReceive('get')
                ->once()
                ->with('validator-loader-laravel::cache-key')
                ->andReturn('the-cache-key');

            // check cache
            $this->cache->shouldReceive('get')
                ->once()
                ->with('the-cache-key')
                ->andReturn($cacheResult);
        } else {

            // read cache config
            $this->config->shouldReceive('get')
                ->once()
                ->with('validator-loader-laravel::cache')
                ->andReturn(array());
            $this->config->shouldReceive('get')
                ->once()
                ->with('validator-loader-laravel::cache-key')
                ->andReturn(array());

            // don't check cache
            $this->cache->shouldReceive('get')
                ->never();
        }

        return $cacheResult;
    }


}