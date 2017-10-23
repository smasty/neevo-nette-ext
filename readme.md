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

1.  Add the extension to your Composer configuration:

	```json
	{
		"require": {
			"smasty/neevo-nette-ext": "1.*"
		}
	}
	```

2.  Add new sections to your config file (e.g. `app/config/config.neon`)
    and place there your Neevo configuration:

		extensions:
			neevo: Neevo\Nette\Extension

		neevo:
			driver: MySQLi
			username: root
			password: ****
			database: my_database

4.  There is no step three.
