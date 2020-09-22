<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\Api;

use AdScale\Helpers\Helper;
use Configuration;

class ApiManager
{
    
    
    /**
     * Get google_site_verification from API and save it
     *
     * @return bool|string
     */
    public static function processApiGsvCode()
    {
        $code = self::getApiGsvCode();
        
        if ($code) {
            self::saveApiGsvCode($code);
            
            return $code;
        }
        
        return false;
    }
    
    
    
    
    public static function getApiGsvCode()
    {
        if (! $request_template = Helper::getConfigSetting('gsv_code_request_tpl', '')) {
            return false;
        }
        
        $request_url = str_replace('%SHOP_DOMAIN%', Helper::getShopDomain(), $request_template);
        
        $result = ApiProcess::process($request_url, 'gsv_code');
        
        $is_success = ! empty($result['data']['success']) && $result['data']['success'] === '1';
        $code       = ! empty($result['data']['tag']) ? $result['data']['tag'] : '';
        
        if ($is_success && $code) {
            return $code;
        }
        
        return false;
    }
    
    
    
    
    public static function saveApiGsvCode($code)
    {
        Configuration::updateValue('ADSCALE_GSV', $code);
    }
    
    
    
    
    public static function getSavedApiGsvCode()
    {
        return Configuration::get('ADSCALE_GSV');
    }
    
    
    
    
    public static function processHeartbeat()
    {
        if (! $request_template = Helper::getConfigSetting('heartbeat/request_tpl', '')) {
            return false;
        }
        
        $request_url = str_replace('%SHOP_DOMAIN%', Helper::getShopDomain(), $request_template);
        $result      = ApiProcess::process($request_url, 'processHeartbeat');
        $is_success  = ! empty($result['data']['success']) && $result['data']['success'] === '1';
        
        return $is_success;
    }
    
    
    
    
    public static function processModuleEvent($request_template, $request_event_name)
    {
        if (! $request_template || ! $request_event_name) {
            return false;
        }
        
        $request_url = str_replace(
            [
                '%SHOP_DOMAIN%',
                '%EVENT%',
                '%PLATFORM%',
                '%EMAIL%',
            ],
            [
                Helper::getShopDomain(),
                $request_event_name,
                Helper::getConfigSetting('shop/platform', 'presta'),
                Helper::getResolvedActivationEmail(),
            ],
            $request_template
        );
        
        $result     = ApiProcess::process($request_url, 'Events');
        $is_success = ! empty($result['success']) && $result['success'] === true;
        
        return $is_success;
    }
}
