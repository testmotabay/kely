<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

use AdScale\PluginApi\Categories;
use AdScale\Helpers\Helper;

class AdscaleCategoriesModuleFrontController extends ModuleFrontController
{
    
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        try {
            ob_start();
            
            // Error handler
            Categories::setErrorHandler();
            
            parent::initContent();
            
            Helper::nocacheHeaders();
            
            // Check Request (Guards)
            Categories::checkRequest();
            
            ob_end_clean();
            
            // Handle Request
            Categories::handleRequest();
            
            // Default error
            Helper::sendResponseFormattedError(Categories::makeErrorBody());
        } catch (\AdScale\PluginApi\PluginApiExeption $ex) {
            Helper::sendResponseFormattedError(Categories::makeErrorBody($ex->getApiErrorCode(), $ex->getMessage()));
        } catch (\Exception $ex) {
            Helper::sendResponseFormattedError(Categories::makeErrorBody($ex->getCode(), $ex->getMessage()));
        }
    }
}
