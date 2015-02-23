# ZfcUserCrud 

ZfcUserCrud provide a CRUD interface to manage Users and Rolers.
Require [ZfcUserDoctrineOrm](https://github.com/ZF-Commons/ZfcUserDoctrineORM).

## Instalation

	php composer.phar require fabiopaiva/zfc-user-crud:dev-master

## Usage

In application.config.php enable this modules:

	<?php //
		return array(
    			'modules' => array(
				'DoctrineModule',
				'DoctrineORMModule',
				'ZfcBase',
				'ZfcUser',
				'ZfcUserDoctrineORM',
				'ZfcUserCrud',
				// .. Another modules you use
				'Application'
				 ),
				...

Don't forget to configure your Doctrine ORM
eg: doctrine.local.php

	<?php
		return array(
		    'doctrine' => array(
		        'connection' => array(
		            'orm_default' => array(
		                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
		                'params' => array(
                		    'host' => 'localhost',
		                    'port' => '3306',
                		    'user' => 'dbUser',
		                    'password' => 'dbPass',
		                    'dbname' => 'dbName',
		        )))));


## Create tables

    ./vendor/bin/doctrine-module orm:schema-tool:update --dump-sql
    #if it's ok, execute
    ./vendor/bin/doctrine-module orm:schema-tool:update --force

In your view use this routes:

	<?php echo $this->url('zfc-user-crud');?> For user interface
	<?php echo $this->url('zfc-user-crud-role');?> For role interface
        <?php echo $this->url('zfc-user-crud-password');?> For change user password

Override configuration if you wanna use your own entities

	return array(
	    'zfcusercrud' => array(
        	'userEntity' => 'ZfcUserCrud\Entity\User',
	        'roleEntity' => 'ZfcUserCrud\Entity\Role'
	    )

#ZfcAdmin

To use with ZfcAdmin, just override the route like this:

	<?php
	return array(
	    'router' => array(
	        'routes' => array(
	            'zfcadmin' => array(
	                'child_routes' => array(
	                    'zfc-user-crud' => array(
	                        'type' => 'segment',
	                        'options' => array(
	                            'route' => '/users[/:action][/:id]',
	                            'defaults' => array(
	                                'controller' => 'ZfcUserCrud\Controller\Crud',
	                                'action'     => 'index',
	                            ),
	                        ),
	                    ),
	                ),
	            ),
	            'zfc-user-crud' => array(
	                'options' => array(
	                    //if you change your ZfcAdmin url(admin), you must use your new url
	                    'route' => '/admin/users[/:action][/:id]'
	                )
	            )
	        ),
	    )
	);
