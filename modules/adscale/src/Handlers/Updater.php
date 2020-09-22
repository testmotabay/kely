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
use AdScale\ServiceApi\ServiceApiExeption;
use Configuration;

class Updater
{
    
    /**
     * @param array $requestParams
     *
     * @throws ServiceApiExeption
     */
    public static function actionProcessor($requestParams = [])
    {
        $action   = Helper::getArrayValue($requestParams, 'action');
        $property = Helper::getArrayValue($requestParams, 'property');
        $value    = Helper::getArrayValue($requestParams, 'value');
        
        if ('modify' === $action && is_string($property) && $property) {
            self::modifyProperty($property, $value);
        }
        
        if ('create_api_credentials' === $action) {
            self::createApiCredentials();
        }
        
        if ('enable_api' === $action) {
            self::enableApi();
        }
        
        if ('disable_api' === $action) {
            self::disableApi();
        }
        
        if ('enable_cgi_mode_for_php' === $action) {
            self::enableCgiModeForPhp();
        }
        
        if ('disable_cgi_mode_for_php' === $action) {
            self::disableCgiModeForPhp();
        }
    }
    
    
    
    
    public static function enableCgiModeForPhp()
    {
        Configuration::updateValue('PS_WEBSERVICE_CGI_HOST', 1);
        $savedValue  = Configuration::get('PS_WEBSERVICE_CGI_HOST');
        $saveSuccess = ('1' === (string)$savedValue);
        
        if ($saveSuccess) {
            Logger::log('action: enable_cgi_mode_for_php', 'Success > ', 'process_Update');
            // body == { success: true }
            Helper::sendResponseFormattedSuccess(['success' => true]);
        } else {
            Logger::log('action: enable_cgi_mode_for_php', 'Fail > ', 'process_Update');
        }
    }
    
    
    
    
    public static function disableCgiModeForPhp()
    {
        Configuration::updateValue('PS_WEBSERVICE_CGI_HOST', '');
        $savedValue  = Configuration::get('PS_WEBSERVICE_CGI_HOST');
        $saveSuccess = ('' === (string)$savedValue);
        
        if ($saveSuccess) {
            Logger::log('action: disable_cgi_mode_for_php', 'Success > ', 'process_Update');
            // body == { success: true }
            Helper::sendResponseFormattedSuccess(['success' => true]);
        } else {
            Logger::log('action: disable_cgi_mode_for_php', 'Fail > ', 'process_Update');
        }
    }
    
    
    
    
    public static function enableApi()
    {
        Configuration::updateValue('PS_WEBSERVICE', 1);
        $savedValue  = Configuration::get('PS_WEBSERVICE');
        $saveSuccess = ('1' === (string)$savedValue);
        
        if ($saveSuccess) {
            Logger::log('action: enable_api', 'Success > ', 'process_Update');
            // body == { success: true }
            Helper::sendResponseFormattedSuccess(['success' => true]);
        } else {
            Logger::log('action: enable_api', 'Fail > ', 'process_Update');
        }
    }
    
    
    
    
    public static function disableApi()
    {
        Configuration::updateValue('PS_WEBSERVICE', '');
        $savedValue  = Configuration::get('PS_WEBSERVICE');
        $saveSuccess = ('' === (string)$savedValue);
        
        if ($saveSuccess) {
            Logger::log('action: disable_api', 'Success > ', 'process_Update');
            // body == { success: true }
            Helper::sendResponseFormattedSuccess(['success' => true]);
        } else {
            Logger::log('action: disable_api', 'Fail > ', 'process_Update');
        }
    }
    
    
    
    
    public static function createApiCredentials()
    {
        // clear existing credentials
        // --------------------------
        
        // delete api key if exists
        ShopKeys::deleteWebServiceKey();
        // clear relative options
        Configuration::deleteByName('ADSCALE_SHOP_WEBSERVICE_ACCOUNT_ID');
        
        
        // processing new credentials
        // --------------------------
        $keyValue = ShopKeys::processShopAccessKeys();
        if (! empty($keyValue)
            &&
            ($webServiceKey = ShopKeys::getAccessKeyExisted())
            &&
            is_object($webServiceKey)
            &&
            $webServiceKey->key === $keyValue
        ) {
            Logger::log('action: create_api_credentials', 'Success > ', 'process_Update');
            
            // body == json from the GetKeys endpoint
            Helper::sendResponseFormattedSuccess([
                'shop_host' => Helper::getShopDomain(),
                'platform'  => Helper::getConfigSetting('shop/platform', 'presta'),
                'api_key'   => $webServiceKey->key,
                'version'   => ADSCALE_INTERNAL_MODULE_VERSION,
            ]);
        } else {
            Logger::log('action: create_api_credentials', 'Fail > ', 'process_Update');
        }
    }
    
    
    
    
    /**
     * @param $propertyName
     * @param $propertyValue
     *
     * @throws ServiceApiExeption
     */
    public static function modifyProperty($propertyName, $propertyValue)
    {
        $dbOptionName = self::getDbOptionNameByPropertyName($propertyName);
        
        if (! $dbOptionName) {
            throw new ServiceApiExeption('System error: DbOptionName not found for PropertyName', '000');
        }
        
        // process
        Configuration::updateValue($dbOptionName, $propertyValue);
        $savedPropertyValue = Configuration::get($dbOptionName);
        $saveSuccess        = ($savedPropertyValue === $propertyValue);
        
        if ($saveSuccess) {
            Logger::log(
                "action: modify, property: {$propertyName}, value: {$propertyValue}",
                'Success > ',
                'process_Update'
            );
            
            //json format : {property: "%propertyName%", value: "%propertyValue%"}
            Helper::sendResponseFormattedSuccess(['property' => $propertyName, 'value' => $propertyValue]);
        }
        
        Logger::log(
            "action: modify, property: {$propertyName}, value: {$propertyValue}",
            'Fail > ',
            'process_Update'
        );
    }
    
    
    
    
    /**
     * @param string $propertyName
     *
     * @return string|null DbOptionName
     */
    public static function getDbOptionNameByPropertyName($propertyName)
    {
        $properties = [
            'shopHost'               => 'ADSCALE_SHOP_DOMAIN',
            'googleVerificationCode' => 'ADSCALE_GSV',
        ];
        
        return isset($properties[$propertyName]) ? (string)$properties[$propertyName] : null;
    }
}
