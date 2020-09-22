<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\ServiceApi;

use AdScale\Helpers\Helper;
use AdScale\Helpers\Logger;
use AdScale\Handlers\LoginToken as LoginTokenHandler;

class LoginToken extends ServiceApiBase
{
    
    public static function handleRequest()
    {
        $loginToken = LoginTokenHandler::getLoginToken();
        //$isEmptyLT  = empty($loginToken) ? 'empty' : 'not empty';
        //Logger::log("process loginToken: OK [$isEmptyLT]", 'Success > ', 'process_loginToken');
        LoginTokenHandler::clearLoginToken();  // clear after getting
        // body == {"token":"%TOKEN%"}
        Helper::sendResponseFormattedSuccess(['token' => $loginToken]);
    }
}
