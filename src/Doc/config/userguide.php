<?php


return array
    (
    // Enable the API browser.  true or false
    'api_browser' => true,
    // Enable these packages in the API browser.  true for all packages, or a string of comma seperated packages, using 'None' for a class with no @package
    // Example: 'api_packages' => 'Bootphp,Bootphp/Database,Bootphp/ORM,None',
    'api_packages' => true,
    // Enables Disqus comments on the API and User Guide pages
    'show_comments' => Core::$environment === Core::PRODUCTION,
    // Leave this alone
    'modules' => array(
        // This should be the path to this modules userguide pages, without the 'guide/'. Ex: '/guide/modulename/' would be 'modulename'
        'userguide' => array(
            // Whether this modules userguide pages should be shown
            'enabled' => true,
            // The name that should show up on the userguide index page
            'name' => 'Userguide',
            // A short description of this module, shown on the index page
            'description' => 'Documentation viewer and api generation.',
            // Copyright message, shown in the footer for this module
            'copyright' => '&copy; 2008–2014 Bootphp Team',
        )
    ),
    // Set transparent class name segments
    'transparent_prefixes' => array(
        'Bootphp' => true,
    )
);
