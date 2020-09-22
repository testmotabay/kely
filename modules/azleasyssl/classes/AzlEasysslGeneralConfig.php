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

include_once dirname(__FILE__).'/AzlEasySSLModel.php';

/**
 * @property AzlEasyssl $module
 */

class AzlEasysslGeneralConfig extends AzlEasySSLModel
{
    public $alert;
    public $ssl;
    public $ssl_everywhere;
    public $force_ssl;
    public $ssl_redirect;
    
    public function loadFromConfig()
    {
        parent::loadFromConfig();
        $this->ssl = Configuration::get('PS_SSL_ENABLED');
        $this->ssl_everywhere = Configuration::get('PS_SSL_ENABLED_EVERYWHERE');
    }
    
    public function rules()
    {
        return array(
            array(
                array(
                    'alert', 'ssl', 'ssl_everywhere', 'force_ssl', 'ssl_redirect'
                ), 'safe'
            )
        );
    }
    
    public function attributeTypes()
    {
        return array(
            'alert' => 'html',
            'ssl' => 'switch',
            'ssl_everywhere' => 'switch',
            'force_ssl' => 'switch',
            'ssl_redirect' => 'switch'
        );
    }
    
    public function htmlFields()
    {
        return array(
            'alert' => $this->module->render('_partials/_alert.tpl', array(
                'message' => $this->l('Please use these settings only after installing SSL certificate.', 'AzlEasysslGeneralConfig')
            ))
        );
    }
    
    public function attributeLabels()
    {
        return array(
            'alert' => '',
            'ssl' => $this->l('Enable SSL', 'AzlEasysslGeneralConfig'),
            'ssl_everywhere' => $this->l('Enable SSL on all pages', 'AzlEasysslGeneralConfig'),
            'force_ssl' => $this->l('Force SSL', 'AzlEasysslGeneralConfig'),
            'ssl_redirect' => $this->l('Redirect from HTTP to HTTPS', 'AzlEasysslGeneralConfig'),
        );
    }
    
    public function attributeDescriptions()
    {
        return array(
            'ssl' => $this->l('Enable SSL for your shop', 'AzlEasysslGeneralConfig'),
            'ssl_everywhere' => $this->l('When enabled, all the pages of your shop will be SSL-secured.', 'AzlEasysslGeneralConfig'),
            'force_ssl' => $this->l('This option adds "SetEnv HTTPS on" to .htaccess file', 'AzlEasysslGeneralConfig')
        );
    }
    
    public function getFormTitle()
    {
        return $this->l('Shop configuration', 'AzlEasysslGeneralConfig');
    }
}
