<?php
/**
 * Recipe Service
 * 
 * @category    ResponsiveImage
 * @package     Service
 * @author      Peter Hough <peterh@mnatwork.com>
 */

namespace ResponsiveImage\Service;

use Wurfl\Configuration\InMemoryConfig as WurflConfig;
use Wurfl\Manager as WurflManager;

class RecipeService extends AbstractService
{

    /**
     * Instance of the WURFL Manager
     * 
     * @var \Wurfl\Manager 
     */
    protected $_wurflInstance;

    /**
     * The detected device
     * 
     * @var \Wurfl\CustomDevice 
     */
    protected $_device;

    /**
     * The recipe object
     * 
     * @var object
     */
    protected $_recipe;
    
    /**
     * @var \ResponsiveImage\Service\ConfigService 
     */
    protected $_configService;
    
    /**
     * @var \ResponsiveImage\Service\ConvertService 
     */
    protected $_convertService;

    /**
     * Call the configured manipulation methods and 
     * render the image
     * 
     * @param string $imageFilename
     * @return \Zend\Http\Response
     */
    public function render($imageFilename) {
        $this->getConvertService()->setFilename($imageFilename);

        $device = 'default';
        if ($this->isMobile()) {
            $device = 'mobile';
        } elseif ($this->isTablet()) {
            $device = 'tablet';
        } elseif ($this->isDesktop()) {
            $device = 'desktop';
        }

        $this->scale($device);
        $this->compress($device);
        $this->artDirection($device);
        
        return $this->getConvertService()->render();
    }

    /**
     * Compress the image based on the device config
     * 
     * @param string $device
     * @return void
     */
    private function compress($device = 'default') {
        if (!isset($this->recipe()->$device)) {
            return;
        }

        if (isset($this->recipe()->$device->quality)) {
            $this->getConvertService()->compress($this->recipe()->$device->quality);
        }
    }

    /**
     * Apply art direction based on the device config
     * 
     * @param string $device
     * @return void
     */
    private function artDirection($device = 'default') {
        if (
            !isset($this->recipe()->$device) ||
            !isset($this->recipe()->$device->artDirection)
        ) {
            return;
        }

        $x = (isset($this->recipe()->$device->artDirection->x) ?
                $this->recipe()->$device->artDirection->x : null);
        $y = (isset($this->recipe()->$device->artDirection->y) ?
                $this->recipe()->$device->artDirection->y : null);
        $width = (isset($this->recipe()->$device->artDirection->width) ?
                $this->recipe()->$device->artDirection->width : null);
        $height = (isset($this->recipe()->$device->artDirection->height) ?
                $this->recipe()->$device->artDirection->height : null);

        if (!is_null($x) && !is_null($y)) {
            $this->getConvertService()->artDirection($x, $y, $width, $height);
        }
    }

    /**
     * Scale the image based on the device config
     * 
     * @param string $device
     * @return void
     */
    private function scale($device = 'default') {
        if (!isset($this->recipe()->$device)) {
            return;
        }

        $width = (isset($this->recipe()->$device->width) ?
                $this->recipe()->$device->width : null);
        $height = (isset($this->recipe()->$device->height) ?
                $this->recipe()->$device->height : null);

        if (!is_null($width) && !is_null($height)) {
            // both width & height
            $this->getConvertService()->scale($width, $height);
            if (isset($this->recipe()->$device->ratio)) {
                $this->getConvertService()->aspectRatio($this->recipe()->$device->ratio);
            }
        } elseif (!is_null($width) || !is_null($height)) {
            // either with or height
            $this->getConvertService()->scale($width, $height);
        }
        
        if (isset($this->recipe()->$device->enlarging)) {
            $this->getConvertService()->allowEnlarging($this->recipe()->$device->enlarging);
        }
    }
    
    /**
     * Is the device a Tablet?
     * 
     * @return bool
     */
    public function isTablet() {
        return ($this->device()->getCapability('is_tablet') === 'true');
    }

    /**
     * Is the device a Mobile?
     * 
     * @return bool
     */
    public function isMobile() {
        return (
            $this->device()->getCapability('is_tablet') === 'false' &&
            $this->device()->getCapability('is_wireless_device') === 'true'
            );
    }

    /**
     * Is the device a Desktop?
     * 
     * @return bool
     */
    public function isDesktop() {
        return (
            $this->device()->getCapability('is_wireless_device') === 'false' &&
            $this->device()->getCapability('is_tablet') === 'false'
            );
    }
    
    /**
     * Get recipe
     * 
     * @return object
     * @throws \Exception
     */
    private function recipe() {
        if (!$this->_recipe) {
            throw new \Exception('No recipe set');
        }
        return $this->_recipe;
    }
    
    /**
     * Set the recipe
     * 
     * @param object $recipe
     */
    public function setRecipe($recipe) {
        $this->_recipe = $recipe;
    }
    
    /**
     * Does the recipe exist?
     * 
     * @param string $recipe
     * @return bool
     */
    public function recipeExists($recipe) {
        $recipeFile = $this->getConfigService()->getValue('recipe', 'dir');
        $recipeFile .= '/' . $recipe;
        $recipeFile .= $this->getConfigService()->getValue('recipe', 'ext');
        
        return is_file($recipeFile);
    }
    
    /**
     * Get the WURFL Device
     * 
     * @return \Wurfl\CustomDevice
     */
    private function device() {
        if (!$this->_device) {
            $request = $this->getServiceManager()->get('Request');
            if (!$request) {
                throw new \Exception('Unable to get the Request');
            }
            $this->_device = $this->wurfl()->getDeviceForHttpRequest($request->getServer());
        }
        return $this->_device;
    }

    /**
     * Get an instance of the WURFL manager
     * 
     * @return \ResponsiveImage\Wurfl\Manager;
     */
    private function wurfl() {
        if (!$this->_wurflInstance) {
            $this->_wurflInstance = new WurflManager($this->wurflConfig());
        }

        return $this->_wurflInstance;
    }

    /**
     * Create a WURFL config
     * 
     * @return \ResponsiveImage\Wurfl\Config
     */
    private function wurflConfig() {
        $wurflConfig = new WurflConfig();
        $wurflConfig->wurflFile($this->getConfigService()->getValue('wurfl', 'wurflFile'));
        $wurflConfig->matchMode($this->getConfigService()->getValue('wurfl', 'matchMode'));
        $wurflConfig->persistence(
            $this->getConfigService()->getValue('wurfl', 'persistence', 'provider'),
            $this->getConfigService()->getValue('wurfl', 'persistence', 'params')
        );
        $wurflConfig->cache(
            $this->getConfigService()->getValue('wurfl', 'cache', 'provider'),
            $this->getConfigService()->getValue('wurfl', 'cache', 'params')
        );
        
        return $wurflConfig;
    }
    
    /**
     * Get Config Service
     * 
     * @return \ResponsiveImage\Service\ConfigService
     */
    private function getConfigService() {
        if (!$this->_configService) {
            $this->_configService = $this->getServiceManager()->get('RI_ConfigService');
        }
        return $this->_configService;
    }
    
    /**
     * Get Convert Service
     * 
     * @return \ResponsiveImage\Service\ConvertService
     */
    private function getConvertService() {
        if (!$this->_convertService) {
            $this->_convertService = $this->getServiceManager()->get('RI_ConvertService');
        }
        
        return $this->_convertService;
    }

}
