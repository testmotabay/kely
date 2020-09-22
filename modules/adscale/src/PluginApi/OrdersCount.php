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
use Context;
use Db;
use ObjectModel;
use PrestaShopDatabaseException;
use Shop;

class OrdersCount extends PluginApiBase
{
    
    public static function checkRequest()
    {
        parent::checkRequest();
        self::initParams();
        
        // Start date
        if (self::checkIfRequestParamExists('start') && ! self::checkParamStartFormatValid()) {
            throw new PluginApiExeption('\'start\' parameter is in wrong format, expected DDMMYYYY', '003');
        }
        
        // End date
        if (self::checkIfRequestParamExists('end') && ! self::checkParamEndFormatValid()) {
            throw new PluginApiExeption('\'end\' parameter is in wrong format, expected DDMMYYYY', '004');
        }
    }
    
    
    
    public static function initParams()
    {
        self::$requestParams['start'] = Helper::getPostBodyDataValue('start');
        self::$requestParams['end']   = Helper::getPostBodyDataValue('end');
    }
    
    
    
    /**
     * Check date param 'start' exists
     *
     * @return bool
     */
    public static function checkParamStartFormatValid()
    {
        return ! empty(self::$requestParams['start']) && Helper::validateDate(self::$requestParams['start'], 'dmY');
    }
    
    
    
    /**
     * Check date param 'end'
     *
     * @return bool is valid
     */
    public static function checkParamEndFormatValid()
    {
        return ! empty(self::$requestParams['end']) && Helper::validateDate(self::$requestParams['end'], 'dmY');
    }
    
    
    
    /**
     * @throws PluginApiExeption
     * @throws PrestaShopDatabaseException
     */
    public static function handleRequest()
    {
        $count = self::getFilteredObjectListCount();
        if (is_numeric($count)) {
            Helper::sendResponseFormattedSuccess(['count' => (int)$count]);
        } else {
            throw new PluginApiExeption('System error: Unexpected Db result', '000');
        }
    }
    
    
    
    
    /**
     * @return string|false|null
     * @throws PluginApiExeption
     * @throws PrestaShopDatabaseException
     */
    public static function getFilteredObjectListCount()
    {
        $sql_join   = '';
        $sql_filter = '';
        $sql_limit  = '';
        
        $sql_filter .= Shop::addSqlRestriction(Shop::SHARE_ORDER, 'main');
        
        // START
        $start = self::$requestParams['start'];
        if ($start && ($start_reformatted = Helper::reformatDate($start, 'dmY', 'Y-m-d'))) {
            $sql_filter .= ' AND `main`.`date_add` >= "' . pSQL("{$start_reformatted} 00:00:00") . '"';
        }
        
        // END
        $end = self::$requestParams['end'];
        if ($end && ($end_reformatted = Helper::reformatDate($end, 'dmY', 'Y-m-d'))) {
            $sql_filter .= ' AND `main`.`date_add` <= "' . pSQL("{$end_reformatted} 23:59:59") . '"';
        }
        
        return self::getWebserviceObjectListCount('Order', $sql_join, $sql_filter, $sql_limit);
    }
    
    
    
    
    /**
     * Returns object list count from db
     *
     * @param string $class_name
     * @param string $sql_join
     * @param string $sql_filter
     * @param string $sql_limit
     *
     * @return string|false|null
     *
     * @throws PrestaShopDatabaseException
     * @throws PluginApiExeption
     */
    public static function getWebserviceObjectListCount($class_name, $sql_join, $sql_filter, $sql_limit)
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
        SELECT COUNT( 
                DISTINCT 
                main.`' . bqSQL($definition['primary']) . '` 
                ) as `count`
        FROM `' . _DB_PREFIX_ . bqSQL($definition['table']) . '` AS main
        ' . $sql_join . '
        WHERE 1 ' . $sql_filter . '
        ' . ($sql_limit != '' ? $sql_limit : '');
        
        //die('<pre>' . print_r($query, true) . '</pre>');
        
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query, false);
    }
}
