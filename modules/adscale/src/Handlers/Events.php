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
use AdScale\Api\ApiManager;

class Events
{
    
    public static function moduleEnableStarted()
    {
        Logger::log('', 'moduleEnableStarted event starting', 'Events');
        $request_template   = Helper::getConfigSetting('module_events/module_enable_started/request_tpl', '');
        $request_event_name = Helper::getConfigSetting('module_events/module_enable_started/event_name', '');
        $is_success         = ApiManager::processModuleEvent($request_template, $request_event_name);
        Logger::log($is_success, 'moduleEnableStarted : is_success : ', 'Events');
    }
    
    
    
    public static function moduleEnabled()
    {
        Logger::log('', 'moduleEnabled event starting', 'Events');
        $request_template   = Helper::getConfigSetting('module_events/module_enabled/request_tpl', '');
        $request_event_name = Helper::getConfigSetting('module_events/module_enabled/event_name', '');
        $is_success         = ApiManager::processModuleEvent($request_template, $request_event_name);
        Logger::log($is_success, 'moduleEnabled : is_success : ', 'Events');
    }
    
    
    
    public static function moduleDisable()
    {
        Logger::log('', 'moduleDisable event starting', 'Events');
        $request_template   = Helper::getConfigSetting('module_events/module_disable/request_tpl', '');
        $request_event_name = Helper::getConfigSetting('module_events/module_disable/event_name', '');
        $is_success         = ApiManager::processModuleEvent($request_template, $request_event_name);
        Logger::log($is_success, 'moduleDisable : is_success : ', 'Events');
    }
    
    
    
    
    public static function moduleUninstall()
    {
        Logger::log('', 'moduleUninstall event starting', 'Events');
        $request_template   = Helper::getConfigSetting('module_events/module_uninstall/request_tpl', '');
        $request_event_name = Helper::getConfigSetting('module_events/module_uninstall/event_name', '');
        $is_success         = ApiManager::processModuleEvent($request_template, $request_event_name);
        Logger::log($is_success, 'moduleUninstall : is_success : ', 'Events');
    }
}
