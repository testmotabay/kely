<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

return [
    
    'gsv_code_request_tpl' => 'https://ecommerce-scripts.adscale.com/ecommerce/script/%SHOP_DOMAIN%/metatag',
    
    'adscale_script_url_base'         => 'https://ecommerce-scripts.adscale.com/ecommerce/script/',
    'adscale_script_handle'           => 'module-adscale-ecommerce', // id
    'adscale_addtocart_script_handle' => 'module-adscale-ecommerce-addtocart', // id
    'adscale_heartbeat_script_handle' => 'module-adscale-ecommerce-heartbeat', // id
    
    'js_variables' => [
        'product_id'     => 'adscale_product_id',
        'product_value'  => 'adscale_product_value',
        'order_id'       => 'adscale_order_id',
        'order_value'    => 'adscale_order_value',
        'order_currency' => 'adscale_order_currency',
    ],
    
    'heartbeat' => [
        'request_tpl' => 'https://ecommerce-scripts.adscale.com/ecommerce/script/%SHOP_DOMAIN%/heartbeat',
        'interval'    => 60 * 60 * 6 // sec
    ],
    
    'module_events' => [
        'module_enable_started' => [
            'request_tpl' => 'https://tools.adscale.com/EcommerceEvent?'
                             . 'stage=%EVENT%&email=%EMAIL%&platform=%PLATFORM%&shop_host=%SHOP_DOMAIN%',
            'event_name'  => 'PluginInstallationStarted',
        ],
        'module_enabled'        => [
            'request_tpl' => 'https://tools.adscale.com/EcommerceEvent?'
                             . 'stage=%EVENT%&email=%EMAIL%&platform=%PLATFORM%&shop_host=%SHOP_DOMAIN%',
            'event_name'  => 'PluginInstalled',
        ],
        'module_disable'        => [
            'request_tpl' => 'https://app.adscale.com/EcommerceEventListner?shopHost=%SHOP_DOMAIN%&event=%EVENT%',
            'event_name'  => 'module_disable',
        ],
        'module_uninstall'      => [
            'request_tpl' => 'https://app.adscale.com/EcommerceEventListner?shopHost=%SHOP_DOMAIN%&event=%EVENT%',
            'event_name'  => 'module_uninstall',
        ],
    ],
    
    'logger' => [
        'enabled'            => true,
        'dir'                => 'adscale',
        'time_in_ms'         => true,
        'need_deny_htaccess' => true,
    ],
    
    'shop' => [
        'platform'               => 'presta',
        'webservice_auto_enable' => true,
    ],

];
