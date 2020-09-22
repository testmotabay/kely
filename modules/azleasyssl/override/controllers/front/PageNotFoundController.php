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

class PageNotFoundController extends PageNotFoundControllerCore
{
    public function init()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, '.well-known/acme-challenge') !== false) {
            if (Module::isInstalled('azleasyssl')) {
                $data = explode('/', $uri);
                $file = $data[3];
                $module = Module::getInstanceByName('azleasyssl');
                $path = $module->getPath() . '.well-known/' . $file;
                if (file_exists($path)) {
                    $content = Tools::file_get_contents($path);
                    header('HTTP/1.1 200 OK');
                    header('Status: 200 OK');
                    die($content);
                }
            }
        }
        parent::init();
    }
}
