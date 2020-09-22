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
use Carrier;
use Configuration;
use Context;
use Country;
use Db;
use ObjectModel;
use PrestaShopDatabaseException;
use PrestaShopException;
use RangePrice;
use RangeWeight;
use Shop;
use Validate;
use Zone;

class Shipping extends PluginApiBase
{
    
    /**
     * @throws PluginApiExeption
     * @throws PrestaShopException
     */
    public static function handleRequest()
    {
        $language_id = Configuration::get('PS_LANG_DEFAULT');
        
        $filteredObjectList = self::getFilteredObjectList($language_id);
        
        $zones = [];
        
        foreach ($filteredObjectList as $zoneObj) {
            /** @var Zone $zoneObj */
            
            if (! Validate::isLoadedObject($zoneObj)) {
                continue;
            }
            
            // Countries
            // --------
            $countries     = [];
            $countriesData = Country::getCountriesByZoneId($zoneObj->id, $language_id);
            foreach ($countriesData as $countryData) {
                $countries[] = [
                    'id'   => Helper::getArrayValue($countryData, 'id_country'),
                    'name' => Helper::getArrayValue($countryData, 'name'),
                    'code' => Helper::getArrayValue($countryData, 'iso_code'),
                ];
            }
            
            // Rates (From Carriers)
            // --------
            $rates = [];
            
            $carriersData = Carrier::getCarriers($language_id, true, false, $zoneObj->id, null);
            
            $rate_types = [
                Carrier::SHIPPING_METHOD_DEFAULT => 'default',
                Carrier::SHIPPING_METHOD_WEIGHT  => 'weight',
                Carrier::SHIPPING_METHOD_PRICE   => 'price',
                Carrier::SHIPPING_METHOD_FREE    => 'free',
            ];
            
            foreach ($carriersData as $carrierData) {
                $carrier_id = (int)Helper::getArrayValue($carrierData, 'id_carrier');
                $carrierObj = new Carrier($carrier_id, $language_id);
                
                if (! Validate::isLoadedObject($carrierObj)) {
                    continue;
                }
                
                $carrier_rate_type_id = $carrierObj->getShippingMethod();
                $carrier_rate_type    = Helper::getArrayValue($rate_types, $carrier_rate_type_id);
                
                $rate_is_free = false;
                if ($carrier_rate_type_id === Carrier::SHIPPING_METHOD_WEIGHT) {
                    $range_table = 'range_weight';
                    $rangesData  = RangeWeight::getRanges($carrier_id);
                } elseif ($carrier_rate_type_id === Carrier::SHIPPING_METHOD_PRICE) {
                    $range_table = 'range_price';
                    $rangesData  = RangePrice::getRanges($carrier_id);
                } elseif ($carrier_rate_type_id === Carrier::SHIPPING_METHOD_FREE) {
                    $rate_is_free = true;
                    $range_table  = false;
                    $rangesData   = [];
                } else {
                    $range_table = false;
                    $rangesData  = [];
                }
                
                if (! $range_table) { // skip carrier with type 'free' too
                    continue;
                }
                
                $ranges = [];
                
                $priceRangeData = ! $rate_is_free
                    ? Carrier::getDeliveryPriceByRanges($range_table, (int)$carrierObj->id)
                    : [];
                
                foreach ($rangesData as $rangeData) {
                    $range_id = Helper::getArrayValue($rangeData, 'id_' . $range_table);
                    $price    = self::getPriceForRange(
                        $range_id,
                        $priceRangeData,
                        $range_table,
                        $zoneObj->id,
                        $carrierObj->id
                    );
                    
                    $ranges[] = [
                        'from'  => Helper::getArrayValue($rangeData, 'delimiter1'),
                        'to'    => Helper::getArrayValue($rangeData, 'delimiter2'),
                        'price' => $price,
                    ];
                }
                
                $rates[] = [
                    'carrier_id'   => (int)$carrierObj->id,
                    'carrier_name' => $carrierObj->name,
                    'type'         => $carrier_rate_type,
                    'ranges'       => $ranges,
                ];
            }
            
            // skip zones with empty rates
            if (empty($rates)) {
                continue;
            }
            
            //    ZONES
            // ===========
            $zones[] = [
                'id'        => $zoneObj->id,
                'name'      => $zoneObj->name,
                'countries' => $countries,
                'rates'     => $rates,
            ];
        }
        
        Helper::sendResponseFormattedSuccess([
            'shipping_zones' => $zones,
        ]);
    }
    
    
    
    public static function getPriceForRange($range_id, $ranges_data, $range_table, $zone_id, $carrier_id)
    {
        foreach ($ranges_data as $range_data) {
            $data_zone_id     = (int)Helper::getArrayValue($range_data, 'id_zone');
            $data_carrier_id  = (int)Helper::getArrayValue($range_data, 'id_carrier');
            $data_range_id    = (int)Helper::getArrayValue($range_data, 'id_' . $range_table);
            $data_range_price = Helper::getArrayValue($range_data, 'price');
            
            if ($data_range_id && (int)$range_id === $data_range_id &&
                $data_zone_id && (int)$zone_id === $data_zone_id &&
                $data_carrier_id && (int)$carrier_id === $data_carrier_id) {
                return $data_range_price;
            }
        }
        
        return null;
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
        $sql_sort   = '';
        $sql_limit  = '';
        
        $sqlObjects = self::getWebserviceObjectList('Zone', $sql_join, $sql_filter, $sql_sort, $sql_limit);
        
        if ($sqlObjects) {
            foreach ($sqlObjects as $sqlObject) {
                $objects[] = new Zone((int)$sqlObject['id_zone'], $lang_id);
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
        //die('<pre>$assoc ' . print_r($assoc, true) . '</pre>');
        
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
