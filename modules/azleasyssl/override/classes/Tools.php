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

class Tools extends ToolsCore
{
    public static function generateHtaccess($path = null, $rewrite_settings = null, $cache_control = null, $specific = '', $disable_multiviews = null, $medias = false, $disable_modsec = null)
    {
        if (Module::isInstalled('azleasyssl') && Module::isEnabled('azleasyssl')) {
            if (defined('PS_INSTALLATION_IN_PROGRESS') && $rewrite_settings === null) {
                return true;
            }

            // Default values for parameters
            if (is_null($path)) {
                $path = _PS_ROOT_DIR_.'/.htaccess';
            }

            if (!is_writable($path)) {
                return false;
            }
            
            if (is_null($cache_control)) {
                $cache_control = (int)Configuration::get('PS_HTACCESS_CACHE_CONTROL');
            }
            if (is_null($disable_multiviews)) {
                $disable_multiviews = (int)Configuration::get('PS_HTACCESS_DISABLE_MULTIVIEWS');
            }

            if ($disable_modsec === null) {
                $disable_modsec = (int)Configuration::get('PS_HTACCESS_DISABLE_MODSEC');
            }

            // Check current content of .htaccess and save all code outside of prestashop comments
            $specific_before = $specific_after = '';
            if (file_exists($path)) {
                $content = self::file_get_contents($path);
                $content = preg_replace('{#~~azleasyssl.*?azleasyssl~~#}is', '', $content);
                $sslContent = '';
                if (Configuration::get('AZLSSL_FORCE_SSL')) {
                    $sslContent .= 'SetEnv HTTPS on' . PHP_EOL;
                }
                if (Configuration::get('AZLSSL_SSL_REDIRECT')) {
                    $sslContent .= 'RewriteEngine on' . PHP_EOL . PHP_EOL;
                    $sslContent .= 'RewriteCond     %{SERVER_PORT} ^80$' . PHP_EOL;
                    $sslContent .= 'RewriteRule     ^(.*)$ https://%{SERVER_NAME}%{REQUEST_URI} [L,R]' . PHP_EOL;
                }
                file_put_contents($path, '#~~azleasyssl##' . PHP_EOL . PHP_EOL . $sslContent . PHP_EOL . '##azleasyssl~~#' . $content);
            }
        }
        return parent::generateHtaccess($path, $rewrite_settings, $cache_control, $specific, $disable_multiviews, $medias, $disable_modsec);
    }
}
