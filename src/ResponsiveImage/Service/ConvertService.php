<?php
/**
 * Convert Service
 * 
 * @category    ResponsiveImage
 * @package     Service
 * @author      Peter Hough <peterh@mnatwork.com>
 */

namespace ResponsiveImage\Service;

use ResponsiveImage\PhpThumb;
use \phpthumb_functions;
use Zend\Http\Response;

class ConvertService extends AbstractService
{

    /**
     * Instance of the phpThumb class
     * 
     * @var PhpThumb 
     */
    protected $_phpThumbInstance;
    
    /**
     * @var \ResponsiveImage\Service\ConfigService 
     */
    protected $_configService;

    /**
     * Set the filename
     * 
     * @param string $filename
     */
    public function setFilename($filename) {
        $this->phpThumb()->setSourceFilename($filename);
    }

    /**
     * Sets the scale
     * 
     * @param int $width
     * @param int $height
     */
    public function scale($width = null, $height = null) {
        $this->phpThumb()->setParameter('w', $width);
        $this->phpThumb()->setParameter('h', $height);
    }

    /**
     * Sets the quality, 1 worse - 95 best
     * 
     * @param int $quality
     */
    public function compress($quality = 75) {
        $this->phpThumb()->setParameter('q', $quality);
    }
    
    /**
     * Set whether to allow output enlarging
     * If true the image may loose quality
     * 
     * @param bool $allow
     */
    public function allowEnlarging($allow = false) {
        $this->phpThumb()->setParameter('aoe', ($allow ? 1 : 0));
    }
    
    /**
     * Set whether to use aspect ratio
     * If false the image will be zoom cropped
     * 
     * @param bool $use
     */
    public function aspectRatio($use = true) {
        //$this->phpThumb()->setParameter('iar', ($use ? 0 : 1));
        if (!$use) {
            $this->phpThumb()->setParameter('zc', 'C');
        }
    }

    /**
     * Perform scale based on a centre point defined by $x and $y params
     * A width ($w) and height ($h) can be specified
     * - $x and $y are calculated after resize to this width & height
     * 
     * @param int $x
     * @param int $y
     * @param int $w
     * @param int $h
     */
    public function artDirection($x, $y, $w = null, $h = null) {
        $width = $this->phpThumb()->getParameter('w');
        $height = $this->phpThumb()->getParameter('h');
        if (is_null($width) && is_null($height)) {
            // required to be set
            return;
        }

        // Alert width & height scaling
        $this->phpThumb()->setParameter('w', $w);
        $this->phpThumb()->setParameter('h', $h);

        $sourceWidth = (is_null($w) ?
                $this->phpThumb()->getSourceWidth() :
                $w
            );
        $sourceHeight = (is_null($h) ?
                $this->phpThumb()->getSourceHeight() :
                $h
            );

        $l = $x - ($width / 2);
        $r = ($sourceWidth - $x) - ($width / 2);
        $t = $y - ($height / 2);
        $b = ($sourceHeight - $y) - ($height / 2);

        $this->phpThumb()->setParameter('fltr', "crop|$l|$r|$t|$b");
    }

    /**
     * Cache & render the image
     * 
     * @return \Zend\Http\Response
     * @throws Exception
     */
    public function render() {
        $this->phpThumb()->SetCacheFilename();
        if (@is_readable($this->phpThumb()->cache_filename)) {
            return $this->renderCached();
        } else {
            if ($this->phpThumb()->GenerateThumbnail()) {
                $this->saveCache();
                return $this->phpThumb()->OutputThumbnail();
            }
        }
        throw new \Exception('No cache and unable to generate image');
    }

    /**
     * Saves an image in the cache
     * 
     * @return bool
     */
    private function saveCache() {
        $cacheFilename = $this->phpThumb()->cache_filename;
        phpthumb_functions::EnsureDirectoryExists(dirname($cacheFilename));
        if (
            is_writable(dirname($cacheFilename)) ||
            (
            file_exists($cacheFilename) && is_writable($cacheFilename)
            )
        ) {
            $this->phpThumb()->CleanUpCacheDirectory();
            if (
                $this->phpThumb()->RenderToFile($cacheFilename) &&
                is_readable($cacheFilename)
            ) {
                chmod($cacheFilename, 0644);
                return true;
            }
        }

        return false;
    }

    /**
     * Renders a cached image
     * 
     * @return \Zend\Http\Response
     * @throws Exception
     */
    private function renderCached() {
        if (headers_sent()) {
            throw new \Exception('Headers already sent');
        }

        $dateFormat = 'D, d M Y H:i:s T';
        $cacheFilename = $this->phpThumb()->cache_filename;
        $modified = filemtime($cacheFilename);
        $eTag = md5($cacheFilename);
        $expires = gmdate(
            $dateFormat, strtotime($this->getConfigService()->getValue('phpThumb', 'expires'))
        );
        
        $response = new Response();
        
        $response->getHeaders()->addHeaders(array(
            'Cache-Control' => 'private',
            'Expires'       => $expires,
            'Etag'          => '"' . $eTag . '"',
        ));

        $ifModifiedSince = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ?
            $_SERVER['HTTP_IF_MODIFIED_SINCE'] :
            false;
        $ifNoneMatch = isset($_SERVER['HTTP_IF_NONE_MATCH']) ?
            $_SERVER['HTTP_IF_NONE_MATCH'] :
            false;

        if (
            ($ifModifiedSince && $modified == strtotime($ifModifiedSince)) ||
            ($ifNoneMatch && $eTag == trim($ifNoneMatch, '"'))
        ) {
            $response->setStatusCode(304);
            return $response;
        }
        
        $response->getHeaders()->addHeaderLine('Last-Modified', gmdate($dateFormat, $modified));

        if ($getimagesize = @getimagesize($cacheFilename)) {
            $mime = phpthumb_functions::ImageTypeToMIMEtype($getimagesize[2]);
            $response->getHeaders()->addHeaderLine('Content-Type', $mime);
        } elseif (preg_match('#\\.ico$#i', $cacheFilename)) {
            $response->getHeaders()->addHeaderLine('Content-Type', 'image/x-icon');
        }
        
        $response->getHeaders()->addHeaderLine('Content-Length', filesize($cacheFilename));
        
        ob_start();
        @readfile($cacheFilename);
        $content = ob_get_clean();
        $response->setContent($content);
        
        return $response;
    }

    /**
     * Get an instance of the phpThumb class
     * 
     * @return PhpThumb
     */
    private function phpThumb() {
        if (!$this->_phpThumbInstance) {
            $this->_phpThumbInstance = new PhpThumb();

            $config = $this->getConfigService()->getValue('phpThumb', 'config');
            if (!empty($config)) {
                foreach ($config as $key => $value) {
                    $this->_phpThumbInstance->setParameter(
                        'config_' . $key, $value
                    );
                }
            }
        }

        return $this->_phpThumbInstance;
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

}
