<?php
/**
 * Config Service
 * 
 * @category    ResponsiveImage
 * @package     Service
 * @author      Peter Hough <peterh@mnatwork.com>
 */

namespace ResponsiveImage\Service;

class ConfigService extends AbstractService
{
    /**
     * @var array 
     */
    protected $_config;
    
    /**
     * Get Config value by the given keys
     * Passed as unlimited arguments
     * 
     * @params string
     * @return mixed
     */
    public function getValue() {
        $values = $this->getConfig();

        $keys = func_get_args();
        foreach ($keys as $key) {
            if (isset($values[$key])) {
                $values = $values[$key];
            } else {
                $values = null;
            }
        }

        return $values;
    }
    
    /**
     * Get *absolute* public path
     * 
     * @return string
     */
    public function getPublicPath() {
        return __DIR__ . '/../../../../../../public';
    }
    
    /**
     * Get Config
     * 
     * @return array
     * @throws \Exception
     */
    private function getConfig() {
        if (!$this->_config) {
            $config = $this->getServiceManager()->get('config');
            if (!isset($config['config'])) {
                throw new \Exception('No config option found');
            }
            $this->_config = $config['config'];
        }
        
        return $this->_config;
    }

}
