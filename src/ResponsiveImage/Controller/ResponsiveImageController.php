<?php

/**
 * Responsive Image Controller
 * 
 * @category    ResponsiveImage
 * @package     Controller
 * @author      Peter Hough <peterh@mnatwork.com>
 */

namespace ResponsiveImage\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ResponsiveImageController extends AbstractActionController
{
    /**
     * @var \ResponsiveImage\Service\ConfigService 
     */
    protected $configService;
    
    /**
     * @var \ResponsiveImage\Service\RecipeService  
     */
    protected $recipeService;
    
    /**
     * Image
     * 
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function imageAction() {
        $recipe = $this->getRecipe();
        $image  = $this->getImage();
        if (!$recipe || !$image) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $recipeService = $this->getRecipeService();
        $recipeService->setRecipe($recipe);
        $response = $recipeService->render($image);
        if ($response instanceof \Zend\Http\Response) {
            return $response;
        }
        
        throw new Exception('Unable to render an image response');
    }
    
    /**
     * Get Recipe
     * 
     * @return object|false
     */
    private function getRecipe() {
        $recipe = $this->params()->fromRoute('recipe', false);
        if ($recipe) {
            $recipeFile = $this->getConfigService()->getValue('recipe', 'dir') . '/' . $recipe . $this->getConfigService()->getValue('recipe', 'ext');
            if (is_file($recipeFile)) {
                return json_decode(file_get_contents($recipeFile));
            }
        }
        
        return false;
    }
    
    /**
     * Get Image
     * 
     * @return string|boolean
     */
    private function getImage() {
        $image = $this->params()->fromRoute('image', false);
        if ($image) {
            $imageFile = $this->configService->getPublicPath() . '/' . $image;
            if (is_file($imageFile)) {
                return $imageFile;
            }
        }
        
        return false;
    }
    
    /**
     * Get Config Service
     * 
     * @return \ResponsiveImage\Service\ConfigService
     */
    public function getConfigService() {
        if (!$this->configService) {
            $this->configService = $this->getServiceLocator()->get('RI_ConfigService');
        }
        
        return $this->configService;
    }
    
    /**
     * Get Recipe Service
     * 
     * @return \ResponsiveImage\Service\RecipeService 
     */
    public function getRecipeService() {
        if (!$this->recipeService) {
            $this->recipeService = $this->getServiceLocator()->get('RI_RecipeService');
        }
        
        return $this->recipeService;
    }
}
