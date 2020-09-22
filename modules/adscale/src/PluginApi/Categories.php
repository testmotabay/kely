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
use AdScale\Helpers\Logger;
use Category;
use Configuration;
use Context;
use Db;
use ObjectModel;
use PrestaShopDatabaseException;
use Shop;
use Tools;

class Categories extends PluginApiBase
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
     * @throws PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function handleRequest()
    {
        $context = Context::getContext();
        
        $language_id = Configuration::get('PS_LANG_DEFAULT');
        
        $filteredObjectList = self::getFilteredObjectList();
        //die('<pre>$filteredObjectList' . print_r($filteredObjectList, true) . '</pre>');
        
        $categories = [];
        
        foreach ($filteredObjectList as $categoryObj) {
            /** @var Category $categoryObj */
            
            $title        = $categoryObj->getFieldByLang('name', $language_id);
            $link_rewrite = $categoryObj->getFieldByLang('link_rewrite', $language_id);
            $keywords     = $categoryObj->getFieldByLang('meta_keywords', $language_id);
            
            $category_url = $context->link->getCategoryLink(
                $categoryObj,
                $link_rewrite,
                (int)$language_id,
                null,
                $context->shop->id
            );
            
            $image_id   = $categoryObj->id_image;
            $image_path = _PS_CAT_IMG_DIR_ . (int)$image_id . '.jpg';
            $image_url  = $image_id && is_file($image_path)
                ? str_replace(_PS_ROOT_DIR_, Tools::getShopDomainSsl(true), $image_path)
                : '';
            
            $_products = $categoryObj->getProductsWs();
            $products  = self::createArrayOfIds($_products);
            
            $categories[] = [
                'id'        => $categoryObj->id_category,
                'title'     => $title,
                'url'       => $category_url,
                'keywords'  => $keywords,
                'image_url' => $image_url,
                'products'  => $products,
            ];
        }
        
        Helper::sendResponseFormattedSuccess([
            'categories' => $categories,
        ]);
    }
    
    
    
    /**
     * @return array
     * @throws PluginApiExeption
     * @throws PrestaShopDatabaseException
     */
    public static function getFilteredObjectList()
    {
        $objects = [];
        
        $sql_join   = '';
        $sql_filter = ' AND `main`.`active`= "1"';
        $sql_sort   = ' ORDER BY `main`.`id_category` ASC';
        $sql_limit  = '';
        
        // LAST ID FROM THE PREVIOUS PAGE
        $last_id_prev = self::$requestParams['after'];
        if ($last_id_prev) {
            $sql_filter .= ' AND `main`.`id_category` > ' . pSQL($last_id_prev);
        }
        
        // NUMBER OF ITEMS PER PAGE
        $items_per_page = self::$requestParams['limit'];
        if ($items_per_page) {
            $sql_limit .= ' LIMIT ' . pSQL($items_per_page);
        }
        
        $sqlObjects = self::getWebserviceObjectList('Category', $sql_join, $sql_filter, $sql_sort, $sql_limit);
        
        if ($sqlObjects) {
            foreach ($sqlObjects as $sqlObject) {
                $objects[] = new Category((int)$sqlObject['id_category']);
            }
        }
        
        return $objects;
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
