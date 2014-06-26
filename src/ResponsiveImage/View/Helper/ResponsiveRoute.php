<?php

/**
 * Responsive Route View Helper
 * 
 * @category    ResponsiveImage
 * @package     View
 * @subpackage  Helper
 * @author      Peter Hough <peterh@mnatwork.com>
 */

namespace ResponsiveImage\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ResponsiveRoute extends AbstractHelper implements ServiceLocatorAwareInterface
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface  
     */
    protected $serviceLocator;
    
    /**
     * Invoke the View Helper
     * 
     * @param string $recipe
     * @param string $image
     * @return string
     */
    public function __invoke($recipe, $image) {
        $image = ltrim($image, '/');
        $params = array('recipe' => $recipe, 'image' => $image);
            
        $router = $this->getServiceLocator()->get('Router');
        $url = $router->assemble($params, array('name' => 'responsiveimage'));
        return str_replace(rawurlencode($params['image']), $image, $url);
    }
    
    /** 
     * Get the service locator. 
     * 
     * @return \Zend\ServiceManager\ServiceLocatorInterface 
     */  
    public function getServiceLocator()  
    {  
        if (get_class($this->serviceLocator) !== 'Zend\ServiceManager\ServiceManager') {
            return $this->serviceLocator->getServiceLocator();
        }
        return $this->serviceLocator;  
    }
    
    /** 
     * Set the service locator. 
     * 
     * @param ServiceLocatorInterface $serviceLocator 
     * @return CustomHelper 
     */  
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)  
    {
        $this->serviceLocator = $serviceLocator;  
        return $this;  
    }
}
