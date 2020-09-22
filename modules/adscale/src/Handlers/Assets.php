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
use AdscaleHeartbeatModuleFrontController;
use Context;
use Module;
use Media;

require_once dirname(__FILE__) . '/../../controllers/front/Heartbeat.php';




class Assets
{
    
    
    public static function loadAssets()
    {
        self::addProductVars();
        self::addOrderVars();
        self::addHearbeatVars();
        self::enqueueAddToCartScript();
        self::enqueueHeartbeatScript();
        self::enqueueAdscaleScript();
    }
    
    
    
    
    public static function loadBackOfficeAssets()
    {
        self::enqueueCommonBackOfficeCss();
        //self::addBackofficeVars();
        //self::enqueueCommonBackOfficeJs();
    }
    
    
    
    
    public static function enqueueAdscaleScript()
    {
        
        $adscale_script_url_base = Helper::getConfigSetting('adscale_script_url_base', '');
        $adscale_script_handle   = Helper::getConfigSetting('adscale_script_handle', '');
        
        if (! $adscale_script_url_base || ! $adscale_script_handle) {
            return;
        }
        
        if (! $shop_domain = Helper::getShopDomain()) {
            return;
        }
        
        $adscale_script_url = "{$adscale_script_url_base}{$shop_domain}.js";
        
        /** @var $context Context */
        $context = App::instance()->getContext();
        
        if (method_exists($context->controller, 'registerJavascript') && self::canRemoteJS()) {
            $context->controller->registerJavascript($adscale_script_handle, $adscale_script_url, [
                'server'     => 'remote',
                'position'   => 'bottom',
                'priority'   => 20,
                'attributes' => 'async',
            ]);
        } else { // presta 1.6.x, 1.7.0.0, 1.7.0.1
            self::addScriptLegacy($adscale_script_url, 'async');
        }
    }
    
    
    
    
    public static function canRemoteJS()
    {
        return ! in_array(_PS_VERSION_, ['1.7.0.0', '1.7.0.1']);
    }
    
    
    
    
    public static function addScriptLegacy($src, $atts)
    {
        /** @var $context Context */
        $context = App::instance()->getContext();
        
        $templateVars = $context->smarty->getTemplateVars();
        
        $atts_arr = [];
        if (is_string($atts)) {
            $atts_arr[] = $atts;
        } elseif (is_array($atts)) {
            $atts_arr = $atts;
        }
        $atts_str = implode(' ', $atts_arr);
        
        
        $script = "<script src=\"{$src}\" {$atts_str}></script>";
        
        
        if (isset($templateVars['HOOK_HEADER']) && is_string($templateVars['HOOK_HEADER'])) {
            return $templateVars['HOOK_HEADER'] . $script;
        } elseif (! isset($templateVars['HOOK_HEADER'])) {
            $context->smarty->assign('HOOK_HEADER', $script);
        }
    }
    
    
    
    
    public static function enqueueAddToCartScript()
    {
        
        $script_handle = Helper::getConfigSetting('adscale_addtocart_script_handle', '');
        if (! $script_handle) {
            return;
        }
        
        /** @var $context Context */
        $context = App::instance()->getContext();
        /** @var $module Module */
        $module = App::instance()->getModule();
        
        if (method_exists($context->controller, 'registerJavascript')) {
            $context->controller->registerJavascript(
                $script_handle,
                'modules/' . $module->name . '/views/js/add_to_cart.js',
                [
                    'position' => 'bottom',
                    'priority' => 10,
                ]
            );
        } else {
            // presta 1.6.x
            $context->controller->addJS('/modules/' . $module->name . '/views/js/add_to_cart.js', false);
        }
    }
    
    
    
    public static function addProductVars()
    {
        if (! Helper::isProductPage()) {
            return;
        }
        
        $js_var_name__product_id    = Helper::getConfigSetting('js_variables/product_id', '');
        $js_var_name__product_value = Helper::getConfigSetting('js_variables/product_value', '');
        
        if (! $js_var_name__product_id || ! $js_var_name__product_value) {
            return;
        }
        
        self::enqueueJsVars([
            $js_var_name__product_id    => Helper::getProductId(),
            $js_var_name__product_value => Helper::getProductValue(),
        ]);
    }
    
    
    
    public static function addOrderVars()
    {
        
        if (! Helper::isOrderConfirmedPage()) {
            return;
        }
        
        $js_var_name__order_id       = Helper::getConfigSetting('js_variables/order_id', '');
        $js_var_name__order_value    = Helper::getConfigSetting('js_variables/order_value', '');
        $js_var_name__order_currency = Helper::getConfigSetting('js_variables/order_currency', '');
        
        if (! $js_var_name__order_id || ! $js_var_name__order_value || ! $js_var_name__order_currency) {
            return;
        }
        
        $order_id       = Helper::getOrderIdFromOrderConfirmation();
        $order_value    = Helper::getOrderValueFromOrderConfirmation();
        $order_currency = Helper::getOrderCurrencyFromOrderConfirmation();
        
        self::enqueueJsVars([
            $js_var_name__order_id       => $order_id,
            $js_var_name__order_value    => $order_value,
            $js_var_name__order_currency => $order_currency,
        ]);
    }
    
    
    
    public static function addHearbeatVars()
    {
        if (! AdscaleHeartbeatModuleFrontController::isHeartbeatTimeHasCome()) {
            return;
        }
        
        self::enqueueJsVars([
            'adscale_heartbeat_link'   => AdscaleHeartbeatModuleFrontController::getAjaxLink(),
            'adscale_heartbeat_action' => AdscaleHeartbeatModuleFrontController::getActionName(),
        ]);
    }
    
    
    
    public static function addBackofficeVars()
    {
        //self::enqueueJsVars([]);
    }
    
    
    
    public static function enqueueHeartbeatScript()
    {
        
        if (! AdscaleHeartbeatModuleFrontController::isHeartbeatTimeHasCome()) {
            return;
        }
        
        $script_handle = Helper::getConfigSetting('adscale_heartbeat_script_handle', '');
        if (! $script_handle) {
            return;
        }
        
        /** @var $context Context */
        $context = App::instance()->getContext();
        /** @var $module Module */
        $module = App::instance()->getModule();
        
        if (method_exists($context->controller, 'registerJavascript')) {
            $context->controller->registerJavascript(
                $script_handle,
                'modules/' . $module->name . '/views/js/heartbeat.js',
                [
                    'position' => 'bottom',
                    'priority' => 10,
                ]
            );
        } else { // presta 1.6.x
            $context->controller->addJS('/modules/' . $module->name . '/views/js/heartbeat.js', false);
        }
    }
    
    
    
    
    /**
     * enqueue js vars before AdScale script
     *
     * @param array $js_vars
     */
    public static function enqueueJsVars(array $js_vars)
    {
        if ($js_vars) {
            Media::addJsDef($js_vars);
        }
    }
    
    
    
    
    public static function enqueueCommonBackOfficeCss()
    {
        /** @var $context Context */
        $context = App::instance()->getContext();
        /** @var $module Module */
        $module = App::instance()->getModule();
        
        $context->controller->addCSS('/modules/' . $module->name . '/views/css/back.css');
    }
    
    
    
    
    public static function enqueueCommonBackOfficeJs()
    {
        /** @var $context Context */
        $context = App::instance()->getContext();
        /** @var $module Module */
        $module = App::instance()->getModule();
        
        $context->controller->addJquery();
        $context->controller->addJS('/modules/' . $module->name . '/views/js/back.js', false);
    }
}
