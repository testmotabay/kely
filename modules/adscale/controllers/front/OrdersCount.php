<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

use AdScale\PluginApi\OrdersCount;
use AdScale\Helpers\Helper;

class AdscaleOrdersCountModuleFrontController extends ModuleFrontController
{
    
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        try {
            ob_start();
            
            // Error handler
            OrdersCount::setErrorHandler();
            
            parent::initContent();
            
            Helper::nocacheHeaders();
            
            // Check Request (Guards)
            OrdersCount::checkRequest();
            
            ob_end_clean();
            
            // Handle Request
            OrdersCount::handleRequest();
            
            // Default error
            Helper::sendResponseFormattedError(OrdersCount::makeErrorBody());
        } catch (\AdScale\PluginApi\PluginApiExeption $ex) {
            Helper::sendResponseFormattedError(OrdersCount::makeErrorBody($ex->getApiErrorCode(), $ex->getMessage()));
        } catch (\Exception $ex) {
            Helper::sendResponseFormattedError(OrdersCount::makeErrorBody($ex->getCode(), $ex->getMessage()));
        }
    }
}
