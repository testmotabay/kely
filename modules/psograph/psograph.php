<?php

if (!defined('_PS_VERSION_'))
	exit;

class psOGraph extends Module
{
	/* @var boolean error */
	protected $_errors = false;
	
	public function __construct()
	{
		$this->name = 'psograph';
		$this->tab = 'seo';
		$this->version = '1.0.1';
		$this->author = 'Nemo';
		$this->need_instance = 0;

	 	parent::__construct();

		$this->displayName = $this->l('PS Facebook OpenGraph Tags');
		$this->description = $this->l('Add Open Graph tags to your store.');
		$this->table_name = 'psograph_images';
	}
	
	public function install()
	{
		if (!parent::install() OR	
			!$this->_installTable() OR
			!$this->registerHook('displayBackOfficeHeader') OR
			!$this->registerHook('header'))
			return false;
		return true;
	}
	
	public function uninstall()
	{
		if (!parent::uninstall() OR !$this->_eraseTable() or Configuration::deleteByName('PSOGRAPH_REMOVE_SITE_NAME'))
			return false;
		return true;
	}

	private function _installTable(){

		$sql = 'CREATE TABLE  `'._DB_PREFIX_.$this->table_name.'` (
				`type` VARCHAR( 16 ),
				`id_lang` INT( 12 ) NOT NULL,
				`img` TEXT NOT NULL,
				PRIMARY KEY (  `type`, `id_lang` )
				) ENGINE =' ._MYSQL_ENGINE_;

		if (!Db::getInstance()->Execute($sql) )
			return false;
		else return true;
	}

	private function _eraseTable(){
		if(!Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.$this->table_name.'`'))
			return false;
		else return true;
	}

	public function getContent(){
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		$this->_postProcess();


		$this->_displayForm();
		return	$this->_html;
	}
	


	private function _postProcess()
	{
		if (Tools::isSubmit('saveSettings')) // handles the basic config update
		{
			// array with all pages currently supported
			$supported_pages = array('indexImage', 'bestSalesImage', 'priceDropImage', 'newProductsImage');

			$custom_images  = $this->getCustomImages();

			    
			foreach ($supported_pages as $type) {
				$this->uploadImage($type, $custom_images);
				// Check if the user wants to erase an image
				
				if ($images_to_erase = Tools::getValue($type . '_delete'))
				{
					foreach ($images_to_erase as $id_lang => $image) {
						if(file_exists(dirname(__FILE__).'/uploads/' . $image ))
							unlink(dirname(__FILE__).'/uploads/' . $image );
						$this->deleteImage($type, $id_lang);
					}
				}
			}

			Configuration::updateValue('PSOGRAPH_REMOVE_SITE_NAME', Tools::getValue('remove_site_name'));



			if($this->_errors)
				$this->_html .= $this->displayError(implode($this->_errors, '<br />'));
			else $this->_html .= $this->displayConfirmation($this->l('Settings Updated'));
		}
	}

	private function deleteImage($type, $id_lang)
	{
		if(!Db::getInstance()->delete($this->table_name, 'type = "' .pSQL($type) . '" AND id_lang = ' . $id_lang))
			$this->_errors[] = $this->l('Error: ').mysql_error();
	}


	private function _displayForm()
	{
		// Add new block or edit existing one
		

		$id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
		$languages = Language::getLanguages(false);
		$supported_pages = array('indexImage', 'bestSalesImage', 'priceDropImage', 'newProductsImage');
		$divLangName = 'indexImage¤bestSalesImage¤priceDropImage¤newProductsImage';

		$custom_images = $this->getCustomImages();

		$this->_html .= '<div id="fb-root"></div>
					<script>(function(d, s, id) {
					  var js, fjs = d.getElementsByTagName(s)[0];
					  if (d.getElementById(id)) return;
					  js = d.createElement(s); js.id = id;
					  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
					  fjs.parentNode.insertBefore(js, fjs);
					}(document, \'script\', \'facebook-jssdk\'));</script>';
		$this->_html .= '<p>'.$this->l('Provided for FREE by').' <a style="text-decoration:underline" target="_blank" href="http://store.nemops.com#fromograph" title="store.nemops.com!">store.nemops.com!</a> '.$this->l('Would you like to support Nemo\'s Post Scriptum to get more free modules?').'</p>';



		$this->_html .= '<div class="fb-like-box" style="float:left" data-href="https://www.facebook.com/pages/Nemos-Post-Scriptum/358370864236645" data-width="250" data-colorscheme="light" data-show-faces="false" data-header="false" data-stream="false" data-show-border="false"></div>';

		$this->_html .= '
			<div  style="float:left">

			<form style="text-align:right" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="GSG68TUKQC24J">
			<input type="image" src="https://www.paypalobjects.com/en_US/IT/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
			</div>';		


		$this->_html .= '<div class="clear">&nbsp;</div>';

		$this->_html .= '
			<form action="'.AdminController::$currentIndex.'&token='.Tools::getValue('token').'&configure='.$this->name.'" method="post" enctype="multipart/form-data">
				<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Module Configuration').'</legend>
		';


		$this->_html .= '<p>'.$this->l('This configuration is entirely optional. You don\'t need to add any image to use this module. However, if you wish, you can customize the image being displayed on the following pages, for every language of your store.').'</p>';

		foreach ($supported_pages as $type) {
			
			switch ($type) {
				case 'indexImage':
					$label = $this->l('Default Image to be displayed (If empty, the shop logo will be shown):');
					break;
				case 'bestSalesImage':
					$label = $this->l('Best Sales Page:');
					break;
				case 'priceDropImage':
					$label = $this->l('Special Products Page:');
					break;
				case 'newProductsImage':
					$label = $this->l('New Products Page:');
					break;		
			}

			$this->_html .='

						<label>'.$label.'</label>
						<div class="margin-form">';
						foreach ($languages as $language)
						{	
							$image_link = isset($custom_images[$type][$language['id_lang']]) ? '<img src="'.$this->_path.'/uploads/' . $custom_images[$type][$language['id_lang']] . '" />' : '';



							$this->_html .= '
								<div id="'.$type.'_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $id_lang_default ? 'block' : 'none').'; float: left;">
									'.$image_link.'
									<div class="clear"></div>
									<input type="file" name="'.$type.'['.$language['id_lang'].']" id="the'.$type.'_'.$language['id_lang'].'" value="" />
									'.($image_link? '<input type="checkbox" name="'.$type.'_delete['.$language['id_lang'].']" value="'.($custom_images[$type][$language['id_lang']]).'"> <span>'.$this->l('Erase this image').'</span>' : '').'
								</div>';
						}
							
						$this->_html .= $this->displayFlags($languages, $id_lang_default, $divLangName, $type, true);
						$this->_html .= '
							<div class="clear"></div>

						</div>
						<div class="separation"></div>
						<p class="clear"></p>
			';	


		}

		// remove site name from title?
		$this->_html .= '

		<label>'.$this->l('Remove site name from og:title').'</label>
		<div class="margin-form">
			<input type="checkbox" name="remove_site_name" value="1" ' . ( Configuration::get('PSOGRAPH_REMOVE_SITE_NAME') == 1 ? 'checked="checked"' : '' ) . '>
		</div>
		';

		/* Submit button */
		$this->_html .='<p class="center"><input type="submit" name="saveSettings" value="'.$this->l('Save').'" class="button"></p>';


		$this->_html .= '
				</fieldset>
			</form>';		
	}	

	public function uploadImage($type, $current_items)
	{
		foreach ($_FILES[$type]['tmp_name'] as $id_lang => $value) {

			// no sense to upload empty fields!
			
			$image_name = '';

			if(!empty($value))
			{

				$image_name = date('Ymdhis').'_'.$_FILES[$type]['name'][$id_lang];

				if (!move_uploaded_file($value, dirname(__FILE__).'/uploads/'.$image_name))
					$this->_errors[] = $this->displayError($this->l('An error occurred during the image upload.'));


				if (isset($tmpName))
					unlink($tmpName);					


				if(!$this->_errors)
				{
					// check if this image is already there, if so, remove previous one, and set an update instead of insert
					if(isset($current_items[$type]) && isset($current_items[$type][$id_lang]))
					{
						unlink(dirname(__FILE__).'/uploads/'.$current_items[$type][$id_lang]);
						if(!Db::getInstance()->update($this->table_name, array('img' => $image_name), 'id_lang = '. $id_lang . ' AND type = "' . $type . '"'))
							$this->_errors[] = $this->l('Error: ').mysql_error();
					} else {
						$data_to_insert = array(
							'id_lang' => $id_lang,
							'type' => $type,
							'img' => $image_name
						);

						if(!Db::getInstance()->insert($this->table_name, $data_to_insert))
							$this->_errors[] = $this->l('Error: ').mysql_error();	
					}
					


				} // end if !errors

			} // end if empty value


		} // end foreach uploaded image		
	}

	public function getCustomImages()
	{
		$already_existing = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
				SELECT *
				FROM '._DB_PREFIX_.$this->table_name);		


		if($already_existing)
		{
			foreach ($already_existing as $image) {
				if($image['img'])
					$final_list[$image['type']][$image['id_lang']] = $image['img'];
			}
			return $final_list;
		} 
		else return false;

	}
	public function getCustomImage($type, $protocol)
	{
		if ($image = Db::getInstance()->getValue('SELECT img FROM '._DB_PREFIX_.$this->table_name .' WHERE type = "'.$type.'" AND id_lang = ' .$this->context->language->id))
		{

			return $protocol . '://' . Tools::getHttpHost().__PS_BASE_URI__. 'modules/psograph/uploads/' . $image;
		}
	}


	public function hookHeader($params)
	{

		if (version_compare(_PS_VERSION_, '1.5.0.0', '>='))
		{	
			return $this->header15($params);
		} else {
			global $smarty;

		}

		
		
	}


	public function header15($params)
	{
		// get current protocol
		
		$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
		$sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
	    $protocol = substr($sp, 0, strpos($sp, "/")) . $s;


		// Choose if you have to display open graphs. Only show them on the index, category pages, cms pages, product pages
		
		if(isset($this->context->controller->php_self) )
		{
			$og_site_name = Configuration::get('PS_SHOP_NAME');
			$og_type = ''; // set nothing as default, we will hide og for this case
			$og_image = $this->getCustomImage('indexImage', $protocol); // check if a default custom image exists


			switch ($this->context->controller->php_self) {
				case 'index':
					$og_type = 'website';
					break;
				case 'best-sales':
					$og_type = 'website';
					$og_image = $this->getCustomImage('bestSalesImage', $protocol);
					break;
				case 'new-products':
					$og_type = 'website';
					$og_image = $this->getCustomImage('newProductsImage', $protocol);
					break;
				case 'prices-drop':
					$og_type = 'website';
					$og_image = $this->getCustomImage('priceDropImage', $protocol);
					break;
				case 'cms':
					$og_type = 'website';
					break;
				case 'manufacturer':
					$og_type = 'website';
					$id_manufacturer = Tools::getValue('id_manufacturer');
					// check if a manufacturer logo is set
					if(file_exists(_PS_MANU_IMG_DIR_ . (int)$id_manufacturer . '.jpg'))
						$og_image = $protocol . '://' . Tools::getHttpHost().__PS_BASE_URI__. 'img/m/' . (int)$id_manufacturer . '.jpg';
					else if(file_exists(_PS_MANU_IMG_DIR_ . (int)$id_manufacturer . '.png'))
						$og_image = $protocol . '://' . Tools::getHttpHost().__PS_BASE_URI__. 'img/m/' . (int)$id_manufacturer . '.png'; 
					break;
				case 'supplier':
					$og_type = 'website';
					$id_supplier = Tools::getValue('id_supplier');
					// check if a manufacturer logo is set
					if(file_exists(_PS_MANU_IMG_DIR_ . (int)$id_supplier . '.jpg'))
						$og_image = $protocol . '://' . Tools::getHttpHost().__PS_BASE_URI__. 'img/su/' . (int)$id_supplier . '.jpg';
					else if(file_exists(_PS_MANU_IMG_DIR_ . (int)$id_supplier . '.png'))
						$og_image = $protocol . '://' . Tools::getHttpHost().__PS_BASE_URI__. 'img/su/' . (int)$id_supplier . '.png'; 
					break;									
				case 'category':
					$og_type = 'website';
					$id_category = (int)Tools::getValue('id_category');
					$category_image_id = ($id_category && file_exists(_PS_CAT_IMG_DIR_.(int)$id_category.'.jpg')) ? (int)$id_category : false;
					$link_rewrite = Category::getLinkRewrite($id_category, $this->context->language->id);
					$og_image = $this->context->link->getCatImageLink($link_rewrite, $category_image_id, 'category_default');
					break;

				
				case 'product':
					$og_type = 'og:product';
					$this->assignProductOG();
					break;
			}


			$this->context->smarty->assign(array(
				'og_type' => $og_type,
				'og_image' => $og_image,
				'og_site_name' => $og_site_name,
				'remove_site_name_title' => Configuration::get('PSOGRAPH_REMOVE_SITE_NAME')
			));

		}
    		


		return $this->display(__FILE__, 'psograph.tpl');
	}

	protected function assignProductOG()
	{
		// get product info
		// product:price:amount
		// product:price:currency
		if ($id_product = (int)Tools::getValue('id_product'))
		{
			$product_price = Product::getPriceStatic($id_product);
			$currency = $this->context->currency->iso_code;

			// Product Image
			$id_cover = Product::getCover($id_product);
			
			if($id_cover)
				$cover_img =  $this->context->link->getImageLink($id_product, $id_cover['id_image']);
			else $cover_img = '';

			$this->context->smarty->assign(array(
				'og_product_price_amount' => number_format($product_price, 2),
				'og_product_image' => $cover_img,
				'og_product_price_currency' => $currency
			));			

		}

	}	
	
	public function hookDisplayBackOfficeHeader($params)
	{

		if( isset($this->context->controller->dh_support) )
			return;

		$this->context->controller->dh_support = 1;
		if (version_compare(@_PS_VERSION_,'1.6','<'))
			$this->context->controller->addJS($this->_path . '/js/dh42_15.js', 'all');
		else
			$this->context->controller->addJS($this->_path . '/js/dh42.js', 'all');
		return;

	}
}
