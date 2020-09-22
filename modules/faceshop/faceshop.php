<?php

class faceshop extends Module
{
    function __construct()
    {
        @ini_set("display_errors", 0);
        @error_reporting(0);
        $this->name = 'faceshop';
        $this->tab = 'social_networks';
        $this->version = '1.9.9.8';
        $this->author = 'MyPresta.eu';
        $this->dir = '/modules/faceshop/';
        $this->psver = $this->psversion();
        $this->mypresta_link = 'https://mypresta.eu/modules/social-networks/facebook-shop.html';
        $this->bootstrap = 1;
        parent::__construct();
        $this->displayName = $this->l('FaceShop');
        $this->description = $this->l('With this feature you can add your shop to your Facebook fanpage');
        $this->checkforupdates();
    }

    public function checkforupdates($display_msg = 0, $form = 0)
    {
        // ---------- //
        // ---------- //
        // VERSION 12 //
        // ---------- //
        // ---------- //

        $this->mkey = "nlc";
        if (@file_exists('../modules/' . $this->name . '/key.php'))
        {
            @require_once('../modules/' . $this->name . '/key.php');
        }
        else
        {
            if (@file_exists(dirname(__file__) . $this->name . '/key.php'))
            {
                @require_once(dirname(__file__) . $this->name . '/key.php');
            }
            else
            {
                if (@file_exists('modules/' . $this->name . '/key.php'))
                {
                    @require_once('modules/' . $this->name . '/key.php');
                }
            }
        }

        if ($form == 1)
        {
            return '
            <div class="panel" id="fieldset_myprestaupdates" style="margin-top:20px;">
            ' . ($this->psversion() == 6 || $this->psversion() == 7 ? '<div class="panel-heading"><i class="icon-wrench"></i> ' . $this->l('MyPresta updates') . '</div>' : '') . '
			<div class="form-wrapper" style="padding:0px!important;">
            <div id="module_block_settings">
                    <fieldset id="fieldset_modu\le_block_settings">
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
        }
        else
        {
            if (defined('_PS_ADMIN_DIR_'))
            {
                if (Tools::isSubmit('submit_settings_updates'))
                {
                    Configuration::updateValue('mypresta_updates', Tools::getValue('mypresta_updates'));
                }
                if (Configuration::get('mypresta_updates') != 0 || (bool)Configuration::get('mypresta_updates') == false)
                {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200))
                    {
                        $actual_version = faceshopUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                    if (faceshopUpdate::version($this->version) < faceshopUpdate::version(Configuration::get('updatev_' . $this->name)))
                    {
                        $this->warning = $this->l('New version available, check http://MyPresta.eu for more informations');
                    }
                }
                if ($display_msg == 1)
                {
                    if (faceshopUpdate::version($this->version) < faceshopUpdate::version(faceshopUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version)))
                    {
                        return "<span style='color:red; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('New version available!') . "</span>";
                    }
                    else
                    {
                        return "<span style='color:green; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('Module is up to date!') . "</span>";
                    }
                }
            }
        }
    }

    function install()
    {
        if (parent::install() == false or (!$this->createdb()) or Configuration::updateValue('update_' . $this->name, '0') == false or Configuration::updateValue('faceshop_last_tab', '1') == false or Configuration::updateValue('faceshop_menu_catstyle', '1') == false or Configuration::updateValue('faceshop_homepage_itemcount', '8') == false or Configuration::updateValue('faceshop_homepage_disphot', '1') == false or Configuration::updateValue('faceshop_homepage_dispnew', '1') == false or Configuration::updateValue('faceshop_header_disp', '1') == false or Configuration::updateValue('faceshop_cat_root', '1') == false or Configuration::updateValue('faceshop_cat_dispnew', '1') == false or Configuration::updateValue('faceshop_cat_disphot', '1') == false or Configuration::updateValue('faceshop_cat_itemcount', '8') == false or Configuration::updateValue('faceshop_slider_disp', '1') == false or Configuration::updateValue('faceshop_slider_time', '10') == false or Configuration::updateValue('faceshop_slider_place', '2') == false or Configuration::updateValue('faceshop_menu_newproducts', '1') == false or Configuration::updateValue('faceshop_menu_saleproducts', '1') == false or Configuration::updateValue('faceshop_menu_informations', '1') == false or Configuration::updateValue('faceshop_menu_contact', '1') == false or Configuration::updateValue('faceshop_display_breadcrumb', '1') == false or Configuration::updateValue('faceshop_pdisp_trim', '42') == false or Configuration::updateValue('faceshop_pdisp_price', '1') == false or Configuration::updateValue('faceshop_pdisp_name', '1') == false or Configuration::updateValue('faceshop_pdisp_image', '1') == false or Configuration::updateValue('faceshop_social_facebook', 1) == false or Configuration::updateValue('faceshop_social_pinterest', 0) == false or Configuration::updateValue('faceshop_social_google', 0) == false or Configuration::updateValue('faceshop_footer_disp', 0) == false or Configuration::updateValue('faceshop_cat_dispstyle', 1) == false or Configuration::updateValue('faceshop_lang_disp', 1) == false or Configuration::updateValue('faceshop_curr_disp', 1) == false or Configuration::updateValue('faceshop_modrew', 1) == false or Configuration::updateValue('faceshop_menu_manuf', 1) == false)
        {
            return false;
        }
        return true;
    }

    function createdb()
    {
        $db = Db::getInstance();
        $query = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "faceshop_slider` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `filename` TEXT NOT NULL,
        `shop` INT DEFAULT 0,
        `url` TEXT NOT NULL
        ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8";
        $db->Execute($query);

        $insert = "INSERT INTO `" . _DB_PREFIX_ . "faceshop_slider`
		(filename, shop, url) VALUES
		('5.jpg','0','http://mypresta.eu'),
		('4.jpg','0','http://mypresta.eu'),
		('3.jpg','0','http://mypresta.eu'),
		('2.jpg','0','http://mypresta.eu'),
		('1.jpg','0','http://mypresta.eu')";
        $db->Execute($insert);
        return true;
    }

    public function psversion()
    {
        $version = _PS_VERSION_;
        $exp = $explode = explode(".", $version);
        return $exp[1];
    }

    public function getconf()
    {
        $var = new stdClass();
        $var->faceshop_last_tab = Configuration::get('faceshop_last_tab');
        $var->faceshop_menu_catstyle = Configuration::get('faceshop_menu_catstyle');
        $var->faceshop_homepage_root = Configuration::get('faceshop_homepage_root');
        $var->faceshop_homepage_itemcount = Configuration::get('faceshop_homepage_itemcount');
        $var->faceshop_homepage_disphot = Configuration::get('faceshop_homepage_disphot');
        $var->faceshop_homepage_dispnew = Configuration::get('faceshop_homepage_dispnew');
        $var->faceshop_homepage_root = Configuration::get('faceshop_homepage_root');
        $var->faceshop_header_disp = Configuration::get('faceshop_header_disp');
        $var->faceshop_cat_root = Configuration::get('faceshop_cat_root');
        $var->faceshop_cat_dispnew = Configuration::get('faceshop_cat_dispnew');
        $var->faceshop_cat_disphot = Configuration::get('faceshop_cat_disphot');
        $var->faceshop_cat_itemcount = Configuration::get('faceshop_cat_itemcount');
        $var->faceshop_slider_disp = Configuration::get('faceshop_slider_disp');
        $var->faceshop_slider_time = Configuration::get('faceshop_slider_time');
        $var->faceshop_slider_place = Configuration::get('faceshop_slider_place');
        $var->faceshop_menu_newproducts = Configuration::get('faceshop_menu_newproducts');
        $var->faceshop_menu_saleproducts = Configuration::get('faceshop_menu_saleproducts');
        $var->faceshop_menu_manuf = Configuration::get('faceshop_menu_manuf');
        $var->faceshop_menu_contact = Configuration::get('faceshop_menu_contact');
        $var->faceshop_menu_informations = Configuration::get('faceshop_menu_informations');
        $var->faceshop_display_breadcrumb = Configuration::get('faceshop_display_breadcrumb');
        $var->faceshop_pdisp_trim = Configuration::get('faceshop_pdisp_trim');
        $var->faceshop_pdisp_name = Configuration::get('faceshop_pdisp_name');
        $var->faceshop_pdisp_price = Configuration::get('faceshop_pdisp_price');
        $var->faceshop_pdisp_image = Configuration::get('faceshop_pdisp_image');
        $var->faceshop_pdisp_imgtpe = Configuration::get('faceshop_pdisp_imgtpe');
        $var->faceshop_social_google = Configuration::get('faceshop_social_google');
        $var->faceshop_social_pinterest = Configuration::get('faceshop_social_pinterest');
        $var->faceshop_social_facebook = Configuration::get('faceshop_social_facebook');
        $var->faceshop_social_facebook_share = Configuration::get('faceshop_social_facebook_share');
        $var->faceshop_footer_disp = Configuration::get('faceshop_footer_disp');
        $var->faceshop_footer_button = Configuration::get('faceshop_footer_button');
        $var->faceshop_cat_dispstyle = Configuration::get('faceshop_cat_dispstyle');
        $var->faceshop_lang_disp = Configuration::get('faceshop_lang_disp');
        $var->faceshop_curr_disp = Configuration::get('faceshop_curr_disp');
        $var->faceshop_modrew = Configuration::get('faceshop_modrew');
        $var->faceshop_preprice = Configuration::get('faceshop_preprice');
        $var->faceshop_prices_notax = Configuration::get('faceshop_prices_notax');

        return $var;
    }

    public function changecurrency($cid)
    {
        if ($this->psversion() == 5 || $this->psversion() == 6 || $this->psversion() == 7)
        {
            Context::getContext()->cookie->id_currency = $cid;
        }
        else
        {
            global $cookie;
            $cookie->id_currency = $cid;
        }
        setcookie("fcbshop_curr", $cid, time() + 360000);
    }

    public function getcookiecurr()
    {
        if (isset($_COOKIE['fcbshop_curr']))
        {
            return ($_COOKIE['fcbshop_curr']);
        }
        else
        {
            return Configuration::get('PS_CURRENCY_DEFAULT');
        }
    }

    public function getcurrencyiso($cid)
    {
        $currency = new Currency($cid);
        return $currency->iso_code;
    }

    public function changelang($lid)
    {
        if ($this->psversion() == 5 || $this->psversion() == 6 || $this->psversion() == 7)
        {
            Context::getContext()->cookie->id_lang = $lid;
        }
        else
        {
            global $cookie;
            $cookie->id_lang = $lid;
        }
        setcookie("fcbshop_lang", $lid, time() + 360000);
    }

    public function getcookielang()
    {
        if (isset($_COOKIE['fcbshop_lang']))
        {
            return ($_COOKIE['fcbshop_lang']);
        }
        else
        {
            return Configuration::get('PS_LANG_DEFAULT');
        }
    }

    public function get_sliderfiles()
    {
        if ($this->psver == 6 || $this->psversion() == 7)
        {
            $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
            $query = "SELECT * FROM `" . _DB_PREFIX_ . "faceshop_slider` WHERE shop=" . $this->context->shop->id . " OR shop=0 ORDER BY id DESC";
            $array = $db->ExecuteS($query);
            return $array;
        }
        if ($this->psver == 5)
        {
            $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
            $query = "SELECT * FROM `" . _DB_PREFIX_ . "faceshop_slider` WHERE shop=" . $this->context->shop->id . " OR shop=0 ORDER BY id DESC";
            $array = $db->ExecuteS($query);
            return $array;
        }
        if ($this->psver == 4)
        {
            $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
            $query = "SELECT * FROM `" . _DB_PREFIX_ . "faceshop_slider` ORDER BY id DESC";
            $array = $db->ExecuteS($query);
            return $array;
        }
        if ($this->psver == 3)
        {
            $db = Db::getInstance();
            $query = "SELECT * FROM `" . _DB_PREFIX_ . "faceshop_slider` ORDER BY id DESC";
            $array = $db->ExecuteS($query);
            return $array;
        }
        if ($this->psver == 2)
        {
            $db = Db::getInstance();
            $query = "SELECT * FROM `" . _DB_PREFIX_ . "faceshop_slider` ORDER BY id DESC";
            $array = $db->ExecuteS($query);
            return $array;
        }
    }

    public function getContent()
    {
        $output = "";
        if (Tools::isSubmit('selecttab'))
        {
            Configuration::updateValue('faceshop_last_tab', "$_POST[selecttab]");
        }

        if (Tools::isSubmit('submit_breadcrumb'))
        {
            $faceshop_display_breadcrumb = Tools::getValue('faceshop_display_breadcrumb');
            Configuration::updateValue('faceshop_display_breadcrumb', $faceshop_display_breadcrumb, true);
            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Settings updated') . '</div>';
        }

        if (Tools::isSubmit('catstyle'))
        {
            Configuration::updateValue('faceshop_menu_disable', Tools::getValue('faceshop_menu_disable', 0));

            $faceshop_menu_catstyle = Tools::getValue('faceshop_menu_catstyle');
            Configuration::updateValue('faceshop_menu_catstyle', $faceshop_menu_catstyle, true);

            $faceshop_cat_dispstyle = Tools::getValue('faceshop_cat_dispstyle');
            Configuration::updateValue('faceshop_cat_dispstyle', $faceshop_cat_dispstyle, true);

            $faceshop_cat_root = Tools::getValue('faceshop_cat_root');
            Configuration::updateValue('faceshop_cat_root', $faceshop_cat_root, true);

            Configuration::updateValue('faceshop_cat_exclude', trim($_POST['faceshop_cat_exclude']));

            $faceshop_menu_newproducts = Tools::getValue('faceshop_menu_newproducts');
            if (!$faceshop_menu_newproducts == "1")
            {
                $faceshop_menu_newproducts = 0;
            }
            Configuration::updateValue('faceshop_menu_newproducts', $faceshop_menu_newproducts, true);

            $faceshop_menu_manuf = Tools::getValue('faceshop_menu_manuf');
            if (!$faceshop_menu_manuf == "1")
            {
                $faceshop_menu_manuf = 0;
            }
            Configuration::updateValue('faceshop_menu_manuf', $faceshop_menu_manuf, true);

            $faceshop_menu_saleproducts = Tools::getValue('faceshop_menu_saleproducts');
            if (!$faceshop_menu_saleproducts == "1")
            {
                $faceshop_menu_saleproducts = 0;
            }
            Configuration::updateValue('faceshop_menu_saleproducts', $faceshop_menu_saleproducts, true);

            $faceshop_menu_informations = Tools::getValue('faceshop_menu_informations');
            if (!$faceshop_menu_informations == "1")
            {
                $faceshop_menu_informations = 0;
            }
            Configuration::updateValue('faceshop_menu_informations', $faceshop_menu_informations, true);

            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Settings updated') . '</div>';
        }


        if (Tools::isSubmit('header_option'))
        {
            if ($this->psversion() == 5 || $this->psversion() == 6 || $this->psversion() == 7)
            {
                $id_of_shop = $this->context->shop->id;
            }
            else
            {
                $id_of_shop = 0;
            }
            $faceshop_header_disp = Tools::getValue('faceshop_header_disp');
            Configuration::updateValue('faceshop_header_link', (Tools::getValue('faceshop_header_link') == 1 ? '1' : '0'));
            Configuration::updateValue('faceshop_header_linkurl', Tools::getValue('faceshop_header_url'));
            if (!$faceshop_header_disp == "1")
            {
                $faceshop_header_disp = 0;
            }
            Configuration::updateValue('faceshop_header_disp', $faceshop_header_disp, true);
            $plik_tmp = $_FILES['file']['tmp_name'];
            $plik_nazwa = $_FILES['file']['name'];
            $plik_rozmiar = $_FILES['file']['size'];
            if (is_uploaded_file($plik_tmp))
            {
                if (move_uploaded_file($plik_tmp, '..' . $this->dir . "img/" . $id_of_shop . "-header.gif"))
                {
                    $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('File uploaded properly') . '</div>';
                }
            }
        }

        if (Tools::isSubmit('footer_option'))
        {
            if ($this->psversion() == 5 || $this->psversion() == 6 || $this->psversion() == 7)
            {
                $id_of_shop = $this->context->shop->id;
            }
            else
            {
                $id_of_shop = 0;
            }
            $faceshop_footer_disp = Tools::getValue('faceshop_footer_disp');
            if (!$faceshop_footer_disp == "1")
            {
                $faceshop_footer_disp = 0;
            }
            Configuration::updateValue('faceshop_footer_disp', $faceshop_footer_disp, true);

            $faceshop_footer_button = Tools::getValue('faceshop_footer_button');
            Configuration::updateValue('faceshop_footer_button', $faceshop_footer_button, true);


            $plik_tmp = $_FILES['file']['tmp_name'];
            $plik_nazwa = $_FILES['file']['name'];
            $plik_rozmiar = $_FILES['file']['size'];
            if (is_uploaded_file($plik_tmp))
            {
                if (move_uploaded_file($plik_tmp, '..' . $this->dir . "img/" . $id_of_shop . "-footer.gif"))
                {
                    $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('File uploaded properly') . '</div>';
                }
            }
        }


        if (Tools::isSubmit('homepage_items'))
        {
            $faceshop_homepage_itemcount = Tools::getValue('faceshop_homepage_itemcount');
            Configuration::updateValue('faceshop_homepage_itemcount', $faceshop_homepage_itemcount, true);

            $faceshop_homepage_root = Tools::getValue('faceshop_homepage_root');
            Configuration::updateValue('faceshop_homepage_root', $faceshop_homepage_root, true);

            $faceshop_homepage_disphot = Tools::getValue('faceshop_homepage_disphot');
            if (!$faceshop_homepage_disphot == "1")
            {
                $faceshop_homepage_disphot = 0;
            }
            Configuration::updateValue('faceshop_homepage_disphot', $faceshop_homepage_disphot, true);

            $faceshop_homepage_dispnew = Tools::getValue('faceshop_homepage_dispnew');
            if (!$faceshop_homepage_dispnew == "1")
            {
                $faceshop_homepage_dispnew = 0;
            }
            Configuration::updateValue('faceshop_homepage_dispnew', $faceshop_homepage_dispnew, true);

            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Settings updated') . '</div>';
        }

        if (Tools::isSubmit('submit_pdisp'))
        {
            $faceshop_pdisp_trim = Tools::getValue('faceshop_pdisp_trim');
            Configuration::updateValue('faceshop_pdisp_trim', $faceshop_pdisp_trim, true);
            Configuration::updateValue('faceshop_prices_notax', Tools::getValue('faceshop_prices_notax', 0));

            $faceshop_pdisp_price = Tools::getValue('faceshop_pdisp_price');
            Configuration::updateValue('faceshop_pdisp_price', $faceshop_pdisp_price, true);

            $faceshop_pdisp_name = Tools::getValue('faceshop_pdisp_name');
            Configuration::updateValue('faceshop_pdisp_name', $faceshop_pdisp_name, true);

            $faceshop_pdisp_image = Tools::getValue('faceshop_pdisp_image');
            Configuration::updateValue('faceshop_pdisp_image', $faceshop_pdisp_image, true);

            $faceshop_pdisp_imgtpe = Tools::getValue('faceshop_pdisp_imgtpe');
            Configuration::updateValue('faceshop_pdisp_imgtpe', $faceshop_pdisp_imgtpe, true);
            Configuration::updateValue('faceshop_pdisp_acart', Tools::getValue('faceshop_pdisp_acart'));
            Configuration::updateValue('faceshop_acart_ssl', Tools::getValue('faceshop_acart_ssl'));
            Configuration::updateValue('faceshop_preprice', Tools::getValue('faceshop_preprice'));
            Configuration::updateValue('faceshop_analytics', Tools::getValue('faceshop_analytics'));


            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Settings updated') . '</div>';
        }


        if (Tools::isSubmit('submit_social'))
        {
            $faceshop_social_facebook = Tools::getValue('faceshop_social_facebook');
            Configuration::updateValue('faceshop_social_facebook', $faceshop_social_facebook, true);

            Configuration::updateValue('fshp_fbapp', Tools::getValue('fshp_fbapp'));

            Configuration::updateValue('faceshop_share', Tools::getValue('faceshop_social_facebook_share'));

            $faceshop_social_google = Tools::getValue('faceshop_social_google');
            Configuration::updateValue('faceshop_social_google', $faceshop_social_google, true);

            $faceshop_social_pinterest = Tools::getValue('faceshop_social_pinterest');
            Configuration::updateValue('faceshop_social_pinterest', $faceshop_social_pinterest, true);

            Configuration::updateValue('fsh_langarray', Tools::getValue('fsh_langarray'), true);

            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Settings updated') . '</div>';
        }


        if (Tools::isSubmit('cat_items'))
        {
            $faceshop_cat_itemcount = Tools::getValue('faceshop_cat_itemcount');
            Configuration::updateValue('faceshop_cat_itemcount', $faceshop_cat_itemcount, true);

            $faceshop_cat_disphot = Tools::getValue('faceshop_cat_disphot');
            if (!$faceshop_cat_disphot == "1")
            {
                $faceshop_cat_disphot = 0;
            }
            Configuration::updateValue('faceshop_cat_disphot', $faceshop_cat_disphot, true);

            $faceshop_cat_dispnew = Tools::getValue('faceshop_cat_dispnew');
            if (!$faceshop_cat_dispnew == "1")
            {
                $faceshop_cat_dispnew = 0;
            }
            Configuration::updateValue('faceshop_cat_dispnew', $faceshop_cat_dispnew, true);
            Configuration::updateValue('faceshop_orderby', Tools::getValue('faceshop_orderby'));
            Configuration::updateValue('faceshop_orderway', Tools::getValue('faceshop_orderway'));

            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Settings updated') . '</div>';
        }


        if (Tools::isSubmit('delete_nivoslider_file'))
        {
            if (file_exists(".." . $this->dir . "nivoslider/" . $_POST['fnivo']))
            {
                unlink(".." . $this->dir . "nivoslider/" . $_POST['fnivo']);
            }
            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Settings updated') . '</div>';
            $db = Db::getInstance();
            $query = "DELETE FROM `" . _DB_PREFIX_ . "faceshop_slider` WHERE filename='{$_POST['fnivo']}'";
            $db->Execute($query);
        }

        if (Tools::isSubmit('url_nivoslider_file'))
        {
            $db = Db::getInstance();
            $query = "UPDATE `" . _DB_PREFIX_ . "faceshop_slider` SET url='{$_POST['furl']}' WHERE filename='{$_POST['fnivo']}'";
            if ($db->Execute($query))
            {
                $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Settings updated') . '</div>';
            }
        }


        if (Tools::isSubmit('slider_option'))
        {
            $faceshop_slider_disp = Tools::getValue('faceshop_slider_disp');
            $faceshop_slider_time = Tools::getValue('faceshop_slider_time');
            $faceshop_slider_place = Tools::getValue('faceshop_slider_place');

            Configuration::updateValue('faceshop_slider_disp', $faceshop_slider_disp, true);
            Configuration::updateValue('faceshop_slider_time', $faceshop_slider_time, true);
            Configuration::updateValue('faceshop_slider_place', $faceshop_slider_place, true);


            $plik_tmp = $_FILES['file']['tmp_name'];
            $plik_nazwa = $_FILES['file']['name'];
            $plik_rozmiar = $_FILES['file']['size'];
            if (is_uploaded_file($plik_tmp))
            {
                $plik_nazwa_hashed = md5(date("U"));
                $db = Db::getInstance();
                if ($this->psversion() == 6 || $this->psversion() == 5 || $this->psversion() == 7)
                {
                    $id_of_shop = $this->context->shop->id;
                }
                else
                {
                    $id_of_shop = 0;
                }
                $query = "INSERT INTO `" . _DB_PREFIX_ . "faceshop_slider` (shop,filename,url) VALUES ('" . $id_of_shop . "','$plik_nazwa_hashed-$plik_nazwa','{$_POST['faceshop_slide_url']}')";
                $db->Execute($query);

                if ($this->psver == 5 || $this->psver == 6 || $this->psversion() == 7)
                {
                    $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
                }
                if ($this->psver == 4)
                {
                    $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
                }
                if ($this->psver == 3)
                {
                    $db = Db::getInstance();
                }
                if ($this->psver == 2)
                {
                    $db = Db::getInstance();
                }

                $query = "SELECT * FROM `" . _DB_PREFIX_ . "faceshop_slider` WHERE filename='$plik_nazwa_hashed-$plik_nazwa'";
                $array = $db->ExecuteS($query);
                $plik_nazwa = $plik_nazwa_hashed . "-" . $plik_nazwa;
                if (move_uploaded_file($plik_tmp, '..' . $this->dir . "nivoslider/$plik_nazwa"))
                {
                }
            }

            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Settings updated') . '</div>';
        }


        if (Tools::isSubmit('submit_lang'))
        {
            Configuration::updateValue('faceshop_lang_disp', Tools::getValue('faceshop_lang_disp'), true);
            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Settings updated') . '</div>';
        }

        if (Tools::isSubmit('submit_curr'))
        {
            Configuration::updateValue('faceshop_curr_disp', Tools::getValue('faceshop_curr_disp'), true);
            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Settings updated') . '</div>';
        }

        if (Tools::isSubmit('submit_colors'))
        {

            Configuration::updateValue('fbs_bgc', Tools::getValue('fbs_bgc'));
            Configuration::updateValue('fbs_menu_bgc', Tools::getValue('fbs_menu_bgc'));
            Configuration::updateValue('fbs_menu_bgc_shadow', Tools::getValue('fbs_menu_bgc_shadow'));
            Configuration::updateValue('fbs_menu_bgc_color', Tools::getValue('fbs_menu_bgc_color'));
            Configuration::updateValue('fbs_menu_bgc_hover', Tools::getValue('fbs_menu_bgc_hover'));
            Configuration::updateValue('fbs_menu_bgc_hoverc', Tools::getValue('fbs_menu_bgc_hoverc'));
            Configuration::updateValue('fbs_menu_bgc_hovers', Tools::getValue('fbs_menu_bgc_hovers'));
            Configuration::updateValue('fbs_product_bgc_color', Tools::getValue('fbs_product_bgc_color'));
            Configuration::updateValue('fbs_product_bgc_namec', Tools::getValue('fbs_product_bgc_namec'));
            Configuration::updateValue('fbs_product_bgc_name', Tools::getValue('fbs_product_bgc_name'));
            Configuration::updateValue('fbs_product_bgc_names', Tools::getValue('fbs_product_bgc_names'));
            Configuration::updateValue('fbs_product_bgc', Tools::getValue('fbs_product_bgc'));
            Configuration::updateValue('fbs_product_bgc_borderc', Tools::getValue('fbs_product_bgc_borderc'));
            Configuration::updateValue('fbs_product_bgc_dcolor', Tools::getValue('fbs_product_bgc_dcolor'));


            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Settings updated') . '</div>';
        }


        if (Tools::isSubmit('submit_modrew'))
        {
            Configuration::updateValue('faceshop_modrew', Tools::getValue('faceshop_modrew'), true);
            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />' . $this->l('Settings updated') . '</div>';

        }


        return $output . $this->displayForm();
    }


    public function displayForm()
    {
        $form='';
        $var = $this->getconf();
        if ($var->faceshop_last_tab == 0)
        {
            $selected0 = "selected";
        }
        else
        {
            $selected0 = "";
        }
        if ($var->faceshop_last_tab == 1)
        {
            $selected1 = "selected";
        }
        else
        {
            $selected1 = "";
        }
        if ($var->faceshop_last_tab == 2)
        {
            $selected2 = "selected";
        }
        else
        {
            $selected2 = "";
        }
        if ($var->faceshop_last_tab == 3)
        {
            $selected3 = "selected";
        }
        else
        {
            $selected3 = "";
        }
        if ($var->faceshop_last_tab == 4)
        {
            $selected4 = "selected";
        }
        else
        {
            $selected4 = "";
        }
        if ($var->faceshop_last_tab == 5)
        {
            $selected5 = "selected";
        }
        else
        {
            $selected5 = "";
        }
        if ($var->faceshop_last_tab == 6)
        {
            $selected6 = "selected";
        }
        else
        {
            $selected6 = "";
        }
        if ($var->faceshop_last_tab == 7)
        {
            $selected7 = "selected";
        }
        else
        {
            $selected7 = "";
        }
        if ($var->faceshop_last_tab == 8)
        {
            $selected8 = "selected";
        }
        else
        {
            $selected8 = "";
        }
        if ($var->faceshop_last_tab == 9)
        {
            $selected9 = "selected";
        }
        else
        {
            $selected9 = "";
        }
        if ($var->faceshop_last_tab == 10)
        {
            $selected10 = "selected";
        }
        else
        {
            $selected10 = "";
        }
        if ($var->faceshop_last_tab == 11)
        {
            $selected11 = "selected";
        }
        else
        {
            $selected11 = "";
        }
        if ($var->faceshop_last_tab == 12)
        {
            $selected12 = "selected";
        }
        else
        {
            $selected12 = "";
        }


        $sliderarray = $this->get_sliderfiles();
        $sliderfiles = '<div style="text-align:left; clear:both; margin-bottom:5px;display:block; padding:5px 10px; background:#FFF; border:1px solid #c0c0c0;"><table style="width:100%;">
		<tr><th>' . $this->l('ID') . '</th><th>' . $this->l('Preview image') . '</th><th>' . $this->l('URL') . '</th><th>' . $this->l('Delete') . '</th></tr>
		';
        if (count($sliderarray) > 0)
        {
            foreach ($sliderarray as $key => $value)
            {
                $sliderfiles = $sliderfiles . '
	            <tr>
					<td style="width:45px;">
						<label style="width:auto; margin-right:10px;">' . $value['id'] . '</label>
					</td>
					<td style="">
						<a class="button" href="' . _PS_BASE_URL_ . _MODULE_DIR_ . 'faceshop/nivoslider/' . $value['filename'] . '" target="_blank">' . $this->l('preview') . '</a>
					</td>
					<td>
						<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" enctype="multipart/form-data">
							<input type="hidden" name="fnivo" value="' . $value['filename'] . '">
							<input type="text" name="furl" value="' . $value['url'] . '">
							<input type="submit" value="' . $this->l('change') . '" name="url_nivoslider_file" class="button"/>
						</form>
					</td>
					<td>
						<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" enctype="multipart/form-data">
							<input type="hidden" name="fnivo" value="' . $value['filename'] . '"><input type="submit" value="' . $this->l('delete') . '" name="delete_nivoslider_file" class="button"/>
						</form>
					</td>
				</tr>
				';
            }
        }
        $sliderfiles .= '</table></div>';

        if ($var->faceshop_header_disp == 1)
        {
            $checked_header = 'checked';
        }
        else
        {
            $checked_header = "";
        }
        if ($var->faceshop_slider_disp == 1)
        {
            $checked_slider = 'checked';
        }
        else
        {
            $checked_slider = "";
        }
        if ($var->faceshop_slider_place == 0)
        {
            $slider_place0 = 'selected="yes"';
        }
        else
        {
            $slider_place0 = '';
        }
        if ($var->faceshop_slider_place == 1)
        {
            $slider_place1 = 'selected="yes"';
        }
        else
        {
            $slider_place1 = '';
        }
        if ($var->faceshop_slider_place == 2)
        {
            $slider_place2 = 'selected="yes"';
        }
        else
        {
            $slider_place2 = '';
        }
        if ($var->faceshop_slider_place == 3)
        {
            $slider_place3 = 'selected="yes"';
        }
        else
        {
            $slider_place3 = '';
        }
        if ($var->faceshop_menu_catstyle == 1)
        {
            $select1 = 'selected="yes"';
        }
        else
        {
            $select1 = "";
        }
        if ($var->faceshop_menu_catstyle == 2)
        {
            $select2 = 'selected="yes"';
        }
        else
        {
            $select2 = "";
        }
        if ($var->faceshop_menu_newproducts == 1)
        {
            $checked_newproducts = 'checked';
        }
        else
        {
            $checked_newproducts = "";
        }
        if ($var->faceshop_menu_manuf == 1)
        {
            $checked_manuf = 'checked';
        }
        else
        {
            $checked_manuf = "";
        }
        if ($var->faceshop_menu_saleproducts == 1)
        {
            $checked_saleproducts = 'checked';
        }
        else
        {
            $checked_saleproducts = "";
        }
        if ($var->faceshop_menu_informations == 1)
        {
            $checked_informations = 'checked';
        }
        else
        {
            $checked_informations = "";
        }
        if ($var->faceshop_homepage_disphot == 1)
        {
            $checked_hot = 'checked';
        }
        else
        {
            $checked_hot = "";
        }
        if ($var->faceshop_homepage_dispnew == 1)
        {
            $checked_new = 'checked';
        }
        else
        {
            $checked_new = "";
        }
        if ($var->faceshop_cat_dispnew == 1)
        {
            $checked_catnew = 'checked';
        }
        else
        {
            $checked_catnew = "";
        }
        if ($var->faceshop_cat_disphot == 1)
        {
            $checked_cathot = 'checked';
        }
        else
        {
            $checked_cathot = "";
        }
        if ($var->faceshop_display_breadcrumb == 1)
        {
            $checked_breadcrumb = 'checked';
        }
        else
        {
            $checked_breadcrumb = "";
        }
        if ($var->faceshop_pdisp_price == 1)
        {
            $checked_pdisp_price = 'checked';
        }
        else
        {
            $checked_pdisp_price = "";
        }
        if ($var->faceshop_pdisp_name == 1)
        {
            $checked_pdisp_name = 'checked';
        }
        else
        {
            $checked_pdisp_name = "";
        }
        if ($var->faceshop_pdisp_image == 1)
        {
            $checked_pdisp_image = 'checked';
        }
        else
        {
            $checked_pdisp_image = "";
        }
        if ($var->faceshop_social_facebook == 1)
        {
            $checked_social_facebook = 'checked';
        }
        else
        {
            $checked_social_facebook = "";
        }
        if ($var->faceshop_social_pinterest == 1)
        {
            $checked_social_pinterest = 'checked';
        }
        else
        {
            $checked_social_pinterest = "";
        }
        if ($var->faceshop_social_google == 1)
        {
            $checked_social_google = 'checked';
        }
        else
        {
            $checked_social_google = "";
        }
        if ($var->faceshop_footer_disp == 1)
        {
            $checked_footer = 'checked';
        }
        else
        {
            $checked_footer = "";
        }
        if ($var->faceshop_footer_button == 1)
        {
            $checked_footer_button = 'checked';
        }
        else
        {
            $checked_footer_button = "";
        }
        if ($var->faceshop_prices_notax == 1)
        {
            $faceshop_prices_notax = 'checked';
        }
        else
        {
            $faceshop_prices_notax = "";
        }

        $cat_dispstyle1 = "";
        $cat_dispstyle2 = "";
        $cat_dispstyle3 = "";
        $cat_dispstyle4 = "";
        if ($var->faceshop_cat_dispstyle == 1)
        {
            $cat_dispstyle1 = 'checked="yes"';
        }
        if ($var->faceshop_cat_dispstyle == 2)
        {
            $cat_dispstyle2 = 'checked="yes"';
        }
        if ($var->faceshop_cat_dispstyle == 3)
        {
            $cat_dispstyle3 = 'checked="yes"';
        }
        if ($var->faceshop_cat_dispstyle == 4)
        {
            $cat_dispstyle4 = 'checked="yes"';
        }
        if ($var->faceshop_lang_disp == 1)
        {
            $checked_lang_disp = 'checked="checked"';
        }
        else
        {
            $checked_lang_disp = "";
        }
        if ($var->faceshop_curr_disp == 1)
        {
            $checked_curr_disp = 'checked="checked"';
        }
        else
        {
            $checked_curr_disp = "";
        }
        if ($var->faceshop_modrew == 1)
        {
            $checked_modrew = 'checked="checked"';
        }
        else
        {
            $checked_modrew = "";
        }


        $images_types = ImageType::getImagesTypes('products');

        $images_types_options = "";
        foreach ($images_types as $k => $v)
        {
            $imgtpe = "";
            if ($v['name'] == $var->faceshop_pdisp_imgtpe)
            {
                $imgtpe = "selected=\"yes\"";
            }
            $images_types_options .= "<option value=\"{$v['name']}\" $imgtpe>{$v['name']}</option>";
        }


        /** INSTALL ON FANPAGE **/
        if ($var->faceshop_last_tab == "12")
        {
            $form = '<a target="blank" href="https://apps.facebook.com/prestashopstore">' . $this->l('open app installation page') . '</a>';

        }


        /** HEADER & FOOTER **/
        if ($var->faceshop_last_tab == "1")
        {
            if ($this->psversion() == 5 || $this->psversion() == 6 || $this->psversion() == 7)
            {
                $id_of_shop = $this->context->shop->id;
            }
            else
            {
                $id_of_shop = 0;
            }

            if (file_exists('../modules/faceshop/img/' . $id_of_shop . '-header.gif'))
            {
                $id_of_shop_image = $this->context->shop->id . "-";
            }
            else
            {
                $id_of_shop_image = "";
            }


            $form = '
			<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Shop header') . '</legend>
                <div style="clear:both;display:block;">
				    <label>' . $this->l('Header picture') . ':</label>
					<div class="margin-form" valign="middle">
                        <img src="' . $this->_path . 'img/' . $id_of_shop_image . 'header.gif" style="width:510px;">
                        <p class="clear">' . $this->l('Header picture is an image placed on the top in your facebook shop tab (above main menu).') . '</p>
					</div>
                </div>

                <div style="clear:both;display:block;">
				    <label>' . $this->l('Display header picture') . ':</label>
					<div class="margin-form" valign="middle">
                        <div style="margin-top:7px;">
			                 <input type="checkbox" name="faceshop_header_disp" value="1" ' . $checked_header . '> yes
                        </div>
					</div>
                </div>

                <div style="clear:both;display:block;">
				    <label>' . $this->l('Add link to header') . ':</label>
					<div class="margin-form" valign="middle">
                        <div style="margin-top:7px;">
			                 <input type="checkbox" name="faceshop_header_link" value="1" ' . (Configuration::get('faceshop_header_link') == 1 ? 'checked="yes"' : '') . '> yes
                             ' . $this->l('url:') . '<input type="text" name="faceshop_header_url" value="' . Configuration::get('faceshop_header_linkurl') . '" />
                        </div>
					</div>
                </div>

                <div style="clear:both;display:block; margin-top:20px;">
				<label>' . $this->l('Upload new file') . ':</label>
					<div class="margin-form">
                        <input type="file" name="file" />
						<p class="clear">' . $this->l('Header') . ' ' . $this->l('file size should be:') . '<br/>' . $this->l('width: 810px height: 100px') . '</p>
					</div>
                </div>
				<center><input type="submit" name="header_option" value="' . $this->l('Save') . '" class="button" /></center>
			</fieldset>
			</form>';


            if ($this->psversion() == 5 || $this->psversion() == 6 || $this->psversion() == 7)
            {
                $id_of_shop = $this->context->shop->id;
            }
            else
            {
                $id_of_shop = 0;
            }

            if (file_exists('../modules/faceshop/img/' . $id_of_shop . '-footer.gif'))
            {
                $id_of_shop_image = $this->context->shop->id . "-";
            }
            else
            {
                $id_of_shop_image = "";
            }

            $form .= '
			<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" enctype="multipart/form-data" style="margin-top:10px;">
			<fieldset>
				<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Shop footer') . '</legend>
                <div style="clear:both;display:block;">
				    <label>' . $this->l('Footer picture') . ':</label>
					<div class="margin-form" valign="middle">
                        <img src="' . $this->_path . 'img/' . $id_of_shop_image . 'footer.gif" style="width:510px;">
                        <p class="clear">' . $this->l('Footer picture is an image placed on the bottom in your facebook shop tab.') . '</p>
					</div>
                </div>

                <div style="clear:both;display:block;">
				    <label>' . $this->l('Display footer picture') . ':</label>
					<div class="margin-form" valign="middle">
                        <div style="margin-top:7px;">
			                 <input type="checkbox" name="faceshop_footer_disp" value="1" ' . $checked_footer . '> yes
                        </div>
					</div>
                </div>

                <div style="clear:both;display:block; margin-top:20px;">
				<label>' . $this->l('Upload new file') . ':</label>
					<div class="margin-form">
                        <input type="file" name="file" />
						<p class="clear">' . $this->l('Footer') . ' ' . $this->l('file size should be:') . '<br/>' . $this->l('width: 810px height: 100px') . '</p>
					</div>
                </div>

                <div style="clear:both;display:block;">
				    <label>' . $this->l('Display button "Open our shop"') . ':</label>
					<div class="margin-form" valign="middle">
                        <div style="margin-top:7px;">
			                 <input type="checkbox" name="faceshop_footer_button" value="1" ' . $checked_footer_button . '> yes
                        </div>
					</div>
                </div>

				<center><input type="submit" name="footer_option" value="' . $this->l('Save') . '" class="button" /></center>
			</fieldset>
			</form>';
        }


        /** SLIDER **/
        if ($var->faceshop_last_tab == "2")
        {
            $form = '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Slider') . '</legend>
                <div style="clear:both;display:block;">
				    <label>' . $this->l('Display slider: ') . '</label>
					<div class="margin-form" valign="middle">
                        <div style="margin-top:7px;">
			                 <input type="checkbox" name="faceshop_slider_disp" value="1" ' . $checked_slider . '> ' . $this->l('yes') . '
			                 <p class="clear">' . $this->l('Check if you want to display slider') . '</p>
                        </div>
					</div>
                </div>

                <div style="clear:both;display:block; margin-top:10px;">
					<label>' . $this->l('Slider place') . ':</label>
					<div class="margin-form">
						<SELECT name="faceshop_slider_place">
							<option value=\'0\'' . $slider_place0 . '>' . $this->l('Before header') . '</option>
	                        <option value=\'1\'' . $slider_place1 . '>' . $this->l('Before main menu') . '</option>
	                        <option value=\'2\'' . $slider_place2 . '>' . $this->l('After main menu') . '</option>
							<option value=\'3\'' . $slider_place3 . '>' . $this->l('After products list') . '</option>
                        </SELECT>
						<p class="clear">' . $this->l('Select place where you want to display slider') . '</p>
					</div>
                </div>

                <div style="clear:both;display:block; margin-top:10px;">
				    <label>' . $this->l('Display time') . ':</label>
					<div class="margin-form">
						<input type="text" name="faceshop_slider_time" value="' . Configuration::get('faceshop_slider_time') . '">
						<p class="clear">' . $this->l('Specify the display time for each slide') . '</p>
					</div>
                </div>

                <div style="clear:both;display:block; margin-top:30px;">
				<label>' . $this->l('Add new slide') . '</label>
					<div class="margin-form">
                        <input type="file" name="file" />
						<p class="clear">' . $this->l('Slider') . ' ' . $this->l('file size should be:') . '<br/>' . $this->l('width: 810px height: 180px') . '</p>
					</div>
                </div>

                <div style="clear:both;display:block;">
				<label>' . $this->l('slider URL') . '</label>
					<div class="margin-form">
                        <input type="text" name="faceshop_slide_url">
						<p class="clear">' . $this->l('type the address to which the user will be redirected') . '</p>
					</div>
                </div>

				<center><input type="submit" name="slider_option" value="' . $this->l('Save') . '" class="button" /></center>
			</fieldset>
		</form>

		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" enctype="multipart/form-data" style="margin-top:10px;">
			<fieldset>
				<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Slider files') . '</legend>
			' . $sliderfiles . '</fieldset>
		</form>
		';
        }


        /** MAIN MENU **/
        if ($var->faceshop_last_tab == "3")
        {
            $cats = $this->getSimpleCategories();
            $cat_select = '';
            if (count($cats) > 0)
            {
                foreach ($cats as $key => $value)
                {
                    if ($var->faceshop_cat_root == $value['id_category'])
                    {
                        $cat_selected = 'selected="yes"';
                    }
                    else
                    {
                        $cat_selected = '';
                    }
                    $cat_select .= "<option value=\"{$value['id_category']}\" $cat_selected>{$value['name']}</option>";
                }
            }

            $form = '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
			<fieldset>
				<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Main menu') . '</legend>
				<div style="display:block; clear:both;">
				<label>' . $this->l('Disable menu') . ':</label>
					<div class="margin-form">
						<SELECT name="faceshop_menu_disable">
	                        <option value=\'1\'' .(Configuration::get('faceshop_menu_disable') == 1 ? 'selected':'').'>'.$this->l('Yes').'</option>
	                        <option value=\'0\'' .(Configuration::get('faceshop_menu_disable') != 1 ? 'selected':'').'>'.$this->l('No').'</option>
                        </SELECT>
						<p class="clear">' . $this->l('Enable this option if you want to remove the menu bar from the facebook shop') . '</p>
					</div>
                </div>
                
                <div style="display:block; clear:both;">
				<label>' . $this->l('Shop categories') . ':</label>
					<div class="margin-form">
						<SELECT name="faceshop_menu_catstyle">
	                        <option value=\'1\'' . $select1 . '>Only main categories</option>
	                        <option value=\'2\'' . $select2 . '>Main categories & subcategories</option>
                        </SELECT>
						<p class="clear">' . $this->l('Specify the "shop categories" menu content') . '</p>
					</div>
                </div>

                <div style="display:block; clear:both;">
				<label>' . $this->l('Category root') . ':</label>
					<div class="margin-form">
						<SELECT name="faceshop_cat_root">
	                        ' . $cat_select . '
                        </SELECT>
						<p class="clear">' . $this->l('Select the categories root') . '</p>
					</div>
                </div>

                <div style="display:block; clear:both;">
				<label>' . $this->l('Exclude categories') . ':</label>
					<div class="margin-form">
						<input type="text" name="faceshop_cat_exclude" value="' . Configuration::get('faceshop_cat_exclude') . '">
						<p class="clear">' . $this->l('If you want exclude categories from menu, type their ID here, separate each ID with comma') . ' <a href="http://mypresta.eu/en/art/basic-tutorials/prestashop-how-to-get-category-id.html" target="_blank">' . $this->l('Read how to get category ID') . '</a></p>
					</div>
                </div>

                <div style="display:block; clear:both;">
                <label>' . $this->l('Category display style') . ':</label>
	                <div class="margin-form">
	                	<div style="display:inline-block; width:100px; text-align:center;">
	                		<img src="' . $this->_path . 'img/cat-list.png" style="margin-bottom:5px;"/></br>
	                		<input type="radio" ' . $cat_dispstyle1 . ' name="faceshop_cat_dispstyle" value="1" />
	                	</div>
	                	<div style="display:inline-block; width:100px; text-align:center;">
	                		<img src="' . $this->_path . 'img/cat-box.png" style="margin-bottom:5px;"/></br>
	                		<input type="radio" ' . $cat_dispstyle2 . ' name="faceshop_cat_dispstyle" value="2" />
	                	</div>
	                	<div style="display:inline-block; width:100px; text-align:center;">
	                		<img src="' . $this->_path . 'img/cat-box3.png" style="margin-bottom:5px;"/></br>
	                		<input type="radio" ' . $cat_dispstyle3 . ' name="faceshop_cat_dispstyle" value="3" />
	                	</div>
                        <div style="display:inline-block; width:100px; text-align:center;">
	                		<img src="' . $this->_path . 'img/cat-box4.png" style="margin-bottom:5px;"/></br>
	                		<input type="radio" ' . $cat_dispstyle4 . ' name="faceshop_cat_dispstyle" value="4" />
	                	</div>
	                </div>
				</div>

                <div style="display:block; clear:both;">
				<label>' . $this->l('Manufacturers') . ':</label>
					<div class="margin-form">
						<input type="checkbox" value="1" name="faceshop_menu_manuf" ' . $checked_manuf . '> ' . $this->l('yes') . '
						<p class="clear">' . $this->l('Displays the manufacturers list') . '</p>
					</div>
                </div>

                <div style="display:block; clear:both;">
				<label>' . $this->l('New products') . ':</label>
					<div class="margin-form">
						<input type="checkbox" value="1" name="faceshop_menu_newproducts" ' . $checked_newproducts . '> ' . $this->l('yes') . '
						<p class="clear">' . $this->l('Displays the "new products" in the menu. It leads to a list of new items') . '</p>
					</div>
                </div>

                <div style="display:block; clear:both;">
				<label>' . $this->l('Sales') . ':</label>
					<div class="margin-form">
						<input type="checkbox" value="1" name="faceshop_menu_saleproducts" ' . $checked_saleproducts . '> ' . $this->l('yes') . '
						<p class="clear">' . $this->l('Displays the "promotions" in the menu. It leads to a list of items in promotion') . '</p>
					</div>
                </div>

                <div style="display:block; clear:both;">
				<label>' . $this->l('Informations') . ':</label>
					<div class="margin-form">
						<input type="checkbox" value="1" name="faceshop_menu_informations" ' . $checked_informations . '> ' . $this->l('yes') . '
						<p class="clear">' . $this->l('The drop-down tab with informations. Requires an installed CMS module') . '</p>
					</div>
                </div>

				<center><input type="submit" name="catstyle" value="' . $this->l('Save') . '" class="button" /></center>
			</fieldset>
		</form>';
        }


        /** FEATURED PRODUCTS **/
        if ($var->faceshop_last_tab == "4")
        {
            $cats = $this->getSimpleCategories();
            $cat_select = '';
            if (count($cats) > 0)
            {
                foreach ($cats as $key => $value)
                {
                    if ($var->faceshop_homepage_root == $value['id_category'])
                    {
                        $cat_selected = 'selected="yes"';
                    }
                    else
                    {
                        $cat_selected = '';
                    }
                    $cat_select .= "<option value=\"{$value['id_category']}\" $cat_selected>{$value['name']}</option>";
                }
            }

            $form = '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
			<fieldset>
				<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Featured products') . '</legend>
                <div style="clear:both;display:block;">
				    <label>' . $this->l('How many') . ':</label>
					<div class="margin-form">
						<input type="text" name="faceshop_homepage_itemcount" value="' . Configuration::get('faceshop_homepage_itemcount') . '">
						<p class="clear">' . $this->l('How many products you want to display in featured (homepage)?') . '</p>
					</div>
                </div>

                <div style="display:block; clear:both;">
				<label>' . $this->l('Category') . ':</label>
					<div class="margin-form">
						<SELECT name="faceshop_homepage_root">
	                        ' . $cat_select . '
                        </SELECT>
						<p class="clear">' . $this->l('Select category of featured products') . '</p>
					</div>
                </div>


                <div style="clear:both;display:block; ">
				    <label><span style="display:table-cell; vertical-align:middle;">' . $this->l('Add corner tag to product in SALE') . '</span><div style="display:table-cell; padding-left:10px;"><img src="' . $this->_path . 'img/promo.png" style="height:30px;" /></div></label>
					<div class="margin-form" valign="middle">
                        <div style="margin-top:7px;">
						<input type="checkbox" name="faceshop_homepage_disphot" value="1" ' . $checked_hot . '> ' . $this->l('yes') . '
                        </div>
					</div>
                </div>
                <div style="clear:both; display:block; margin-top:30px;">
				    <label><span style="display:table-cell; vertical-align:middle;">' . $this->l('Add corner tag to NEW products') . '</span><div style="display:table-cell; padding-left:10px;"><img src="' . $this->_path . 'img/new.png" style="height:30px;" /></div></label>
					<div class="margin-form" valign="middle">
                        <div style="margin-top:7px;">
						<input type="checkbox" name="faceshop_homepage_dispnew" value="1" ' . $checked_new . '> ' . $this->l('yes') . '
                        </div>
					</div>
                </div>
				<div style="display:block; clear:both">
				<center><input type="submit" name="homepage_items" value="' . $this->l('Save') . '" class="button" /></center>
				</div>
			</fieldset>
		</form>';
        }

        /** PRODUCTS IN CATEGORIES **/
        if ($var->faceshop_last_tab == "5")
        {
            $form = '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
			<fieldset>
				<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Products in categories') . '</legend>
                <div style="clear:both;display:block;">
				    <label>' . $this->l('How many') . ':</label>
					<div class="margin-form">
						<input type="text" name="faceshop_cat_itemcount" value="' . Configuration::get('faceshop_cat_itemcount') . '">
						<p class="clear">' . $this->l('How many products you want to display in each category?') . '</p>
					</div>
                </div>
                <div style="clear:both;display:block;">
				    <label><span style="display:table-cell; vertical-align:middle;">' . $this->l('Add corner tag to product in SALE') . '</span><div style="display:table-cell; padding-left:10px;"><img src="' . $this->_path . 'img/promo.png" style="height:30px;" /></div></label>
					<div class="margin-form" valign="middle">
                        <div style="margin-top:7px;">
						<input type="checkbox" name="faceshop_cat_disphot" value="1" ' . $checked_cathot . '> ' . $this->l('yes') . '
                        </div>
					</div>
                </div>
                <div style="clear:both;display:block; margin-top:30px;">
				    <label><span style="display:table-cell; vertical-align:middle;">' . $this->l('Add corner tag to NEW products') . '</span><div style="display:table-cell; padding-left:10px;"><img src="' . $this->_path . 'img/new.png" style="height:30px;" /></div></label>
					<div class="margin-form" valign="middle">
                        <div style="margin-top:7px;">
						<input type="checkbox" name="faceshop_cat_dispnew" value="1" ' . $checked_catnew . '> ' . $this->l('yes') . '
                        </div>
					</div>
                </div>
                <div style="display:block; clear:both;">
                        <label>' . $this->l('Sort method') . ':</label>
                            <div class="margin-form">
            					<input type="radio" name="faceshop_orderby" id="sort_on" value="0" ' . (Configuration::get('faceshop_orderby') == 0 ? 'checked="checked" ' : '') . '/>
            					<label class="t" for="sort_on"> <img src="../modules/' . $this->name . '/sort_number.png" alt="' . $this->l('Enabled') . '" title="' . $this->l('By ID') . '" />' . $this->l('By ID') . '</label>
            					<input type="radio" name="faceshop_orderby" id="sort_off" value="1" ' . (Configuration::get('faceshop_orderby') == 1 ? 'checked="checked" ' : '') . '/>
            					<label class="t" for="sort_off"> <img src="../modules/' . $this->name . '/sort_alphabet.png" alt="' . $this->l('Disabled') . '" title="' . $this->l('By name') . '" />' . $this->l('By name') . '</label> -
            					<select name="faceshop_orderway">
            						<option value="1" ' . (Configuration::get('orderway') == 1 ? 'selected="yes" ' : '') . '>' . $this->l('Ascending') . '</option>
            						<option value="0" ' . (Configuration::get('orderway') != 1 ? 'selected="yes" ' : '') . '>' . $this->l('Descending') . '</option>
            					</select>
                            </div>
            		</div>
				<div style="display:block; clear:both;">
				<center><input type="submit" name="cat_items" value="' . $this->l('Save') . '" class="button" /></center>
				</div>
			</fieldset>
		</form>';
        }


        /** BREADCRUMB **/
        if ($var->faceshop_last_tab == 6)
        {
            $form = '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
			<fieldset>
				<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Breadcrumb') . '</legend>

                <div style="clear:both;display:block;">
				    <label>' . $this->l('Display breadcrumb') . ':</label>
					<div class="margin-form" valign="middle">
                        <div style="margin-top:7px;">
						<input type="checkbox" name="faceshop_display_breadcrumb" value="1" ' . $checked_breadcrumb . '> ' . $this->l('yes') . '
						<p class="clear">' . $this->l('Check if you want to display breadcrumb') . '</p>
                        </div>
					</div>
                </div>

				<div style="display:block; clear:both;">
				<center><input type="submit" name="submit_breadcrumb" value="' . $this->l('Save') . '" class="button" /></center>
				</div>
			</fieldset>
		</form>';
        }

        /** ABOUT **/
        if ($var->faceshop_last_tab == 7)
        {
            $form = '
            <div style="width:100%; display:block;">
				<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
				<fieldset style="">
				<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Installation verification (debugging)') . '</legend>
                ' . $this->inconsistency() . '
                </fieldset>
                </form>
            </div>';
            $form .= '
                <div id="module_block_settings">
                    ' . $this->checkforupdates(0, 1) . '
                </div>';
        }

        /** PRODUCT LIST PROPERTIES **/
        if ($var->faceshop_last_tab == 8)
        {
            $langiso = "";
            $languages = Language::getLanguages(false);
            $id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
            foreach (Language::getLanguages(false) as $language)
            {
                $langiso .= '<div id="header_fsh_langarray_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $id_lang_default ? 'block' : 'none') . ';float: left;">
                <input type="text" id="fsh_langarray_' . $language['id_lang'] . '" name="fsh_langarray[' . $language['id_lang'] . ']" value="' . Configuration::get('fsh_langarray', $language['id_lang']) . '" style="width:110px;">
            </div>';
            }
            $langiso .= '<div class="flags_block">' . $this->displayFlags(Language::getLanguages(false), $id_lang_default, 'header_fsh_langarray', 'header_fsh_langarray', true) . '</div>';

            $form = '
			<div style="">
				<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
				<fieldset style="">
					<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Product list properties') . '</legend>

	                <div style="">
					<label>' . $this->l('Display name') . ':</label>
						<div class="margin-form">
							<input type="checkbox" value="1" name="faceshop_pdisp_name" ' . $checked_pdisp_name . '> ' . $this->l('yes') . '
							<p class="clear">' . $this->l('Check if you want to display product name') . '</p>
						</div>
	                </div>

	                <div style="clear:both;display:block;">
					    <label>' . $this->l('Trim product name') . ':</label>
						<div class="margin-form" valign="middle">
	                        <div style="margin-top:7px;">
							<input type="text" name="faceshop_pdisp_trim" value="' . Configuration::get('faceshop_pdisp_trim') . '">
							<p class="clear">' . $this->l('Enter the maximum length of the product name') . '</p>
	                        </div>
						</div>
	                </div>

	                <div style="display:block; clear:both;">
					<label>' . $this->l('Display price') . ':</label>
						<div class="margin-form">
							<input type="checkbox" value="1" name="faceshop_pdisp_price" ' . $checked_pdisp_price . '> ' . $this->l('yes') . '
							<p class="clear">' . $this->l('Check if you want to display product price') . '</p>
						</div>
	                </div>
	                <div style="display:block; clear:both;">
					<label>' . $this->l('Prices without tax') . ':</label>
						<div class="margin-form">
							<input type="checkbox" value="1" name="faceshop_prices_notax" ' . $faceshop_prices_notax . '> ' . $this->l('yes') . '
							<p class="clear">' . $this->l('Check if you want to display product prices without tax') . '</p>
						</div>
	                </div>


                    <div style="display:block; clear:both;">
					<label>' . $this->l('Currency sign as prefix') . ':</label>
						<div class="margin-form">
							<input type="checkbox" value="1" name="faceshop_preprice" ' . (Configuration::get('faceshop_preprice') ? 'checked="yes"' : '') . '> ' . $this->l('yes') . '
							<p class="clear">' . $this->l('Check if you want to display currency sign as prefix') . '</p>
						</div>
	                </div>

	                <div style="display:block; clear:both;">
					<label>' . $this->l('Display image') . ':</label>
						<div class="margin-form">
							<input type="checkbox" value="1" name="faceshop_pdisp_image" ' . $checked_pdisp_image . '> ' . $this->l('yes') . '
							<p class="clear">' . $this->l('Check if you want to display product cover image') . '</p>
						</div>
	                </div>

                    <div style="display:block; clear:both;">
					<label>' . $this->l('Product cover image') . ':</label>
						<div class="margin-form">
							<select name="faceshop_pdisp_imgtpe">' . $images_types_options . '</select>
						</div>
	                </div>

                    <div style="display:block; clear:both;">
					<label>' . $this->l('Display Add To Cart') . ':</label>
						<div class="margin-form">
							<input type="checkbox" value="1" name="faceshop_pdisp_acart" ' . (Configuration::get('faceshop_pdisp_acart') == 1 ? 'checked' : '') . '> ' . $this->l('yes') . '
							<p class="clear">' . $this->l('Check if you want to display add to cart button. Module will add product to cart and redirect customer to cart page.') . '</p>
						</div>
	                </div>
					<label>' . $this->l('SSL link to add to cart') . ':</label>
						<div class="margin-form">
							<input type="checkbox" value="1" name="faceshop_acart_ssl" ' . (Configuration::get('faceshop_acart_ssl') == 1 ? 'checked' : '') . '> ' . $this->l('yes') . '
							<p class="clear">' . $this->l('Check if you want to build "add to cart" links based on ssl protocol') . '</p>
						</div>
	                <label>' . $this->l('Analytics support') . ':</label>
						<div class="margin-form">
							<input type="checkbox" value="1" name="faceshop_analytics" ' . (Configuration::get('faceshop_analytics') == 1 ? 'checked' : '') . '> ' . $this->l('yes') . '
							<p class="clear">' . $this->l('Check if you want to create links to product pages with utm_source and utm_medium params') . '</p>
						</div>
	                </div>




					<div style="display:block; clear:both;">
					<center><input type="submit" name="submit_pdisp" value="' . $this->l('Save') . '" class="button" /></center>
					</div>
				</fieldset>
			</form>

			<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
				<fieldset style="margin-top:20px;">
					<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Social networks') . '</legend>
					<div style="display:block; clear:both;">
					<label>' . $this->l('Include Facebook application') . ':</label>
						<div class="margin-form">
							<input type="checkbox" value="1" name="fshp_fbapp" ' . (Configuration::get('fshp_fbapp') == 1 ? 'checked' : '') . '> ' . $this->l('yes') . '
							<p class="clear">' . $this->l('Use this feature if you want to use ssl connection between facebook shop and your shop') . '</p>
						</div>
	                </div>

	                <div style="display:block; clear:both;">
					<label>' . $this->l('Facebook like') . ':</label>
						<div class="margin-form">
							<input type="checkbox" value="1" name="faceshop_social_facebook" ' . $checked_social_facebook . '> ' . $this->l('yes') . '
							<p class="clear">' . $this->l('Check if you want to display facebook like button') . '</p>
						</div>
	                </div>
                    <div style="display:block; clear:both;">
                    <label>' . $this->l('Facebook share') . ':</label>
						<div class="margin-form">
							<input type="checkbox" value="1" name="faceshop_social_facebook_share" ' . (configuration::get('faceshop_share') == 1 ? 'checked="yes"' : '') . '> ' . $this->l('yes') . '
							<p class="clear">' . $this->l('Check if you want to display facebook share button') . '</p>
						</div>
	                </div>
                    <div style="display:block; clear:both;">
                    <label>' . $this->l('Language ISO CODE') . ':</label>
						<div class="margin-form" style="width:150px;">
							' . $langiso . '
							<p class="clear"><a href="http://mypresta.eu/en/art/know-how/facebook-list-of-local-language-codes.html" target="_blank"/>' . $this->l('Check how to define language ISO CODE') . '</a></p>
						</div>
                    </div>
                    <br/>
                    <br/>

	                <div style="display:block; clear:both;">
					<label>' . $this->l('Google+') . ':</label>
						<div class="margin-form">
							<input type="checkbox" value="1" name="faceshop_social_google" ' . $checked_social_google . '> ' . $this->l('yes') . '
							<p class="clear">' . $this->l('Check if you want to display google+ button') . '</p>
						</div>
	                </div>

	                <div style="display:block; clear:both;">
					<label>' . $this->l('Pinterest') . ':</label>
						<div class="margin-form">
							<input type="checkbox" value="1" name="faceshop_social_pinterest" ' . $checked_social_pinterest . '> ' . $this->l('yes') . '
							<p class="clear">' . $this->l('Select') . '</p>
						</div>
	                </div>

					<div style="display:block; clear:both;">
					<center><input type="submit" name="submit_social" value="' . $this->l('Save') . '" class="button" /></center>
					</div>
				</fieldset>
			</form>
		</div>
		';
        }

        /** Languages **/
        if ($var->faceshop_last_tab == 9)
        {
            $form = '
			<div style=" display:block;  overflow:hidden; margin:auto;">
				<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
				<fieldset>
					<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Languages') . '</legend>

	                <div style="display:block; clear:both;">
					<label>' . $this->l('Display language switcher') . ':</label>
						<div class="margin-form">
							<input type="checkbox" value="1" name="faceshop_lang_disp" ' . $checked_lang_disp . '> ' . $this->l('yes') . '
							<p class="clear">' . $this->l('check if you want to display language switcher') . '</p>
						</div>
	                </div>


					<div style="display:block; clear:both;">
					<center><input type="submit" name="submit_lang" value="' . $this->l('Save') . '" class="button" /></center>
					</div>
				</fieldset>
			</form>
		</div>
		';
        }


        /** Currencies **/
        if ($var->faceshop_last_tab == 10)
        {
            $form = '
			<div style=" display:block; overflow:hidden; margin:auto;">
				<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
				<fieldset style="display:block; width:100%; vertical-align:top;">
					<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Currencies') . '</legend>

	                <div style="display:block; clear:both;">
					<label>' . $this->l('Display currency switcher') . ':</label>
						<div class="margin-form">
							<input type="checkbox" value="1" name="faceshop_curr_disp" ' . $checked_curr_disp . '> ' . $this->l('yes') . '
							<p class="clear">' . $this->l('check if you want to display currency switcher') . '</p>
						</div>
	                </div>


					<div style="display:block; clear:both;">
					<center><input type="submit" name="submit_curr" value="' . $this->l('Save') . '" class="button" /></center>
					</div>
				</fieldset>
			</form>
		</div>
		';
        }


        /** colors **/
        if ($var->faceshop_last_tab == 11)
        {
            $form = '
            <script type="text/javascript" src="../modules/faceshop/jscolor/jscolor.js"></script>
			<div style="display:block; overflow:hidden; margin:auto;">
				<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
				<fieldset style="display:block; ertical-align:top;">
					<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Colors') . '</legend>

                    <div style="display:block; clear:both;">
					<label>' . $this->l('Faceshop Background Color') . ':</label>
						<div class="margin-form">
							<input class="color" style="width:40px;" type="text" id="fbs_bgc" name="fbs_bgc" value="' . Configuration::get('fbs_bgc') . '">
                            <span style="cursor:pointer;" onclick="$(\'#fbs_bgc\').val(\'FFFFFF\')">' . $this->l('reset to default') . '</span>
						</div>
	                </div>
                    <br/>
                    <br/>

	                <div style="display:block; clear:both;">
					<label>' . $this->l('Menu background color') . ':</label>
						<div class="margin-form">
							<input class="color" style="width:40px;" type="text" name="fbs_menu_bgc" id="fbs_menu_bgc" value="' . Configuration::get('fbs_menu_bgc') . '">
                            <span style="cursor:pointer;" onclick="$(\'#fbs_menu_bgc\').val(\'5372b0\')">' . $this->l('reset to default') . '</span>

						</div>
	                </div>
                    <div style="display:block; clear:both;">
					<label>' . $this->l('Menu item text color') . ':</label>
						<div class="margin-form">
							<input class="color" style="width:40px;" type="text" name="fbs_menu_bgc_color" id="fbs_menu_bgc_color" value="' . Configuration::get('fbs_menu_bgc_color') . '">
                            <span style="cursor:pointer;" onclick="$(\'#fbs_menu_bgc_color\').val(\'FFFFFF\')">' . $this->l('reset to default') . '</span>
						</div>
	                </div>
                    <div style="display:block; clear:both;">
					<label>' . $this->l('Menu item text shadow color') . ':</label>
						<div class="margin-form">
							<input class="color" style="width:40px;" type="text" name="fbs_menu_bgc_shadow" id="fbs_menu_bgc_shadow" value="' . Configuration::get('fbs_menu_bgc_shadow') . '">
                            <span style="cursor:pointer;" onclick="$(\'#fbs_menu_bgc_shadow\').val(\'3b5998\')">' . $this->l('reset to default') . '</span>
						</div>
	                </div>
                    <br/><br/>
					<div style="display:block; clear:both;">
					<label>' . $this->l('Menu item hover background color') . ':</label>
						<div class="margin-form">
							<input class="color" style="width:40px;" type="text" name="fbs_menu_bgc_hover" id="fbs_menu_bgc_hover" value="' . Configuration::get('fbs_menu_bgc_hover') . '">
                            <span style="cursor:pointer;" onclick="$(\'#fbs_menu_bgc_hover\').val(\'3B5998\')">' . $this->l('reset to default') . '</span>
						</div>
	                </div>
                    <div style="display:block; clear:both;">
					<label>' . $this->l('Menu item hover text color') . ':</label>
						<div class="margin-form">
							<input class="color" style="width:40px;" type="text" name="fbs_menu_bgc_hoverc" id="fbs_menu_bgc_hoverc" value="' . Configuration::get('fbs_menu_bgc_hoverc') . '">
                            <span style="cursor:pointer;" onclick="$(\'#fbs_menu_bgc_hoverc\').val(\'FFFFFF\')">' . $this->l('reset to default') . '</span>
						</div>
	                </div>
                    <div style="display:block; clear:both;">
					<label>' . $this->l('Menu item hover text shadow color') . ':</label>
						<div class="margin-form">
							<input class="color" style="width:40px;" type="text" name="fbs_menu_bgc_hovers" id="fbs_menu_bgc_hovers" value="' . Configuration::get('fbs_menu_bgc_hovers') . '">
                            <span style="cursor:pointer;" onclick="$(\'#fbs_menu_bgc_hovers\').val(\'2E4575\')">' . $this->l('reset to default') . '</span>
						</div>
	                </div>
                    <br/><br/><br/><br/>
                    <div style="display:block; clear:both;">
					<label>' . $this->l('Product background color') . ':</label>
						<div class="margin-form">
							<input class="color" style="width:40px;" type="text" name="fbs_product_bgc" id="fbs_product_bgc" value="' . Configuration::get('fbs_product_bgc') . '">
                            <span style="cursor:pointer;" onclick="$(\'#fbs_product_bgc\').val(\'FFFFFF\')">' . $this->l('reset to default') . '</span>
						</div>
	                </div>
                    <div style="display:block; clear:both;">
					<label>' . $this->l('Product border color') . ':</label>
						<div class="margin-form">
							<input class="color" style="width:40px;" type="text" name="fbs_product_bgc_borderc" id="fbs_product_bgc_borderc" value="' . Configuration::get('fbs_product_bgc_borderc') . '">
                            <span style="cursor:pointer;" onclick="$(\'#fbs_product_bgc_borderc\').val(\'E0E0E0\')">' . $this->l('reset to default') . '</span>
						</div>
	                </div>
                    <div style="display:block; clear:both;">
					<label>' . $this->l('Product old price color') . ':</label>
						<div class="margin-form">
							<input class="color" style="width:40px;" type="text" name="fbs_product_bgc_dcolor" id="fbs_product_bgc_dcolor" value="' . Configuration::get('fbs_product_bgc_dcolor') . '">
                            <span style="cursor:pointer;" onclick="$(\'#fbs_product_bgc_dcolor\').val(\'B5B5B5\')">' . $this->l('reset to default') . '</span>
						</div>
	                </div>

                    <div style="display:block; clear:both;">
					<label>' . $this->l('Product price color') . ':</label>
						<div class="margin-form">
							<input class="color" style="width:40px;" type="text" name="fbs_product_bgc_color" id="fbs_product_bgc_color" value="' . Configuration::get('fbs_product_bgc_color') . '">
                            <span style="cursor:pointer;" onclick="$(\'#fbs_product_bgc_color\').val(\'4D58FF\')">' . $this->l('reset to default') . '</span>
						</div>
	                </div>
                    <div style="display:block; clear:both;">
					<label>' . $this->l('Product name background') . ':</label>
						<div class="margin-form">
							<input class="color" style="width:40px;" type="text" name="fbs_product_bgc_name" id="fbs_product_bgc_name" value="' . Configuration::get('fbs_product_bgc_name') . '">
                            <span style="cursor:pointer;" onclick="$(\'#fbs_product_bgc_name\').val(\'F0F0F0\')">' . $this->l('reset to default') . '</span>
						</div>
	                </div>
                    <div style="display:block; clear:both;">
					<label>' . $this->l('Product name color') . ':</label>
						<div class="margin-form">
							<input class="color" style="width:40px;" type="text" name="fbs_product_bgc_namec" id="fbs_product_bgc_namec" value="' . Configuration::get('fbs_product_bgc_namec') . '">
                            <span style="cursor:pointer;" onclick="$(\'#fbs_product_bgc_namec\').val(\'919191\')">' . $this->l('reset to default') . '</span>
						</div>
	                </div>
                    <div style="display:block; clear:both;">
					<label>' . $this->l('Product name shadow color') . ':</label>
						<div class="margin-form">
							<input class="color" style="width:40px;" type="text" name="fbs_product_bgc_names" id="fbs_product_bgc_names" value="' . Configuration::get('fbs_product_bgc_names') . '">
                            <span style="cursor:pointer;" onclick="$(\'#fbs_product_bgc_names\').val(\'FFFFFF\')">' . $this->l('reset to default') . '</span>
						</div>
	                </div>
					<div style="display:block; clear:both;">
					<center><input type="submit" name="submit_colors" value="' . $this->l('Save') . '" class="button" /></center>
					</div>
				</fieldset>
			</form>
		</div>
		';
        }

        return '<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">' . "<script>/*<![CDATA[*/window.zEmbed||function(e,t){var n,o,d,i,s,a=[],r=document.createElement(\"iframe\");window.zEmbed=function(){a.push(arguments)},window.zE=window.zE||window.zEmbed,r.src=\"javascript:false\",r.title=\"\",r.role=\"presentation\",(r.frameElement||r).style.cssText=\"display: none\",d=document.getElementsByTagName(\"script\"),d=d[d.length-1],d.parentNode.insertBefore(r,d),i=r.contentWindow,s=i.document;try{o=s}catch(c){n=document.domain,r.src='javascript:var d=document.open();d.domain=\"'+n+'\";void(0);',o=s}o.open()._l=function(){var o=this.createElement(\"script\");n&&(this.domain=n),o.id=\"js-iframe-async\",o.src=e,this.t=+new Date,this.zendeskHost=t,this.zEQueue=a,this.body.appendChild(o)},o.write('<body onload=\"document._l();\">'),o.close()}(\"//assets.zendesk.com/embeddable_framework/main.js\",\"prestasupport.zendesk.com\");/*]]>*/</script>" . '
        <link href="../modules/faceshop/css.css" rel="stylesheet" type="text/css" />
        <form name="selectform0" id="selectform0" action="' . $_SERVER['REQUEST_URI'] . '" method="post"><input type="hidden" name="selecttab" value="0"></form>
        <form name="selectform1" id="selectform1" action="' . $_SERVER['REQUEST_URI'] . '" method="post"><input type="hidden" name="selecttab" value="1"></form>
        <form name="selectform2" id="selectform2" action="' . $_SERVER['REQUEST_URI'] . '" method="post"><input type="hidden" name="selecttab" value="2"></form>
        <form name="selectform3" id="selectform3" action="' . $_SERVER['REQUEST_URI'] . '" method="post"><input type="hidden" name="selecttab" value="3"></form>
        <form name="selectform4" id="selectform4" action="' . $_SERVER['REQUEST_URI'] . '" method="post"><input type="hidden" name="selecttab" value="4"></form>
        <form name="selectform5" id="selectform5" action="' . $_SERVER['REQUEST_URI'] . '" method="post"><input type="hidden" name="selecttab" value="5"></form>
        <form name="selectform6" id="selectform6" action="' . $_SERVER['REQUEST_URI'] . '" method="post"><input type="hidden" name="selecttab" value="6"></form>
		<form name="selectform7" id="selectform7" action="' . $_SERVER['REQUEST_URI'] . '" method="post"><input type="hidden" name="selecttab" value="7"></form>
		<form name="selectform8" id="selectform8" action="' . $_SERVER['REQUEST_URI'] . '" method="post"><input type="hidden" name="selecttab" value="8"></form>
        <form name="selectform9" id="selectform9" action="' . $_SERVER['REQUEST_URI'] . '" method="post"><input type="hidden" name="selecttab" value="9"></form>
        <form name="selectform10" id="selectform10" action="' . $_SERVER['REQUEST_URI'] . '" method="post"><input type="hidden" name="selecttab" value="10"></form>
        <form name="selectform11" id="selectform11" action="' . $_SERVER['REQUEST_URI'] . '" method="post"><input type="hidden" name="selecttab" value="11"></form>
        <form name="selectform12" id="selectform12" action="' . $_SERVER['REQUEST_URI'] . '" method="post"><input type="hidden" name="selecttab" value="12"></form>

        <div id="cssmenu" class="col-lg-3 col-md-3 col-xs-12 col-sm-12" style="z-index:9;">
            <ul class="form">
                <li class="' . $selected1 . '" onclick="selectform1.submit();"><a><span><i class="fa fa-wrench" aria-hidden="true"></i>' . $this->l('Header & footer') . '</span></a></li>
                <li class="' . $selected6 . '" onclick="selectform6.submit();"><a><span><i class="fa fa-location-arrow" aria-hidden="true"></i>' . $this->l('Breadcrumb') . '</span></a></li>
                <li class="' . $selected2 . '" onclick="selectform2.submit();"><a><span><i class="fa fa-picture-o" aria-hidden="true"></i>' . $this->l('Slider') . '</span></a></li>
                <li class="' . $selected3 . '" onclick="selectform3.submit();"><a><span><i class="fa fa-bars" aria-hidden="true"></i>' . $this->l('Main menu') . '</span></a></li>
                <li class="' . $selected11 . '" onclick="selectform11.submit();"><a><span><i class="fa fa-paint-brush"></i>' . $this->l('Colors') . '</span></a></li>
                <li class="' . $selected4 . '" onclick="selectform4.submit();"><a><span><i class="fa fa-list" aria-hidden="true"></i>' . $this->l('Featured products') . '</span></a></li>
                <li class="' . $selected5 . '" onclick="selectform5.submit();"><a><span><i class="fa fa-cubes" aria-hidden="true"></i>' . $this->l('Products in categories') . '</span></a></li>
                <li class="' . $selected8 . '" onclick="selectform8.submit();"><a><span><i class="fa fa-cogs" aria-hidden="true"></i>' . $this->l('Product list properties') . '</span></a></li>
                <li class="' . $selected9 . '" onclick="selectform9.submit();"><a><span><i class="fa fa-globe" aria-hidden="true"></i>' . $this->l('Languages') . '</span></a></li>
                <li class="' . $selected10 . '" onclick="selectform10.submit();"><a><span><i class="fa fa-money" aria-hidden="true"></i>' . $this->l('Currencies') . '</span></a></li>
                <li class="' . $selected12 . '" onclick="selectform12.submit();"><a><span><i class="fa fa-facebook-square" aria-hidden="true"></i>' . $this->l('Install on fanpage') . '</span></a></li>
                <li class="' . $selected7 . '" onclick="selectform7.submit();"><a><span><i class="fa fa-refresh" aria-hidden="true"></i>' . $this->l('Update') . '</span></a></li>
            </ul>
        </div>
        <div class="col-lg-9 col-md-9 col-xs-12 col-sm-12" style="z-index: 9;"><div class="' . ($this->psversion() == 6 || $this->psversion() == 7 ? 'nobootstrap' : '') . '" style="' . ($this->psversion() == 6 || $this->psversion() == 7 ? 'padding-top:0px!important; background:none!important; margin-top:20px!important; min-width:100%!important; max-width:100px!important; z-index:9;' : 'z-index:9;') . '">' . $form . '</div></div>';
    }

    public function runStatement($statement)
    {
        if (@!Db::getInstance()->Execute($statement))
        {
            return false;
        }
        return true;
    }

    public function display_footer()
    {
        if (Configuration::get('faceshop_footer_disp'))
        {
            echo '<div id="footer">';
            if (Configuration::get('faceshop_footer_button') == 1)
            {
                echo '<a href="' . _PS_BASE_URL_ . __PS_BASE_URI__ . '" target="_blank" class="fbutton">' . $this->l('Open our shop') . ' &rsaquo;</a>';
            }
            echo '</div>';
        }
    }

    public function breadcrumb($catname = null)
    {
        if (Configuration::get('faceshop_display_breadcrumb') == 1)
        {
            if (!(isset($_GET['go'])))
            {
                echo '<h2><span onclick="loadhomepage();">' . $this->l('Homepage') . '</span></h2>';
            }
            else
            {
                if ($_GET['go'] == "homepage")
                {
                    echo '<h2><span onclick="loadhomepage();">' . $this->l('Homepage') . '</span></h2>';
                }
                if ($_GET['go'] == "newproducts")
                {
                    echo '<h2><span onclick="loadnewproducts(' . $_GET['page'] . ');">' . $this->l('New products') . '</span></h2>';
                }
                if ($_GET['go'] == "manufacturers")
                {
                    $manufacturer = new Manufacturer($_GET['cid'], $this->getcookielang());
                    echo '<h2><span>' . $this->l('Manufacturers') . '</span> &rsaquo; ' . $manufacturer->name . '</h2>';
                }
                if ($_GET['go'] == "promotions")
                {
                    echo '<h2><span onclick="loadpromotions(' . $_GET['page'] . ');">' . $this->l('Promotions') . '</span></h2>';
                }
                if ($_GET['go'] == "products")
                {
                    $db = $this->getSimpleCategories();
                    if ($db)
                    {
                        foreach ($db as $k => $v)
                        {
                            if ($v['id_category'] == $_GET['cid'])
                            {
                                echo '<h2><span onclick="loadproducts(' . $_GET['cid'] . ',' . $_GET['page'] . ');">' . $v['name'] . '</span></h2>';
                            }
                        }
                    }
                }
            }
        }
    }

    public function getCMSpage($id)
    {
        if ($this->psver == 6 || $this->psversion() == 7)
        {
            $db = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS("
	        SELECT cl.content FROM " . _DB_PREFIX_ . "cms_lang AS cl
	        WHERE cl.id_lang='" . $this->getcookielang() . "' AND cl.id_cms='$id'
	        ");
        }
        if ($this->psver == 5)
        {
            $db = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS("
	        SELECT cl.content FROM " . _DB_PREFIX_ . "cms_lang AS cl
	        WHERE cl.id_lang='" . $this->getcookielang() . "' AND cl.id_cms='$id'
	        ");
        }
        if ($this->psver == 4)
        {
            $db = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS("
	        SELECT cl.content FROM " . _DB_PREFIX_ . "cms_lang AS cl
	        WHERE cl.id_lang='" . $this->getcookielang() . "' AND cl.id_cms='$id'
	        ");
        }
        if ($this->psver == 3)
        {
            $db = Db::getInstance()->ExecuteS("
	        SELECT cl.content FROM " . _DB_PREFIX_ . "cms_lang AS cl
	        WHERE cl.id_lang='" . $this->getcookielang() . "' AND cl.id_cms='$id'
	        ");
        }
        if ($this->psver == 2)
        {
            $db = Db::getInstance()->ExecuteS("
	        SELECT cl.content FROM " . _DB_PREFIX_ . "cms_lang AS cl
	        WHERE cl.id_lang='" . $this->getcookielang() . "' AND cl.id_cms='$id'
	        ");
        }

        if (isset($db[0]['content']))
        {
            echo $db[0]['content'];
        }
    }

    public function getCMS($lang)
    {
        return CMS::listCms($lang);
    }

    public function getCategories()
    {
        if ($this->psver == 6 || $this->psversion() == 7)
        {
            return Category::getCategories($this->getcookielang());
        }
        if ($this->psver == 5)
        {
            return Category::getCategories($this->getcookielang());
        }
        if ($this->psver == 4)
        {
            return CategoryCore::getCategories($this->getcookielang());
        }
        if ($this->psver == 3)
        {
            return Category::getCategories($this->getcookielang());
        }
        if ($this->psver == 2)
        {
            return Category::getCategories($this->getcookielang());
        }
    }

    public function getSimpleCategories()
    {
        if ($this->psver == 6 || $this->psversion() == 7)
        {
            return CategoryCore::getSimpleCategories($this->getcookielang());
        }
        if ($this->psver == 5)
        {
            return CategoryCore::getSimpleCategories($this->getcookielang());
        }
        if ($this->psver == 4)
        {
            return CategoryCore::getSimpleCategories($this->getcookielang());
        }
        if ($this->psver == 3)
        {
            return Category::getSimpleCategories($this->getcookielang());
        }
        if ($this->psver == 2)
        {
            return Category::getSimpleCategories($this->getcookielang());
        }
    }

    public function nivoSliderGetFiles()
    {
        echo '';
        $dir = opendir('../../modules/faceshop/nivoslider');
        $count = 0;
        while (false !== ($file = readdir($dir)))
        {
            if (($file == ".") || ($file == ".."))
            {
            }
            else
            {
                if (preg_match('@(.*)\.(png|jpg|jpeg|gif)@i', $file))
                {
                    $sliderarray[$count]['name'] = $file;
                    $count++;
                }
            }
        }
        return $sliderarray;
    }

    public function getProducts($pcategory = 0, $sales = 0, $newproducts = 0, $homefeatured = 0, $page = 0, $nb_per_page = 6, $orderby = 0)
    {
        if ($this->psver == 6 || $this->psversion() == 7)
        {
            return ProductCore::getProducts($this->getcookielang(), $page, $nb_per_page, "id_product", "DESC", $pcategory);
        }
        if ($this->psver == 5)
        {
            return ProductCore::getProducts($this->getcookielang(), $page, $nb_per_page, "id_product", "DESC", $pcategory);
        }
        if ($this->psver == 4)
        {
            return ProductCore::getProducts($this->getcookielang(), $page, $nb_per_page, "id_product", "DESC", $pcategory);
        }
        if ($this->psver == 3)
        {
            return Product::getProducts($this->getcookielang(), $page, $nb_per_page, "id_product", "DESC", $pcategory);
        }
        if ($this->psver == 2)
        {
            return Product::getProducts($this->getcookielang(), $page, $nb_per_page, "id_product", "DESC", $pcategory);
        }
    }

    public function get_category_products($counter = 0)
    {
        if (Configuration::get('faceshop_orderby') == "1")
        {
            $orderby = "name";
        }
        else
        {
            $orderby = "id_product";
        }
        if (Configuration::get('faceshop_orderway') == "1")
        {
            $orderway = "ASC";
        }
        else
        {
            $orderway = "DESC";
        }

        $category = new Category($_GET['cid'], $this->getcookielang());
        if ($counter == 1)
        {
            return $category->getProducts($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'), null, null, true);
        }
        else
        {
            return $category->getProducts($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'), $orderby, $orderway, false);
        }
    }


    public function get_manufacturer_products($counter = 0)
    {
        $Manufacurer = new Manufacturer($_GET['cid']);
        if ($counter == 1)
        {
            return $Manufacurer->getProducts($_GET['cid'], $this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'), null, null, true);
        }
        else
        {
            return $Manufacurer->getProducts($_GET['cid'], $this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'), null, null);
        }
    }


    public function get_homefeatured()
    {
        if ($this->psver == 6 || $this->psversion() == 7)
        {
            $category = new Category(Configuration::get('faceshop_homepage_root'), $this->getcookielang());
        }
        if ($this->psver == 5)
        {
            $category = new Category(Configuration::get('faceshop_homepage_root'), $this->getcookielang());
        }
        if ($this->psver == 4)
        {
            $category = new Category(Configuration::get('faceshop_homepage_root'), $this->getcookielang());
        }
        if ($this->psver == 3)
        {
            $category = new Category(Configuration::get('faceshop_homepage_root'), $this->getcookielang());
        }
        if ($this->psver == 2)
        {
            $category = new Category(Configuration::get('faceshop_homepage_root'), $this->getcookielang());
        }
        return $category->getProducts($this->getcookielang(), 1, Configuration::get('faceshop_homepage_itemcount'), null, null, false, true, true, Configuration::get('faceshop_homepage_itemcount'));
    }

    public function get_promotions($counter = 0)
    {
        if ($counter == 1)
        {
            if ($this->psver == 6 || $this->psversion() == 7)
            {
                return ProductCore::getPricesDrop($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'), true);
            }
            if ($this->psver == 5)
            {
                return ProductCore::getPricesDrop($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'), true);
            }
            if ($this->psver == 4)
            {
                return ProductCore::getPricesDrop($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'), true);
            }
            if ($this->psver == 3)
            {
                return Product::getPricesDrop($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'), true);
            }
            if ($this->psver == 2)
            {
                return Product::getPricesDrop($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'), true);
            }
        }
        else
        {
            if ($this->psver == 6 || $this->psversion() == 7)
            {
                return ProductCore::getPricesDrop($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'));
            }
            if ($this->psver == 5)
            {
                return ProductCore::getPricesDrop($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'));
            }
            if ($this->psver == 4)
            {
                return ProductCore::getPricesDrop($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'));
            }
            if ($this->psver == 3)
            {
                return Product::getPricesDrop($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'));
            }
            if ($this->psver == 2)
            {
                return Product::getPricesDrop($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'));
            }
        }
    }

    public function get_new_products($counter = 0)
    {
        if ($counter == 1)
        {
            if ($this->psver == 6 || $this->psversion() == 7)
            {
                return ProductCore::getNewProducts($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'), true);
            }
            if ($this->psver == 5)
            {
                return ProductCore::getNewProducts($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'), true);
            }
            if ($this->psver == 4)
            {
                return ProductCore::getNewProducts($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'), true);
            }
            if ($this->psver == 3)
            {
                return Product::getNewProducts($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'), true);
            }
            if ($this->psver == 2)
            {
                return Product::getNewProducts($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'), true);
            }
        }
        else
        {
            if ($this->psver == 6 || $this->psversion() == 7)
            {
                return ProductCore::getNewProducts($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'));
            }
            if ($this->psver == 5)
            {
                return ProductCore::getNewProducts($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'));
            }
            if ($this->psver == 4)
            {
                return ProductCore::getNewProducts($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'));
            }
            if ($this->psver == 3)
            {
                return Product::getNewProducts($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'));
            }
            if ($this->psver == 2)
            {
                return Product::getNewProducts($this->getcookielang(), $_GET['page'], Configuration::get('faceshop_cat_itemcount'));
            }
        }
    }


    public function nivoSliderGenerateImg()
    {
        $return = "";
        $sliderarray = $this->get_sliderfiles();
        foreach ($sliderarray as $key => $value)
        {
            $return .= '<a href="' . $value['url'] . '" class="nivo-imageLink" style="display:block;" target="_blank"><img src="' . _MODULE_DIR_ . 'faceshop/nivoslider/' . $value['filename'] . '"></a>';
        }
        return $return;
    }

    public function nivoSlider($disp = 1)
    {
        $nivoimg = $this->nivoSliderGenerateImg();
        if ($disp == 1)
        {
            echo '
            <div style="width:810px; display:block; clear:both; margin:auto;">
                <div class="slider-wrapper theme-default" >
                    <div id="slider" class="nivoSlider">' . $nivoimg . '
                    </div>
                </div>
            </div>
            ';
        }
    }

    public function dispHeader($disp = 1)
    {
        if ($disp == 1)
        {
            echo "<div class=\"header\">" . (Configuration::get('faceshop_header_link') == 1 ? '<a target="_blank" href="' . Configuration::get('faceshop_header_linkurl') . '" style="display:block; width:100%; height:100%;">' : '') . "</a></div>";
        }
    }

    public function prepareCategoryTree($db)
    {
        $exclude = explode(',', Configuration::get('faceshop_cat_exclude'));
        foreach ($db as $k => $v)
        {
            foreach ($v as $kk => $vv)
            {
                if (!in_array($vv['infos']['id_category'], $exclude))
                {
                    $da[$k][$kk] = $vv;
                }
            }
        }
        return $da;
    }

    public function displayMenu($db, $new, $sales, $informations, $manufacturers, $displaylang)
    {
        $db = $this->prepareCategoryTree($db);
        $tree = "";
        if (configuration::get('faceshop_cat_dispstyle') == 1)
        {
            foreach ($db as $key => $value)
            {
                foreach ($value as $k => $v)
                {
                    if ($v['infos']['id_parent'] == Configuration::get('faceshop_cat_root'))
                    {
                        $tree .= "<li><div class=\"button\" onclick=\"loadproducts({$v['infos']['id_category']},0);\">" . $v['infos']['name'] . "</div>";
                        if (Configuration::get('faceshop_menu_catstyle') == 2)
                        {
                            foreach ($db as $k2 => $v2)
                            {
                                foreach ($v2 as $subk => $subv)
                                {
                                    if ($subv['infos']['id_parent'] == $v['infos']['id_category'])
                                    {
                                        $tree .= "<div class=\"button level2\" onclick=\"loadproducts($subk,0);\"> " . $subv['infos']['name'] . "</div>";
                                        if (Configuration::get('faceshop_menu_catstyle') == 2)
                                        {
                                            foreach ($db as $k3 => $v3)
                                            {
                                                foreach ($v3 as $subk2 => $subv2)
                                                {
                                                    if ($subv2['infos']['id_parent'] == $subv['infos']['id_category'])
                                                    {
                                                        $tree .= "<div class=\"button level3\" onclick=\"loadproducts($subk2,0);\"> " . $subv2['infos']['name'] . "</div>";
                                                        foreach ($db as $k4 => $v4)
                                                        {
                                                            foreach ($v4 as $subk3 => $subv3)
                                                            {
                                                                if ($subv3['infos']['id_parent'] == $subv2['infos']['id_category'])
                                                                {
                                                                    $tree .= "<div class=\"button level4\" onclick=\"loadproducts($subk3,0);\"> " . $subv3['infos']['name'] . "</div>";
                                                                    foreach ($db as $k5 => $v5)
                                                                    {
                                                                        foreach ($v5 as $subk4 => $subv4)
                                                                        {
                                                                            if ($subv4['infos']['id_parent'] == $subv3['infos']['id_category'])
                                                                            {
                                                                                $tree .= "<div class=\"button level5\" onclick=\"loadproducts($subk4,0);\"> " . $subv4['infos']['name'] . "</div>";
                                                                                foreach ($db as $k6 => $v6)
                                                                                {
                                                                                    foreach ($v6 as $subk5 => $subv5)
                                                                                    {
                                                                                        if ($subv5['infos']['id_parent'] == $subv4['infos']['id_category'])
                                                                                        {
                                                                                            $tree .= "<div class=\"button level6\" onclick=\"loadproducts($subk5,0);\"> " . $subv5['infos']['name'] . "</div>";
                                                                                            foreach ($db as $k7 => $v7)
                                                                                            {
                                                                                                foreach ($v7 as $subk6 => $subv6)
                                                                                                {
                                                                                                    if ($subv6['infos']['id_parent'] == $subv5['infos']['id_category'])
                                                                                                    {
                                                                                                        $tree .= "<div class=\"button level7\" onclick=\"loadproducts($subk6,0);\"> " . $subv6['infos']['name'] . "</div>";
                                                                                                        foreach ($db as $k8 => $v8)
                                                                                                        {
                                                                                                            foreach ($v8 as $subk7 => $subv7)
                                                                                                            {
                                                                                                                if ($subv7['infos']['id_parent'] == $subv6['infos']['id_category'])
                                                                                                                {
                                                                                                                    $tree .= "<div class=\"button level8\" onclick=\"loadproducts($subk7,0);\"> " . $subv7['infos']['name'] . "</div>";
                                                                                                                    foreach ($db as $k9 => $v9)
                                                                                                                    {
                                                                                                                        foreach ($v9 as $subk8 => $subv8)
                                                                                                                        {
                                                                                                                            if ($subv8['infos']['id_parent'] == $subv7['infos']['id_category'])
                                                                                                                            {
                                                                                                                                $tree .= "<div class=\"button level9\" onclick=\"loadproducts($subk8,0);\"> " . $subv8['infos']['name'] . "</div>";
                                                                                                                                foreach ($db as $k10 => $v10)
                                                                                                                                {
                                                                                                                                    foreach ($v10 as $subk9 => $subv9)
                                                                                                                                    {
                                                                                                                                        if ($subv9['infos']['id_parent'] == $subv8['infos']['id_category'])
                                                                                                                                        {
                                                                                                                                            $tree .= "<div class=\"button level10\" onclick=\"loadproducts($subk9,0);\"> " . $subv9['infos']['name'] . "</div>";
                                                                                                                                            foreach ($db as $k11 => $v11)
                                                                                                                                            {
                                                                                                                                                foreach ($v11 as $subk10 => $subv10)
                                                                                                                                                {
                                                                                                                                                    if ($subv10['infos']['id_parent'] == $subv9['infos']['id_category'])
                                                                                                                                                    {
                                                                                                                                                        $tree .= "<div class=\"button level11\" onclick=\"loadproducts($subk10,0);\"> " . $subv10['infos']['name'] . "</div>";
                                                                                                                                                        foreach ($db as $k12 => $v12)
                                                                                                                                                        {
                                                                                                                                                            foreach ($v12 as $subk11 => $subv11)
                                                                                                                                                            {
                                                                                                                                                                if ($subv11['infos']['id_parent'] == $subv10['infos']['id_category'])
                                                                                                                                                                {
                                                                                                                                                                    $tree .= "<div class=\"button level11\" onclick=\"loadproducts($subk11,0);\"> " . $subv11['infos']['name'] . "</div>";

                                                                                                                                                                }
                                                                                                                                                            }
                                                                                                                                                        }
                                                                                                                                                    }
                                                                                                                                                }
                                                                                                                                            }
                                                                                                                                        }
                                                                                                                                    }
                                                                                                                                }
                                                                                                                            }
                                                                                                                        }
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $tree .= "</li>";
                    }
                }
            }
        }

        if (configuration::get('faceshop_cat_dispstyle') == 2 || configuration::get('faceshop_cat_dispstyle') == 3)
        {
            foreach ($db as $key => $value)
            {
                foreach ($value as $k => $v)
                {
                    if ($v['infos']['id_parent'] == Configuration::get('faceshop_cat_root'))
                    {
                        $hiddencat = "";
                        if (configuration::get('faceshop_cat_dispstyle') == 3)
                        {
                            $hiddencat = 'onmouseout="document.getElementById(\'hiddencat_' . $v['infos']['id_category'] . '\').style.display=\'none\'" onmouseover="document.getElementById(\'hiddencat_' . $v['infos']['id_category'] . '\').style.display=\'block\'"';
                        }
                        $tree .= "<li $hiddencat><div class=\"button\" onclick=\"loadproducts({$v['infos']['id_category']},0);\"><h2 style=\"margin-bottom:0px; padding:0px; position:relative;\">" . $v['infos']['name'] . "</h2></div>";

                        $dispnone = "";
                        if (configuration::get('faceshop_cat_dispstyle') == 3)
                        {
                            $dispnone = "	-moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; display:none; position:absolute; top:30px; left:-1px; z-index:2; padding:5px; border: 1px solid #597abd; border-top:none; ";
                        }
                        $tree .= '<div style="' . $dispnone . ' width:178px; background:#5575b5;" id="hiddencat_' . $v['infos']['id_category'] . '">';
                        if (Configuration::get('faceshop_menu_catstyle') == 2)
                        {
                            foreach ($db as $k2 => $v2)
                            {
                                foreach ($v2 as $subk => $subv)
                                {
                                    if ($subv['infos']['id_parent'] == $v['infos']['id_category'])
                                    {
                                        $tree .= "<div class=\"button level2\" onclick=\"loadproducts($subk,0);\"> " . $subv['infos']['name'] . "</div>";
                                        if (Configuration::get('faceshop_menu_catstyle') == 2)
                                        {
                                            foreach ($db as $k3 => $v3)
                                            {
                                                foreach ($v3 as $subk2 => $subv2)
                                                {
                                                    if ($subv2['infos']['id_parent'] == $subv['infos']['id_category'])
                                                    {
                                                        $tree .= "<div class=\"button level3\" onclick=\"loadproducts($subk2,0);\"> " . $subv2['infos']['name'] . "</div>";
                                                        foreach ($db as $k4 => $v4)
                                                        {
                                                            foreach ($v4 as $subk3 => $subv3)
                                                            {
                                                                if ($subv3['infos']['id_parent'] == $subv2['infos']['id_category'])
                                                                {
                                                                    $tree .= "<div class=\"button level4\" onclick=\"loadproducts($subk3,0);\"> " . $subv3['infos']['name'] . "</div>";
                                                                    foreach ($db as $k5 => $v5)
                                                                    {
                                                                        foreach ($v5 as $subk4 => $subv4)
                                                                        {
                                                                            if ($subv4['infos']['id_parent'] == $subv3['infos']['id_category'])
                                                                            {
                                                                                $tree .= "<div class=\"button level5\" onclick=\"loadproducts($subk4,0);\"> " . $subv4['infos']['name'] . "</div>";
                                                                                foreach ($db as $k6 => $v6)
                                                                                {
                                                                                    foreach ($v6 as $subk5 => $subv5)
                                                                                    {
                                                                                        if ($subv5['infos']['id_parent'] == $subv4['infos']['id_category'])
                                                                                        {
                                                                                            $tree .= "<div class=\"button level6\" onclick=\"loadproducts($subk5,0);\"> " . $subv5['infos']['name'] . "</div>";
                                                                                            foreach ($db as $k7 => $v7)
                                                                                            {
                                                                                                foreach ($v7 as $subk6 => $subv6)
                                                                                                {
                                                                                                    if ($subv6['infos']['id_parent'] == $subv5['infos']['id_category'])
                                                                                                    {
                                                                                                        $tree .= "<div class=\"button level7\" onclick=\"loadproducts($subk6,0);\"> " . $subv6['infos']['name'] . "</div>";
                                                                                                        foreach ($db as $k8 => $v8)
                                                                                                        {
                                                                                                            foreach ($v8 as $subk7 => $subv7)
                                                                                                            {
                                                                                                                if ($subv7['infos']['id_parent'] == $subv6['infos']['id_category'])
                                                                                                                {
                                                                                                                    $tree .= "<div class=\"button level8\" onclick=\"loadproducts($subk7,0);\"> " . $subv7['infos']['name'] . "</div>";
                                                                                                                    foreach ($db as $k9 => $v9)
                                                                                                                    {
                                                                                                                        foreach ($v9 as $subk8 => $subv8)
                                                                                                                        {
                                                                                                                            if ($subv8['infos']['id_parent'] == $subv7['infos']['id_category'])
                                                                                                                            {
                                                                                                                                $tree .= "<div class=\"button level9\" onclick=\"loadproducts($subk8,0);\"> " . $subv8['infos']['name'] . "</div>";
                                                                                                                                foreach ($db as $k10 => $v10)
                                                                                                                                {
                                                                                                                                    foreach ($v10 as $subk9 => $subv9)
                                                                                                                                    {
                                                                                                                                        if ($subv9['infos']['id_parent'] == $subv8['infos']['id_category'])
                                                                                                                                        {
                                                                                                                                            $tree .= "<div class=\"button level10\" onclick=\"loadproducts($subk9,0);\"> " . $subv9['infos']['name'] . "</div>";
                                                                                                                                            foreach ($db as $k11 => $v11)
                                                                                                                                            {
                                                                                                                                                foreach ($v11 as $subk10 => $subv10)
                                                                                                                                                {
                                                                                                                                                    if ($subv10['infos']['id_parent'] == $subv9['infos']['id_category'])
                                                                                                                                                    {
                                                                                                                                                        $tree .= "<div class=\"button level11\" onclick=\"loadproducts($subk10,0);\"> " . $subv10['infos']['name'] . "</div>";
                                                                                                                                                        foreach ($db as $k12 => $v12)
                                                                                                                                                        {
                                                                                                                                                            foreach ($v12 as $subk11 => $subv11)
                                                                                                                                                            {
                                                                                                                                                                if ($subv11['infos']['id_parent'] == $subv10['infos']['id_category'])
                                                                                                                                                                {
                                                                                                                                                                    $tree .= "<div class=\"button level11\" onclick=\"loadproducts($subk11,0);\"> " . $subv11['infos']['name'] . "</div>";

                                                                                                                                                                }
                                                                                                                                                            }
                                                                                                                                                        }
                                                                                                                                                    }
                                                                                                                                                }
                                                                                                                                            }
                                                                                                                                        }
                                                                                                                                    }
                                                                                                                                }
                                                                                                                            }
                                                                                                                        }
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }

                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $tree .= '</div>';
                        $tree .= "</li>";
                    }
                }
            }
        }


        if (configuration::get('faceshop_cat_dispstyle') == 4)
        {
            foreach ($db as $key => $value)
            {
                $tree .= "<ul>";
                foreach ($value as $k => $v)
                {
                    if ($v['infos']['id_parent'] == Configuration::get('faceshop_cat_root'))
                    {
                        $tree .= "<li><a class=\"button\" onclick=\"loadproducts({$v['infos']['id_category']},0);\">" . $v['infos']['name'] . "</a>";
                        foreach ($db as $k2 => $v2)
                        {
                            $tree .= "<ul>";
                            foreach ($v2 as $subk => $subv)
                            {
                                if ($subv['infos']['id_parent'] == $v['infos']['id_category'])
                                {
                                    $tree .= "<li><a class=\"button\" onclick=\"loadproducts({$subv['infos']['id_category']},0);\">" . $subv['infos']['name'] . "</a>";
                                    foreach ($db as $k3 => $v3)
                                    {
                                        $tree .= "<ul>";
                                        foreach ($v3 as $subk2 => $subv2)
                                        {
                                            if ($subv2['infos']['id_parent'] == $subv['infos']['id_category'])
                                            {
                                                $tree .= "<li><a class=\"button\" onclick=\"loadproducts({$subv2['infos']['id_category']},0);\">" . $subv2['infos']['name'] . "</a>";
                                                foreach ($db as $k4 => $v4)
                                                {
                                                    $tree .= "<ul>";
                                                    foreach ($v4 as $subk3 => $subv3)
                                                    {
                                                        if ($subv3['infos']['id_parent'] == $subv2['infos']['id_category'])
                                                        {
                                                            $tree .= "<li><a class=\"button\" onclick=\"loadproducts({$subv3['infos']['id_category']},0);\">" . $subv3['infos']['name'] . "</a>";
                                                            foreach ($db as $k5 => $v5)
                                                            {
                                                                $tree .= "<ul>";
                                                                foreach ($v5 as $subk4 => $subv4)
                                                                {
                                                                    if ($subv4['infos']['id_parent'] == $subv3['infos']['id_category'])
                                                                    {
                                                                        $tree .= "<li><a class=\"button\" onclick=\"loadproducts({$subv4['infos']['id_category']},0);\">" . $subv4['infos']['name'] . "</a>";
                                                                    }
                                                                    $tree .= "</li>";
                                                                }
                                                                $tree .= "</ul>";
                                                            }
                                                        }
                                                        $tree .= "</li>";
                                                    }
                                                    $tree .= "</ul>";
                                                }
                                            }
                                            $tree .= "</li>";
                                        }
                                        $tree .= "</ul>";
                                    }
                                }
                                $tree .= "</li>";
                            }
                            $tree .= "</ul>";
                        }
                    }
                    $tree .= "</li>";
                }
                $tree .= "</ul>";
            }
        }


        if (configuration::get('faceshop_cat_dispstyle') == 1)
        {
            echo '
		    <div class="colors_menu" style="width: 810px; display:block; background:#3B5998; color:#FFF; position:relative; margin:auto;">
		    <ul id="menu">
		    <li onclick="loadhomepage();"><span>' . $this->l('Homepage') . '</span></li>
		    <li style="" id="catsub1menu" style="postion:relative;" onmouseout="document.getElementById(\'subcategories1\').style.display=\'none\'" onmouseover="document.getElementById(\'subcategories1\').style.display=\'block\'"><span>' . $this->l('Categories') . '</span>
		    ';
        }

        if (configuration::get('faceshop_cat_dispstyle') == 2 || configuration::get('faceshop_cat_dispstyle') == 3)
        {
            echo '
		    <div class="colors_menu" style="width: 810px; display:block; background:#3B5998; color:#FFF; position:relative; margin:auto;">
		    <ul id="menu">
		    <li onclick="loadhomepage();"><span>' . $this->l('Homepage') . '</span></li>
		    <li style="" id="catsub1menu" style="" onmouseout="document.getElementById(\'subcategories1\').style.display=\'none\'" onmouseover="document.getElementById(\'subcategories1\').style.display=\'block\'"><span>' . $this->l('Categories') . '</span>
		    ';
        }

        if (configuration::get('faceshop_cat_dispstyle') == 4)
        {
            echo '
		    <div class="colors_menu" style="width: 810px; display:block; background:#3B5998; color:#FFF; position:relative; margin:auto;">
		    <ul id="menu">
		    <li onclick="loadhomepage();"><span>' . $this->l('Homepage') . '</span></li>
		    <li style="" id="catsub4menu" style="" onmouseout="document.getElementById(\'subcategories4\').style.display=\'none\'" onmouseover="document.getElementById(\'subcategories4\').style.display=\'block\'"><span>' . $this->l('Categories') . '</span>
		    ';
        }

        if (configuration::get('faceshop_cat_dispstyle') == 1)
        {
            echo "<ul id=\"subcategories1\" class=\"subcategories\">$tree</ul>";
        }

        if (configuration::get('faceshop_cat_dispstyle') == 2 || configuration::get('faceshop_cat_dispstyle') == 3)
        {
            echo "<ul id=\"subcategories1\" class=\"subcategories2\"><div class=\"subcategories2div\">$tree</div></ul>";
        }
        if (configuration::get('faceshop_cat_dispstyle') == 4)
        {
            echo "<ul id=\"subcategories4\" class=\"subcategories\"><li>$tree</li></ul>";
        }


        // LANGUAGES
        $selected_ver = null;
        $languages_option = '';
        $languages_select = '';
        if (configuration::get('faceshop_lang_disp') == 1)
        {
            global $smarty;
            if ($this->psversion() == 3)
            {
                $langdir = $smarty->_tpl_vars['img_lang_dir'];
            }
            elseif ($this->psversion()==7){
                $langdir=_THEME_LANG_DIR_;
            }
            else
            {
                $langdir = $smarty->tpl_vars['img_lang_dir']->value;
            }
            
            
            $languages = Language::getLanguages();
            if (isset($_COOKIE['fcbshop_lang']))
            {
                foreach ($languages as $k => $v)
                {
                    if ($v['id_lang'] == $_COOKIE['fcbshop_lang'])
                    {
                        $selected_ver = 1;
                        $selected = "<img src=\"$langdir{$v['id_lang']}.jpg\" />";
                        break;
                    }
                }
            }
            if (!$selected_ver == 1)
            {
                $selected = "<img src=\"$langdir" . $this->getcookielang() . ".jpg\" />";
            }

            foreach ($languages as $k => $v)
            {
                $languages_option .= "<li><div class=\"button\" onclick=\"changelang('{$v['id_lang']}');\"><img src=\"$langdir{$v['id_lang']}.jpg\" /></div></li>";
            }
            $languages_select = '<li style="float:right;" onmouseout="document.getElementById(\'slanguages\').style.display=\'none\'" onmouseover="document.getElementById(\'slanguages\').style.display=\'block\'"><span>' . $selected . '</span><ul class="subcategories" id="slanguages"><li>' . $languages_option . '</li></ul></li>';
        }


        //CURRENCIES
        $currencies_option = '';
        $currencies_select = '';
        if (configuration::get('faceshop_curr_disp') == 1)
        {
            $currencies = Currency::getCurrencies(false, 1);
            $selected = $this->getcurrencyiso($this->getcookiecurr());
            foreach ($currencies as $k => $v)
            {
                $currencies_option .= "<li><div class=\"button\" onclick=\"changecurrency('{$v['id_currency']}');\">{$v['iso_code']}</div></li>";
            }
            $currencies_select = '<li style="float:right;" onmouseout="document.getElementById(\'scurrencies\').style.display=\'none\'" onmouseover="document.getElementById(\'scurrencies\').style.display=\'block\'"><span>' . $selected . '</span><ul class="subcategories" id="scurrencies"><li>' . $currencies_option . '</li></ul></li>';
        }

        $manufacturers_list = '';
        foreach (Manufacturer::getManufacturers() as $k => $v)
        {
            $manufacturers_list .= "<li onclick=\"loadmanufacturers('{$v['id_manufacturer']}','0');\"><div class=\"button\">{$v['name']}</div></li>";
        }

        $manufacturers_list = "<ul class=\"subcategories\" id=\"manufacturers-list\">" . $manufacturers_list . "</ul>";


        echo "
        </li>";
        if ($manufacturers == "1")
        {
            echo '<li id="manufacturers"><span>' . $this->l('Manufacturers') . '</span>' . $manufacturers_list . '</li>';
        }
        if ($new == "1")
        {
            echo '<li onclick="loadnewproducts(0);"><span>' . $this->l('New products') . '</span></li>';
        }
        if ($sales == "1")
        {
            echo '<li onclick="loadpromotions(0);"><span>' . $this->l('Promotions') . '</span></li>';
        }
        if ($informations == "1")
        {
            echo '
        <li onmouseout="document.getElementById(\'subcategories2\').style.display=\'none\'" onmouseover="document.getElementById(\'subcategories2\').style.display=\'block\'"><span>' . $this->l('Informations') . '</span>
            <ul id="subcategories2" class="subcategories">
                ';
            $db = $this->getCMS($this->getcookielang());
            foreach ($db as $key => $value)
            {
                echo "<li><div class=\"button\" onclick=\"loadcms({$value['id_cms']})\">{$value['meta_title']}</div></li>";
            }
            echo "
            </ul>
        </li>";
        }
        echo "$languages_select $currencies_select
        </ul>

        </div>";
    }
    
    public static function getCurrent()
    {
        return Context::getContext()->currency;
    }
    
    public function product_display($array, $homepage = 0)
    {
        $var = $this->getconf();
        echo "<div style=\"clear:both;\">";
        if ($this->psver == 2)
        {
            global $smarty;
            $currency = $smarty->ps_currency;
        }
        else
        {
            $currency = $this->getCurrent();
        }


        $count = 0;
        if ($array)
        {
            foreach ($array as $key => $value)
            {
                if ($homepage == 1)
                {
                    if ((Configuration::get('faceshop_homepage_dispnew') == "1") && ($value['new']) == "1")
                    {
                        $new = "<img src=\"img/new.png\" style=\"position:absolute; top:-3px; left:-3px;\">";
                    }
                    else
                    {
                        $new = "";
                    }
                    if ((Configuration::get('faceshop_homepage_disphot') == "1") && ($value['on_sale']) == "1")
                    {
                        $hot = "<img src=\"img/promo.png\" style=\"position:absolute; top:-3px; right:-3px;\">";
                    }
                    else
                    {
                        $hot = "";
                    }
                }
                else
                {
                    if ((Configuration::get('faceshop_cat_dispnew') == "1") && ($value['new']) == "1")
                    {
                        $new = "<img src=\"img/new.png\" style=\"position:absolute; top:-3px; left:-3px;\">";
                    }
                    else
                    {
                        $new = "";
                    }
                    if ((Configuration::get('faceshop_cat_disphot') == "1") && ($value['on_sale']) == "1")
                    {
                        $hot = "<img src=\"img/promo.png\" style=\"position:absolute; top:-3px; right:-3px;\">";
                    }
                    else
                    {
                        $hot = "";
                    }
                }

                if ($this->psver == 6 || $this->psversion() == 7)
                {
                    $link = new LinkCore();
                }
                if ($this->psver == 5)
                {
                    $link = new LinkCore();
                }
                if ($this->psver == 4)
                {
                    $link = new LinkCore();
                }

                if ($this->psver == 3)
                {
                    $link = new Link();
                }
                if ($this->psver == 2)
                {
                    $link = new Link();
                }

                $linkimage = $link->getImageLink($value['link_rewrite'], $value['id_image'], $var->faceshop_pdisp_imgtpe);


                if ($this->psver == 6 || $this->psver == 5 || $this->psversion() == 7)
                {
                    $httpver = substr($linkimage, 0, 7);
                    if (!($httpver == "http://"))
                    {
                        $linkimage = "http://" . $linkimage;
                    }
                    if (!empty($_SERVER['HTTPS']))
                    {
                        $linkimage = str_replace("http://", "https://", $linkimage);
                    }

                }

                if ($this->psver == 4)
                {
                    $httpver = substr($linkimage, 0, 7);
                    if (!($httpver == "http://"))
                    {
                        $linkimage = "http://" . $linkimage;
                    }
                    if (!empty($_SERVER['HTTPS']))
                    {
                        $linkimage = str_replace("http://", "https://", $linkimage);
                    }
                }


                if ($value['reduction'] > 0)
                {
                    if ($this->psver == 2)
                    {
                        $price_reduction = "<span style=\"font-size:12px; font-weight:normal; color:#c0c0c0; display:block;clear:both; text-align:center;\"><strike>" . (Configuration::get('faceshop_preprice') == 1 ? $currency->sign . ' ' : '') . number_format("{$value['price_without_reduction']}", $currency->decimals) . (Configuration::get('faceshop_preprice') == 1 ? '' : ' ' . $currency->sign) . "</strike></span>";
                    }
                    else
                    {
                        $price_reduction = "<span style=\"font-size:12px; font-weight:normal; color:#c0c0c0; display:block;clear:both; text-align:center;\"><strike>" . (Configuration::get('faceshop_preprice') == 1 ? $currency->sign . ' ' : '') . number_format("{$value['price_without_reduction']}", $currency->decimals) . (Configuration::get('faceshop_preprice') == 1 ? '' : ' ' . $currency->sign) . "</strike></span>";
                    }
                }
                else
                {
                    $price_reduction = "<span style=\"font-size:12px; font-weight:normal; color:#c0c0c0; display:block;clear:both; text-align:center;\">&nbsp;</span>";
                }

                global $smarty;
                if ($this->psver == 2)
                {
                    $price = (Configuration::get('faceshop_preprice') == 1 ? $currency->sign . ' ' : '') . number_format("{$value['price']}", $currency->decimals) . (Configuration::get('faceshop_preprice') == 1 ? '' : ' ' . $currency->sign);
                }
                else
                {
                    $price = Tools::displayPrice((Configuration::get('faceshop_prices_notax')== 1 ? $value['price_tax_exc']:$value['price']), $currency);
                }


                $count++;
                $margin = "";
                if ($count == "1")
                {
                    $margin = "margin-right:15px;";
                }
                if ($count == "2" || $count == "3")
                {
                    $margin = "margin-right:15px;";
                }


                if (Configuration::get('faceshop_pdisp_name') == 1)
                {
                    $pdisp_name = "<div style=\"diplay:table;\"><div class=\"pname radius5top\">" . substr($value['name'], 0, Configuration::get('faceshop_pdisp_trim')) . "</div></div>";
                }
                else
                {
                    $pdisp_name = "";
                }

                if (Configuration::get('faceshop_pdisp_price') == 1)
                {
                    $pdisp_price = "<h3 style=\"padding:0px; margin:0px; margin-top:10px; margin-bottom:10px; display:block; text-align:center; font-size:20px; font-weight:bold; color:#5372b0; line-height:20px;\">$price_reduction $price</h3>";
                }
                else
                {
                    $pdisp_price = "";
                }


                if (Configuration::get('faceshop_pdisp_image') == 1)
                {
                    $pdisp_image = "<div style=\"margin-top:10px; display:block; text-align:center;\"><img src=\"$linkimage\" class=\"productpic\" style=\"max-width:188px;\"/></div>";
                }
                else
                {
                    $pdisp_image = "";
                }

                if (Configuration::get('faceshop_acart_ssl') == 1)
                {
                    $protocol_add_to_cart = true;
                }
                else
                {
                    $protocol_add_to_cart = false;
                }

                if (Configuration::get('faceshop_pdisp_acart') == 1)
                {
                    if ($this->psversion()==7)
                    {
                        $pdisp_addtocart = "<a href='" . Context::getContext()->link->getBaseLink().'modules/faceshop/tabshop.php?redirectAdd=1&qty=1&amp;action=update&amp;add=1&amp;id_product=' . $value['id_product'] . "&amp;token=" . Tools::getToken(false) . "' target='_blank' class='addtocart'>" . $this->l('Add to cart') . "</a>";    
                    }
                    else
                    {                 
                        $pdisp_addtocart = "<a href='" . $link->getPageLink('cart', $protocol_add_to_cart, null, "add=1&amp;id_product=" . $value['id_product'] . "&amp;token=" . Tools::getToken(false)) . "' target='_blank' class='addtocart'>" . $this->l('Add to cart') . "</a>";
                    }
                }
                else
                {
                    $pdisp_addtocart = "";
                }

                echo "<div class=\"product radius5\" style=\"$margin\">
                $new $hot
                <div style=\"text-align:center;\" onclick=\"gotoproduct({$value['id_product']},'" . $this->getProductLink($value['link'], array(
                        'utm_source' => 'facebook',
                        'utm_medium' => $this->l('shop')
                    )) . "');\">
					$pdisp_name
                    $pdisp_image
                    $pdisp_price
                    <div style=\"display:block; clear:both; overflow:hidden; vertical-align:top; margin-bottom:10px; margin-top:20px;\">
                        <div style=\"display:inline-block;  margin:auto; vertical-align:top;\">";

                global $cookie;
                if (Configuration::get('fsh_langarray', $cookie->id_lang))
                {
                    $fb_lang = Configuration::get('fsh_langarray', $cookie->id_lang);
                }
                else
                {
                    $fb_lang = "en_EN";
                }

                if (configuration::get('faceshop_social_facebook') == 1 || configuration::get('faceshop_share'))
                {
                }

                if (Configuration::get('faceshop_social_facebook') == 1)
                {
                    echo "<div style=\"display:block; clear:both; overflow:hidden;\" class=\"facebook-fblike\" ><div class=\"fb-like\" data-href=\"{$value['link']}\" data-layout=\"button_count\" data-action=\"like\" data-show-faces=\"false\" data-share=\"false\"></div></div>";
                }

                echo '<script>$(document).ready(function(){reloadSocial();});</script>';

                if (Configuration::get('faceshop_share') == 1)
                {

                    echo '<div style=\"display:block; clear:both; overflow:hidden;\"><div class="fb-share-button" data-type="button_count" data-href="' . $value['link'] . '"></div></div>';

                }

                if (Configuration::get('faceshop_social_pinterest') == 1)
                {
                    echo " <a style=\"display:inline-block; margin-left:5px; margin-right:5px;\" target=\"_blank\" href=\"http://pinterest.com/pin/create/button/?url=" . $value['link'] . "&media=" . $linkimage . "&description={$value['name']}\" class=\"pin-it-button\" count-layout=\"horizontal\"><img border=\"0\" src=\"//assets.pinterest.com/images/PinExt.png\" title=\"Pin It\" /></a><script type=\"text/javascript\" src=\"https://assets.pinterest.com/js/pinit.js\"></script>";
                }
                if (Configuration::get('faceshop_social_google') == 1)
                {
                    echo "<div style=\"margin-left:20px;\" class=\"g-plusone\" data-size=\"Medium\" data-href=\"{$value['link']}\"></div>
                            <script type=\"text/javascript\">
            				    (function() {
            					    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
            					    po.src = 'https://apis.google.com/js/plusone.js';
            					    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
            					 })();
            				</script>";
                }

                echo "</div>
                    </div>
                </div>
                    $pdisp_addtocart
                </div>
                ";
                unset($margin);
                unset($new);
                unset($hot);
                if ($count == "4")
                {
                    $count = 0;
                }
            }

            echo "</div>";
        }
        else
        {
            echo $this->l('Sorry, no products available');
        }
    }

    public function getProductLink($url, $params = array(), $use_existing_arguments = false)
    {
        if (Configuration::get('faceshop_analytics') == 1)
        {
            if ($use_existing_arguments)
            {
                $params = $params + $_GET;
            }
            if (!$params)
            {
                return $url;
            }
            $link = $url;
            if (strpos($link, '?') === false)
            {
                $link .= '?';
            } //If there is no '?' add one at the end
            elseif (!preg_match('/(\?|\&(amp;)?)$/', $link))
            {
                $link .= '&amp;';
            } //If there is no '&' at the END, add one.

            $params_arr = array();
            foreach ($params as $key => $value)
            {
                if (gettype($value) == 'array')
                { //Handle array data properly
                    foreach ($value as $val)
                    {
                        $params_arr[] = $key . '[]=' . urlencode($val);
                    }
                }
                else
                {
                    $params_arr[] = $key . '=' . urlencode($value);
                }
            }
            $link .= implode('&amp;', $params_arr);
            return $link;
        } else {
            return $url;
        }
    }

    public function product_pagination($products, $current, $cid, $mode = 0)
    {
        $per = Configuration::get('faceshop_cat_itemcount');
        $pages = $products / $per;
        echo "<ul id=\"pagination\" >";
        for ($i = 1; $i < $pages + 1; $i++)
        {
            $selected = "0";
            if ($i == $current || ($i == 1 && $current == 0))
            {
                $selected = "selected";
            }
            if ($mode == "0")
            {
                echo "<li class=\"$selected\"><a href=\"#top_products\" onclick=\"loadproducts($cid,$i)\">$i</a></li>";
            }
            else
            {
                if ($mode == "newproducts")
                {
                    echo "<li class=\"$selected\"><a href=\"#top_products\" onclick=\"loadnewproducts($i)\">$i</a></li>";
                }
                else
                {
                    if ($mode == "promotions")
                    {
                        $li = $i - 1;
                        if ($li == $current)
                        {
                            $selected = "selected";
                        }
                        else
                        {
                            $selected = "";
                        }
                        echo "<li class=\"$selected\"><a href=\"#top_products\" onclick=\"loadpromotions($li)\">$i</a></li>";
                    }
                }
            }
            unset($selected);
        }
        echo "</ul>";
    }

    public function manufacturer_pagination($products, $current, $cid, $mode = 0)
    {
        $per = Configuration::get('faceshop_cat_itemcount');
        $pages = $products / $per;
        echo "<ul id=\"pagination\" >";
        for ($i = 1; $i < $pages + 1; $i++)
        {
            $selected = "0";
            if ($i == $current || ($i == 1 && $current == 0))
            {
                $selected = "selected";
            }
            if ($mode == "0")
            {
                echo "<li class=\"$selected\"><a href=\"#top_products\" onclick=\"loadmanufacturers($cid,$i)\">$i</a></li>";
            }
            else
            {
                if ($mode == "newproducts")
                {
                    echo "<li class=\"$selected\"><a href=\"#top_products\" onclick=\"loadmanufacturers($i)\">$i</a></li>";
                }
                else
                {
                    if ($mode == "promotions")
                    {
                        $li = $i - 1;
                        if ($li == $current)
                        {
                            $selected = "selected";
                        }
                        else
                        {
                            $selected = "";
                        }
                        echo "<li class=\"$selected\"><a href=\"#top_products\" onclick=\"loadpromotions($li)\">$i</a></li>";
                    }
                }
            }
            unset($selected);
        }
        echo "</ul>";
    }

    public function inconsistency($return_report = 1)
    {
        $form = '';
        $prefix = _DB_PREFIX_;
        $engine = _MYSQL_ENGINE_;
        $table['faceshop_slider']['shop']['type'] = 'int';
        $table['faceshop_slider']['shop']['length'] = 1;
        $table['faceshop_slider']['shop']['default'] = "1";

        $form .= '<table class="inconsistency"><tr><td colspan="3" style="text-align:center">' . $this->l('REPORT') . '</td></tr>';
        foreach ($table['faceshop_slider'] as $key => $field)
        {
            if ($this->maybeUpdateDatabase('faceshop_slider', $key, $field['type']. "(".$field['length'].")", $field['default'], "NOT NULL"))
            {
                $form .= "<tr><td class='inconsistency1'>&nbsp;&nbsp;</td><td>".$key." ".($field['type']. " (".$field['length'].") DEFAULT ". $field['default'])."</td><td>OK</td></tr>";
            }
            else
            {
                $form .= "<tr><td class='inconsistency0'>&nbsp;&nbsp;</td><td>".$key." ".($field['type']. " (".$field['length'].") DEFAULT ". $field['default'])."</td><td>ERROR</td></tr>";
            }
        }
        $form .= "</table>";

        if ($return_report == 1)
        {
            return $form;
        }
        else
        {
            return true;
        }

    }

    private function maybeUpdateDatabase($table, $column, $type = "int(8)", $default = "1", $null = "NULL")
    {
        $sql = 'DESCRIBE ' . _DB_PREFIX_ . $table;
        $columns = Db::getInstance()->executeS($sql);
        $found = false;
        foreach ($columns as $col)
        {
            if ($col['Field'] == $column)
            {
                $found = true;
                break;
            }
        }
        if (!$found)
        {
            if (!Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . $table . '` ADD `' . $column . '` ' . $type . ' DEFAULT ' . $default . ' ' . $null))
            {
                return false;
            }
        }
        return true;
    }
}


class faceshopUpdate extends faceshop
{
    public static function version($version)
    {
        $version = (int)str_replace(".", "", $version);
        if (strlen($version) == 3)
        {
            $version = (int)$version . "0";
        }
        if (strlen($version) == 2)
        {
            $version = (int)$version . "00";
        }
        if (strlen($version) == 1)
        {
            $version = (int)$version . "000";
        }
        if (strlen($version) == 0)
        {
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
        if (ini_get("allow_url_fopen"))
        {
            if (function_exists("file_get_contents"))
            {
                $actual_version = @file_get_contents('http://dev.mypresta.eu/update/get.php?module=' . $module . "&version=" . self::encrypt($version) . "&lic=$key&u=" . self::encrypt(_PS_BASE_URL_ . __PS_BASE_URI__));
            }
        }
        Configuration::updateValue("update_" . $module, date("U"));
        Configuration::updateValue("updatev_" . $module, $actual_version);
        return $actual_version;
    }
}

?>