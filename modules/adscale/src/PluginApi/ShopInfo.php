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
use Configuration;
use Country;
use Currency;
use Language;
use Tools;

class ShopInfo extends PluginApiBase
{
    
    public static function handleRequest()
    {
        $shop_name  = Configuration::get('PS_SHOP_NAME');
        $shop_email = Configuration::get('PS_SHOP_EMAIL');
        $phone      = Configuration::get('PS_SHOP_PHONE');
        $timezone   = Configuration::get('PS_TIMEZONE');
        
        $shop_domain_w_protocol = Tools::getShopDomainSsl(true);
        
        $owner_name = null;
        
        
        // Language
        // --------
        $language_id = Configuration::get('PS_LANG_DEFAULT');
        $Language    = new Language($language_id);
        
        /** @var string 2-letter iso code [en] */
        $language_iso_code = $Language->iso_code;
        
        /** @var string 5-letter iso code [en-US] */
        $language_locale = property_exists(Language::class, 'locale') ? $Language->locale : '';
        
        /** @var string 5-letter iso code [en-us] */
        $language_code = $Language->language_code;
        
        if ($language_iso_code) {
            $locale = $language_iso_code;
        } elseif ($language_locale) {
            $locale = $language_locale;
        } else {
            $locale = $language_code;
        }
        
        $is_lang_id_1_en = Language::getIsoById(1) === 'en';
        
        
        // Country
        // --------
        $country_id = Configuration::get('PS_COUNTRY_DEFAULT');
        $Country    = new Country($country_id);
        
        /** @var string 2 letters iso code */
        $country_code = $Country->iso_code;
        
        if (is_string($Country->name)) {
            $country_name = $Country->name;
        } elseif (is_array($Country->name) && $is_lang_id_1_en) {
            $country_name = $Country->name[1];
        } elseif (is_array($Country->name)) {
            $country_name = $Country->name[$language_id];
        } else {
            $country_name = '';
        }
        
        
        // Currency
        // --------
        $Currency          = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $currency_iso_code = $Currency->iso_code;
        
        Helper::sendResponseFormattedSuccess([
            'shop' => [
                'name'         => $shop_name,
                'email'        => $shop_email,
                'url'          => $shop_domain_w_protocol,
                'phone'        => $phone ?: '',
                'locale'       => $locale,
                'country_code' => $country_code,
                'country_name' => $country_name,
                'currency'     => $currency_iso_code,
                'timezone'     => $timezone,
                'owner'        => $owner_name,
            ],
        ]);
    }
}
