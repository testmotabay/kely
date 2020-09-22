<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

use AdScale\PluginApi\Shipping;
use AdScale\Helpers\Helper;

class AdscaleShippingModuleFrontController extends ModuleFrontController
{
    
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        try {
            ob_start();
            
            // Error handler
            Shipping::setErrorHandler();
            
            parent::initContent();
            
            Helper::nocacheHeaders();
            
            // Check Request (Guards)
            Shipping::checkRequest();
            
            ob_end_clean();
            
            // Handle Request
            Shipping::handleRequest();
            
            // Default error
            Helper::sendResponseFormattedError(Shipping::makeErrorBody());
        } catch (\AdScale\PluginApi\PluginApiExeption $ex) {
            Helper::sendResponseFormattedError(Shipping::makeErrorBody($ex->getApiErrorCode(), $ex->getMessage()));
        } catch (\Exception $ex) {
            Helper::sendResponseFormattedError(Shipping::makeErrorBody($ex->getCode(), $ex->getMessage()));
        }
    }
}
