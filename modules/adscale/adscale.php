<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

if (! defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/src/autoload.php';




class Adscale extends Module
{
    
    protected $config_form = false;
    
    
    
    public function __construct()
    {
        defined('ADSCALE_INTERNAL_MODULE_VERSION') || define('ADSCALE_INTERNAL_MODULE_VERSION', 'v20200211');
        $this->name          = 'adscale';
        $this->tab           = 'analytics_stats';
        $this->version       = '1.3.4';
        $this->author        = 'AdScale';
        $this->need_instance = 1;
        
        /**
         * Set $this->bootstrap to true if module is compliant with bootstrap(css-framework) (PrestaShop 1.6)
         */
        $this->bootstrap = true;
        
        parent::__construct();
        
        $this->displayName = $this->l('AdScale');
        
        $this->description = $this->l(
            'AdScale module allows you to automate Ecommerce advertising across all channels.'
        );
        
        $this->confirmUninstall       = $this->l('Are you sure you want to uninstall AdScale Module ?');
        $this->module_key             = '292388663d5402d2b7185d330c10720c';
        $this->ps_versions_compliancy = array('min' => '1.6.0.14', 'max' => _PS_VERSION_);
        
        
        $app    = AdScale\App::instance();
        $config = require dirname(__FILE__) . '/src/config/config.php';
        
        try {
            $app->run($config, $this, $this->context);
        } catch (Exception $exception) {
            AdScale\Helpers\Logger::log($exception, '$app->run Exception >>', 'app');
            header('HTTP/1.1 503 Service Temporarily Unavailable');
            header('Status: 503 Service Temporarily Unavailable');
            header('Retry-After: 300');// 300 seconds.
            die();
        }
    }
    
    
    
    public function install()
    {
        $isParentInstallSuccess = parent::install();
        
        $shopDomain = AdScale\Helpers\Helper::saveComputedShopDomain();
        
        AdScale\Handlers\Events::moduleEnableStarted();
        
        AdScale\Api\ApiManager::processApiGsvCode();
        AdScale\Handlers\ShopKeys::processShopAccessKeys();
        
        $success = $isParentInstallSuccess &&
                   $this->registerHook('displayHeader') &&
                   $this->registerHook('backOfficeHeader') &&
                   $this->registerHook('moduleRoutes') &&
                   $shopDomain &&
                   Configuration::updateValue('ADSCALE_HEARTBEAT_TIME', 10);
        
        AdScale\Handlers\Events::moduleEnabled();
        
        return $success;
    }
    
    
    
    public function uninstall()
    {
        AdScale\Handlers\ShopKeys::deleteWebServiceKey();
        AdScale\Handlers\Events::moduleUninstall(); // Send uninstall event to AdScale
        
        return parent::uninstall() &&
               $this->deleteAllModuleOptions();
    }
    
    
    
    public function disable($force_all = false)
    {
        $isParentDisableSuccess = parent::disable($force_all);
        AdScale\Handlers\Events::moduleDisable(); // Send disable event to AdScale
        
        return $isParentDisableSuccess;
    }
    
    
    
    public function disableDevice($device)
    {
        // prevent disable on devices
        if ($device) {
            return false;
        }
        
        return false;
    }
    
    
    
    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $this->goToAdscaleRedirect();
        $this->firstPageEnterRedirect();
        
        $output = null;
        if (Tools::isSubmit('submit_' . $this->name)) {
            $this->postProcess();
            $output .= $this->displayConfirmation($this->l('The settings have been updated.'));
        }
        
        $adminLink = $this->context->link->getAdminLink('AdminModules', false);
        $token     = Tools::getAdminTokenLite('AdminModules');
        $linkQuery = '&configure=' . $this->name . '&action=gotoadscale' . '&token=' . $token;
        
        $enableAdscaleBtnLink = $adminLink . $linkQuery;
        $enableAdscaleBtnText = $this->l('GO TO ADSCALE');
        
        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('enableAdscaleBtnLink', $enableAdscaleBtnLink);
        $this->context->smarty->assign('enableAdscaleBtnText', $enableAdscaleBtnText);
        $this->context->smarty->assign('enableAdscaleBtnHelpText', '');
        
        $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        
        return $output . $this->renderForm();
    }
    
    
    
    protected function firstPageEnterRedirect()
    {
        if (Configuration::get('ADSCALE_ACTIVATION_REDIRECT_PASSED')) {
            return;
        }
        Configuration::updateValue('ADSCALE_ACTIVATION_REDIRECT_PASSED', 1);
        
        $url = AdScale\Helpers\Helper::getGoToAdscaleUrl();
        
        if ($url) {
            AdScale\Helpers\Logger::log($url, 'getUrl [firstPageEnterRedirect] : success : ', 'GoToAdscale');
            Tools::redirect($url);
        } else {
            AdScale\Helpers\Logger::log($url, 'getUrl [firstPageEnterRedirect] : failed : ', 'GoToAdscale');
        }
    }
    
    
    
    protected function goToAdscaleRedirect()
    {
        if (Tools::getValue('action') !== 'gotoadscale') {
            return;
        }
        
        if (Tools::getValue('token') !== Tools::getAdminTokenLite('AdminModules')) {
            AdScale\Helpers\Logger::log('Nonce error', 'getUrl : failed : ', 'GoToAdscale');
            
            return;
        }
        
        $url = AdScale\Helpers\Helper::getGoToAdscaleUrl();
        
        if ($url) {
            AdScale\Helpers\Logger::log($url, 'getUrl : success : ', 'GoToAdscale');
            Tools::redirect($url);
        } else {
            AdScale\Helpers\Logger::log($url, 'getUrl : failed : ', 'GoToAdscale');
        }
    }
    
    
    
    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();
        
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $helper->module                   = $this;
        $helper->default_form_language    = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        
        $helper->identifier    = $this->identifier;
        $helper->submit_action = 'submit_' . $this->name;
        
        $link_query = '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . $link_query;
        $helper->token        = Tools::getAdminTokenLite('AdminModules');
        
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );
        
        return $helper->generateForm(array($this->getConfigForm()));
    }
    
    
    
    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'col'      => 3,
                        'type'     => 'text',
                        'name'     => 'ADSCALE_SHOP_DOMAIN',
                        'desc'     => $this->l('without protocol and www'),
                        'label'    => $this->l('Shop domain'),
                        'readonly' => true,
                    ),
                    array(
                        'col'      => 3,
                        'type'     => 'text',
                        'name'     => 'ADSCALE_GSV',
                        'label'    => $this->l('Google site verification code'),
                        'readonly' => true,
                    ),
                ),
                /*
                'submit' => array(
                    'title' => $this->l('Save'),
                ),*/
            ),
        );
    }
    
    
    
    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'ADSCALE_SHOP_DOMAIN' => Configuration::get('ADSCALE_SHOP_DOMAIN'),
            'ADSCALE_GSV'         => Configuration::get('ADSCALE_GSV'),
        );
    }
    
    
    
    protected function getAllOptions()
    {
        return array_merge($this->getConfigFormValues(), array(
            'ADSCALE_HEARTBEAT_TIME'             => Configuration::get('ADSCALE_HEARTBEAT_TIME'),
            'ADSCALE_ACTIVATION_REDIRECT_PASSED' => Configuration::get('ADSCALE_ACTIVATION_REDIRECT_PASSED'),
            'ADSCALE_SHOP_WEBSERVICE_ACCOUNT_ID' => Configuration::get('ADSCALE_SHOP_WEBSERVICE_ACCOUNT_ID'),
            'ADSCALE_LOGIN_TOKEN'                => Configuration::get('ADSCALE_LOGIN_TOKEN'),
        ));
    }
    
    
    
    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        
        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }
    
    
    
    protected function deleteAllModuleOptions()
    {
        $options = $this->getAllOptions();
        
        foreach (array_keys($options) as $key) {
            Configuration::deleteByName($key);
        }
        
        return true;
    }
    
    
    
    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        \AdScale\Handlers\Assets::loadBackOfficeAssets();
    }
    
    
    
    
    public function hookDisplayHeader()
    {
        \AdScale\Handlers\Assets::loadAssets();
        
        return \AdScale\Handlers\MetaTags::resolveGSV();
    }
    
    
    
    
    public function hookModuleRoutes()
    {
        return array_merge(
            $this->makeAdscaleRouteArray('adscale/GetKeys', 'GetKeys'),
            $this->makeAdscaleRouteArray('adscale/LoginToken', 'LoginToken'),
            $this->makeAdscaleRouteArray('adscale/Update', 'Update'),
            $this->makeAdscaleRouteArray('adscale/Status', 'Status'),
            $this->makeAdscaleRouteArray('adscale/shopInfo', 'ShopInfo'),
            $this->makeAdscaleRouteArray('adscale/categories', 'Categories'),
            $this->makeAdscaleRouteArray('adscale/categories/count', 'CategoriesCount'),
            $this->makeAdscaleRouteArray('adscale/products', 'Products'),
            $this->makeAdscaleRouteArray('adscale/products/count', 'ProductsCount'),
            $this->makeAdscaleRouteArray('adscale/orders', 'Orders'),
            $this->makeAdscaleRouteArray('adscale/orders/count', 'OrdersCount'),
            $this->makeAdscaleRouteArray('adscale/shipping', 'Shipping')
        );
    }
    
    
    
    /**
     * @param string $rule
     * @param string $class
     *
     * @return array
     */
    public function makeAdscaleRouteArray($rule, $class)
    {
        return [
            'module-adscale-' . $class => [
                'controller' => $class,
                'rule'       => $rule,
                'keywords'   => [],
                'params'     => [
                    'fc'         => 'module',
                    'module'     => 'adscale',
                    'controller' => $class,
                ],
            ],
        ];
    }
}
