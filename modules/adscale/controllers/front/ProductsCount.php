<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

use AdScale\PluginApi\ProductsCount;
use AdScale\Helpers\Helper;

class AdscaleProductsCountModuleFrontController extends ModuleFrontController
{
    
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        try {
            ob_start();
            
            // Error handler
            ProductsCount::setErrorHandler();
            
            parent::initContent();
            
            Helper::nocacheHeaders();
            
            // Check Request (Guards)
            ProductsCount::checkRequest();
            
            ob_end_clean();
            
            // Handle Request
            ProductsCount::handleRequest();
            
            // Default error
            Helper::sendResponseFormattedError(ProductsCount::makeErrorBody());
        } catch (\AdScale\PluginApi\PluginApiExeption $ex) {
            Helper::sendResponseFormattedError(
                ProductsCount::makeErrorBody($ex->getApiErrorCode(), $ex->getMessage())
            );
        } catch (\Exception $ex) {
            Helper::sendResponseFormattedError(ProductsCount::makeErrorBody($ex->getCode(), $ex->getMessage()));
        }
    }
}
