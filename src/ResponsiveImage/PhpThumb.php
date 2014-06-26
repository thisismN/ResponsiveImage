<?php
/**
 * PhpThumb
 * 
 * @category    ResponsiveImage
 * @author      Peter Hough <peterh@mnatwork.com>
 */

namespace ResponsiveImage;

use \phpthumb as OriginalPhpThumb;
use \phpthumb_functions;
use Zend\Http\Response;

class PhpThumb extends OriginalPhpThumb
{

    /**
     * Get the Width of the Source file
     * 
     * @return int
     */
    public function getSourceWidth() {
        if (is_null($this->source_width)) {
            $getimagesize = $this->getSourceImageSize();
            if ($getimagesize) {
                $this->source_width = $getimagesize[0];
                $this->source_height = $getimagesize[1];
            }
        }
        return $this->source_width;
    }

    /**
     * Get the Height of the Source file
     * 
     * @return int
     */
    public function getSourceHeight() {
        if (is_null($this->source_height)) {
            $getimagesize = $this->getSourceImageSize();
            if ($getimagesize) {
                $this->source_width = $getimagesize[0];
                $this->source_height = $getimagesize[1];
            }
        }
        return $this->source_height;
    }

    /**
     * Get the size of the Source image
     * 
     * @see getimagesize
     * @return array
     */
    private function getSourceImageSize() {
        ob_start();
        $getimagesize = getimagesize($this->sourceFilename);
        $getImageSizeError = ob_get_contents();
        ob_end_clean();

        if (is_array($getimagesize)) {
            return $getimagesize;
        } else {
            $message = 'GetImageSize(' . $this->sourceFilename .
                ') FAILED with error "' . $getImageSizeError . '"';
            $this->DebugMessage(
                $message, __FILE__, __LINE__
            );
        }

        return false;
    }
    
    /**
     * Output the Thumbnail
     * 
     * @return \Zend\Http\Response
     * @throws Exception
     */
    public function OutputThumbnail() {
        $this->purgeTempFiles();

        if (!$this->useRawIMoutput && !is_resource($this->gdimg_output)) {
            throw new \Exception('OutputThumbnail() failed because !is_resource($this->gdimg_output)');
        }
        
        if (headers_sent()) {
            throw new \Exeception('OutputThumbnail() failed - headers already sent');
        }

        $downloadfilename = phpthumb_functions::SanitizeFilename(is_string($this->sia) ? $this->sia : ($this->down ? $this->down : 'phpThumb_generated_thumbnail' . '.' . $this->thumbnailFormat));
        
        $response = new Response();
        
        if ($downloadfilename) {
            $response->getHeaders()->addHeaderLine(
                'Content-Disposition', 
                ($this->down ? 'attachment' : 'inline') . '; filename="' . $downloadfilename . '"'
            );
        }

        if ($this->useRawIMoutput) {
            $response->getHeaders()->addHeaderLine(
                'Content-Type',
                phpthumb_functions::ImageTypeToMIMEtype($this->thumbnailFormat)
            );
            $response->setContent($this->IMresizedData);
        } else {
            
            $response->getHeaders()->addHeaderLine(
                'Content-Type',
                phpthumb_functions::ImageTypeToMIMEtype($this->thumbnailFormat)
            );
            
            ImageInterlace(
                $this->gdimg_output,
                intval($this->config_output_interlace)
            );
            
            switch ($this->thumbnailFormat) {
                case 'jpeg':
                    $ImageOutFunction = 'image' . $this->thumbnailFormat;
                    ob_start();
                    @$ImageOutFunction(
                        $this->gdimg_output, 
                        null,
                        $this->thumbnailQuality
                    );
                    $content = ob_get_clean();
                    $response->setContent($content);
                    
                    break;

                case 'png':
                case 'gif':
                    $ImageOutFunction = 'image' . $this->thumbnailFormat;
                    ob_start();
                    @$ImageOutFunction($this->gdimg_output);
                    $content = ob_get_clean();
                    $response->setContent($content);
                    
                    break;

                case 'bmp':
                    if (!@include_once(dirname(__FILE__) . '/phpthumb.bmp.php')) {
                        throw new \Exception('Error including "' . dirname(__FILE__) . '/phpthumb.bmp.php" which is required for BMP format output');
                    }
                    $phpthumb_bmp = new phpthumb_bmp();
                    if (is_object($phpthumb_bmp)) {
                        $bmp_data = $phpthumb_bmp->GD2BMPstring($this->gdimg_output);
                        unset($phpthumb_bmp);
                        if (!$bmp_data) {
                            throw new \Exception('$phpthumb_bmp->GD2BMPstring() failed');
                        }
                        $response->setContent($bmp_data);
                    } else {
                        throw new \Exception('new phpthumb_bmp() failed');
                    }
                    break;

                case 'ico':
                    if (!@include_once(dirname(__FILE__) . '/phpthumb.ico.php')) {
                        throw new \Exception('Error including "' . dirname(__FILE__) . '/phpthumb.ico.php" which is required for ICO format output');
                    }
                    $phpthumb_ico = new phpthumb_ico();
                    if (is_object($phpthumb_ico)) {
                        $arrayOfOutputImages = array($this->gdimg_output);
                        $ico_data = $phpthumb_ico->GD2ICOstring($arrayOfOutputImages);
                        unset($phpthumb_ico);
                        if (!$ico_data) {
                            throw new \Exception('$phpthumb_ico->GD2ICOstring() failed');
                        }
                        $response->setContent($ico_data);
                    } else {
                        throw new \Exception('new phpthumb_ico() failed');
                    }
                    break;

                default:
                    throw new \Exception('OutputThumbnail failed because $this->thumbnailFormat "' . $this->thumbnailFormat . '" is not valid');
            }
        }
        return $response;
    }
    
    /**
     * 
     * @param string $message
     * @param string $file
     * @param string $line
     * @return bool
     */
    function DebugMessage($message, $file='', $line='') {
        if (class_exists('\mnCMS\Service\LogService')) {
            \mnCMS\Service\LogService::debug($message . ' file:' . $file . ' line:' . $line);
        }
            
        return parent::DebugMessage($message, $file, $line);
    }

}
