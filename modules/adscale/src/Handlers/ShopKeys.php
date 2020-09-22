<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\Handlers;

use AdScale\App;
use AdScale\Helpers\Helper;
use AdScale\Helpers\Logger;
use Configuration;
use Context;
use Tools;
use WebserviceKey;
use WebserviceRequest;

class ShopKeys
{
    
    
    public static function processShopAccessKeys()
    {
        
        $keyExisted = self::getAccessKeyExisted();
        
        if ($keyExisted && $keyExisted->key) {
            return $keyExisted->key;
        }
        
        try {
            $webServiceKey      = new WebserviceKey();
            $webServiceKey->key = Helper::generateRandHash();
            while ($webServiceKey::keyExists($webServiceKey->key)) { // prevent key duplication
                $webServiceKey->key = Helper::generateRandHash();
            }
            $webServiceKey->description = self::generateKeyDescription();
            
            if ($result = $webServiceKey->save()) {
                Logger::log($result, 'process_shop_access_keys : save success : ', 'process_shop_access_keys');
                self::setAccessAccountIdToOption($webServiceKey->id);
                //Tools::generateHtaccess();
                $permissionsToSet = self::getWebservicePermissionsToSet();
                WebserviceKey::setPermissionForAccount($webServiceKey->id, $permissionsToSet);
                self::mayBeEnableWebservices();
                
                return $webServiceKey->key;
            } else {
                Logger::log($result, 'process_shop_access_keys : save not success : ', 'process_shop_access_keys');
                
                return false;
            }
        } catch (\PrestaShopDatabaseException $e) {
            Logger::log(
                $e->getMessage(),
                'processShopAccessKeys : PrestaShopDatabaseException : ',
                'process_shop_access_keys'
            );
            
            return false;
        } catch (\PrestaShopException $e) {
            Logger::log($e->getMessage(), 'processShopAccessKeys : PrestaShopException : ', 'process_shop_access_keys');
            
            return false;
        }
    }
    
    
    
    
    public static function generateKeyDescription()
    {
        return 'AdScale service access key';
    }
    
    
    
    public static function getAccessAccountIdFromOption()
    {
        return Configuration::get('ADSCALE_SHOP_WEBSERVICE_ACCOUNT_ID');
    }
    
    
    
    public static function setAccessAccountIdToOption($accountId)
    {
        return Configuration::updateValue('ADSCALE_SHOP_WEBSERVICE_ACCOUNT_ID', $accountId);
    }
    
    
    
    
    public static function getAccessKeyExisted()
    {
        $accountId = (int)self::getAccessAccountIdFromOption();
        Logger::log($accountId, 'getAccessKeyExisted : $accountId : ', 'process_shop_access_keys');
        
        if (! $accountId) {
            return null;
        }
        
        try {
            $webServiceKey = new WebserviceKey($accountId);
            
            return ! empty($webServiceKey->key) ? $webServiceKey : null;
        } catch (\PrestaShopDatabaseException $e) {
            Logger::log(
                $e->getMessage(),
                'getAccessKeyExisted : PrestaShopDatabaseException : ',
                'process_shop_access_keys'
            );
            
            return false;
        } catch (\PrestaShopException $e) {
            Logger::log($e->getMessage(), 'getAccessKeyExisted : PrestaShopException : ', 'process_shop_access_keys');
            
            return false;
        }
    }
    
    
    
    
    public static function getWebservicePermissionsToSet()
    {
        $resources = WebserviceRequest::getResources();
        
        if (! $resources) {
            return [];
        }
        
        $resourcesNames        = array_keys($resources);
        $webservicePermissions = [];
        
        foreach ($resourcesNames as $resourcesName) {
            $webservicePermissions[$resourcesName] = ['GET' => 'on', 'HEAD' => 'on'];
        }
        
        return $webservicePermissions;
    }
    
    
    
    
    public static function deleteWebServiceKey()
    {
        /** @var WebserviceKey $webServiceKey */
        $webServiceKey = self::getAccessKeyExisted();
        
        if (is_object($webServiceKey) && method_exists($webServiceKey, 'delete')) {
            try {
                $webServiceKey->delete();
                
                return true;
            } catch (\PrestaShopException $e) {
                return false;
            }
        }
        
        return false;
    }
    
    
    
    
    public static function mayBeEnableWebservices()
    {
        $webservice_auto_enable = Helper::getConfigSetting('shop/webservice_auto_enable', false);
        
        if ($webservice_auto_enable) {
            Configuration::updateValue('PS_WEBSERVICE', 1);
        }
    }
}
