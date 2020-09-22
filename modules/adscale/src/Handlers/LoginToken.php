<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\Handlers;

use AdScale\Helpers\Helper;
use AdScale\Helpers\Logger;
use Configuration;

class LoginToken
{
    
    public static function processLoginToken()
    {
        $token = Helper::generateRandHash();
        self::saveLoginToken($token);
        
        return $token;
    }
    
    
    
    
    public static function getLoginToken()
    {
        return Configuration::get('ADSCALE_LOGIN_TOKEN');
    }
    
    
    
    
    public static function saveLoginToken($token)
    {
        Configuration::updateValue('ADSCALE_LOGIN_TOKEN', $token);
    }
    
    
    
    
    public static function clearLoginToken()
    {
        self::saveLoginToken('');
    }
}
