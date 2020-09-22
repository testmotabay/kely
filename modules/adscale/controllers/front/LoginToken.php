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
use AdScale\ServiceApi\LoginToken as ServiceLoginToken;

class AdscaleLoginTokenModuleFrontController extends ModuleFrontController
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
            ServiceLoginToken::checkRequest();
            
            ob_end_clean();
            
            // Handle Request
            ServiceLoginToken::handleRequest();
            
            // Default error
            Helper::sendResponseFormattedError(ServiceLoginToken::makeErrorBody());
        } catch (ServiceApiExeption $ex) {
            Helper::sendResponseFormattedError(
                ServiceLoginToken::makeErrorBody($ex->getApiErrorCode(), $ex->getMessage())
            );
        } catch (\Exception $ex) {
            Helper::sendResponseFormattedError(ServiceLoginToken::makeErrorBody($ex->getCode(), $ex->getMessage()));
        }
    }
}
