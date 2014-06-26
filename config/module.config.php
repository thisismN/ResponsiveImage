<?php
/**
 * Module Configuration
 * 
 * @author Peter Hough <peterh@mnatwork.com>
 */
return array(
    'router' => array(
        'routes' => array(
            'responsiveimage' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/responsiveimage/:recipe/:image',
                    'defaults' => array(
                        'controller' => 'RI_Controller',
                        'action'     => 'image',
                    ),
                    'constraints' => array(
                        'image' => '(.)+'
                    )
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'RI_Controller'     => 'ResponsiveImage\Controller\ResponsiveImageController',
        ),
    ),
    'service_manager' => array(
        'invokables' => array(
            'RI_ConvertService' => 'ResponsiveImage\Service\ConvertService',
            'RI_RecipeService'  => 'ResponsiveImage\Service\RecipeService',
            'RI_ConfigService'  => 'ResponsiveImage\Service\ConfigService',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'responsiveRoute' => 'ResponsiveImage\View\Helper\ResponsiveRoute',
        ),
    ),
    'config' => array(
        'wurfl' => array(
            'wurflFile'   => __DIR__ . '/../data/resource/wurfl.xml',
            'matchMode'   => 'performance',
            'persistence' => array(
                'provider' => 'file',
                'params'   => array('dir' => __DIR__ . '/../data/persistence'),
            ),
            'cache'       => array(
                'provider' => 'file',
                'params'   => array(
                    'dir'        => __DIR__ . '/../data/cache',
                    'expiration' => 36000,
                )
            )
        ),
        'recipe' => array(
            'dir' => __DIR__ . '/../recipes',
            'ext' => '.json',
        ),
        'phpThumb' => array(
            'config' => array(
                // see vendor/JamesHeinrich/phpThumb/phpThumb.config.php.default for explainations
                'cache_directory'         => __DIR__ . '/../data/cache', 
                'allow_src_above_docroot' => true,
                'output_format'           => NULL,
            ),
            'expires' => '+ 1 day',
        ),
    ),
);