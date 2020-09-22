<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\ServiceApi;

use AdScale\Handlers\Updater;
use AdScale\Helpers\Helper;

class Update extends ServiceApiBase
{
    
    protected static $allowedActions = [
        'modify',
        'create_api_credentials',
        'enable_api',
        'disable_api',
        'enable_cgi_mode_for_php',
        'disable_cgi_mode_for_php',
    ];
    
    
    
    public static function checkRequest()
    {
        parent::checkRequest();
        self::initParams();
        
        // Missing Action guard
        if (! self::checkIfRequestParamExists('action')) {
            throw new ServiceApiExeption('Required \'action\' argument is missing', '002');
        }
        
        $action = self::$requestParams['action'];
        
        // Action is not a string guard
        if (! is_string($action)) {
            throw new ServiceApiExeption('System error: \'action\' argument is not a string', '000');
        }
        
        // Unknown Action guard
        if (! in_array($action, self::$allowedActions, true)) {
            throw new ServiceApiExeption("Unknown action '{$action}'", '004');
        }
        
        if ('modify' === $action) {
            // Missing Property guard
            if (! self::checkIfRequestParamExists('property')) {
                throw new ServiceApiExeption('Required \'property\' argument is missing', '005');
            }
            
            $property = self::$requestParams['property'];
            
            // Empty Property guard
            if (! is_string($property) || ! $property) {
                throw new ServiceApiExeption('Required \'property\' argument is invalid', '005');
            }
            
            // Missing Value guard
            if (! self::checkIfRequestParamExists('value')) {
                throw new ServiceApiExeption('Required \'value\' argument is missing', '006');
            }
        }
    }
    
    
    
    public static function initParams()
    {
        self::$requestParams['action']   = Helper::getPostBodyDataValue('action');
        self::$requestParams['property'] = Helper::getPostBodyDataValue('property');
        self::$requestParams['value']    = Helper::getPostBodyDataValue('value');
    }
    
    
    
    /**
     * @throws ServiceApiExeption
     */
    public static function handleRequest()
    {
        Updater::actionProcessor(self::$requestParams);
    }
}
