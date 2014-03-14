# Service provider for Validator Loader

Allows you to source your validation rules out into files (.json, .yml, .php) or structure them in directories.
Comes with a simple inheritance feature and variables to reduce writing the same regex (or whatnot) over and over
again (see the Validator [Validator Loader](https://github.com/fortrabbit/validator-loader) package).

## Installation

``` bash
$ php composer.phar require "frbit/validator-loader-laravel:*"
```

Now add the service provider and the facade (if you want) to `app/config/app.php`

``` php
<?php

return array(
    # ...
    'providers' => array(
        # ...
        'Frbit\ValidatorLoader\Laravel\ValidatorLoaderServiceProvider',
    ),

    # ...
    'aliases' => array(
        # ...
        'ValidatorLoader' => 'Frbit\ValidatorLoader\Laravel\Facade\ValidatorLoader'
    )
)
```

## Usage

This package provides a facade and registeres with the [IoC Container](http://laravel.com/docs/ioc).

Please see the readme of the [Validator Loader](https://github.com/fortrabbit/validator-loader) package for examples for validation rules.

### Using the facade

Somewhere in your model or controller:

``` php
<?php

// get the input for validation
$input = Input::all();

// this returns just the same as Laravel's \Validator::make($input, $rules) would
$validator = \ValidatorLoader::get("my-form", $input);
if ($validator->fails()) {
    # ..
)
```

### Using the IoC

Somewhere in your model or controller:

``` php
<?php

// get the input for validation
$input = Input::all();

// this returns just the same as Laravel's \Validator::make($input, $rules) would
$loader    = \App::make("validator-loader");
$validator = $loader->get("my-form", $input);
if ($validator->fails()) {
    # ..
)
```

## Configuration

First publish the configuration

``` bash
$ php artisan config:publish frbit/validator-loader-laravel
```

You can find the config in `app/config/packages/frbit/validator-loader-laravel`

### Rules in file

Having all validation rules in a single file. Set `source` to `file` and write your validation rules in `sources.file`:

``` php
<?php

return array(
    'source'  => 'file',
    'sources' => array(

        // relative paths are considered realtive to app folder
        'file' => 'path/to/file'
    )
);
```

### Rules in directory

When extensive validation rules are required or a neat structure is preferred. Set `source` to `directory` and write your validation rules in `sources.directory`:

``` php
<?php

return array(
    'source'  => 'directory',
    'sources' => array(

        // relative paths are considered realtive to app folder
        'directory' => 'path/to/directory'
    )
);
```

### Rules in array

For testing, I suppose. Set `source` to `array` and write your validation rules in `sources.array`:

``` php
<?php

return array(
    'source'  => 'array',
    'sources' => array(
        'array' => array(
            # ..
        )
    )
);
```

### Caching

Especially for `directory` sources, loading validation rules can lead to increased disk i/o on each request which reduces performances. Caching allows you to mitigate this.

``` php
<?php

return array(
    // time in minutes -> set to 0 to disable caching
    'cache'     => 123,

    // key name under which cache is stored
    'cache-key' => 'some-key-name',
);
```