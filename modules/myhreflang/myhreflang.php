<?php

/**
 * PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
 *
 * @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
 * @copyright 2010-2019 VEKIA
 * @license   This program is not free software and you can't resell and redistribute it
 *
 * CONTACT WITH DEVELOPER http://mypresta.eu
 * support@mypresta.eu
 */
class myhreflang extends Module
{
    public function __construct()
    {
        $this->name = 'myhreflang';
        $this->tab = 'seo';
        $this->author = 'MyPresta.eu';
        $this->mypresta_link = 'https://mypresta.eu/modules/seo/free-hreflang-canonical-urls.html';
        $this->version = '1.2.0';
        parent::__construct();
        $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap = true;
        $this->displayName = $this->l('Hreflang');
        $this->description = $this->l('Hreflang module for PrestaShop');
        $this->checkforupdates();
    }

    public function checkforupdates($display_msg = 0, $form = 0)
    {
        // ---------- //
        // ---------- //
        // VERSION 16 //
        // ---------- //
        // ---------- //
        $this->mkey = "nlc";
        if (@file_exists('../modules/' . $this->name . '/key.php')) {
            @require_once('../modules/' . $this->name . '/key.php');
        } else {
            if (@file_exists(dirname(__FILE__) . $this->name . '/key.php')) {
                @require_once(dirname(__FILE__) . $this->name . '/key.php');
            } else {
                if (@file_exists('modules/' . $this->name . '/key.php')) {
                    @require_once('modules/' . $this->name . '/key.php');
                }
            }
        }
        if ($form == 1) {
            return '
            <div class="panel" id="fieldset_myprestaupdates" style="margin-top:20px;">
            ' . ($this->psversion() == 6 || $this->psversion() == 7 ? '<div class="panel-heading"><i class="icon-wrench"></i> ' . $this->l('MyPresta updates') . '</div>' : '') . '
			<div class="form-wrapper" style="padding:0px!important;">
            <div id="module_block_settings">
                    <fieldset id="fieldset_module_block_settings">
                         ' . ($this->psversion() == 5 ? '<legend style="">' . $this->l('MyPresta updates') . '</legend>' : '') . '
                        <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                            <label>' . $this->l('Check updates') . '</label>
                            <div class="margin-form">' . (Tools::isSubmit('submit_settings_updates_now') ? ($this->inconsistency(0) ? '' : '') . $this->checkforupdates(1) : '') . '
                                <button style="margin: 0px; top: -3px; position: relative;" type="submit" name="submit_settings_updates_now" class="button btn btn-default" />
                                <i class="process-icon-update"></i>
                                ' . $this->l('Check now') . '
                                </button>
                            </div>
                            <label>' . $this->l('Updates notifications') . '</label>
                            <div class="margin-form">
                                <select name="mypresta_updates">
                                    <option value="-">' . $this->l('-- select --') . '</option>
                                    <option value="1" ' . ((int)(Configuration::get('mypresta_updates') == 1) ? 'selected="selected"' : '') . '>' . $this->l('Enable') . '</option>
                                    <option value="0" ' . ((int)(Configuration::get('mypresta_updates') == 0) ? 'selected="selected"' : '') . '>' . $this->l('Disable') . '</option>
                                </select>
                                <p class="clear">' . $this->l('Turn this option on if you want to check MyPresta.eu for module updates automatically. This option will display notification about new versions of this addon.') . '</p>
                            </div>
                            <label>' . $this->l('Module page') . '</label>
                            <div class="margin-form">
                                <a style="font-size:14px;" href="' . $this->mypresta_link . '" target="_blank">' . $this->displayName . '</a>
                                <p class="clear">' . $this->l('This is direct link to official addon page, where you can read about changes in the module (changelog)') . '</p>
                            </div>
                            <div class="panel-footer">
                                <button type="submit" name="submit_settings_updates"class="button btn btn-default pull-right" />
                                <i class="process-icon-save"></i>
                                ' . $this->l('Save') . '
                                </button>
                            </div>
                        </form>
                    </fieldset>
                    <style>
                    #fieldset_myprestaupdates {
                        display:block;clear:both;
                        float:inherit!important;
                    }
                    </style>
                </div>
            </div>
            </div>';
        } else {
            if (defined('_PS_ADMIN_DIR_')) {
                if (Tools::isSubmit('submit_settings_updates')) {
                    Configuration::updateValue('mypresta_updates', Tools::getValue('mypresta_updates'));
                }
                if (Configuration::get('mypresta_updates') != 0 || (bool)Configuration::get('mypresta_updates') != false) {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200)) {
                        $actual_version = myhreflangUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                    if (myhreflangUpdate::version($this->version) < myhreflangUpdate::version(Configuration::get('updatev_' . $this->name)) && Tools::getValue('ajax', 'false') == 'false') {
                        $this->context->controller->warnings[] = '<strong>' . $this->displayName . '</strong>: ' . $this->l('New version available, check http://MyPresta.eu for more informations') . ' <a href="' . $this->mypresta_link . '">' . $this->l('More details in changelog') . '</a>';
                        $this->warning = $this->context->controller->warnings[0];
                    }
                } else {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200)) {
                        $actual_version = myhreflangUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                }
                if ($display_msg == 1) {
                    if (myhreflangUpdate::version($this->version) < myhreflangUpdate::version(myhreflangUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version))) {
                        return "<span style='color:red; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('New version available!') . "</span>";
                    } else {
                        return "<span style='color:green; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('Module is up to date!') . "</span>";
                    }
                }
            }
        }
    }

    public function inconsistency($ret)
    {
        return true;
    }

    public function install()
    {
        if (parent::install() == false ||
            $this->registerHook('actionAdminControllerSetMedia') == false ||
            $this->registerHook('header') == false
        ) {
            return false;
        }
        return true;
    }

    public function hookHeader($params)
    {
        $array_of_links[] = array();
        if (isset($this->context->controller->php_self)) {
            $page = $this->context->controller->php_self;
            $languages = Language::getLanguages(false);
            foreach ($languages AS $key => $lang) {
                if ($page == 'index') {
                    $array_of_links[$lang['language_code']]['url'] = $this->context->link->getPageLink('index', true, $lang['id_lang']);
                } elseif ($page == 'product') {
                    $array_of_links[$lang['language_code']]['url'] = $this->context->link->getProductLink(Tools::getValue('id_product'), null, null, null, $lang['id_lang']);
                } elseif ($page == 'category') {
                    $array_of_links[$lang['language_code']]['url'] = $this->context->link->getCategoryLink(Tools::getValue('id_category'), null, $lang['id_lang']);
                } elseif ($page == 'manufacturer') {
                    $array_of_links[$lang['language_code']]['url'] = $this->context->link->getManufacturerLink(Tools::getValue('id_manufacturer'), null, $lang['id_lang']);
                } elseif ($page == 'supplier') {
                    $array_of_links[$lang['language_code']]['url'] = $this->context->link->getSupplierLink(Tools::getValue('id_supplier'), null, $lang['id_lang']);
                } elseif ($page == 'cms' && Tools::getValue('id_cms','false') != 'false') {
                    $array_of_links[$lang['language_code']]['url'] = $this->context->link->getCmsLink(Tools::getValue('id_cms'), null, null, $lang['id_lang']);
                } elseif ($page == 'cms' && Tools::getValue('id_cms_category','false') != 'false') {
                    $array_of_links[$lang['language_code']]['url'] = $this->context->link->getCmsCategoryLink(Tools::getValue('id_cms_category'), null, $lang['id_lang']);
                } elseif ($page == 'contact') {
                    $array_of_links[$lang['language_code']]['url'] = $this->context->link->getPageLink('contact', null, $lang['id_lang']);
                }

                if (isset($array_of_links[$lang['language_code']]['url'])) {
                    $array_of_links[$lang['language_code']]['rel'] = 'alternate';
                    $array_of_links[$lang['language_code']]['hreflang'] = $lang['language_code'];

                    if ($this->context->language->id == $lang['id_lang']) {
                        $array_of_links['canonical']['url'] = $array_of_links[$lang['language_code']]['url'];
                        $array_of_links['canonical']['rel'] = 'canonical';
                        $array_of_links['canonical']['hreflang'] = '-';
                        $array_of_links['xdefault']['url'] = $array_of_links[$lang['language_code']]['url'];
                        $array_of_links['xdefault']['rel'] = 'alternate';
                        $array_of_links['xdefault']['hreflang'] = 'x-default';
                    }
                }

            }
        }
        if (count($array_of_links) > 0) {
            $this->context->smarty->assign('myhreflang', $array_of_links);
            return $this->display(__file__, 'views/hreflang.tpl');
        }
    }

    public function hookactionAdminControllerSetMedia()
    {
        //HOOK FOR UPDATE NOTIFICATIONS PURPOSES
    }

    public function getContent()
    {
        return $this->checkforupdates(0, true);
    }

    public function psversion($part = 1)
    {
        $version = _PS_VERSION_;
        $exp = $explode = explode(".", $version);
        if ($part == 1) {
            return $exp[1];
        }
        if ($part == 2) {
            return $exp[2];
        }
        if ($part == 3) {
            return $exp[3];
        }
    }
}

class myhreflangUpdate extends myhreflang
{
    public static function version($version)
    {
        $version = (int)str_replace(".", "", $version);
        if (strlen($version) == 3) {
            $version = (int)$version . "0";
        }
        if (strlen($version) == 2) {
            $version = (int)$version . "00";
        }
        if (strlen($version) == 1) {
            $version = (int)$version . "000";
        }
        if (strlen($version) == 0) {
            $version = (int)$version . "0000";
        }
        return (int)$version;
    }

    public static function encrypt($string)
    {
        return base64_encode($string);
    }

    public static function verify($module, $key, $version)
    {
        if (ini_get("allow_url_fopen")) {
            if (function_exists("file_get_contents")) {
                $actual_version = @file_get_contents('http://dev.mypresta.eu/update/get.php?module=' . $module . "&version=" . self::encrypt($version) . "&lic=$key&u=" . self::encrypt(_PS_BASE_URL_ . __PS_BASE_URI__));
            }
        }
        Configuration::updateValue("update_" . $module, date("U"));
        Configuration::updateValue("updatev_" . $module, $actual_version);
        return $actual_version;
    }
}

?>