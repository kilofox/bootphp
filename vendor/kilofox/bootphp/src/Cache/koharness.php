<?php

// Configuration for koharness - builds a standalone skeleton Bootphp app for running unit tests
return array(
    'bootphp_version' => '3.3',
    'modules' => array(
        'cache' => __DIR__,
        'unittest' => __DIR__ . '/vendor/bootphp/unittest'
    ),
);
