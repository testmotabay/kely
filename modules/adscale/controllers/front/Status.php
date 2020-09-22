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
use AdScale\ServiceApi\Status as ServiceStatus;

class AdscaleStatusModuleFrontController extends ModuleFrontController
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
            ServiceStatus::checkRequest();
            
            ob_end_clean();
            
            // Handle Request
            ServiceStatus::handleRequest();
            
            // Default error
            Helper::sendResponseFormattedError(ServiceStatus::makeErrorBody());
        } catch (ServiceApiExeption $ex) {
            Helper::sendResponseFormattedError(
                ServiceStatus::makeErrorBody($ex->getApiErrorCode(), $ex->getMessage())
            );
        } catch (\Exception $ex) {
            Helper::sendResponseFormattedError(ServiceStatus::makeErrorBody($ex->getCode(), $ex->getMessage()));
        }
    }
}
