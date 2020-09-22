<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

use AdScale\Api\ApiManager;
use AdScale\Helpers\Helper;

class AdscaleHeartbeatModuleFrontController extends ModuleFrontController
{
    
    
    private static $action_name = 'adscale_heartbeat';
    
    
    
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        
        $return = null;
        
        if (Tools::getValue('action') === self::getActionName()) {
            $heartbeatTimeHasCome = self::isHeartbeatTimeHasCome();
            
            $return = [
                'adscale_heartbeat_processed'     => true,
                'adscale_heartbeat_time_has_come' => $heartbeatTimeHasCome,
                'adscale_heartbeat_success'       => null,
            ];
            
            if ($heartbeatTimeHasCome) {
                $heartbeat_success                   = ApiManager::processHeartbeat();
                $return['adscale_heartbeat_success'] = $heartbeat_success;
                if ($heartbeat_success) {
                    self::updateHeartbeatLastTimeOption();
                }
            }
        }
        
        header('Content-Type: application/json');
        
        die(json_encode($return));
    }
    
    
    
    public static function isHeartbeatTimeHasCome()
    {
        $heartbeat_interval = Helper::getConfigSetting('heartbeat/interval', '');
        $last_preccessed    = Configuration::get('ADSCALE_HEARTBEAT_TIME');
        $now                = time();
        
        return $now - $last_preccessed > $heartbeat_interval;
    }
    
    
    
    public static function updateHeartbeatLastTimeOption()
    {
        Configuration::updateValue('ADSCALE_HEARTBEAT_TIME', time());
    }
    
    
    
    public static function getAjaxLink()
    {
        $link       = new Link;
        $parameters = ['action' => self::getActionName()];
        
        return $link->getModuleLink('adscale', pathinfo(__FILE__, PATHINFO_FILENAME), $parameters);
    }
    
    
    
    public static function getActionName()
    {
        return self::$action_name;
    }
}
