<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale;

use AdScale\Helpers\Logger;
use Context;
use Module;

final class App extends AbstractSingleton
{
    
    private $config;
    
    /**
     * @var Context
     */
    private $context;
    
    /**
     * @var Module
     */
    private $module;
    
    
    
    protected function __construct()
    {
        parent::__construct();
    }
    
    
    
    
    public function getConfig()
    {
        return $this->config;
    }
    
    
    
    
    public function run($config, $module, $context)
    {
        $this->config  = $config;
        $this->module  = $module;
        $this->context = $context;
        
        Logger::setConfig(! empty($this->config['logger']) ? $this->config['logger'] : []);
    }
    
    
    
    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }
    
    
    
    /**
     * @return Module
     */
    public function getModule()
    {
        return $this->module;
    }
}
