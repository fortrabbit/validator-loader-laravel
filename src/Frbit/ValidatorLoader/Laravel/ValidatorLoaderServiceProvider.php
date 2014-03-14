<?php

namespace Frbit\ValidatorLoader\Laravel;

use Frbit\ValidatorLoader\Factory;
use Frbit\ValidatorLoader\Loader;
use Illuminate\Cache\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory as ValidatorFactory;

/**
 * Service provider for validator loader.
 *
 * @package Frbit\ValidatorLoader\Laravel
 */
class ValidatorLoaderServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     * @codeCoverageIgnore This is tested by laravel
     */
    public function boot()
    {
        $this->package('frbit/validator-loader-laravel');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $self = $this;

        // bind source specific loaders conditionally
        $this->app->bindIf('validator-loader.array-factory', function ($app, array $args) use ($self) {
            return call_user_func_array(array($self, 'buildArrayLoader'), $args);
        }, true);
        $this->app->bindIf('validator-loader.directory-factory', function ($app, array $args) use ($self) {
            return call_user_func_array(array($self, 'buildDirectoryLoader'), $args);
        }, true);
        $this->app->bindIf('validator-loader.file-factory', function ($app, array $args) use ($self) {
            return call_user_func_array(array($self, 'buildFileLoader'), $args);
        }, true);

        // bind loader
        $this->app->bind('validator-loader', function () use ($self) {
            return $self->buildLoader();
        }, true);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array(
            'validator-loader',
            'validator-loader.array-factory',
            'validator-loader.directory-factory',
            'validator-loader.file-factory',
        );
    }

    /**
     * Needs to be overridden due to namespace with depth of three
     *
     * @return string
     */
    public function guessPackagePath()
    {
        $path = with(new \ReflectionClass($this))->getFileName();

        return realpath(dirname($path) . '/../../../');
    }

    /**
     * Builds loader from settings
     *
     * @return Loader
     * @throws \InvalidArgumentException
     */
    public function buildLoader()
    {
        $config    = $this->app->make('config');
        $validator = $this->app->make('validator');

        // try cache
        /** @var Repository $cache */
        $cache     = $this->app->make('cache');
        $cacheTime = $config->get('validator-loader-laravel::cache');
        $cacheKey  = $config->get('validator-loader-laravel::cache-key');
        $useCache  = $cacheTime && $cacheKey;
        if ($useCache && ($cached = $cache->get($cacheKey))) {
            return $this->buildArrayLoader($cached, $validator);
        }

        // create new
        $files = $this->app->make('files');
        $type  = $config->get('validator-loader-laravel::source');
        if (!$type) {
            throw new \InvalidArgumentException("No source defined in \"config.source\"");
        }
        $source = $config->get("validator-loader-laravel::sources.{$type}");
        if (!$source) {
            throw new \InvalidArgumentException("Source \"$type\" not defined in \"config.sources.$type\"");
        }

        // generate
        /** @var Loader $loader */
        $loader = $this->app->make("validator-loader.$type-factory", array($source, $validator, $files));

        // write to cache?
        if ($useCache) {
            $cache->put($cacheKey, $loader->toArray(), $cacheTime);
        }

        return $loader;
    }

    /**
     * Build loader from array
     *
     * @param array            $source
     * @param ValidatorFactory $validator
     *
     * @return Loader
     * @codeCoverageIgnore not testable
     */
    public function buildArrayLoader(array $source, ValidatorFactory $validator)
    {
        return Factory::fromArray($source, $validator);
    }

    /**
     * Build loader from directory
     *
     * @param string           $source
     * @param ValidatorFactory $validator
     * @param Filesystem       $files
     *
     * @return Loader
     * @codeCoverageIgnore not testable
     */
    public function buildDirectoryLoader($source, ValidatorFactory $validator, Filesystem $files)
    {
        return Factory::fromDirectory($source, false, $validator, $files);
    }

    /**
     * Build loader from file
     *
     * @param string           $source
     * @param ValidatorFactory $validator
     * @param Filesystem       $files
     *
     * @return Loader
     * @codeCoverageIgnore not testable
     */
    public function buildFileLoader($source, ValidatorFactory $validator, Filesystem $files)
    {
        return Factory::fromFile($source, $validator, $files);
    }

}
