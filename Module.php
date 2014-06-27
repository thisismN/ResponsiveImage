<?php
/**
 * Module
 *
 * @category ResponsiveImage
 * @author Peter Hough <peterh@mnatwork.com>
 */

namespace ResponsiveImage;

class Module
{
    /**
     * Get Config
     *
     * @return array
     */
    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Get Autoloader Config
     *
     * @return array
     */
    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

}
