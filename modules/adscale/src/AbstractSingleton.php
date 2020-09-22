<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale;

abstract class AbstractSingleton
{
    
    /**
     * Call this method to get singleton
     */
    public static function instance()
    {
        static $instance = false;
        if ($instance === false) {
            $instance = new static();
        }
        
        return $instance;
    }
    
    
    
    /**
     * Make constructor private, so nobody can call "new Class".
     */
    protected function __construct()
    {
    }
    
    
    
    /**
     * Make clone magic method private, so nobody can clone instance.
     */
    private function __clone()
    {
    }
    
    
    
    /**
     * Make sleep magic method private, so nobody can serialize instance.
     */
    private function __sleep()
    {
    }
    
    
    
    /**
     * Make wakeup magic method private, so nobody can unserialize instance.
     */
    private function __wakeup()
    {
    }
}
