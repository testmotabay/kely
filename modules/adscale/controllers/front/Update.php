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
use AdScale\ServiceApi\Update as ServiceUpdate;

class AdscaleUpdateModuleFrontController extends ModuleFrontController
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
            ServiceUpdate::checkRequest();
            
            ob_end_clean();
            
            // Handle Request
            ServiceUpdate::handleRequest();
            
            // Default error
            Helper::sendResponseFormattedError(ServiceUpdate::makeErrorBody());
        } catch (ServiceApiExeption $ex) {
            Helper::sendResponseFormattedError(
                ServiceUpdate::makeErrorBody($ex->getApiErrorCode(), $ex->getMessage())
            );
        } catch (\Exception $ex) {
            Helper::sendResponseFormattedError(ServiceUpdate::makeErrorBody($ex->getCode(), $ex->getMessage()));
        }
    }
}
