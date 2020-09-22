<?PHP
class Body{
    public $cat; 
    function __construct(){
        $this->cat = new Category;
    } 
    
    public function getHttpHost($http = false, $entities = false){
		$host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);
		if ($entities)
			$host = htmlspecialchars($host, ENT_COMPAT, 'UTF-8');
		if ($http)
			$host = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$host;
		return $host;
	}
    
    public function product_display($array,$homepage = 0){
    		
            echo "<div style=\"clear:both;\">";
            $currency = Currency::getCurrent(); 
            $count=0;
            foreach ($array as $key=>$value){
                if ($homepage==1){
                    if ((Configuration::get('fcbshop_homepage_dispnew')=="1")&&($value['new'])=="1"){$new="<img src=\"img/new.png\" style=\"position:absolute; top:-3px; left:-3px;\">";}else{$new="";}
                    if ((Configuration::get('fcbshop_homepage_disphot')=="1")&&($value['on_sale'])=="1"){$hot="<img src=\"img/promo.png\" style=\"position:absolute; top:-3px; right:-3px;\">";}else{$hot="";}
                } else {
                    if ((Configuration::get('fcbshop_cat_dispnew')=="1")&&($value['new'])=="1"){$new="<img src=\"img/new.png\" style=\"position:absolute; top:-3px; left:-3px;\">";}
                    if ((Configuration::get('fcbshop_cat_disphot')=="1")&&($value['on_sale'])=="1"){$hot="<img src=\"img/promo.png\" style=\"position:absolute; top:-3px; right:-3px;\">";}                
                }
                $id_image=$this->defineProductImage($value,6);
                
                $link=$this->getImageLink("$value[link_rewrite]", $id_image, $value['id_product'], "home");
                $price=number_format(Tools::convertPrice("{$value['price']}"),2)." $currency->sign";
                $count++;
                $margin="";
                if ($count=="1"){$margin="margin-right:15px;";}
                if ($count=="2" || $count=="3" ){$margin="margin-right:15px;";}
                echo "<div style=\"margin:0px; padding:0px; $margin margin-bottom:15px; display:inline-block; width:189px;  border:1px solid #c0c0c0; position:relative;\">
                $new $hot 
                <div align=\"center\" onclick=\"gotoproduct({$value['id_product']},'".$this->getHttpHost(1).__PS_BASE_URI__."');\">
                    <span style=\"color:#c0c0c0; margin-top:5px; margin-bottom:10px; font-size:12px; text-align:center; display:block;\">{$value['name']}</span>
                    <img src=\"$link\" class=\"productpic\"/>
                    <h3 style=\"padding:0px; margin:0px; margin-top:10px; margin-bottom:10px; display:block; text-align:center; font-size:20px; font-weight:bold; color:#5372b0; line-height:20px;\">$price</h3>
                    <div style=\"display:block; clear:both; overflow:hidden;\">
                        <div style=\"display:inline-block;  margin:auto;\">
                        <iframe src=\"//www.facebook.com/plugins/like.php?href=".$this->getHttpHost(1).__PS_BASE_URI__."product.php?id_product=$value[id_product]&amp;width=75&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=24&amp;appId=212786305473959\" scrolling=\"no\" frameborder=\"0\" style=\"border:none; overflow:hidden; width:75px; padding:10px; height:24px;\" allowTransparency=\"true\"></iframe></div>
                    </div>
                </div>
                </div>";
                unset ($margin);
                unset ($new);
                unset ($hot);
                if ($count=="4"){$count=0;}
            }  
            echo "</div>";
    }
    
    public function product_categoryname($name){
        echo "<div id=\"p_catname\"><h2>$name</h2></div>";
    }
    
    public function product_pagination($products,$current,$cid,$mode = 0){
        $per=Configuration::get('fcbshop_cat_itemcount');
        $pages=$products/$per;
        echo "<ul id=\"pagination\" >";      
        for ($i=0; $i<$pages; $i++){
            if ($i==$current){$selected="selected";}
            
            if ($mode=="0"){echo"<li class=\"$selected\"><a href=\"#top_products\" onclick=\"loadproducts($cid,$i)\">$i</a></li>";} else if
            ($mode=="newproducts"){echo"<li class=\"$selected\"><a href=\"#top_products\" onclick=\"loadnewproducts($i)\">$i</a></li>";} else if
            ($mode=="promotions"){echo"<li class=\"$selected\"><a href=\"#top_products\" onclick=\"loadpromotions($i)\">$i</a></li>";}
            unset ($selected);
        }
        echo"</ul>";

    }
    
	public function getImageLink($name, $ids, $idp, $type = NULL)
	{
	   $this->allow = intval(Configuration::get('PS_REWRITING_SETTINGS'));
	   //return ($this->allow == 1) ? (__PS_BASE_URI__.$ids.($type ? '-'.$type : '').'/'.$name.'.jpg') : (_THEME_PROD_DIR_.$ids.($type ? '-'.$type : '').'.jpg');
       
       
		global $protocol_content;

		if ((Configuration::get('PS_LEGACY_IMAGES') 
			&& (file_exists(_PS_PROD_IMG_DIR_.$ids.($type ? '-'.$type : '').'.jpg')))
			|| strpos($ids, 'default') !== false) {
			if ($this->allow == 1){
				$uri_path = __PS_BASE_URI__.$ids.($type ? '-'.$type : '').'/'.$name.'.jpg';
            } else {
				$uri_path = _THEME_PROD_DIR_.$ids.($type ? '-'.$type : '').'.jpg';
            }
            
		} else {
			$split_ids = explode('-', $ids);
			$id_image = (isset($split_ids[1]) ? $split_ids[1] : $split_ids[0]);
			//$uri_path = __PS_BASE_URI__.$id_image.($type ? '-'.$type : '').'/'.$name.'.jpg';
			$uri_path = _THEME_PROD_DIR_.Image::getImgFolderStatic($id_image).$id_image.($type ? '-'.$type : '').'.jpg';
		}
		return $uri_path;
        
	}     

    
 	public function defineProductImage($row, $id_lang)
	{  
		if ($row['id_image'])
			return $row['id_product'].'-'.$row['id_image'];
		return Language::getIsoById((int)$id_lang).'-default';
	}   
}
?>