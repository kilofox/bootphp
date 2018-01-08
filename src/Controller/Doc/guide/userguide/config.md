# Configuration

The userguide has the following config options, available in `config/userguide.php`.

	return array
	(
		// Enable the API browser.  true or false
		'api_browser'  => true,

		// Enable these packages in the API browser.  true for all packages, or a string of comma seperated packages, using 'None' for a class with no @package
		// Example: 'api_packages' => 'Bootphp,Bootphp/Database,Bootphp/ORM,None',
		'api_packages' => true,

	);

You can enable or disable the entire API browser, or limit it to only show certain packages.  To disable a module from showing pages in the userguide, simply change that module's `enabled` option using the cascading filesystem.  For example:

	`application/config/userguide.php`

	return array
	(
		'modules' => array
		(
			'kohana' => array
			(
				'enabled' => false,
			),
			'database' => array
			(
				'enabled' => false,
			)
		)
	)

Using this you could make the userguide only show your modules and classes in the API browser, if you wanted to host your own documentation on your site.  Feel free to change the styles and views as well, but be sure to give credit where credit is due!