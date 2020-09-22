<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

use AdScale\Helpers\Helper;
use AdScale\ServiceApi\ServiceApiExeption;
use AdScale\ServiceApi\GetKeys as ServiceGetKeys;

class AdscaleGetKeysModuleFrontController extends ModuleFrontController
{
    
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        try {
            ob_start();
            
            parent::initContent();
            
            Helper::nocacheHeaders();
            
            // Check Request (Guards)
            ServiceGetKeys::checkRequest();
            
            ob_end_clean();
            
            // Handle Request
            ServiceGetKeys::handleRequest();
            
            // Default error
            Helper::sendResponseFormattedError(ServiceGetKeys::makeErrorBody());
        } catch (ServiceApiExeption $ex) {
            Helper::sendResponseFormattedError(
                ServiceGetKeys::makeErrorBody($ex->getApiErrorCode(), $ex->getMessage())
            );
        } catch (\Exception $ex) {
            Helper::sendResponseFormattedError(ServiceGetKeys::makeErrorBody($ex->getCode(), $ex->getMessage()));
        }
    }
}
