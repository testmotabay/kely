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

class AzlAcmeApi
{
    const BASE_URL = 'https://acme.areama.net/';
    const REQUEST_GET = 'GET';
    const REQUEST_POST = 'POST';
    
    private $lastUrl;


    public function issue($params)
    {
        return $this->sendRequest('api/v2/issue', self::REQUEST_POST, $params);
    }
    
    public function requestChallenge($email, $domain)
    {
        $domain = (array)$domain;
        return $this->sendRequest('api/v2/new-challenge', self::REQUEST_POST, array(
            'email' => $email,
            'domains' => (array)$domain
        ));
    }
    
    protected function sendRequest($endpoint, $requestMethod = 'GET', $params = array(), $returnKey = null)
    {
        $url = $this->buildUrl($endpoint, $requestMethod, $params);
        $this->lastUrl = $url;
        //if ($endpoint == 'api/issue'){
        //die($url);
        //}
        $context = null;
        if ($requestMethod === self::REQUEST_GET) {
            $context = stream_context_create(array(
                'http' => array(
                    'method'        => 'GET',
                    'ignore_errors' => true,
                ),
            ));
            $data = Tools::file_get_contents($url, false, $context);
            $json = Tools::jsonDecode($data);
            if ($returnKey === null || !isset($json->$returnKey)) {
                return $json;
            }
            return $json->$returnKey;
        } elseif ($requestMethod === self::REQUEST_POST) {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
                    'ignore_errors' => true,
                    'content' => http_build_query($params),
                ),
            ));
        }
        
        if ($context) {
            $data = Tools::file_get_contents($url, false, $context);
            $json = Tools::jsonDecode($data);
            if ($returnKey === null || !isset($json->$returnKey)) {
                return $json;
            }
            return $json->$returnKey;
        }
        $data = Tools::file_get_contents($url, false);
        $json = Tools::jsonDecode($data);
        if ($returnKey === null || !isset($json->$returnKey)) {
            return $json;
        }
        return $json->$returnKey;
    }


    protected function buildUrl($endpoint, $requestMethod, $urlParams = null)
    {
        if ($urlParams === null) {
            $urlParams = $this->urlParams;
        }
        if ($requestMethod == self::REQUEST_GET) {
            $url = self::BASE_URL . $endpoint . '?' . http_build_query($urlParams);
        } else {
            $url = self::BASE_URL . $endpoint;
        }
        return $url;
    }
    
    public function getLastUrl()
    {
        return $this->lastUrl;
    }
}
