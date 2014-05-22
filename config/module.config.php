<?php

return array(
    'zfcusercrud' => array(
        'userEntity' => 'ZfcUserCrud\Entity\User',
        'roleEntity' => 'ZfcUserCrud\Entity\Role'
    ),
    'doctrine' => array(
        'driver' => array(
            'application_entities' => array(
		'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/ZfcUserCrud/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    'ZfcUserCrud\Entity' => 'application_entities'
                )
            ))),
    'controllers' => array(
        'invokables' => array(
            'ZfcUserCrud\Controller\Crud' => 'ZfcUserCrud\Controller\CrudController',
            'ZfcUserCrud\Controller\Role' => 'ZfcUserCrud\Controller\RoleController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'zfc-user-crud' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/users[/:action][/:id]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'ZfcUserCrud\Controller',
                        'controller' => 'Crud',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
            ),
            'zfc-user-crud-role' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/roles[/:action][/:id]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'ZfcUserCrud\Controller',
                        'controller' => 'Role',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'ZfcUserCrud' => __DIR__ . '/../view',
        ),
    ),
);
