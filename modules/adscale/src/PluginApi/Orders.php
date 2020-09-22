<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\PluginApi;

use Address;
use AdScale\Helpers\Helper;
use Configuration;
use Context;
use Country;
use Currency;
use Db;
use ObjectModel;
use Order;
use PrestaShopDatabaseException;
use PrestaShopException;
use Shop;

class Orders extends PluginApiBase
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
        self::$requestParams['after'] = Helper::getPostBodyDataValue('after');
        self::$requestParams['limit'] = Helper::getPostBodyDataValue('limit');
        self::$requestParams['start'] = Helper::getPostBodyDataValue('start');
        self::$requestParams['end']   = Helper::getPostBodyDataValue('end');
        
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
     * @throws PrestaShopException
     */
    public static function handleRequest()
    {
        $context               = Context::getContext();
        $context->currency->id = (int)Configuration::get('PS_CURRENCY_DEFAULT');
        
        $language_id = Configuration::get('PS_LANG_DEFAULT');
        
        $filteredObjectList = self::getFilteredObjectList($language_id);
        
        $orders = [];
        
        foreach ($filteredObjectList as $orderObj) {
            /** @var Order $orderObj */
            
            // Currency
            // --------
            $currencyObj       = new Currency($orderObj->id_currency);
            $currency_iso_code = $currencyObj->iso_code;
            
            // Buyer
            // --------
            //$customer_id = $orderObj->id_customer;
            $customerObj = $orderObj->getCustomer();
            
            // Address
            // --------
            $address_delivery_id = $orderObj->id_address_delivery;
            $addressObj          = new Address($address_delivery_id, $language_id);
            $phone               = $addressObj->phone;
            $phone_mobile        = $addressObj->phone_mobile;
            $country_id          = $addressObj->id_country;
            //$country_name      = $addressObj->country;
            $country_code = Country::getIsoById($country_id);
            
            // Order Items
            // --------
            $orderItems = [];
            $orderRows  = $orderObj->getWsOrderRows();
            
            if (is_array($orderRows)) {
                foreach ($orderRows as $orderRow) {
                    $orderItems[] = [
                        'product_id' => $orderRow['product_id'],
                        'variant_id' => $orderRow['product_attribute_id'],
                        'quantity'   => $orderRow['product_quantity'],
                        'price'      => Helper::roundToString($orderRow['product_price'], 2),
                        //'row' => $orderRow
                        //'name'       => $orderRow['product_name'],
                    ];
                }
            } else {
                throw new PluginApiExeption('System error: Unexpected result [Order::getWsOrderRows]', '000');
            }
            
            $orders[] = [
                'id'             => $orderObj->id,
                'email'          => $customerObj->email,
                'date'           => $orderObj->date_add,
                'total_price'    => Helper::roundToString($orderObj->total_paid, 2),
                'shipping_price' => Helper::roundToString($orderObj->total_shipping, 2),
                'currency'       => $currency_iso_code,
                'phone'          => $phone ?: $phone_mobile,
                'country'        => $country_code,
                'items'          => $orderItems,
            ];
        }
        
        Helper::sendResponseFormattedSuccess([
            'orders' => $orders,
        ]);
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
        $sql_filter = '';
        $sql_sort   = ' ORDER BY `main`.`id_order` ASC';
        $sql_limit  = '';
        
        $sql_filter .= Shop::addSqlRestriction(Shop::SHARE_ORDER, 'main');
        
        // LAST ID FROM THE PREVIOUS PAGE
        $last_id_prev = self::$requestParams['after'];
        if ($last_id_prev) {
            $sql_filter .= ' AND `main`.`id_order` > ' . pSQL($last_id_prev);
        }
        
        // NUMBER OF ITEMS PER PAGE
        $items_per_page = self::$requestParams['limit'];
        if ($items_per_page) {
            $sql_limit .= ' LIMIT ' . pSQL($items_per_page);
        }
        
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
        
        $sqlObjects = self::getWebserviceObjectList('Order', $sql_join, $sql_filter, $sql_sort, $sql_limit);
        
        if ($sqlObjects) {
            foreach ($sqlObjects as $sqlObject) {
                $objects[] = new Order((int)$sqlObject['id_order'], $lang_id);
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
        
        //die('<pre>$query ' . print_r($query, true) . '</pre>');
        
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query, true, false);
    }
}
