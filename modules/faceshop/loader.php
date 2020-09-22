<?PHP
require_once("../../config/config.inc.php");
require_once("faceshop.php");
$module=new faceshop();

        
if ($module->psversion()==5 || $module->psversion()==6){
    if (isset($_SERVER["HTTPS"])){
        if (isset(Context::getContext()->controller))
        	$controller = Context::getContext()->controller;
        else{
        	$controller = new FrontController();
            $controller->ssl=true;
        	$controller->init();
        }
    } else {
        require_once(dirname(__FILE__).'/../../init.php'); 
    }
} else {
    require_once(dirname(__FILE__).'/../../init.php');
}


if ($_GET['go']=="changelang"){
    $module->changelang($_GET['lid']);
}

if ($_GET['go']=="changecurrency"){
    $module->changecurrency($_GET['cid']);
}

if ($_GET['go']=="cms"){
	$module->getCMSpage($_GET['cid']);
}

if ($_GET['go']=="products"){
    $count=$module->get_category_products(1);
    $db=$module->get_category_products();
    $module->breadcrumb();
    $module->product_display($db);
    $module->product_pagination($count,$_GET['page'],$_GET['cid']);
}


if ($_GET['go']=="manufacturers"){
    $count=$module->get_manufacturer_products(1);
    $db=$module->get_manufacturer_products();
    $module->breadcrumb();
    $module->product_display($db);
    $module->manufacturer_pagination($count,$_GET['page'],$_GET['cid']);
}


if ($_GET['go']=="promotions"){
	$count=$module->get_promotions(1);
	$db=$module->get_promotions();
	$module->breadcrumb();
	$module->product_display($db);
	$module->product_pagination($count,$_GET['page'],NULL,"promotions");
}


if ($_GET['go']=="newproducts"){
	$count=$module->get_new_products(1);
	$db=$module->get_new_products();
	$module->breadcrumb();
	$module->product_display($db);
	$module->product_pagination($count,$_GET['page'],NULL,"newproducts");
}


if ($_GET['go']=="homepage"){
	$module->breadcrumb();
    $module->product_display($module->get_homefeatured(),1);    
}

?>