Neevo extension for Nette Framework
===================================

This package provides tools to optimize Neevo DBAL for use in
a Nette Framework application. It will register Neevo as a service to
the DI Container, add a panel to DebugBar showing performed queries
and a panel to Bluescreen with SQL query in case of NeevoException.
It also provides an adapter for Nette cache storage system.

Only Nette Framework 2.0 and above PHP 5.3 packages are supported.

Instructions
------------

1.  Register the Neevo compiler extension in your application bootstrap
    (e.g. `app/bootstrap.php`):

	```php
	<?php
	$configurator->onCompile[] = function($configurator, $compiler){
		$compiler->addExtension('neevo', new Neevo\Nette\Extension);
	};
	```

2.  Add a new section `neevo` to your config file (e.g. `app/config/config.neon`)
    and place there your Neevo configuration:

		neevo:
			driver: MySQLi
			username: root
			password: ****
			database: my_database

3.  There is no step three.