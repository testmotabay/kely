<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

use AdScale\PluginApi\ShopInfo;
use AdScale\Helpers\Helper;

class AdscaleShopInfoModuleFrontController extends ModuleFrontController
{
    
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        try {
            ob_start();
            
            // Error handler
            ShopInfo::setErrorHandler();
            
            parent::initContent();
            
            Helper::nocacheHeaders();
            
            // Check Request (Guards)
            ShopInfo::checkRequest();
            
            ob_end_clean();
            
            // Handle Request
            ShopInfo::handleRequest();
            
            // Default error
            Helper::sendResponseFormattedError(ShopInfo::makeErrorBody());
        } catch (\AdScale\PluginApi\PluginApiExeption $ex) {
            Helper::sendResponseFormattedError(ShopInfo::makeErrorBody($ex->getApiErrorCode(), $ex->getMessage()));
        } catch (\Exception $ex) {
            Helper::sendResponseFormattedError(ShopInfo::makeErrorBody($ex->getCode(), $ex->getMessage()));
        }
    }
}
