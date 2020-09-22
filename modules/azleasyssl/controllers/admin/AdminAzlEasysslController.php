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

include_once dirname(__FILE__).'/../../classes/AzlAcmeApi.php';

class AdminAzlEasysslController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->display = 'view';
        parent::__construct();
        $this->meta_title = $this->l('AZL Easy SSL');
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }
    
    public function ajaxProcessSaveDomains()
    {
        $domain = Tools::getValue('domain');
        $domain = array_unique($domain);
        Configuration::updateValue('AZL_SSL_DOMAINS', implode(',', $domain));
        die(Tools::jsonEncode(array(
            'success' => 1,
            'content' => sprintf($this->module->l('Certificate will be issued for these domains: %s. Click "Next" to continue.'), implode(', ', $domain))
        )));
    }
    
    public function ajaxProcessCheckDomain()
    {
        $domain = Tools::getValue('domain');
        $baseUrl = Tools::getValue('baseUrl');
        $token = Configuration::get('AZL_SSL_TOKEN');
        $url = Context::getContext()->link->getModuleLink('azleasyssl', 'ajax', array(
            'token' => $token
        ));
        $data = Tools::file_get_contents($url);
        $json = Tools::jsonDecode($data);
        if ($json && isset($json->success)) {
            die(Tools::jsonEncode(array(
                'success' => $json->success,
                'url' => $url
            )));
        }
        die(Tools::jsonEncode(array(
            'success' => 0,
            'url' => $url
        )));
    }
    
    public function ajaxProcessNewChallenge()
    {
        $acme = new AzlAcmeApi();
        $email = Tools::getValue('email');
        Configuration::updateValue('AZL_SSL_EMAIL', $email);
        
        $domains = Configuration::get('AZL_SSL_DOMAINS');
        $domains = explode(',', $domains);
        $result = $acme->requestChallenge($email, $domains);
        
        if ((!isset($result->challenge) || empty($result->challenge)) && ((isset($result->valid) && !$result->valid) || !isset($result->valid))) {
            die(Tools::jsonEncode(array(
                'success' => 0,
                'result' => $result
            )));
        }
        if ($result->challenge) {
            foreach ($result->challenge as $challenge) {
                $path = $this->module->getPath();
                if (!is_dir($path.'.well-known')) {
                    mkdir($path.'.well-known', 0755, true);
                }
                $fullPath = $path . '.well-known/' . $challenge->filename;
                $f = fopen($fullPath, 'w');
                $bytes = fwrite($f, $challenge->content);
                fclose($f);
            }
        }
        
        die(Tools::jsonEncode(array(
            'success' => 1,
            'domains' => $domains,
            'email' => $email,
            'challenge' => $result->challenge,
            'dns_challenge' => $result->dns_challenge,
        )));
    }
    
    public function ajaxProcessIssue()
    {
        $data = Tools::getValue('data');
        $acme = new AzlAcmeApi();
        $params = array(
            'email' => $data['email'],
            'domains' => $data['domains']
        );
        
        $res = $acme->issue($params);
        if ($res) {
            if (isset($res->success) && $res->success) {
                $fileName = basename($res->data->zip);
                $dest = $this->module->getPath() . 'crt/' . $fileName;
                if (!is_dir($this->module->getPath() . 'crt/')) {
                    mkdir($this->module->getPath() . 'crt/', 0755, true);
                }
                Tools::copy($res->data->zip, $dest);
                $res->data->zip = $this->module->getModuleBaseUrl() . 'crt/' . $fileName;
                
                Configuration::updateValue('AZL_SSL_CERT', $res->data->certificate);
                Configuration::updateValue('AZL_SSL_CHAIN', $res->data->chain);
                Configuration::updateValue('AZL_SSL_FULLCHAIN', $res->data->fullchain_certificate);
                //Configuration::updateValue('AZL_SSL_CSR', $res->last_csr);
                Configuration::updateValue('AZL_SSL_PRIVATE', $res->data->private_key);
                Configuration::updateValue('AZL_SSL_PUBLIC', $res->data->public_key);
                Configuration::updateValue('AZL_SSL_ZIP', $res->data->zip);
                Configuration::updateValue('AZL_SSL_TS', time());
            }
            die(Tools::jsonEncode($res));
        }
        die(Tools::jsonEncode(array(
            'success' => 0,
            'res' => $res,
            'url' => $acme->getLastUrl()
        )));
    }
    
    public function ajaxProcessIssued()
    {
        die(Tools::jsonEncode(array(
            'success' => 1,
            'certificate' => Configuration::get('AZL_SSL_CERT'),
            'chain' => Configuration::get('AZL_SSL_CHAIN'),
            'fullchain_certificate' => Configuration::get('AZL_SSL_FULLCHAIN'),
            'last_csr' => Configuration::get('AZL_SSL_CSR'),
            'private_key' => Configuration::get('AZL_SSL_PRIVATE'),
            'public_key' => Configuration::get('AZL_SSL_PUBLIC'),
            'zip' => Configuration::get('AZL_SSL_ZIP'),
            'ts' => Configuration::get('AZL_SSL_TS')
        )));
    }
}
