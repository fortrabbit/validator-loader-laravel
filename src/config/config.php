<?php
/**
 * This class is part of BackendApi
 */

return array(

    /*
    |--------------------------------------------------------------------------
    | Source
    |--------------------------------------------------------------------------
    |
    | Source can be either "file", "directory" or "array"
    |
    */
    'source'    => 'array',

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Time in minutes for caching the validation rules. Set to 0 to disable.
    |
    */
    'cache'     => 10,

    /*
    |--------------------------------------------------------------------------
    | Cache Key
    |--------------------------------------------------------------------------
    |
    | Name of the key used for caching
    |
    */
    'cache-key' => 'validation-rules',


    /*
    |--------------------------------------------------------------------------
    | Configuration for sources
    |--------------------------------------------------------------------------
    */
    'sources'   => array(

        /*
        |--------------------------------------------------------------------------
        | File source
        |--------------------------------------------------------------------------
        |
        | Path to a file (.json, .yml or .php) containing the validation rules.
        |
        | Paths (not beginning with "/" (or "\" on windows)) are considered
        | relative to the app folder.
        |
        */
        'file'      => '',

        /*
        |--------------------------------------------------------------------------
        | Directory source
        |--------------------------------------------------------------------------
        |
        | Path to a directory, which contains the validation rule files. Supported
        | rule files are .json, .yml and .php
        |
        | Paths (not beginning with "/" (or "\" on windows)) are considered
        | relative to the app folder.
        |
        */
        'directory' => '',


        /*
        |--------------------------------------------------------------------------
        | Array source
        |--------------------------------------------------------------------------
        |
        | Set of validation rules in the presented format. For testing.
        |
        */
        'array'     => array(
            'variables'  => array(
                'Foo' => 'bar'
            ),
            'methods'    => array( #'custom' => 'Class@method'
            ),
            'validators' => array(
                'my-form'    => array(
                    'rules'    => array(
                        'parameter' => array(
                            'min:3',
                            'max:6',
                        )
                    ),
                    'messages' => array(
                        'parameter.min' => 'Too short',
                        'parameter.max' => 'Too Long'
                    )
                ),
                'other-form' => array(
                    'extends'  => 'my-form',
                    'rules'    => array(
                        'parameter' => array(
                            'required',
                            'min:4'
                        )
                    ),
                    'messages' => array(
                        'parameter.required' => 'Missing!'
                    )
                )
            )
        )
    )
);