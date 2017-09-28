<?php


return array(
    // If you don't use a whitelist then only files included during the request will be counted
    // If you do, then only whitelisted items will be counted
    'use_whitelist' => true,
    // Items to whitelist, only used in cli
    'whitelist' => array(
        // Should the app be whitelisted?
        // Useful if you just want to test your application
        'app' => true,
        // Set to array(true) to include all modules, or use an array of module names
        // (the keys of the array passed to Core::modules() in the bootstrap)
        // Or set to false to exclude all modules
        'modules' => array(true),
        // If you don't want the Bootphp code coverage reports to pollute your app's,
        // then set this to false
        'system' => true,
    ),
    // Does what it says on the tin
    // Blacklisted files won't be included in code coverage reports
    // If you use a whitelist then the blacklist will be ignored
    'use_blacklist' => false,
    // List of individual files/folders to blacklist
    'blacklist' => array(
    ),
);
