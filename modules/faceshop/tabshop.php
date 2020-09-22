<?PHP
require_once("../../config/config.inc.php");
require_once("faceshop.php");
$module = new faceshop();

if ($module->psversion() == 5 || $module->psversion() == 6 || $module->psversion() == 7)
{
    if (isset($_SERVER["HTTPS"]))
    {
        if (isset(Context::getContext()->controller))
        {
            $controller = Context::getContext()->controller;
        }
        else
        {
            $controller = new FrontController();
            $controller->ssl = true;
            $controller->init();
        }
    }
    else
    {
        require_once(dirname(__FILE__) . '/../../init.php');
    }
    
    if (Configuration::get('faceshop_acart_ssl') == 1)
    {
        $protocol_add_to_cart = true;
    }
    else
    {
        $protocol_add_to_cart = false;
    }
    
    if (Tools::getValue('redirectAdd')){
        header('Location: ' . $link->getPageLink('cart', $protocol_add_to_cart, null, "action=update&qty=1&add=1&amp;id_product=".Tools::getValue('id_product')."&amp;token=" . Tools::getToken(false)));
    }

    
    $langexist = 0;
    if (isset($_COOKIE['fcbshop_lang']))
    {
        foreach (Language::getLanguages(true) as $lang => $value)
        {
            if ($_COOKIE['fcbshop_lang'] == $value['id_lang'])
            {
                $langexist = 1;
                $module->changelang($value['id_lang']);
                setcookie("fcbshop_lang", $value['id_lang'], time() + 360000);
            }
        }
        if ($langexist == 0)
        {
            $module->changelang(Configuration::get('PS_LANG_DEFAULT'));
            setcookie("fcbshop_lang", Configuration::get('PS_LANG_DEFAULT'), time() + 360000);
        }
    }
    else
    {
        $module->changelang(Configuration::get('PS_LANG_DEFAULT'));
        setcookie("fcbshop_lang", Configuration::get('PS_LANG_DEFAULT'), time() + 360000);
    }
}

if ($module->psversion() == 4)
{
    require_once(dirname(__FILE__) . '/../../init.php');
    $langexist = 0;
    if (isset($_COOKIE['fcbshop_lang']))
    {
        foreach (Language::getLanguages(true) as $lang => $value)
        {
            if ($_COOKIE['fcbshop_lang'] == $lang)
            {
                $langexist = 1;
                $module->changelang($lang);
                setcookie("fcbshop_lang", $lang, time() + 360000);
            }
        }
        if ($langexist == 0)
        {
            $module->changelang(Configuration::get('PS_LANG_DEFAULT'));
            setcookie("fcbshop_lang", Configuration::get('PS_LANG_DEFAULT'), time() + 360000);
        }
    }
    else
    {
        $module->changelang(Configuration::get('PS_LANG_DEFAULT'));
        setcookie("fcbshop_lang", Configuration::get('PS_LANG_DEFAULT'), time() + 360000);
    }
}


if ($module->psversion() == 5 || $module->psversion() == 6 || $module->psversion() == 7)
{
    //print_r(Context::getContext()->cookie);
    $languages = Language::getLanguages();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.nivo.slider.pack.js"></script>
    <script type="text/javascript" src="js/advajax.js"></script>
    <?PHP
    if ($module->psversion() == 5 || $module->psversion() == 6 || $module->psversion() == 7)
    {
        $context = Context::getContext();
        $id_of_shop = $context->shop->id;
    }
    else
    {
        $id_of_shop = 0;
    }

    if (file_exists('img/' . $id_of_shop . '-header.gif'))
    {
        $context = Context::getContext();
        $id_of_shop_image = $context->shop->id . "-";
    }
    else
    {
        $id_of_shop_image = "";
    }
    if (file_exists('img/' . $id_of_shop . '-footer.gif'))
    {
        $context = Context::getContext();
        $id_of_shop_image_footer = $context->shop->id . "-";
    }
    else
    {
        $id_of_shop_image_footer = "";
    }

    echo "<style>
            .header {background:url('img/" . $id_of_shop_image . "header.gif')!important;}
            #footer {background:url('img/" . $id_of_shop_image_footer . "footer.gif')!important;}
            </style>";

    echo "<style>";
    if (Configuration::get('fbs_bgc') != '' && Configuration::get('fbs_bgc') != null)
    {
        echo "body {background:#" . Configuration::get('fbs_bgc') . "!important;}";
    }

    if (Configuration::get('fbs_menu_bgc') != '' && Configuration::get('fbs_menu_bgc') != null)
    {
        echo "
                .colors_menu h2 {border-bottom:1px solid #" . Configuration::get('fbs_menu_bgc') . "!important;}
                .colors_menu ul.subcategories2 li {border:1px solid #" . Configuration::get('fbs_menu_bgc') . "!important;}
                .colors_menu ul.subcategories2 li div div, .colors_menu ul.subcategories2 li div, .colors_menu ul.subcategories2 li, .colors_menu {background:#" . Configuration::get('fbs_menu_bgc') . "!important;}
                ";
    }
    if (Configuration::get('fbs_menu_bgc_hover') != '' && Configuration::get('fbs_menu_bgc_hover') != null)
    {
        echo "#menu ul.subcategories li div.button:hover, .colors_menu .subcategories2, .colors_menu .subcategories, .colors_menu #subcategories2, .colors_menu #subcategories4 ul, .colors_menu li:hover {background:#" . Configuration::get('fbs_menu_bgc_hover') . "!important; }";
    }
    if (Configuration::get('fbs_menu_bgc_shadow') != '' && Configuration::get('fbs_menu_bgc_shadow') != null)
    {
        echo "#menu {text-shadow: 1px 1px 0px #" . Configuration::get('fbs_menu_bgc_shadow') . "!important;}";
    }
    if (Configuration::get('fbs_menu_bgc_color') != '' && Configuration::get('fbs_menu_bgc_color') != null)
    {
        echo ".colors_menu, #menu {color: #" . Configuration::get('fbs_menu_bgc_color') . "!important;}";
    }
    if (Configuration::get('fbs_menu_bgc_hoverc') != '' && Configuration::get('fbs_menu_bgc_hoverc') != null)
    {
        echo ".colors_menu li:hover, .colors_menu:hover ul,  #menu:hover ul {color: #" . Configuration::get('fbs_menu_bgc_hoverc') . "!important;}";
    }
    if (Configuration::get('fbs_menu_bgc_hovers') != '' && Configuration::get('fbs_menu_bgc_hovers') != null)
    {
        echo ".colors_menu li:hover, .colors_menu:hover ul,  #menu:hover ul {text-shadow:1px 1px 0px #" . Configuration::get('fbs_menu_bgc_hovers') . "!important;}";
    }
    if (Configuration::get('fbs_product_bgc_color') != '' && Configuration::get('fbs_product_bgc_color') != null)
    {
        echo ".product h3 {color: #" . Configuration::get('fbs_product_bgc_color') . "!important;}";
    }
    if (Configuration::get('fbs_product_bgc_dcolor') != '' && Configuration::get('fbs_product_bgc_dcolor') != null)
    {
        echo ".product strike {color: #" . Configuration::get('fbs_product_bgc_dcolor') . "!important;}";
    }
    if (Configuration::get('fbs_product_bgc_namec') != '' && Configuration::get('fbs_product_bgc_namec') != null)
    {
        echo ".product .pname {color: #" . Configuration::get('fbs_product_bgc_namec') . "!important;}";
    }
    if (Configuration::get('fbs_product_bgc_name') != '' && Configuration::get('fbs_product_bgc_name') != null)
    {
        echo ".product .pname {background: #" . Configuration::get('fbs_product_bgc_name') . "!important;}";
    }
    if (Configuration::get('fbs_product_bgc_names') != '' && Configuration::get('fbs_product_bgc_names') != null)
    {
        echo ".product .pname {text-shadow: 1px 1px 0px #" . Configuration::get('fbs_product_bgc_names') . "!important;}";
    }
    if (Configuration::get('fbs_product_bgc') != '' && Configuration::get('fbs_product_bgc') != null)
    {
        echo "div.product {background: #" . Configuration::get('fbs_product_bgc') . "!important;}";
    }
    if (Configuration::get('fbs_product_bgc_borderc') != '' && Configuration::get('fbs_product_bgc_borderc') != null)
    {
        echo "div.product {border: 1px solid #" . Configuration::get('fbs_product_bgc_borderc') . "!important;}";
    }
    echo "</style>";
    ?>

    <script type="text/javascript">
        $(window).load(function () {
            reloadSocial();
            $('#slider').nivoSlider({
                effect: 'random',
                controlNav: false,
                pauseTime:<?PHP $time = Configuration::get('faceshop_slider_time') * 1000; echo $time; ?>});
        });
    </script>
    <script>
        $(document).ready(function () {
            $(".productpic").hover(
                function () {
                    $(this).fadeTo("fast", 0.6);
                },
                function () {
                    $(this).fadeTo("fast", 1.0);
                }
            );
        });
    </script>
    <link href="css/style.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="default/default.css" type="text/css"/>
</head>
<div id="fb-root"></div>
<body>
<div class="container">
    <div id="pg">
        <div id="pg_header">
            <div style="text-align:right;">
                <img src="img/button_close.png" onclick="pgclose()"/>
            </div>
        </div>
        <div id="pg_body">
        </div>
    </div>
</div>
<?PHP
if (Configuration::get('faceshop_slider_place') == 0)
{
    $module->nivoSlider(Configuration::get('faceshop_slider_disp'));
}
$module->dispHeader(Configuration::get('faceshop_header_disp'));
if (Configuration::get('faceshop_slider_place') == 1)
{
    $module->nivoSlider(Configuration::get('faceshop_slider_disp'));
}

if (Configuration::get('faceshop_menu_disable') != 1)
{
    $module->displayMenu($module->getCategories(), Configuration::get('faceshop_menu_newproducts'), Configuration::get('faceshop_menu_saleproducts'), Configuration::get('faceshop_menu_informations'), Configuration::get('faceshop_menu_manuf'), Configuration::get('faceshop_disp_lang'));
}


if (Configuration::get('faceshop_slider_place') == 2)
{
    $module->nivoSlider(Configuration::get('faceshop_slider_disp'));
}
echo "<a name=\"top_products\"></a>";
?>
<div id="contentload">
    <div id="contentload_loader"><img src="img/ajax-loader2.gif"/></div>
    <div id="contentload_body" style="cursor:pointer; width:810px; display:block; clear:both; margin:auto; margin-top:10px; clear:both; overflow:hidden;padding-top:3px;">
        <?php
        $module->breadcrumb();
        $module->product_display($module->get_homefeatured(), 1);
        if (Configuration::get('faceshop_slider_place') == 3)
        {
            $module->nivoSlider(Configuration::get('faceshop_slider_disp'));
        }
        ?>
    </div>
</div>
<?PHP
$module->display_footer();
?>
<?PHP
if (Configuration::get('fshp_fbapp') == 1)
{
    echo "<script>(function(d, s, id) {
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) {return;}
              js = d.createElement(s); js.id = id;
              js.src = \"//connect.facebook.net/" . Configuration::get('fsh_langarray', $module->getcookielang()) . "/all.js#xfbml=1\";
              fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));</script>";
}
echo '<script type="text/javascript" src="js/shop2.js"></script>';
?>
</body>