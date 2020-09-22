<?PHP

class Category{
    
       
    
    
    public function getCountProducts($pcategory = 0, $sales = 0, $newproducts = 0, $homefeatured = 0){
        $SELECT_newproducts= ", DATEDIFF(p.date_add, DATE_SUB(NOW(), INTERVAL ".(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20)." DAY)) > 0 AS new";
        if ($sales=="1"){$WHERE_sales="AND p.on_sale='1'";}
        if ($homefeatured=="1"){$WHERE_homefeatured="AND cp.id_category='1'";}
        if ($newproducts=="1") {$WHERE_newproducts="AND DATEDIFF(p.date_add, DATE_SUB(NOW(), INTERVAL ".(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20)." DAY)) > 0";}
        if (!$pcategory=="0"){$JOIN_category="LEFT JOIN "._DB_PREFIX_."category_product as cp ON cp.id_product = p.id_product"; $WHERE_pcategory="AND cp.id_category='$pcategory'";}
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS("
        SELECT COUNT(*) as licznik FROM "._DB_PREFIX_."product as p
        LEFT JOIN "._DB_PREFIX_."product_lang as pl ON p.id_product = pl.id_product
        $JOIN_category
        WHERE p.active='1' AND pl.id_lang='".Configuration::get('PS_LANG_DEFAULT')."' $WHERE_homefeatured $WHERE_new_products $WHERE_pcategory $WHERE_sales
        ");
        return $db[0][licznik];
    }
    
    
       
    public function getProducts($pcategory = 0, $sales = 0, $newproducts = 0, $homefeatured = 0, $page = 0, $nb_per_page = 6, $orderby = 0){
        $SELECT_newproducts= ", DATEDIFF(p.date_add, DATE_SUB(NOW(), INTERVAL ".(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20)." DAY)) > 0 AS new";
        if ($sales=="1"){$ORDERBY="ORDER BY RAND()"; $WHERE_sales="AND p.on_sale='1'";}
        if ($homefeatured=="1"){$ORDERBY="ORDER BY RAND()"; $WHERE_homefeatured="AND cp.id_category='1'";}
        if ($newproducts=="1") {$WHERE_newproducts="AND DATEDIFF(p.date_add, DATE_SUB(NOW(), INTERVAL ".(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20)." DAY)) > 0";}
        if (!$pcategory=="0"){$WHERE_pcategory="AND cp.id_category='$pcategory'";}
        if ($orderby=="1"){$ORDERBY="";}
        $page=$page*$nb_per_page;
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS("
        SELECT im.id_image, pl.id_product, pl.name, p.on_sale, pl.link_rewrite, (p.price * IF(t.rate,((100 + (t.rate))/100),1)) AS price
        $SELECT_newproducts
        FROM "._DB_PREFIX_."category_product AS cp
        LEFT JOIN "._DB_PREFIX_."product_lang AS pl ON cp.id_product = pl.id_product
        LEFT JOIN "._DB_PREFIX_."product AS p ON cp.id_product = p.id_product
        LEFT JOIN "._DB_PREFIX_."tax AS t ON p.id_tax_rules_group = t.id_tax
        LEFT JOIN "._DB_PREFIX_."image AS im ON cp.id_product = im.id_product
        WHERE p.active='1' AND pl.id_lang='".Configuration::get('PS_LANG_DEFAULT')."' AND im.cover='1' $WHERE_homefeatured $WHERE_new_products $WHERE_pcategory $WHERE_sales
        GROUP BY cp.id_product
        $ORDERBY
        LIMIT $page , $nb_per_page
        ");
        return $db;        
    }
    
    public function getCategoryName($id){
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS("SELECT cl.name FROM "._DB_PREFIX_."category as c LEFT JOIN "._DB_PREFIX_."category_lang as cl ON c.id_category = cl.id_category WHERE active=1 AND c.id_parent<>'0' AND cl.id_lang='".Configuration::get('PS_LANG_DEFAULT')."' AND c.id_category = '$id' ");
        return $db[0][name];
    }
    
}    

?>