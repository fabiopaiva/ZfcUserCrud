<?php

/**
 * A CRUD Interface for ZfcUser
 * @author      Fábio Paiva <paiva.fabiofelipe@gmail.com>
 * @link        ZfcUserCrud
 * @license     http://opensource.org/licenses/MIT
 */

namespace ZfcUserCrud;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface {

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/', __NAMESPACE__),
                ),
            ),
        );
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig() {
        return array(
            'factories' => array(
                'zfcusercrud_options' => function ($sm) {
            $config = $sm->get('Config');
            return $config['zfcusercrud'];
        }
            )
        );
    }

}
