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

abstract class ServiceApiBase
{
    
    protected static $requestParams = [];
    
    
    
    public static function handleRequest()
    {
        Helper::sendResponseFormattedSuccess(null);
    }
    
    
    
    /**
     * The set of checks
     *
     * @throws ServiceApiExeption
     */
    public static function checkRequest()
    {
        // Wrong HTTP Method guard
        if (! self::checkHTTPMethod()) {
            throw new ServiceApiExeption('System error: HTTP Method not supported', '000');
        }
        
        // Missing Key guard
        if (! self::checkKeyExists()) {
            throw new ServiceApiExeption('Required \'key\' argument is missing', '001');
        }
        
        // Key not valid guard
        if (! self::checkKeyValid()) {
            throw new ServiceApiExeption('Key validation failed', '003');
        }
    }
    
    
    
    
    /**
     * @param $paramName
     *
     * @return bool is exist
     */
    public static function checkIfRequestParamExists($paramName)
    {
        return null !== self::$requestParams[$paramName];
    }
    
    
    
    
    /**
     * Return true if Request HTTP method as needed, else - false
     *
     * @return bool
     */
    public static function checkHTTPMethod()
    {
        return 'POST' === $_SERVER['REQUEST_METHOD'];
    }
    
    
    
    
    /**
     * Return true if Key Exists, else - false
     *
     * @return bool
     */
    public static function checkKeyExists()
    {
        return null !== Helper::getPostBodyDataValue('key');
    }
    
    
    
    
    /**
     * Return true if Key Valid, else - false
     *
     * @return bool
     */
    public static function checkKeyValid()
    {
        return ! empty($key = Helper::getAccessKey()) && Helper::validateSecKey($key);
    }
    
    
    
    
    /**
     * @param string|int $code
     * @param string $message
     *
     * @return array
     */
    public static function makeErrorBody($code = '000', $message = 'System error')
    {
        $code = (! is_int($code) && ! is_string($code)) ? '000' : $code;
        Logger::log("code: {$code}, message: {$message}", 'Error > ', 'ServiceApi_' . self::getCalledClassShortName());
        
        return [  // json format : {error: {code: "%code%, message: '%message%'}}
            'error' => [
                'code'    => (string)$code,
                'message' => $message,
            ],
        ];
    }
    
    
    
    
    /**
     * @return string
     */
    public static function getCalledClassShortName()
    {
        try {
            $reflectionClass = new \ReflectionClass(get_called_class());
            
            return $reflectionClass->getShortName();
        } catch (\Exception $ex) {
            return '';
        }
    }
}
