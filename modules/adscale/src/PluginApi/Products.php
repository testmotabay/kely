<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\PluginApi;

use AdScale\Helpers\Helper;
use Attribute;
use AttributeGroup;
use Combination;
use Configuration;
use Context;
use Db;
use ImageType;
use ObjectModel;
use PrestaShopDatabaseException;
use PrestaShopException;
use Product;
use Shop;
use StockAvailable;
use Tools;

class Products extends PluginApiBase
{
    
    public static function checkRequest()
    {
        parent::checkRequest();
        self::initParams();
        
        // last id from the previous page
        if (! self::checkParamAfter()) {
            throw new PluginApiExeption('System error: Invalid param \'after\'', '000');
        }
        
        // Number of items per page, min
        if (! self::checkParamLimitForMin()) {
            throw new PluginApiExeption('System error: Invalid param \'limit\'', '000');
        }
        
        // Number of items per page,  max
        if (! self::checkParamLimitForMax()) {
            throw new PluginApiExeption('\'limit\' parameter is above ' . self::$maxLimit, '005');
        }
    }
    
    
    
    public static function initParams()
    {
        self::$requestParams['after'] = Helper::getPostBodyDataValue('after');
        self::$requestParams['limit'] = Helper::getPostBodyDataValue('limit');
        
        // Set default params if not exists
        if (null === self::$requestParams['after']) {
            self::$requestParams['after'] = 0;
        }
        if (null === self::$requestParams['limit']) {
            self::$requestParams['limit'] = self::$defaultLimit;
        }
        
        // Params Type Hinting
        self::$requestParams['after'] = (int)self::$requestParams['after'];
        self::$requestParams['limit'] = (int)self::$requestParams['limit'];
    }
    
    
    
    /**
     * last id from the previous page, check if is valid
     *
     * @return bool is valid
     */
    public static function checkParamAfter()
    {
        return self::$requestParams['after'] >= 0;
    }
    
    
    
    /**
     * Check min Number of items per page
     *
     * @return bool is valid
     */
    public static function checkParamLimitForMin()
    {
        return self::$requestParams['limit'] > 0;
    }
    
    
    
    /**
     * Check max Number of items per page
     *
     * @return bool is valid
     */
    public static function checkParamLimitForMax()
    {
        return self::$requestParams['limit'] <= self::$maxLimit;
    }
    
    
    
    /**
     * @throws PluginApiExeption
     * @throws PrestaShopException
     */
    public static function handleRequest()
    {
        $context               = Context::getContext();
        $context->currency->id = (int)Configuration::get('PS_CURRENCY_DEFAULT');
        
        $language_id = Configuration::get('PS_LANG_DEFAULT');
        
        $filteredObjectList = self::getFilteredObjectList($language_id);
        
        $products = [];
        
        foreach ($filteredObjectList as $productObj) {
            /** @var Product $productObj */
            
            $productHasAttributes = $productObj->hasAttributes();
            
            $title        = $productObj->getFieldByLang('name', $language_id);
            $link_rewrite = $productObj->getFieldByLang('link_rewrite', $language_id);
            $keywords     = $productObj->getFieldByLang('meta_keywords', $language_id);
            
            $product_url = $context->link->getProductLink(
                $productObj,
                $link_rewrite,
                htmlspecialchars(strip_tags($productObj->category)),
                $productObj->ean13,
                (int)$language_id,
                $context->shop->id,
                0
            );
            
            $_categories = $productObj->getWsCategories();
            $categories  = self::createArrayOfIds($_categories);
            
            $_images     = $productObj->getWsImages();
            $images_ids  = self::createArrayOfIds($_images);
            $images_urls = self::createArrayOfImgUrls($productObj, $images_ids);
            
            $stockAvailables = $productObj->getWsStockAvailables();
            
            $productQuantities = [];
            foreach ($stockAvailables as $stockAvailableData) {
                $stockAvailableObj = new StockAvailable((int)$stockAvailableData['id'], $language_id);
                
                $productQuantities[$stockAvailableData['id_product_attribute']] = $stockAvailableObj->quantity;
            }
            
            $available_for_order_by_visibility = (bool)$productObj->available_for_order; // true/false
            
            // combinations
            $_combinations_ids = $productObj->getWsCombinations();
            $combinations_ids  = self::createArrayOfIds($_combinations_ids);
            $combinations      = [];
            
            foreach ($combinations_ids as $combination_id) {
                $combinationObj = new Combination((int)$combination_id, $language_id);
                
                // combinations Titles
                $combinationsTitles = [];
                
                $_product_option_values = $combinationObj->getWsProductOptionValues();
                $product_option_values  = self::createArrayOfIds($_product_option_values);
                
                foreach ($product_option_values as $attribute_id) {
                    $attributeObj = new Attribute((int)$attribute_id, $language_id);
                    
                    $attribute_name     = $attributeObj->getFieldByLang('name', $language_id);
                    $attribute_group_id = $attributeObj->id_attribute_group;
                    $attributeGroupObj  = new AttributeGroup((int)$attribute_group_id, $language_id);
                    
                    $attribute_group_name = $attributeGroupObj->getFieldByLang('name', $language_id);
                    
                    $combinationsTitles[$combination_id][] = "{$attribute_group_name} - {$attribute_name}";
                }
                
                // combinations Images
                $_combination_images     = $combinationObj->getWsImages();
                $combination_images_ids  = self::createArrayOfIds($_combination_images);
                $combination_images_urls = self::createArrayOfImgUrls($productObj, $combination_images_ids);
                
                $price_before_discount = self::priceCalculation($productObj->id, $combinationObj->id, false, 1, 1);
                $price_before_discount = Helper::roundToString($price_before_discount, 2);
                
                //$price_before_discount_notax = self::priceCalculation($productObj->id, $combinationObj->id, 0,0,0);
                //$price_before_discount_notax = Helper::roundToString($price_before_discount_notax, 2);
                
                $price = self::priceCalculation($productObj->id, $combinationObj->id, true, 1, 1);
                $price = Helper::roundToString($price, 2);
                
                //$price_notax = self::priceCalculation($productObj->id, $combinationObj->id, true, 0, 0);
                //$price_notax = Helper::roundToString($price_notax, 2);
                
                $qty_for_order              = 1;
                $available_for_order_by_qty = self::availableForOrderByQty(
                    $productObj->id,
                    $combinationObj->id,
                    $qty_for_order
                );
                
                $combinations[] = [
                    'id'         => $combinationObj->id,
                    'title'      => $combinationsTitles[$combinationObj->id]
                        ? implode(', ', $combinationsTitles[$combinationObj->id])
                        : '',
                    'images_url' => $combination_images_urls,
                    
                    'inventory_quantity'                => $productQuantities[$combinationObj->id],
                    'available_for_order_by_quantity'   => $available_for_order_by_qty,
                    'available_for_order_by_visibility' => $available_for_order_by_visibility,
                    
                    'sku'                   => $combinationObj->reference,
                    'barcode_ean13'         => $combinationObj->ean13,
                    'barcode_upc'           => $combinationObj->upc,
                    'price_before_discount' => $price_before_discount,
                    //'price_before_discount_notax' => $price_before_discount_notax,
                    'price'                 => $price,
                    //'price_notax'                 => $price_notax,
                ];
            }
            
            // If product has no variants - making variant with id of product
            if (! $combinations && ! $productHasAttributes) {
                $price_before_discount = self::priceCalculation($productObj->id, 0, false, 1, 1);
                $price_before_discount = Helper::roundToString($price_before_discount, 2);
                
                //$price_before_discount_notax = self::priceCalculation($productObj->id, 0, false, 0, 0);
                //$price_before_discount_notax = Helper::roundToString($price_before_discount_notax, 2);
                
                $price = self::priceCalculation($productObj->id, 0, true, 1, 1);
                $price = Helper::roundToString($price, 2);
                
                //$price_notax = self::priceCalculation($productObj->id, 0, true, 0, 0);
                //$price_notax = Helper::roundToString($price_notax, 2);
                
                $qty_for_order              = 1;
                $available_for_order_by_qty = self::availableForOrderByQty($productObj->id, null, $qty_for_order);
                
                $combinations[] = [
                    'id'         => 0, //$productObj->id,
                    'title'      => $title,
                    'images_url' => $images_urls,
                    
                    'inventory_quantity'                => $productQuantities[0],
                    'available_for_order_by_quantity'   => $available_for_order_by_qty,
                    'available_for_order_by_visibility' => $available_for_order_by_visibility,
                    
                    'sku'                   => $productObj->reference,
                    'barcode_ean13'         => $productObj->ean13,
                    'barcode_upc'           => $productObj->upc,
                    'price_before_discount' => $price_before_discount,
                    //'price_before_discount_notax' => $price_before_discount_notax,
                    'price'                 => $price,
                    //'price_notax'                 => $price_notax,
                ];
            }
            
            $products[] = [
                'id'         => $productObj->id,
                'title'      => $title,
                'url'        => $product_url,
                'images_url' => $images_urls,
                'keywords'   => $keywords,
                'categories' => $categories,
                'variants'   => $combinations,
            ];
        }
        
        Helper::sendResponseFormattedSuccess([
            'products' => $products,
        ]);
    }
    
    
    
    
    /**
     * Check product availability.
     *
     * @param int $product_id
     * @param int|null $product_attribute_id
     * @param int $qty Quantity desired
     *
     * @return bool True if product is available with this quantity, false otherwise
     */
    public static function availableForOrderByQty($product_id, $product_attribute_id = null, $qty = 1)
    {
        if (Product::isAvailableWhenOutOfStock(StockAvailable::outOfStock((int)$product_id))) {
            return true;
        }
        
        $availableQuantity = StockAvailable::getQuantityAvailableByProduct((int)$product_id, $product_attribute_id);
        
        return $qty <= $availableQuantity;
    }
    
    
    
    
    /**
     * @param int $id_product
     * @param int $id_product_attribute
     * @param bool|int $use_reduction
     * @param bool|int $use_tax
     * @param bool|int $with_ecotax
     *
     * @return float
     */
    public static function priceCalculation(
        $id_product,
        $id_product_attribute = 0,
        $use_reduction = true,
        $use_tax = true,
        $with_ecotax = true
    ) {
        $context = Context::getContext();
        
        $specific_price_output = null;
        //$use_tax     = (int)((null === $use_tax) ? Configuration::get('PS_TAX') : $use_tax);
        //$with_ecotax = (int)((null === $with_ecotax) ? Configuration::get('PS_USE_ECOTAX') : $with_ecotax);
        
        return Product::priceCalculation(
            (int)$context->shop->id,
            (int)$id_product,
            (int)$id_product_attribute,
            (int)Configuration::get('PS_COUNTRY_DEFAULT'),
            0,
            0,
            (int)Configuration::get('PS_CURRENCY_DEFAULT'),
            (int)Configuration::get('PS_CUSTOMER_GROUP'),
            1,
            (int)$use_tax,
            2,
            false,
            (int)$use_reduction,
            (int)$with_ecotax,
            $specific_price_output,
            null
        );
    }
    
    
    
    
    /**
     * @param int|null $lang_id
     *
     * @return array
     * @throws PluginApiExeption
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getFilteredObjectList($lang_id = null)
    {
        $objects = [];
        
        $sql_join   = '';
        $sql_filter = ' AND `main`.`active`= "1"';
        $sql_sort   = ' ORDER BY `main`.`id_product` ASC';
        $sql_limit  = '';
        
        // LAST ID FROM THE PREVIOUS PAGE
        $last_id_prev = self::$requestParams['after'];
        if ($last_id_prev) {
            $sql_filter .= ' AND `main`.`id_product` > ' . pSQL($last_id_prev);
        }
        
        // NUMBER OF ITEMS PER PAGE
        $items_per_page = self::$requestParams['limit'];
        if ($items_per_page) {
            $sql_limit .= ' LIMIT ' . pSQL($items_per_page);
        }
        
        $sqlObjects = self::getWebserviceObjectList('Product', $sql_join, $sql_filter, $sql_sort, $sql_limit);
        
        if ($sqlObjects) {
            foreach ($sqlObjects as $sqlObject) {
                $objects[] = new Product((int)$sqlObject['id_product'], false, $lang_id);
            }
        }
        
        return $objects;
    }
    
    
    
    
    /**
     * @param Product $product
     * @param array $images_ids
     *
     * @return array
     */
    public static function createArrayOfImgUrls($product, $images_ids)
    {
        return is_array($images_ids)
            ? array_map(
                function ($image_id) use ($product) {
                    $context = Context::getContext();
                    
                    $type = method_exists('ImageType', 'getFormattedName')
                        ? ImageType::getFormattedName('large')
                        : ImageType::getFormatedName('large'); // legacy
                    
                    $image_link = $context->link->getImageLink(
                        $product->link_rewrite,
                        $product->id . '-' . (int)$image_id,
                        $type
                    );
                    
                    $virtual_uri      = rtrim($context->shop->virtual_uri, '/');
                    $image_link_parts = explode('/', $image_link);
                    
                    if (! in_array($virtual_uri, $image_link_parts, true)) {
                        $image_link = str_replace(
                            [
                                'https',
                                $context->shop->domain . $context->shop->physical_uri,
                            ],
                            [
                                'http',
                                $context->shop->domain . $context->shop->physical_uri . $context->shop->virtual_uri,
                            ],
                            $image_link
                        );
                    }
                    
                    return $image_link;
                },
                $images_ids
            )
            : [];
    }
    
    
    
    
    /**
     * Returns object list from db
     *
     * @param string $class_name
     * @param string $sql_join
     * @param string $sql_filter
     * @param string $sql_sort
     * @param string $sql_limit
     *
     * @return array|null
     *
     * @throws PluginApiExeption
     * @throws PrestaShopDatabaseException
     */
    public static function getWebserviceObjectList($class_name, $sql_join, $sql_filter, $sql_sort, $sql_limit)
    {
        if (! $class_name || ! is_string($class_name)) {
            throw new PluginApiExeption('System error: Invalid ObjectModel Class', '000');
        }
        
        $definition = ObjectModel::getDefinition($class_name);
        
        if (empty($definition['table'] || empty($definition['primary']))) {
            throw new PluginApiExeption('System error: Invalid ObjectModel definition', '000');
        }
        
        $assoc = Shop::getAssoTable($definition['table']);
        
        if ($assoc !== false) {
            if ($assoc['type'] !== 'fk_shop') {
                $multi_shop_join = ' LEFT JOIN `' . _DB_PREFIX_ . bqSQL($definition['table']) .
                                   '_' . bqSQL($assoc['type']) . '`
                                        AS `multi_shop_' . bqSQL($definition['table']) . '`
                                        ON (main.`' . bqSQL($definition['primary']) . '` = `multi_shop_' .
                                   bqSQL($definition['table']) . '`.`' . bqSQL($definition['primary']) . '`)';
                $sql_filter      = 'AND `multi_shop_' . bqSQL($definition['table']) . '`.id_shop = ' .
                                   Context::getContext()->shop->id . ' ' . $sql_filter;
                $sql_join        = $multi_shop_join . ' ' . $sql_join;
            } else {
                $shopIDs = Shop::getShops(true, null, true);
                $or      = [];
                foreach ($shopIDs as $id_shop) {
                    $or[] = '(main.id_shop = ' . (int)$id_shop . (isset($definition['fields']['id_shop_group'])
                            ? ' OR (id_shop = 0 AND id_shop_group=' .
                              (int)Shop::getGroupFromShop((int)$id_shop) . ')' : '') . ')';
                }
                
                $prepend = '';
                if (count($or)) {
                    $prepend = 'AND (' . implode('OR', $or) . ')';
                }
                $sql_filter = $prepend . ' ' . $sql_filter;
            }
        }
        
        $query = '
        SELECT DISTINCT 
            main.`' . bqSQL($definition['primary']) . '` 
        FROM `' . _DB_PREFIX_ . bqSQL($definition['table']) . '` AS main
        ' . $sql_join . '
        WHERE 1 ' . $sql_filter . '
        ' . ($sql_sort != '' ? $sql_sort : '') . '
        ' . ($sql_limit != '' ? $sql_limit : '');
        
        //die('<pre>' . print_r($query, true) . '</pre>');
        
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query, true, false);
    }
}
