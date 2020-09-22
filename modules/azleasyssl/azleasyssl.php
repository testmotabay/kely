<?php
/**
* 2012-2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@azelab.com so we can send you a copy immediately.
*
*  @author    Azelab <support@azelab.com>
*  @copyright 2017 Azelab
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once dirname(__FILE__).'/classes/AzlEasysslGeneralConfig.php';

class AzlEasyssl extends Module
{
    const REMIND_TO_RATE = 259200; // 3 days
    const ADDONS_ID = 43104;
    
    protected $html;
    protected $configModel;
    
    protected $securityKey;


    public function __construct()
    {
        $this->name = 'azleasyssl';
        $this->tab = 'front_office_features';
        $this->version = '1.3.4';
        $this->author = 'Azelab';
        $this->controllers = array('ajax');
        $this->need_instance = 0;
        $this->bootstrap = true;
        if ($this->is17()) {
            $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        }
        $this->module_key = '116ad4b789ecf80b014a8f579f010776';
        $this->author_address = '0x45c659C9b74aBcDf503f434b8e72FD20c3643bE5';
        parent::__construct();

        $this->displayName = $this->l("Free SSL certificate by Let's Encrypt");
        $this->description = $this->l('Install SSL certificate in one click.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete all data?');
        
        $this->configModel = new AzlEasysslGeneralConfig($this, 'azlssl_');
        $this->configModel->loadFromConfig();
    }
    
    public function getConfigModel()
    {
        if (!$this->configModel->isLoaded()) {
            $this->configModel->loadFromConfig();
        }
        return $this->configModel;
    }
    
    public function install()
    {
        $this->updateSecurityKey();
        if (!parent::install()
                || !$this->installTab()
                || !$this->installDefaults()
            ) {
            return false;
        }
        
        return true;
    }
    
    public function installTab()
    {
        // Prepare tab
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminAzlEasyssl';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'AdminAzlEasyssl';
        }
        $tab->id_parent = -1;
        $tab->module = $this->name;
        return $tab->add();
    }
    
    public function reset()
    {
        return $this->installDefaults();
    }
    
    public function uninstall()
    {
        return parent::uninstall() && $this->clearConfig();
    }
    
    protected function clearConfig()
    {
        foreach ($this->getForms() as $model) {
            $model->clearConfig();
        }
        Configuration::deleteByName('AZL_SSL_DOMAINS');
        Configuration::deleteByName('AZL_SSL_TOKEN');
        Configuration::deleteByName('AZL_SSL_EMAIL');
        Configuration::deleteByName('AZL_SSL_CERT');
        Configuration::deleteByName('AZL_SSL_CHAIN');
        Configuration::deleteByName('AZL_SSL_FULLCHAIN');
        Configuration::deleteByName('AZL_SSL_PRIVATE');
        Configuration::deleteByName('AZL_SSL_PUBLIC');
        Configuration::deleteByName('AZL_SSL_ZIP');
        Configuration::deleteByName('AZL_SSL_TS');
        return true;
    }
    
    protected function getFrontAjaxUrl()
    {
        return Context::getContext()->link->getModuleLink($this->name, 'ajax');
    }


    protected function getCacheId($name = null)
    {
        $id = parent::getCacheId($name);
        return $id . '|' . $this->isMobile();
    }

    public function isMobile()
    {
        return Context::getContext()->getMobileDetect()->isMobile() || Context::getContext()->getMobileDetect()->isTablet();
    }
    
    protected function installDefaults()
    {
        foreach ($this->getForms() as $model) {
            $model->loadDefaults();
            $model->saveToConfig(false);
        }
        return true;
    }
    
    public function getForms()
    {
        return array(
            $this->configModel
        );
    }
    
    public function getContent()
    {
        if ($this->isSubmit()) {
            if ($this->postValidate()) {
                $this->postProcess();
            }
        }
        Context::getContext()->controller->addJqueryPlugin('tablednd');
        Context::getContext()->controller->addCss($this->_path.'views/css/admin.css');
        Context::getContext()->controller->addJS($this->_path.'views/js/admin.js');
        $this->html .= $this->renderForm();
        return $this->html;
    }
    
    public function isSubmit()
    {
        foreach ($this->getAllowedSubmits() as $submit) {
            if (Tools::isSubmit($submit)) {
                return true;
            }
        }
    }
    
    public function getAllowedSubmits()
    {
        $submits = array();
        foreach ($this->getForms() as $model) {
            $submits[] = get_class($model);
        }
        return $submits;
    }
    
    public function postProcess()
    {
        foreach ($this->getForms() as $model) {
            if (Tools::isSubmit(get_class($model))) {
                $model->populate();
                if (get_class($model) == 'AzlEasysslGeneralConfig') {
                    if ($model->ssl) {
                        Configuration::updateValue('PS_SSL_ENABLED', 1);
                    } else {
                        Configuration::updateValue('PS_SSL_ENABLED', 0);
                    }
                    if ($model->ssl_everywhere) {
                        Configuration::updateValue('PS_SSL_ENABLED_EVERYWHERE', 1);
                    } else {
                        Configuration::updateValue('PS_SSL_ENABLED_EVERYWHERE', 0);
                    }
                    if (!$model->ssl) {
                        $model->force_ssl = 0;
                    }
                }
                if ($model->saveToConfig()) {
                    $this->html .= $this->displayConfirmation($this->l('Settings updated'));
                } else {
                    $this->postValidate();
                }
            }
        }
        Tools::generateHtaccess();
        $this->updateSecurityKey();
    }
    
    public function updateSecurityKey()
    {
        Configuration::updateValue('AZL_SSL_TOKEN', Tools::passwdGen(8));
    }
    
    public function postValidate()
    {
        foreach ($this->getForms() as $model) {
            if (Tools::isSubmit(get_class($model))) {
                $model->loadFromConfig();
                $model->populate();
                if (!$model->validate()) {
                    foreach ($model->getErrors() as $errors) {
                        foreach ($errors as $error) {
                            $this->html .= $this->displayError($error);
                        }
                    }
                    return false;
                }
                return true;
            }
        }
    }
    
    public function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='
            .$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'path' => $this->getPath(),
        );
        $helper->base_folder =  dirname(__FILE__);
        $helper->base_tpl = '/views/templates/admin/azleasyssl/helpers/form/form.tpl';
        $id_lang = Context::getContext()->language->id;
        $email = Configuration::get('AZL_SSL_EMAIL');
        if (!$email) {
            $email = Context::getContext()->employee->email;
        }
        
        $configForm = $this->getForm($this->configModel);
        if (!$this->isHtaccessExists()) {
            $configForm['form']['input']['AZLSSL_FORCE_SSL']['disabled'] = true;
            $configForm['form']['input']['AZLSSL_FORCE_SSL']['hint'] = $this->l('.htaccess file does not exists on your server');
            
            $configForm['form']['input']['AZLSSL_SSL_REDIRECT']['disabled'] = true;
            $configForm['form']['input']['AZLSSL_SSL_REDIRECT']['hint'] = $this->l('.htaccess file does not exists on your server');
        } elseif (!$this->isHtaccessWritable()) {
            $configForm['form']['input']['AZLSSL_FORCE_SSL']['disabled'] = true;
            $configForm['form']['input']['AZLSSL_FORCE_SSL']['hint'] = $this->l('.htaccess file is not writable on your server');
            
            $configForm['form']['input']['AZLSSL_SSL_REDIRECT']['disabled'] = true;
            $configForm['form']['input']['AZLSSL_SSL_REDIRECT']['hint'] = $this->l('.htaccess file is not writable on your server');
        }
        //print_r($configForm);die();
        $this->smarty->assign(array(
            'form' => $helper,
            'formParams' => array($configForm),
            'languages' => $this->context->controller->getLanguages(),
            'defaultFormLanguage' => (int)(Configuration::get('PS_LANG_DEFAULT')),
            'callbacks' => array(),
            'link' => $this->context->link,
            'path' => $this->getPath(),
            'moduleUrl' => $this->getModuleBaseUrl(),
            'ajaxUrl' => $this->getAjaxUrl(),
            'name' => $this->displayName,
            'email' => $email,
            'issued' => Configuration::get('AZL_SSL_TS'),
            'version' => $this->version,
            'active_tab' => $this->getActiveTab(),
            'domains' => $this->getDomains()
        ));
        return $this->display(__FILE__, 'config.tpl');
    }
    
    public function isHtaccessExists()
    {
        $path = _PS_ROOT_DIR_.'/.htaccess';
        return file_exists($path);
    }
    
    public function isHtaccessWritable()
    {
        $path = _PS_ROOT_DIR_.'/.htaccess';
        return is_writable($path);
    }
    
    public function getDomains()
    {
        $domains = array();
        if ($d = Configuration::get('AZL_SSL_DOMAINS')) {
            return explode(',', $d);
        }
        if (Shop::isFeatureActive()) {
            $shops = Shop::getShops();
            ksort($shops);
            foreach ($shops as $shop) {
                $domains[] = $shop['domain'];
            }
        } else {
            $domains[] = Configuration::get('PS_SHOP_DOMAIN');
        }
        return array_unique($domains);
    }
    
    public function getActiveTab()
    {
        foreach ($this->getForms() as $model) {
            if (Tools::isSubmit(get_class($model))) {
                return get_class($model);
            }
        }
        return null;
    }
    
    public function getFormConfigs()
    {
        $configs = array();
        foreach ($this->getForms() as $form) {
            $configs[] = $this->getForm($form);
        }
        return $configs;
    }
    
    public function getForm($model)
    {
        $model->populate();
        $model->validate(false);
        $config = $model->getFormHelperConfig();
        return array(
            'form' => array(
                'name' => get_class($model),
                'legend' => array(
                    'title' => $model->getFormTitle(),
                    'icon' => $model->getFormIcon()
                ),
                'input' => $config,
                'submit' => array(
                    'name' => get_class($model),
                    'class' => $this->is15()? 'button' : null,
                    'title' => $this->l('Save'),
                )
            )
        );
    }
    
    public function getConfigFieldsValues()
    {
        $values = array();
        foreach ($this->getForms() as $model) {
            $model->loadFromConfig();
            $model->populate();
            foreach ($model->getAttributes() as $attr => $value) {
                $values[$model->getConfigAttribueName($attr)] = $value;
            }
        }
        return $values;
    }
    
    public function render($template, $params = array())
    {
        $this->smarty->assign($params);
        return $this->display(__FILE__, $template);
    }
    
    public function is15()
    {
        if ((version_compare(_PS_VERSION_, '1.5.0', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.6.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function is16()
    {
        if ((version_compare(_PS_VERSION_, '1.6.0', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.7.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function is17()
    {
        if ((version_compare(_PS_VERSION_, '1.7.0', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.8.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function getAjaxUrl()
    {
        return __PS_BASE_URI__;
        return Context::getContext()->link->getModuleLink('azleasyssl', 'ajax');
    }
    
    public function getPath($abs = true)
    {
        if ($abs) {
            return pathinfo(__FILE__, PATHINFO_DIRNAME) . '/';
        }
        return $this->_path;
    }
    
    public function getModuleBaseUrl()
    {
        return Tools::getShopDomainSsl(true, true).__PS_BASE_URI__ . 'modules/' . $this->name . '/';
    }
}
