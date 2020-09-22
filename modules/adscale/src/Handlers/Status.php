<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\Handlers;

use AdScale\App;
use AdScale\Helpers\Helper;
use AdScale\Helpers\Logger;
use Configuration;
use ConfigurationTest;
use Context;
use Module;
use Tools;
use WebserviceKey;

class Status
{
    
    
    public static function renderDataToJson(array $data)
    {
        return json_encode($data);
    }
    
    
    
    
    public static function renderDataToHtml(array $data)
    {
        
        ob_start();
        ?>
        <style>
            .adscale-debug {
                font-family: Arial, Helvetica, sans-serif;
            }
            
            .adscale-debug h3 {
                font-size: 20px;
                margin-top: 40px;
            }
            
            .adscale-debug-panel-desc {
                font-size: 16px;
            }
            
            .adscale-debug-table {
                background: #fff;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
                border-spacing: 0;
                width: 100%;
                clear: both;
                margin: 0;
            }
            
            .adscale-debug-table * {
                word-wrap: break-word;
            }
            
            .adscale-debug-table > tbody > :nth-child(odd) {
                background-color: #eef8fd;
            }
            
            .adscale-debug-table td {
                width: 35%;
                vertical-align: top;
                font-size: 14px;
                line-height: 1.5em;
            }
            
            .adscale-debug-table td:first-child {
                width: 25%;
            }
            
            .adscale-debug-table td:last-child {
                width: 40%;
            }
            
            .adscale-debug-table td, .adscale-debug-table th {
                padding: 8px 10px;
                color: #555;
            }
        </style>
        
        <div class="adscale-debug">
            <?php
            
            foreach ($data as $section => $details) {
                if (! isset($details['fields']) || empty($details['fields'])) {
                    continue;
                }
                ?>
                
                <h3 class="adscale-debug-heading">
                    <?php echo Tools::safeOutput($details['label']); ?>
                    <?php
                    if (isset($details['show_count']) && $details['show_count']) {
                        printf('(%d)', count($details['fields']));
                    }
                    ?>
                </h3>
                
                <div class="adscale-debug-panel adscale-debug-panel-<?php echo Tools::safeOutput($section); ?>">
                    <?php
                    
                    if (isset($details['description']) && ! empty($details['description'])) {
                        printf('<p class="adscale-debug-panel-desc">%s</p>', $details['description']);
                    }
                    
                    ?>
                    <table class="adscale-debug-table">
                        <tbody>
                        <?php
                        
                        foreach ($details['fields'] as $field) {
                            $label = ! empty($field['label']) ? Tools::safeOutput($field['label']) : '';
                            
                            if (is_array($field['value'])) {
                                $values = '<ul>';
                                
                                foreach ($field['value'] as $name => $value) {
                                    $values .= sprintf(
                                        '<li>%s: %s</li>',
                                        Tools::safeOutput($name),
                                        Tools::safeOutput($value)
                                    );
                                }
                                
                                $values .= '</ul>';
                            } else {
                                $values = ! empty($field['value']) ? Tools::safeOutput($field['value']) : '';
                            }
                            
                            $note = ! empty($field['note']) ? Tools::safeOutput($field['note']) : '';
                            
                            printf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>', $label, $values, $note);
                        }
                        
                        ?>
                        </tbody>
                    </table>
                </div>
                <?php
            } ?>
        </div>
        
        <?php
        return ob_get_clean();
    }
    
    
    
    
    public static function getStatusData()
    {
        // Common vars
        $not_available = 'Not available';
        /** @var $context Context */
        $context = App::instance()->getContext();
        
        // Set up the array that holds all debug information.
        $info = [];
        
        /*
         * Data array structure
         *
         * (string) label       - The title for this section of the debug output.
         * (string) description - Optional. A description for your information section.
         * (boolean) show_count - Optional. If set to `true` the amount of fields will be included in the title for
         *                                 this section.
         * (array) fields {
         *         An associative array containing the data to be displayed.
         *
         *    (string) label - The label for this piece of information.
         *    (string) value - The output that is displayed for this field. Can be
         *                       an associative array that is displayed as name/value pairs.
         *    (mixed) debug  - Optional. The raw data value for this field(genuine value).
         *                       If not set, the content of `value` is used.
         *    (string) note -  Optional. Some additional information. Warnings, notes, errors etc.
         * }
         */
        
        /*
        |--------------------------------------------------------------------------
        | Sections init
        |--------------------------------------------------------------------------
        */
        
        $info['general'] = [
            'label'  => 'General',
            'fields' => [],
        ];
        
        $info['server'] = [
            'label'       => 'Server',
            'description' => 'The options shown below relate to server setup.',
            'fields'      => [],
        ];
        
        $info['adscale-plugin'] = [
            'label'  => 'Adscale plugin',
            'fields' => [],
        ];
        
        $info['ps-shop-api'] = [
            'label'  => 'Shop API',
            'fields' => [],
        ];
        
        $info['ps-system-test'] = [
            'label'  => 'System test',
            'fields' => [],
        ];
        
        $info['ps-system-test-required'] = [
            'label'  => 'System test (required conditions)',
            'fields' => [],
        ];
        
        $info['ps-system-test-optional'] = [
            'label'  => 'System test (optional conditions)',
            'fields' => [],
        ];
        
        $info['ps-active-theme'] = [
            'label'  => 'Active Theme',
            'fields' => [],
        ];
        
        $info['ps-modules-active'] = [
            'label'      => 'Active Modules',
            'show_count' => true,
            'fields'     => [],
        ];
        
        $info['ps-modules-inactive'] = [
            'label'      => 'Inactive Modules',
            'show_count' => true,
            'fields'     => [],
        ];
        
        $info['ps-constants'] = [
            'label'  => 'Main constants',
            'fields' => [],
        ];
        
        /*
        |--------------------------------------------------------------------------
        | Section: general
        |--------------------------------------------------------------------------
        */
        $cms_email          = Configuration::get('PS_SHOP_EMAIL');
        $cms_urls_rewriting = (bool)Configuration::get('PS_REWRITING_SETTINGS');
        
        $info['general']['fields'] = [
            'platform'         => [
                'label' => 'Platform',
                'value' => 'Prestashop',
                'debug' => 'prestashop',
            ],
            'platform_version' => [
                'label' => 'Platform version',
                'value' => _PS_VERSION_,
            ],
            'site_url'         => [
                'label' => 'Site URL',
                'value' => Tools::getShopDomainSsl(true),
            ],
            'site_path'        => [
                'label' => 'Site path',
                'value' => _PS_ROOT_DIR_,
            ],
            'cms_email'          => [
                'label' => 'Email',
                'value' => $cms_email,
            ],
            'cms_urls_rewriting' => [
                'label' => 'Urls rewriting (Friendly URL)',
                'value' => $cms_urls_rewriting ? 'Enabled' : 'Disabled',
                'debug' => $cms_urls_rewriting,
                'note'  => ($cms_urls_rewriting
                    ? ''
                    : 'Attention!'
                ),
            ],
        ];
        
        /*
        |--------------------------------------------------------------------------
        | Section: server
        |--------------------------------------------------------------------------
        */
        
        // Populate the server debug fields.
        if (function_exists('php_uname')) {
            $server_architecture = sprintf('%s %s %s', php_uname('s'), php_uname('r'), php_uname('m'));
        } else {
            $server_architecture = 'unknown';
        }
        
        if (function_exists('phpversion')) {
            $php_version_debug = phpversion();
            // Whether PHP supports 64bit
            $php64bit = (PHP_INT_SIZE * 8 === 64);
            
            $php_version = sprintf(
                '%s %s',
                $php_version_debug,
                ($php64bit ? '(Supports 64bit values)' : '(Does not support 64bit values)')
            );
            
            if ($php64bit) {
                $php_version_debug .= ' 64bit';
            }
        } else {
            $php_version       = 'Unable to determine PHP version';
            $php_version_debug = 'unknown';
        }
        
        if (function_exists('php_sapi_name')) {
            $php_sapi                      = php_sapi_name();
            $php_sapi_is_cgi_subtype_debug = stripos($php_sapi, 'cgi') !== false;
            $php_sapi_is_cgi_subtype       = $php_sapi_is_cgi_subtype_debug ? 'Yes' : 'No';
        } else {
            $php_sapi                      = 'unknown';
            $php_sapi_is_cgi_subtype_debug = 'unknown';
            $php_sapi_is_cgi_subtype       = 'unknown';
        }
        
        if (function_exists('\json_decode') && function_exists('\json_last_error')) {
            $json_extension_debug = true;
            $json_extension_note  = '';
        } else {
            $json_extension_debug = false;
            $json_extension_note  = 'Attention! Adscale plugin does not work without JSON extension!';
        }
        
        $info['server']['fields']['server_architecture']     = [
            'label' => 'Server architecture',
            'value' => (
            'unknown' !== $server_architecture
                ? $server_architecture
                : 'Unable to determine server architecture'
            ),
            'debug' => $server_architecture,
        ];
        $info['server']['fields']['httpd_software']          = [
            'label' => 'Web server',
            'value' => (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE']
                : 'Unable to determine what web server software is used'),
            'debug' => (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown'),
        ];
        $info['server']['fields']['php_version']             = [
            'label' => 'PHP version',
            'value' => $php_version,
            'debug' => $php_version_debug,
        ];
        $info['server']['fields']['php_sapi']                = [
            'label' => 'PHP Server API (SAPI)',
            'value' => ('unknown' !== $php_sapi ? $php_sapi : 'Unable to determine PHP SAPI'),
            'debug' => $php_sapi,
        ];
        $info['server']['fields']['php_sapi_is_cgi_subtype'] = [
            'label' => 'PHP Server API is subtype of CGI',
            'value' => (
            'unknown' !== $php_sapi_is_cgi_subtype
                ? $php_sapi_is_cgi_subtype
                : 'Unable to determine PHP SAPI'
            ),
            'debug' => $php_sapi_is_cgi_subtype_debug,
        ];
        
        // Some servers disable `ini_set()` and `ini_get()`, we check this before trying to get configuration values.
        if (! function_exists('ini_get')) {
            $info['server']['fields']['ini_get'] = [
                'label' => 'Server settings',
                'value' => sprintf(
                    'Unable to determine some settings, as the %s function has been disabled.',
                    'ini_get()'
                ),
                'debug' => 'ini_get() is disabled',
            ];
        } else {
            $info['server']['fields']['max_input_variables'] = [
                'label' => 'PHP max input variables',
                'value' => ini_get('max_input_vars'),
            ];
            $info['server']['fields']['time_limit']          = [
                'label' => 'PHP time limit',
                'value' => ini_get('max_execution_time'),
            ];
            $info['server']['fields']['memory_limit']        = [
                'label' => 'PHP memory limit',
                'value' => ini_get('memory_limit'),
            ];
            $info['server']['fields']['max_input_time']      = [
                'label' => 'Max input time',
                'value' => ini_get('max_input_time'),
            ];
            $info['server']['fields']['upload_max_size']     = [
                'label' => 'Upload max filesize',
                'value' => ini_get('upload_max_filesize'),
            ];
            $info['server']['fields']['php_post_max_size']   = [
                'label' => 'PHP post max size',
                'value' => ini_get('post_max_size'),
            ];
        }
        
        if (function_exists('curl_version')) {
            $curl = curl_version();
            
            $info['server']['fields']['curl_version'] = [
                'label' => 'cURL version',
                'value' => sprintf('%s %s', $curl['version'], $curl['ssl_version']),
            ];
        } else {
            $info['server']['fields']['curl_version'] = [
                'label' => 'cURL version',
                'value' => $not_available,
                'debug' => 'not available',
            ];
        }
        
        $info['server']['fields']['json_extension'] = [
            'label' => 'JSON extension',
            'value' => $json_extension_debug ? 'Enabled' : 'Disabled',
            'debug' => $json_extension_debug,
        ];
        
        if ($json_extension_note) {
            $info['server']['fields']['json_extension']['note'] = $json_extension_note;
        }
        
        
        if (function_exists('apache_get_modules')) {
            $apache_modules       = apache_get_modules();
            $mod_auth_basic_debug = in_array('mod_auth_basic', $apache_modules, true);
            $mod_rewrite_debug    = in_array('mod_rewrite', $apache_modules, true);
            
            $info['server']['fields']['apache_get_modules'] = [
                'label' => 'Apache module check available',
                'value' => 'Yes',
                'debug' => true,
            ];
            
            $info['server']['fields']['mod_auth_basic'] = [
                'label' => 'Apache module check: mod_auth_basic',
                'value' => $mod_auth_basic_debug ? 'Enabled' : 'Disabled',
                'debug' => $mod_auth_basic_debug,
                'note'  => ($mod_auth_basic_debug
                    ? ''
                    : 'Attention! Please activate the "mod_auth_basic" Apache module
                     to allow authentication of Shop API.'
                ),
            ];
            
            $info['server']['fields']['mod_rewrite'] = [
                'label' => 'Apache module check: mod_rewrite',
                'value' => $mod_rewrite_debug ? 'Enabled' : 'Disabled',
                'debug' => $mod_rewrite_debug,
                'note'  => ($mod_rewrite_debug
                    ? ''
                    : 'Attention! Please activate the "mod_rewrite" Apache module
                     to allow the Shop API.'
                ),
            ];
        } else {
            $info['server']['fields']['apache_get_modules'] = [
                'label' => 'Apache module check available',
                'value' => 'No',
                'debug' => false,
                'note'  => 'Attention! Impossible to check to see if basic authentication and rewrite extensions 
                  have been activated. Please manually check if they\'ve been activated in order to use the
                  Shop API.',
            ];
        }
        
        /*
        |--------------------------------------------------------------------------
        | Section: adscale-plugin
        |--------------------------------------------------------------------------
        */
        
        $shopHost               = Helper::getShopDomain();
        $googleVerificationCode = Helper::getGSVCode();
        
        $info['adscale-plugin']['fields'] = [
            'internal_version'       => [
                'label' => 'Internal version',
                'value' => (defined('ADSCALE_INTERNAL_MODULE_VERSION')
                    ? ADSCALE_INTERNAL_MODULE_VERSION : 'Undefined'),
                'debug' => (defined('ADSCALE_INTERNAL_MODULE_VERSION')
                    ? ADSCALE_INTERNAL_MODULE_VERSION : 'undefined'),
            ],
            'shopHost'               => [
                'label' => 'shopHost',
                'value' => $shopHost,
                'note'  => $shopHost ? '' : 'Attention! Empty property shopHost',
            ],
            'googleVerificationCode' => [
                'label' => 'googleVerificationCode',
                'value' => $googleVerificationCode,
                'note'  => $googleVerificationCode ? '' : 'Attention! Empty property googleVerificationCode',
            ],
        ];
        
        /*
        |--------------------------------------------------------------------------
        | Section: ps-shop-api
        |--------------------------------------------------------------------------
        */
        
        // webservice
        $webservice_debug = Configuration::get('PS_WEBSERVICE');
        $webservice       = $webservice_debug ? 'Enabled' : 'Disabled';
        
        // webservice_cgi_host
        $webservice_cgi_host_debug = Configuration::get('PS_WEBSERVICE_CGI_HOST');
        $webservice_cgi_host       = $webservice_cgi_host_debug ? 'Enabled' : 'Disabled';
        if ($php_sapi_is_cgi_subtype_debug && ! $webservice_cgi_host_debug) {
            $webservice_cgi_host_note = 'Attention! Detected PHP Server API is subtype of CGI. 
				May be the reason for the lack of access to API.
				Solution: enable the option "Advanced parameters > Webservice > Enable CGI mode for PHP"';
        } else {
            $webservice_cgi_host_note = '';
        }
        
        // webservice_adscale_key
        $webservice_adscale_key             = '';
        $webservice_adscale_key_desc        = '';
        $webservice_adscale_key_state_debug = '';
        $webservice_adscale_key_obj         = ShopKeys::getAccessKeyExisted();
        
        if ($webservice_adscale_key_obj && $webservice_adscale_key_obj->key) {
            $webservice_adscale_key             = $webservice_adscale_key_obj->key;
            $webservice_adscale_key_desc        = $webservice_adscale_key_obj->description;
            $webservice_adscale_key_state_debug = WebserviceKey::isKeyActive($webservice_adscale_key);
        }
        
        $info['ps-shop-api']['fields'] = [
            'webservice'             => [
                'label' => 'Webservice',
                'value' => $webservice,
                'debug' => $webservice_debug,
                'note'  => $webservice_debug ? '' : 'Attention! The Webservice must be enable for access',
            ],
            'webservice_cgi_host'    => [
                'label' => 'CGI mode for PHP',
                'value' => $webservice_cgi_host,
                'debug' => $webservice_cgi_host_debug,
                'note'  => $webservice_cgi_host_note,
            ],
            'webservice_adscale_key' => [
                'label' => 'Webservice API Key for Adscale',
                'value' => $webservice_adscale_key,
                'note'  => $webservice_adscale_key
                    ? $webservice_adscale_key_desc
                    : 'Attention! There is no key for access',
            ],
        ];
        
        if ($webservice_adscale_key) {
            $info['ps-shop-api']['fields']['webservice_adscale_key_state'] = [
                'label' => 'Webservice API Key for Adscale state',
                'value' => $webservice_adscale_key_state_debug ? 'Enabled' : 'Disabled',
                'debug' => $webservice_adscale_key_state_debug,
                'note'  => $webservice_adscale_key_state_debug ? '' : 'Attention! The key must be enable for access',
            ];
        }
        
        /*
        |--------------------------------------------------------------------------
        | Section: ps-system-test
        |--------------------------------------------------------------------------
        */
        
        $canSystemTest = self::canSystemTest();
        
        $info['ps-system-test']['fields']['system_test_available'] = [
            'label' => 'System test available',
            'value' => $canSystemTest ? 'Yes' : 'No',
            'debug' => $canSystemTest,
        ];
        
        if ($canSystemTest) {
            $systemTestResult = self::getSystemSummary();
            //echo '<pre>'; echo var_export($systemTestResult, true); echo '</pre>';  die();
    
            $failRequired = isset($systemTestResult['failRequired']) ? $systemTestResult['failRequired'] : null;
            $failOptional = isset($systemTestResult['failOptional']) ? $systemTestResult['failOptional'] : null;
    
            $info['ps-system-test']['fields']['system_test_required'] = [
                'label' => 'System test (required conditions) ',
                'value' => $failRequired === null ? '(unknown)' : (! $failRequired ? 'OK' : 'Fail'),
                'debug' => $failRequired === null ? '(unknown)' : ! $failRequired,
            ];
    
            $info['ps-system-test']['fields']['system_test_optional'] = [
                'label' => 'System test (optional conditions) ',
                'value' => $failOptional === null ? '(unknown)' : (! $failOptional ? 'OK' : 'Fail'),
                'debug' => $failOptional === null ? '(unknown)' : ! $failOptional,
            ];
        }
        
        
        /*
        |--------------------------------------------------------------------------
        | Section: ps-system-test-required 
        | Section: ps-system-test-optional
        | (depends on  Section: ps-system-test)
        |--------------------------------------------------------------------------
        */
        
        $systemTestsMessages = ! empty($systemTestResult['testsMessages']) ? $systemTestResult['testsMessages'] : [];
        if (! empty($systemTestResult['testsRequired']) && is_array($systemTestResult['testsRequired'])) {
            foreach ($systemTestResult['testsRequired'] as $name => $result) {
                $field = [
                    'label' => $systemTestsMessages[$name]['title'],
                    'value' => $result,
                ];
                if ($result === 'fail') {
                    $field['note'] = $systemTestsMessages[$name]['err_msg'];
                }
        
                $info['ps-system-test-required']['fields'][$name] = $field;
            }
        }
        
        
        if (! empty($systemTestResult['testsOptional']) && is_array($systemTestResult['testsOptional'])) {
            foreach ($systemTestResult['testsOptional'] as $name => $result) {
                $field = [
                    'label' => $systemTestsMessages[$name]['title'],
                    'value' => $result,
                ];
                if ($result === 'fail') {
                    $field['note'] = $systemTestsMessages[$name]['err_msg'];
                }
        
                $info['ps-system-test-optional']['fields'][$name] = $field;
            }
        }
        
        /*
        |--------------------------------------------------------------------------
        | Section: ps-active-theme
        |--------------------------------------------------------------------------
        */
        
        $active_theme_name = 'unknown';
        if (method_exists($context->shop, 'getName')) {
            $active_theme_name = $context->shop->getName();
        } elseif (! empty($context->shop->theme_name)) {
            $active_theme_name = $context->shop->theme_name; // legacy
        }
        
        $active_theme_path = 'unknown';
        if (! empty($context->shop->theme) && method_exists($context->shop->theme, 'getDirectory')) {
            $active_theme_path = $context->shop->theme->getDirectory();
        } elseif (! empty($context->shop->theme_directory)) { // legacy
            $active_theme_path = _PS_ALL_THEMES_DIR_ . $context->shop->theme_directory;
        }
        
        $info['ps-active-theme']['fields'] = [
            'name'       => [
                'label' => 'Name',
                'value' => $active_theme_name,
            ],
            'theme_path' => [
                'label' => 'Theme directory location',
                'value' => $active_theme_path,
            ],
        ];
        
        /*
        |--------------------------------------------------------------------------
        | Section: ps-modules-active
        | Section: ps-modules-inactive
        |--------------------------------------------------------------------------
        */
        
        // List all available modules.
        $modules = $modules = Module::getModulesOnDisk(true);
        //echo '<pre>'; echo print_r($modules, true); echo '</pre>';  die();
        
        foreach ($modules as $module) {
            $module_part = '';
            if ($module->id) { // installed or not (not by field installed, dont work on 1.6)
                $module_part = $module->active ? 'ps-modules-active' : 'ps-modules-inactive';
            }
            if (! $module_part) {
                continue;
            }
    
    
            $module_name         = $module->name;
            $module_display_name = ! empty($module->displayName) ? $module->displayName : '';
            //$module_type             = ! empty($module->type) ? $module->type : '';
            $module_author  = ! empty($module->author) ? $module->author : '';
            $module_version = ! empty($module->version) ? $module->version : '';
            //$module_description_full = ! empty($module->description_full) ? $module->description_full : '';
    
            $module_version_string       = 'No version or author information is available.';
            $module_version_string_debug = 'author: (undefined), version: (undefined)';
    
            if (! empty($module_version) && ! empty($module_author)) {
                $module_version_string       = sprintf('Version %1$s by %2$s', $module_version, $module_author);
                $module_version_string_debug = sprintf('version: %s, author: %s', $module_version, $module_author);
            } else {
                if (! empty($module_author)) {
                    $module_version_string       = sprintf('By %s', $module_author);
                    $module_version_string_debug = sprintf('author: %s, version: (undefined)', $module_author);
                }
        
                if (! empty($module_version)) {
                    $module_version_string       = sprintf('Version %s', $module_version);
                    $module_version_string_debug = sprintf('author: (undefined), version: %s', $module_version);
                }
            }
    
            $info[$module_part]['fields'][Tools::safeOutput($module_name)] = [
                'label' => $module_display_name,
                'value' => $module_version_string,
                'debug' => $module_version_string_debug,
            ];
        }
        
        /*
        |--------------------------------------------------------------------------
        | Section: ps-constants
        |--------------------------------------------------------------------------
        */
        
        // Check _PS_DEBUG_PROFILING_
        if (defined('_PS_DEBUG_PROFILING_')) {
            $_ps_debug_profiling_      = _PS_DEBUG_PROFILING_ ? 'Enabled' : 'Disabled';
            $_ps_debug_profiling_debug = _PS_DEBUG_PROFILING_ ? 'true' : 'false';
        } else {
            $_ps_debug_profiling_      = 'Undefined';
            $_ps_debug_profiling_debug = 'undefined';
        }
        
        // Check _PS_DEBUG_SQL_
        if (defined('_PS_DEBUG_SQL_')) {
            $_ps_debug_sql_      = _PS_DEBUG_SQL_ ? 'Enabled' : 'Disabled';
            $_ps_debug_sql_debug = _PS_DEBUG_SQL_ ? 'true' : 'false';
        } else {
            $_ps_debug_sql_      = 'Undefined';
            $_ps_debug_sql_debug = 'undefined';
        }
        
        // Check _PS_MODE_DEMO_
        if (defined('_PS_MODE_DEMO_')) {
            $_ps_mode_demo_      = _PS_MODE_DEMO_ ? 'Enabled' : 'Disabled';
            $_ps_mode_demo_debug = _PS_MODE_DEMO_ ? 'true' : 'false';
        } else {
            $_ps_mode_demo_      = 'Undefined';
            $_ps_mode_demo_debug = 'undefined';
        }
        
        // Check _PS_MODE_DEV_
        if (defined('_PS_MODE_DEV_')) {
            $_ps_mode_dev_      = _PS_MODE_DEV_ ? 'Enabled' : 'Disabled';
            $_ps_mode_dev_debug = _PS_MODE_DEV_ ? 'true' : 'false';
        } else {
            $_ps_mode_dev_      = 'Undefined';
            $_ps_mode_dev_debug = 'undefined';
        }
        
        $info['ps-constants']['fields'] = [
            '_PS_VERSION_'            => [
                'label' => '_PS_VERSION_',
                'value' => (defined('_PS_VERSION_') ? _PS_VERSION_ : 'Undefined'),
                'debug' => (defined('_PS_VERSION_') ? _PS_VERSION_ : 'undefined'),
            ],
            '_PS_ROOT_DIR_'           => [
                'label' => '_PS_ROOT_DIR_',
                'value' => (defined('_PS_ROOT_DIR_') ? _PS_ROOT_DIR_ : 'Undefined'),
                'debug' => (defined('_PS_ROOT_DIR_') ? _PS_ROOT_DIR_ : 'undefined'),
            ],
            '_PS_CORE_DIR_'           => [
                'label' => '_PS_CORE_DIR_',
                'value' => (defined('_PS_CORE_DIR_') ? _PS_CORE_DIR_ : 'Undefined'),
                'debug' => (defined('_PS_CORE_DIR_') ? _PS_CORE_DIR_ : 'undefined'),
            ],
            '_PS_CONFIG_DIR_'         => [
                'label' => '_PS_CONFIG_DIR_',
                'value' => (defined('_PS_CONFIG_DIR_') ? _PS_CONFIG_DIR_ : 'Undefined'),
                'debug' => (defined('_PS_CONFIG_DIR_') ? _PS_CONFIG_DIR_ : 'undefined'),
            ],
            '__PS_BASE_URI__'         => [
                'label' => '__PS_BASE_URI__',
                'value' => (defined('__PS_BASE_URI__') ? __PS_BASE_URI__ : 'Undefined'),
                'debug' => (defined('__PS_BASE_URI__') ? __PS_BASE_URI__ : 'undefined'),
            ],
            '_PS_MODULE_DIR_'         => [
                'label' => '_PS_MODULE_DIR_',
                'value' => (defined('_PS_MODULE_DIR_') ? _PS_MODULE_DIR_ : 'Undefined'),
                'debug' => (defined('_PS_MODULE_DIR_') ? _PS_MODULE_DIR_ : 'undefined'),
            ],
            '_MODULE_DIR_'            => [
                'label' => '_MODULE_DIR_',
                'value' => (defined('_MODULE_DIR_') ? _MODULE_DIR_ : 'Undefined'),
                'debug' => (defined('_MODULE_DIR_') ? _MODULE_DIR_ : 'undefined'),
            ],
            '_PS_HOST_MODE_'          => [
                'label' => '_PS_HOST_MODE_',
                'value' => (defined('_PS_HOST_MODE_') ? 'Defined' : 'Undefined'),
                'debug' => (defined('_PS_HOST_MODE_') ? 'defined' : 'undefined'),
            ],
            '_THEME_NAME_'            => [
                'label' => '_THEME_NAME_',
                'value' => (defined('_THEME_NAME_') ? _THEME_NAME_ : 'Undefined'),
                'debug' => (defined('_THEME_NAME_') ? _THEME_NAME_ : 'undefined'),
            ],
            '_PS_ALL_THEMES_DIR_'     => [
                'label' => '_PS_ALL_THEMES_DIR_',
                'value' => (defined('_PS_ALL_THEMES_DIR_') ? _PS_ALL_THEMES_DIR_ : 'Undefined'),
                'debug' => (defined('_PS_ALL_THEMES_DIR_') ? _PS_ALL_THEMES_DIR_ : 'undefined'),
            ],
            '_PS_JQUERY_VERSION_'     => [
                'label' => '_PS_JQUERY_VERSION_',
                'value' => (defined('_PS_JQUERY_VERSION_') ? _PS_JQUERY_VERSION_ : 'Undefined'),
                'debug' => (defined('_PS_JQUERY_VERSION_') ? _PS_JQUERY_VERSION_ : 'undefined'),
            ],
            '_PS_CUSTOM_CONFIG_FILE_' => [
                'label' => '_PS_CUSTOM_CONFIG_FILE_',
                'value' => (defined('_PS_CUSTOM_CONFIG_FILE_') ? _PS_CUSTOM_CONFIG_FILE_ : 'Undefined'),
                'debug' => (defined('_PS_CUSTOM_CONFIG_FILE_') ? _PS_CUSTOM_CONFIG_FILE_ : 'undefined'),
            ],
            '_PS_DEBUG_PROFILING_'    => [
                'label' => '_PS_DEBUG_PROFILING_',
                'value' => $_ps_debug_profiling_,
                'debug' => $_ps_debug_profiling_debug,
            ],
            '_PS_DEBUG_SQL_'          => [
                'label' => '_PS_DEBUG_SQL_',
                'value' => $_ps_debug_sql_,
                'debug' => $_ps_debug_sql_debug,
            ],
            '_PS_MODE_DEMO_'          => [
                'label' => '_PS_MODE_DEMO_',
                'value' => $_ps_mode_demo_,
                'debug' => $_ps_mode_demo_debug,
            ],
            '_PS_MODE_DEV_'           => [
                'label' => '_PS_MODE_DEV_',
                'value' => $_ps_mode_dev_,
                'debug' => $_ps_mode_dev_debug,
            ],

        ];
        
        return $info;
    }
    
    
    
    public static function canSystemTest()
    {
        return is_callable('ConfigurationTest::check')
               &&
               is_callable('ConfigurationTest::getDefaultTests')
               &&
               is_callable('ConfigurationTest::getDefaultTestsOp')
               &&
               is_callable('ConfigurationTest::test_files');
    }
    
    
    
    /**
     * Returns a summary of all system requirements.
     *
     * @return array
     */
    public static function getSystemSummary()
    {
        if (! self::canSystemTest()) {
            return [];
        }
        
        $paramsRequiredResults = ConfigurationTest::check(ConfigurationTest::getDefaultTests());
        
        $isHostMode = defined('_PS_HOST_MODE_');
        
        if (! $isHostMode) {
            $paramsOptionalResults = ConfigurationTest::check(ConfigurationTest::getDefaultTestsOp());
        }
        
        $failRequired = in_array('fail', $paramsRequiredResults);
        
        $testsMessages = self::getSystemMessages();
        
        if ($failRequired && 'ok' !== $paramsRequiredResults['files']) {
            $tmp = ConfigurationTest::test_files(true);
            if (is_array($tmp) && count($tmp)) {
                $testsMessages['files']['err_msg'] .= ' (' . implode(', ', $tmp) . ')';
            }
        }
        
        $testsMessages = self::fillMissingDescriptions($testsMessages, $paramsRequiredResults);
        
        $results = [
            'failRequired'  => $failRequired,
            'testsMessages' => $testsMessages,
            'testsRequired' => $paramsRequiredResults,
        ];
        
        if (! $isHostMode) {
            $paramsOptionalResultsWithoutRequired = array_diff_assoc($paramsOptionalResults, $paramsRequiredResults);
            
            $results = array_merge($results, [
                'testsMessages' => self::fillMissingDescriptions($testsMessages, $paramsOptionalResults),
                'failOptional'  => in_array('fail', $paramsOptionalResultsWithoutRequired),
                'testsOptional' => $paramsOptionalResultsWithoutRequired,
            ]);
        }
        
        return $results;
    }
    
    
    
    /**
     * @return array
     */
    public static function getSystemMessages()
    {
        return [
            // 1.6 too
            'phpversion'                => [
                'title'   => 'PHP version',
                'err_msg' => 'Update your PHP version.',
            ],
            // 1.6 too
            'upload'                    => [
                'title'   => 'Allow file uploads',
                'err_msg' => 'Configure your server to allow file uploads.',
            ],
            // 1.6 too
            'system'                    => [
                'title'   => 'System functions',
                'err_msg' => 'Configure your server to allow the creation of directories and files
							with write permissions.',
            ],
            'curl'                      => [
                'title'   => 'CURL extension',
                'err_msg' => 'Enable the CURL extension on your server.',
            ],
            'dom'                       => [
                'title'   => 'DOM extension',
                'err_msg' => 'Enable the DOM extension on your server.',
            ],
            'fileinfo'                  => [
                'title'   => 'Fileinfo extension',
                'err_msg' => 'Enable the Fileinfo extension on your server.',
            ],
            // 1.6 too
            'gd'                        => [
                'title'   => 'GD library',
                'err_msg' => 'Enable the GD library on your server.',
            ],
            'json'                      => [
                'title'   => 'JSON extension',
                'err_msg' => 'Enable the JSON extension on your server.',
            ],
            'mbstring'                  => [
                'title'   => 'Mbstring extension',
                'err_msg' => 'Enable the Mbstring extension on your server.',
            ],
            'openssl'                   => [
                'title'   => 'OpenSSL extension',
                'err_msg' => 'Enable the OpenSSL extension on your server.',
            ],
            'pdo_mysql'                 => [
                'title'   => 'PDO Mysql extension',
                'err_msg' => 'Enable the PDO Mysql extension on your server.',
            ],
            'simplexml'                 => [
                'title'   => 'Simple XML extension',
                'err_msg' => 'Enable the Simple XML extension on your server.',
            ],
            'zip'                       => [
                'title'   => 'ZIP extension',
                'err_msg' => 'Enable the ZIP extension on your server.',
            ],
            'intl'                      => [
                'title'   => 'Intl extension',
                'err_msg' => 'Enable the Intl extension on your server.',
            ],
            // 1.6 too
            'mysql_support'             => [
                'title'   => 'MySQL support',
                'err_msg' => 'Enable the MySQL support on your server.',
            ],
            // 1.6 too
            'config_dir'                => [
                'title'   => '"config" folder',
                'err_msg' => 'Set write permissions for the "config" folder.',
            ],
            // 1.6 too
            'cache_dir'                 => [
                'title'   => '"cache" folder',
                'err_msg' => 'Set write permissions for the "cache" folder.',
            ],
            // 1.6 too
            'sitemap'                   => [
                'title'   => '"sitemap.xml" file',
                'err_msg' => 'Set write permissions for the "sitemap.xml" file.',
            ],
            // 1.6 too
            'img_dir'                   => [
                'title'   => '"img" folder',
                'err_msg' => 'Set write permissions for the "img" folder and subfolders.',
            ],
            // 1.6 too
            'log_dir'                   => [
                'title'   => '"log" folder',
                'err_msg' => 'Set write permissions for the "log" folder and subfolders.',
            ],
            // 1.6 too
            'mails_dir'                 => [
                'title'   => '"mails" folder',
                'err_msg' => 'Set write permissions for the "mails" folder and subfolders.',
            ],
            // 1.6 too
            'module_dir'                => [
                'title'   => '"modules" folder',
                'err_msg' => 'Set write permissions for the "modules" folder and subfolders.',
            ],
            'theme_cache_dir'           => [
                'title'   => sprintf(
                    '"themes/%s/cache/" folder',
                    _THEME_NAME_
                ),
                'err_msg' => sprintf(
                    'Set write permissions for the "themes/%s/cache/" folder and subfolders, recursively.',
                    _THEME_NAME_
                ),
            ],
            // 1.6 too
            'theme_lang_dir'            => [
                'title'   => sprintf(
                    '"themes/%s/lang/" folder',
                    _THEME_NAME_
                ),
                'err_msg' => sprintf(
                    'Set write permissions for the "themes/%s/lang/" folder and subfolders, recursively.',
                    _THEME_NAME_
                ),
            ],
            'theme_pdf_lang_dir'        => [
                'title'   => sprintf(
                    '"themes/%s/pdf/lang/" folder',
                    _THEME_NAME_
                ),
                'err_msg' => sprintf(
                    'Set write permissions for the "themes/%s/pdf/lang/" folder and subfolders, recursively.',
                    _THEME_NAME_
                ),
            ],
            'config_sf2_dir'            => [
                'title'   => '"app/config/" folder',
                'err_msg' => 'Set write permissions for the "app/config/" folder and subfolders, recursively.',
            ],
            'translations_sf2'          => [
                'title'   => '"app/Resources/translations/" folder',
                'err_msg' => 'Set write permissions for the "app/Resources/translations/" folder and subfolders.',
            ],
            // 1.6 too
            'translations_dir'          => [
                'title'   => '"translations" folder',
                'err_msg' => 'Set write permissions for the "translations" folder and subfolders.',
            ],
            // 1.6 too
            'customizable_products_dir' => [
                'title'   => '"upload" folder',
                'err_msg' => 'Set write permissions for the "upload" folder and subfolders.',
            ],
            // 1.6 too
            'virtual_products_dir'      => [
                'title'   => '"download" folder',
                'err_msg' => 'Set write permissions for the "download" folder and subfolders.',
            ],
            // 1.6 too
            'fopen'                     => [
                'title'   => 'PHP fopen() function',
                'err_msg' => 'Allow the PHP fopen() function on your server.',
            ],
            // 1.6 only
            'register_globals'          => [
                'title'   => 'PHP "register_globals" Off',
                'err_msg' => 'Set PHP "register_globals" option to "Off".',
            ],
            // 1.6 too
            'gz'                        => [
                'title'   => 'GZIP compression',
                'err_msg' => 'Enable GZIP compression on your server.',
            ],
            // 1.6 too
            'files'                     => [
                'title'   => 'Some PrestaShop files existence',
                'err_msg' => 'Some PrestaShop files are missing from your server.',
            
            ],
            'new_phpversion'            => [
                'title'   => 'PHP ready for the future',
                'err_msg' => sprintf(
                    'You are using PHP %s version. 
            Soon, the latest PHP version supported by PrestaShop will be PHP 5.6. 
            To make sure you’re ready for the future, we recommend you to upgrade to PHP 5.6 now!',
                    PHP_VERSION
                ),
            ],
            'apache_mod_rewrite'        => [
                'title'   => 'Apache mod_rewrite module',
                'err_msg' => 'Enable the Apache mod_rewrite module',
            ],
        ];
    }
    
    
    
    /**
     * Add default message on missing check descriptions.
     *
     * @param array $errorMessages
     * @param array $checks
     *
     * @return array Error messages with fallback for missing entries
     */
    public static function fillMissingDescriptions($errorMessages, $checks)
    {
        foreach (array_keys(array_diff_key($checks, $errorMessages)) as $key) {
            $errorMessages[$key]['title']   = $key;
            $errorMessages[$key]['err_msg'] = 'Attention!';
        }
        
        return $errorMessages;
    }
}
