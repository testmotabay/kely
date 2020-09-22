<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\ServiceApi;

use AdScale\Handlers\ShopKeys;
use AdScale\Helpers\Helper;

class GetKeys extends ServiceApiBase
{
    
    /**
     * @throws ServiceApiExeption
     */
    public static function handleRequest()
    {
        $api_key   = ShopKeys::getAccessKeyExisted();
        $shop_host = Helper::getShopDomain();
        $platform  = Helper::getConfigSetting('shop/platform', 'presta');
        
        if (! is_object($api_key)) {
            throw new ServiceApiExeption('System error: $api_key is_object check fail', '000');
        }
        
        if (! $api_key->key) {
            throw new ServiceApiExeption('System error: $api_key->key is empty', '000');
        }
        
        if (! $shop_host) {
            throw new ServiceApiExeption('System error: $shop_host is empty', '000');
        }
        
        if (! $platform) {
            throw new ServiceApiExeption('System error: $platform is empty', '000');
        }
        
        Helper::sendResponseFormattedSuccess([
            'shop_host' => $shop_host,
            'platform'  => $platform,
            'api_key'   => $api_key->key,
            'version'   => ADSCALE_INTERNAL_MODULE_VERSION,
        ]);
    }
}
