<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\Helpers;

class Logger
{
    
    
    protected static $config = [];
    
    
    
    
    public static function fileLog($var, $desc = ' >> ', $log_file_destination = null, $clear_log = false)
    {
        
        if (! self::isLoggerEnabled()) {
            return;
        }
        
        if (! $log_file_destination) {
            return;
        }
        
        $dirname = dirname($log_file_destination);
        
        // try to make directory if not exists
        if (! self::resolveDir($dirname)) {
            return;
        }
        
        if ($clear_log) {
            file_put_contents($log_file_destination, '');
        }
        
        if (self::isLoggerTimeInMS()) {
            $time_mark = '[' . self::getMicroTime() . '] ';
        } else {
            $time_mark = '[' . date('Y-m-d H:i:s') . '] ';
        }
        
        error_log($time_mark . $desc . ' ' . print_r($var, true) . PHP_EOL, 3, $log_file_destination);
    }
    
    
    
    
    public static function log($var, $desc = ' ', $name = 'default', $separate = false)
    {
        
        if (! self::isLoggerEnabled()) {
            return;
        }
        
        $date = $separate ? date('Y-m-d_H') : date('Y-m-d');
        $dir  = self::getDir();
        
        $log_file_destination = Helper::trailingslashit($dir) . $name . '_' . $date . '.log';
        self::fileLog($var, $desc, $log_file_destination, false);
    }
    
    
    
    // ==============================================================================
    
    
    
    public static function setConfig(array $config)
    {
        return self::$config = $config;
    }
    
    
    
    
    public static function getConfig()
    {
        return self::$config;
    }
    
    
    
    
    public static function isLoggerEnabled()
    {
        return isset(self::$config['enabled']) ? self::$config['enabled'] : false;
    }
    
    
    
    
    public static function isLoggerTimeInMS()
    {
        return isset(self::$config['time_in_ms']) ? self::$config['time_in_ms'] : true;
    }
    
    
    
    
    public static function getDir()
    {
        $dir      = isset(self::$config['dir']) ? self::$config['dir'] : 'adscale';
        $full_dir = is_dir(_PS_ROOT_DIR_ . '/var/logs')
            ? _PS_ROOT_DIR_ . '/var/logs/' . $dir
            : _PS_ROOT_DIR_ . '/log/' . $dir; // for presta 1.6
        
        return $full_dir;
    }
    
    
    
    
    public static function getMicroTime()
    {
        $t     = microtime(true);
        $micro = sprintf('%06d', ($t - floor($t)) * 1000000);
        $d     = null;
        try {
            $d = new \DateTime(date('Y-m-d H:i:s.' . $micro, $t));
        } catch (\Exception $e) {
        }
        
        return $d ? $d->format('Y-m-d H:i:s.u') : ''; //note "u" is microseconds (1 seconds = 1000000 µs).
    }
    
    
    
    
    public static function resolveDir($dirname)
    {
        
        $dir_exists = is_dir($dirname);
        
        if (! $dir_exists) {
            $dir_exists = @mkdir($dirname, 0775, true);
        }
        
        return $dir_exists;
    }
}
