<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\Api;

use AdScale\Exception\AdScaleExeption;
use AdScale\Helpers\Helper;
use AdScale\Helpers\Logger;

class ApiProcess
{
    
    
    public static $err_codes = [
        777100 => 'ERR__CURL_ERROR',
        777200 => 'ERR__RESPONSE_HTTP_ERROR',
    ];
    
    
    
    
    public static function process($request_url, $log_name = 'common')
    {
        try {
            // ------------------------------------------------------------------
            $request = new ApiRequest();
            
            $options = [
                CURLOPT_URL        => $request_url,
                CURLOPT_HTTPHEADER => [],
            ];
            
            $request->init($options);
            $res = $request->exec();
            
            
            // error cURL
            if ($res['curl_error']) {
                throw new AdScaleExeption(
                    $res['curl_error'],
                    self::getErrorCode('ERR__CURL_ERROR'),
                    null,
                    Helper::getModuleTrans('error cURL ', 'ApiProcess')
                );
            }
            
            // error
            if ($res['http_code'] !== 200) {
                throw new AdScaleExeption(
                    "HTTP Code = {$res['http_code']}",
                    self::getErrorCode('ERR__RESPONSE_HTTP_ERROR'),
                    null,
                    Helper::getModuleTrans('response http code error', 'ApiProcess')
                );
            }
            // ------------------------------------------------------------------
        } catch (AdScaleExeption $e) {
            Logger::log(
                "API: ERROR [request_url({$request_url})] [{$e->getCode()}|"
                . self::getErrorName($e->getCode()) . "] : {$e->getMessage()}",
                '',
                $log_name
            );
            
            return [
                'success'       => false,
                'message'       => "API: ERROR [{$e->getCode()}|" . self::getErrorName($e->getCode())
                                   . "] : {$e->getMessage()}",
                'message_front' => $e->getMessageFront(),
                'error_code'    => $e->getCode(),
                'data'          => [],
            ];
        }
        
        // http_code === 200 OK
        
        Logger::log("API: SUCCESS [request_url({$request_url})]", '', $log_name);
        
        return [
            'success'       => true,
            'message'       => '',
            'message_front' => '',
            'error_code'    => 0,
            'data'          => $res['body'],
        ];
    }
    
    
    
    // ==========================================================================
    
    
    
    public static function getErrorCode($error_name)
    {
        return array_search($error_name, self::$err_codes, true);
    }
    
    
    
    public static function getErrorName($error_code)
    {
        return ! empty(self::$err_codes[$error_code]) ? self::$err_codes[$error_code] : false;
    }
}
