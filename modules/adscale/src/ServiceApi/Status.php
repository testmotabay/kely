<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\ServiceApi;

use AdScale\Handlers\Status as StatusHandler;
use AdScale\Helpers\Helper;
use Tools;

class Status extends ServiceApiBase
{
    
    public static function handleRequest()
    {
        $statusData = StatusHandler::getStatusData();
        
        $is_format_json = Tools::strtoupper(Helper::getPostBodyDataValue('format')) === 'JSON';
        $is_format_json = $is_format_json
            ? $is_format_json
            : ! empty(Tools::getValue('format')) && Tools::strtoupper(Tools::getValue('format')) === 'JSON';
        
        if ($is_format_json) {
            $response = StatusHandler::renderDataToJson($statusData);
            Helper::sendResponse(Helper::formatResponse($response), 200, '', 'text/plain');
        } else {
            $response = StatusHandler::renderDataToHtml($statusData);
            Helper::sendResponse(Helper::formatResponse($response), 200, '', 'text/html');
        }
    }
}
