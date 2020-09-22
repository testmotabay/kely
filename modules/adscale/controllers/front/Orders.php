<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

use AdScale\PluginApi\Orders;
use AdScale\Helpers\Helper;

class AdscaleOrdersModuleFrontController extends ModuleFrontController
{
    
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        try {
            ob_start();
            
            // Error handler
            Orders::setErrorHandler();
            
            parent::initContent();
            
            Helper::nocacheHeaders();
            
            // Check Request (Guards)
            Orders::checkRequest();
            
            ob_end_clean();
            
            // Handle Request
            Orders::handleRequest();
            
            // Default error
            Helper::sendResponseFormattedError(Orders::makeErrorBody());
        } catch (\AdScale\PluginApi\PluginApiExeption $ex) {
            Helper::sendResponseFormattedError(Orders::makeErrorBody($ex->getApiErrorCode(), $ex->getMessage()));
        } catch (\Exception $ex) {
            Helper::sendResponseFormattedError(Orders::makeErrorBody($ex->getCode(), $ex->getMessage()));
        }
    }
}
