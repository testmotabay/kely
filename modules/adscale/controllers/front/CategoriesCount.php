<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

use AdScale\PluginApi\CategoriesCount;
use AdScale\Helpers\Helper;

class AdscaleCategoriesCountModuleFrontController extends ModuleFrontController
{
    
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        try {
            ob_start();
            
            // Error handler
            CategoriesCount::setErrorHandler();
            
            parent::initContent();
            
            Helper::nocacheHeaders();
            
            // Check Request (Guards)
            CategoriesCount::checkRequest();
            
            ob_end_clean();
            
            // Handle Request
            CategoriesCount::handleRequest();
            
            // Default error
            Helper::sendResponseFormattedError(CategoriesCount::makeErrorBody());
        } catch (\AdScale\PluginApi\PluginApiExeption $ex) {
            Helper::sendResponseFormattedError(
                CategoriesCount::makeErrorBody($ex->getApiErrorCode(), $ex->getMessage())
            );
        } catch (\Exception $ex) {
            Helper::sendResponseFormattedError(CategoriesCount::makeErrorBody($ex->getCode(), $ex->getMessage()));
        }
    }
}
