<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\PluginApi;

use AdScale\Helpers\Helper;
use AdScale\Helpers\Logger;

abstract class PluginApiBase
{
    
    protected static $requestParams = [];
    
    protected static $defaultLimit = 100;
    
    protected static $maxLimit = 1000;
    
    
    
    public static function handleRequest()
    {
        Helper::sendResponseFormattedSuccess(null);
    }
    
    
    
    /**
     * The set of checks
     *
     * @throws PluginApiExeption
     */
    public static function checkRequest()
    {
        // Wrong HTTP Method guard
        if (! self::checkHTTPMethod()) {
            throw new PluginApiExeption('System error: HTTP Method not supported', '000');
        }
        
        // Missing Key guard
        if (! self::checkKeyExists()) {
            throw new PluginApiExeption('Required \'key\' argument is missing', '001');
        }
        
        // Key not valid guard
        if (! self::checkKeyValid()) {
            throw new PluginApiExeption('Key validation failed', '002');
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
        Logger::log("code: {$code}, message: {$message}", 'Error > ', 'PluginApi_' . self::getCalledClassShortName());
        
        return [  // json format : {error: {code: "%code%, message: '%message%'}}
            'error' => [
                'code'    => (string)$code,
                'message' => $message,
            ],
        ];
    }
    
    
    
    
    public static function setErrorHandler()
    {
        ini_set('html_errors', 'off');
        set_error_handler([static::class, 'errorHandler']);
        register_shutdown_function([static::class, 'errorShutdownHandler']);
    }
    
    
    
    
    /**
     * Used to replace the default PHP error handler.
     *
     * @param int $errno contains the level of the error
     * @param string $errstr contains the error message
     * @param string $errfile errfile, which contains the filename that the error was raised in
     * @param int $errline errline, which contains the line number the error was raised at
     *
     * @return bool Always return true to avoid the default PHP error handler
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (error_reporting() === 0) { // @
            return true;
        }
        
        $errorType = [
            E_ERROR             => 'Error', // handle at shutdown
            E_WARNING           => 'Warning',
            E_PARSE             => 'Parse', // handle at shutdown
            E_NOTICE            => 'Notice',
            E_CORE_ERROR        => 'Core Error', // handle at shutdown
            E_CORE_WARNING      => 'Core Warning', // handle at shutdown
            E_COMPILE_ERROR     => 'Compile Error', // handle at shutdown
            E_COMPILE_WARNING   => 'Compile Warning', // handle at shutdown
            E_USER_ERROR        => 'Error',
            E_USER_WARNING      => 'User warning',
            E_USER_NOTICE       => 'User notice',
            E_STRICT            => 'Runtime Notice', // handle at shutdown
            E_RECOVERABLE_ERROR => 'Recoverable error',
            E_DEPRECATED        => 'Deprecated notice',
            E_USER_DEPRECATED   => 'Deprecated notice',
        ];
        
        $type = isset($errorType[$errno]) ? $errorType[$errno] : 'Unknown error';
        
        $errorMessage = "[PHP $type level#{$errno}] $errstr ($errfile, line $errline)";
        
        switch ($errno) {
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
                Logger::log($errorMessage, 'PHP Notice > ', 'PluginApi_' . self::getCalledClassShortName());
                break;
            case E_WARNING:
            case E_USER_WARNING:
            case E_RECOVERABLE_ERROR:
                Logger::log($errorMessage, 'PHP Warning > ', 'PluginApi_' . self::getCalledClassShortName());
                break;
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_ERROR:
            case E_COMPILE_WARNING:
            case E_USER_ERROR:
            default:
                Logger::log($errorMessage, 'PHP Error > ', 'PluginApi_' . self::getCalledClassShortName());
                Helper::sendResponseFormattedError(static::makeErrorBody('000', 'System error: ' . $errorMessage));
        }
        
        return true;
    }
    
    
    
    
    public static function errorShutdownHandler()
    {
        $error_types_to_handle = [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_CORE_WARNING,
            E_COMPILE_ERROR,
            E_COMPILE_WARNING,
            E_USER_ERROR,
            E_STRICT,
        ];
        
        $last_err = error_get_last();
        
        if (isset($last_err['type']) && in_array($last_err['type'], $error_types_to_handle, true)) {
            static::errorHandler($last_err['type'], $last_err['message'], $last_err['file'], $last_err['line']);
        }
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
    
    
    
    
    /**
     * @param array $array_to_process
     *
     * @return array
     */
    public static function createArrayOfIds($array_to_process)
    {
        return is_array($array_to_process)
            ? array_map(
                function ($v) {
                    return $v['id'];
                },
                $array_to_process
            )
            : [];
    }
    
    
    
    public static function getArrayValue($array, $key)
    {
        return isset($array[$key]) ? $array[$key] : null;
    }
}
