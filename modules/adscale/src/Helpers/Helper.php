<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\Helpers;

use AdScale\Api\ApiManager;
use AdScale\App;
use Configuration;
use Context;
use Currency;
use Module;
use Product;
use Order;
use ProductControllerCore;
use OrderConfirmationControllerCore;
use Tools;
use Validate;

class Helper
{
    
    /**
     * Appends a trailing slash.
     */
    public static function trailingslashit($string)
    {
        return self::untrailingslashit($string) . '/';
    }
    
    
    
    /**
     * Removes trailing forward slashes and backslashes if they exist.
     */
    public static function untrailingslashit($string)
    {
        return rtrim($string, '/\\');
    }
    
    
    
    
    public static function getModuleInstance()
    {
        return \Module::getInstanceByName('adscale');
    }
    
    
    
    public static function getModuleTrans($str, $customModuleClass)
    {
        $module = self::getModuleInstance();
        
        return $module->l($str, $customModuleClass);
    }
    
    
    
    public static function getShopDomainComputed()
    {
        $shop_domain = parse_url(Tools::getShopDomain(true), PHP_URL_HOST);
        $prefix      = 'www.';
        if (strpos($shop_domain, $prefix) === 0) {
            $shop_domain = Tools::substr($shop_domain, Tools::strlen($prefix));
        }
        
        return $shop_domain;
    }
    
    
    
    public static function getShopDomainFromOption()
    {
        return Configuration::get('ADSCALE_SHOP_DOMAIN');
    }
    
    
    
    public static function getShopDomain()
    {
        return self::getShopDomainFromOption();
    }
    
    
    
    public static function saveComputedShopDomain()
    {
        return Configuration::updateValue('ADSCALE_SHOP_DOMAIN', self::getShopDomainComputed());
    }
    
    
    
    public static function getGSVCode()
    {
        return ApiManager::getSavedApiGsvCode();
    }
    
    
    
    public static function isProductPage()
    {
        /** @var $context Context */
        $context = App::instance()->getContext();
        
        return 'product' === $context->controller->php_self;
    }
    
    
    
    public static function isOrderConfirmedPage()
    {
        /** @var $context Context */
        $context = App::instance()->getContext();
        
        return 'order-confirmation' === $context->controller->php_self;
    }
    
    
    
    public static function getProduct()
    {
        /** @var $context Context */
        $context = App::instance()->getContext();
        
        /** @var $controller ProductControllerCore */
        $controller = $context->controller;
        
        $product = $controller instanceof ProductControllerCore && method_exists($controller, 'getProduct')
            ? $controller->getProduct()
            : false;
        
        if (! Validate::isLoadedObject($product)) {
            return false;
        }
        
        return $product;
    }
    
    
    
    public static function getProductId()
    {
        /** @var $product Product | bool */
        $product = self::getProduct();
        
        if (! Validate::isLoadedObject($product)) {
            return false;
        }
        
        return $product->id;
    }
    
    
    
    public static function getProductEmbeddedAttributes()
    {
        /** @var $context Context */
        $context = App::instance()->getContext();
        
        /** @var $controller ProductControllerCore */
        $controller = $context->controller;
        
        if ($controller instanceof ProductControllerCore) {
            if (method_exists($controller, 'getTemplateVarProduct')) {
                $templateVarProduct = $controller->getTemplateVarProduct();
            } else {
                // presta 1.6.x
                $product            = $controller->getProduct();
                $templateVarProduct = ['embedded_attributes' => get_object_vars($product)];
            }
            
            if (is_array($templateVarProduct) && isset($templateVarProduct['embedded_attributes'])) {
                return $templateVarProduct['embedded_attributes'];
            }
            if (is_object($templateVarProduct) && method_exists($templateVarProduct, 'getEmbeddedAttributes')) {
                return $templateVarProduct->getEmbeddedAttributes();
            }
        }
        
        return false;
    }
    
    
    
    public static function getProductValue()
    {
        $productAttributes = self::getProductEmbeddedAttributes();
        
        $price        = isset($productAttributes['price_amount'])
            ? Tools::ps_round($productAttributes['price_amount'], 2)
            : false;
        $price_legacy = isset($productAttributes['price'])
            ? Tools::ps_round($productAttributes['price'], 2)
            : false;
        
        return $price !== false ? $price : $price_legacy;
    }
    
    
    
    public static function getOrderFromOrderConfirmation()
    {
        /** @var $context Context */
        $context = App::instance()->getContext();
        
        /** @var $controller OrderConfirmationControllerCore */
        $controller = $context->controller;
        
        if ($controller instanceof OrderConfirmationControllerCore && ! empty($controller->id_cart)) {
            $order_id = 0;
            if (method_exists(Order::class, 'getIdByCartId')) {
                $order_id = Order::getIdByCartId((int)$controller->id_cart);
            } elseif (method_exists(Order::class, 'getOrderByCartId')) {
                $order_id = Order::getOrderByCartId((int)$controller->id_cart);
            }
            
            if (! $order_id) {
                return false;
            }
            
            $order = new Order($order_id);
            
            return Validate::isLoadedObject($order) ? $order : false;
        }
        
        return false;
    }
    
    
    
    public static function getOrderIdFromOrderConfirmation()
    {
        /** @var $context Context */
        $context = App::instance()->getContext();
        
        /** @var $controller OrderConfirmationControllerCore */
        $controller = $context->controller;
        
        $id_order = $controller instanceof OrderConfirmationControllerCore && isset($controller->id_order)
            ? (int)$controller->id_order
            : false;
        
        return $id_order;
    }
    
    
    
    public static function getOrderValueFromOrderConfirmation()
    {
        /** @var $order Order */
        $order = self::getOrderFromOrderConfirmation();
        
        return isset($order->total_paid) ? Tools::ps_round($order->total_paid, 2) : false;
    }
    
    
    
    public static function getOrderCurrencyFromOrderConfirmation()
    {
        /** @var $order Order */
        $order = self::getOrderFromOrderConfirmation();
        
        if (! Validate::isLoadedObject($order)) {
            return null;
        }
        
        /** @var $currency Currency */
        $currency = new Currency($order->id_currency);
        
        if (! Validate::isLoadedObject($currency)) {
            return null;
        }
        
        return isset($currency->iso_code) ? $currency->iso_code : null;
    }
    
    
    
    public static function getConfigSetting($name, $default)
    {
        $parts = explode('/', $name);
        
        $config = App::instance()->getConfig();
        
        if (! isset($config[$parts[0]])) {
            return $default;
        }
        
        $value = $config[array_shift($parts)];
        
        foreach ($parts as $part) {
            if (is_array($value) && isset($value[$part])) {
                $value = $value[$part];
            } else {
                return $default;
            }
        }
        
        return $value;
    }
    
    
    
    public static function randomFloat($min = 0, $max = 1)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
    
    
    
    public static function generateRandString($length = 32)
    {
        $chars          = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
        $chars_length   = Tools::strlen($chars);
        $generatedValue = '';
        
        for ($i = 1; $i <= $length; ++$i) {
            $generatedValue .= Tools::substr($chars, (int)floor(self::randomFloat(0, 1) * ($chars_length - 1)), 1);
        }
        
        return $generatedValue;
    }
    
    
    
    public static function generateRandHash($limit = 32)
    {
        if (! function_exists('openssl_random_pseudo_bytes')) {
            return self::generateRandString($limit);
        }
        
        $bytes = openssl_random_pseudo_bytes($limit);
        $hex   = Tools::strtoupper(bin2hex($bytes));
        
        return Tools::substr($hex, 0, $limit);
    }
    
    
    
    public static function getNocacheHeaders()
    {
        return [
            'Expires'       => 'Wed, 11 Jan 1984 05:00:00 GMT',
            'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
        ];
    }
    
    
    
    public static function nocacheHeaders()
    {
        $headers = self::getNocacheHeaders();
        
        // In PHP 5.3+, make sure we are not sending a Last-Modified header.
        if (function_exists('header_remove')) {
            @header_remove('Last-Modified');
        } else {
            // In PHP 5.2, send an empty Last-Modified header, but only as a
            // last resort to override a header already sent. #WP23021
            foreach (headers_list() as $header) {
                if (0 === stripos($header, 'Last-Modified')) {
                    $headers['Last-Modified'] = '';
                    break;
                }
            }
        }
        
        foreach ($headers as $name => $field_value) {
            @header("{$name}: {$field_value}");
        }
    }
    
    
    
    public static function statusHeader($code, $description = '')
    {
        if (! $description) {
            $description = self::getStatusHeaderDesc($code);
        }
        
        if (empty($description)) {
            return;
        }
        
        $protocol      = self::getServerProtocol();
        $status_header = "$protocol $code $description";
        
        @header($status_header, true, $code);
    }
    
    
    
    public static function getStatusHeaderDesc($code)
    {
        $code = abs((int)$code);
        
        $wp_header_to_desc = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            103 => 'Early Hints',
            
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            226 => 'IM Used',
            
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Reserved',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            421 => 'Misdirected Request',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            451 => 'Unavailable For Legal Reasons',
            
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
        ];
        
        return isset($wp_header_to_desc[$code]) ? $wp_header_to_desc[$code] : '';
    }
    
    
    
    public static function getServerProtocol()
    {
        $protocol = $_SERVER['SERVER_PROTOCOL'];
        if (! in_array($protocol, array('HTTP/1.1', 'HTTP/2', 'HTTP/2.0'))) {
            $protocol = 'HTTP/1.0';
        }
        
        return $protocol;
    }
    
    
    
    public static function getAccessKey()
    {
        return @hex2bin(self::getPostBodyDataValue('key'));
    }
    
    
    
    public static function getPostBodyDataValue($key)
    {
        $data = json_decode(Tools::file_get_contents('php://input'), false);
        
        return isset($data->$key) ? $data->$key : null;
    }
    
    
    
    public static function getGoToAdscaleUrl()
    {
        $shopHost   = self::getShopDomain();
        $loginToken = \AdScale\Handlers\LoginToken::processLoginToken();
        $email      = self::getResolvedActivationEmail();
        $platform   = self::getConfigSetting('shop/platform', 'presta');
        
        return 'https://app.adscale.com/EcommerceStart' .
               "?shopHost={$shopHost}&loginToken={$loginToken}&email={$email}&platform={$platform}";
    }
    
    
    
    /**
     * Send a JSON response back to an Ajax request, indicating success.
     *
     * @param mixed $data Data to encode as JSON, then print and die.
     * @param int $status_code The HTTP status code to output.
     */
    public static function sendJsonSuccess($data = null, $status_code = null)
    {
        $response = ['success' => true];
        
        if (isset($data)) {
            $response['data'] = $data;
        }
        
        self::sendJson($response, $status_code);
    }
    
    
    
    /**
     * Send a JSON response back to an Ajax request, indicating failure.
     *
     * @param mixed $data Data to encode as JSON, then print and die.
     * @param int $status_code The HTTP status code to output.
     */
    public static function sendJsonError($data = null, $status_code = null)
    {
        $response = ['success' => false];
        
        if (isset($data)) {
            $response['data'] = $data;
        }
        
        self::sendJson($response, $status_code);
    }
    
    
    
    public static function sendJson($response, $statusCode = null)
    {
        if (! headers_sent()) {
            header('Content-Type: application/json');
            if (null !== $statusCode) {
                self::statusHeader($statusCode);
            }
        }
        
        die(json_encode($response));
    }
    
    
    
    public static function getResolvedActivationEmail()
    {
        $infoFileEmail = self::getInfoFileEmail();
        
        return $infoFileEmail ? $infoFileEmail : self::getSiteEmail();
    }
    
    
    
    public static function getSiteEmail()
    {
        return Configuration::get('PS_SHOP_EMAIL');
    }
    
    
    
    public static function getInfoFileEmail()
    {
        return self::getInfoFileValue('email_on_download');
    }
    
    
    
    public static function getInfoFileValue($key)
    {
        $data = self::getInfoFileData();
        
        return isset($data[$key]) ? $data[$key] : null;
    }
    
    
    
    public static function getInfoFileData()
    {
        /** @var $module Module */
        $module     = App::instance()->getModule();
        $moduleName = $module->name;
        
        $infoFilePath = _PS_MODULE_DIR_ . $moduleName . '/info.php';
        
        if (is_file($infoFilePath)) {
            return include $infoFilePath;
        }
        
        return [];
    }
    
    
    
    /**
     * @param string $response
     *
     * @return string
     */
    public static function formatResponse($response)
    {
        return "ADSCALE_START{$response}ADSCALE_END";
    }
    
    
    
    /**
     * Send a JSON response, indicating success.
     *
     * @param mixed $data Data to encode as JSON, then print and die.
     * @param int $statusCode
     * @param string $statusDesc
     */
    public static function sendResponseJsonSuccess($data = null, $statusCode = 200, $statusDesc = '')
    {
        self::sendResponseJson($data, $statusCode, $statusDesc);
    }
    
    
    
    /**
     * Send a JSON response, indicating failure.
     *
     * @param mixed $data Data to encode as JSON, then print and die.
     * @param int $statusCode The HTTP status code to output.
     * @param string $statusDesc
     */
    public static function sendResponseJsonError($data = null, $statusCode = 500, $statusDesc = '')
    {
        self::sendResponseJson($data, $statusCode, $statusDesc);
    }
    
    
    
    public static function sendResponseJson($response, $statusCode = null, $statusDesc = '')
    {
        self::sendResponse(json_encode($response), $statusCode, $statusDesc, 'application/json');
    }
    
    
    
    /**
     * Send a Formatted response, indicating success.
     *
     * @param mixed $data Data to encode as JSON, then print and die.
     * @param int $statusCode
     * @param string $statusDesc
     */
    public static function sendResponseFormattedSuccess($data = null, $statusCode = 200, $statusDesc = '')
    {
        self::sendResponseFormatted($data, $statusCode, $statusDesc);
    }
    
    
    
    /**
     * Send a Formatted response, indicating failure.
     *
     * @param mixed $data Data to encode as JSON, then print and die.
     * @param int $statusCode The HTTP status code to output.
     * @param string $statusDesc
     */
    public static function sendResponseFormattedError($data = null, $statusCode = 500, $statusDesc = '')
    {
        self::sendResponseFormatted($data, $statusCode, $statusDesc);
    }
    
    
    
    public static function sendResponseFormatted($response, $statusCode = null, $statusDesc = '')
    {
        self::sendResponse(self::formatResponse(json_encode($response)), $statusCode, $statusDesc, 'text/plain');
    }
    
    
    
    public static function sendResponse($response, $statusCode = null, $statusDesc = '', $contentType = 'text/plain')
    {
        if (! headers_sent()) {
            if (null !== $statusCode) {
                self::statusHeader($statusCode, $statusDesc);
            }
            if (is_string($contentType) && $contentType) {
                header("Content-Type: {$contentType}");
            }
        }
        echo $response;
        die();
    }
    
    
    
    
    public static function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);
        
        return $d && $d->format($format) === $date;
    }
    
    
    
    
    /**
     * @param string $date
     * @param string $formatFrom
     * @param string $formatTo
     *
     * @return bool|string
     */
    public static function reformatDate($date, $formatFrom = 'dmY', $formatTo = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($formatFrom, $date);
        
        return $d ? $d->format($formatTo) : false;
    }
    
    
    
    
    /**
     * @param $val
     * @param int $precision
     * @param int $mode
     *
     * @return float
     */
    public static function roundToString($val, $precision = 2, $mode = PHP_ROUND_HALF_UP)
    {
        $val = (float)$val;
        $val = round($val, $precision, $mode);
        
        return number_format($val, $precision, '.', '');
    }
    
    
    
    
    public static function getArrayValue($array, $key)
    {
        return isset($array[$key]) ? $array[$key] : null;
    }
    
    
    
    
    public static function validateSecKey($key)
    {
        /** @var $module Module */
        $module     = App::instance()->getModule();
        $moduleName = $module->name;
        
        if (! class_exists('\Crypt_RSA')) {
            $libPath = _PS_MODULE_DIR_ . $moduleName . '/lib/phpseclib';
            set_include_path(get_include_path() . PATH_SEPARATOR . $libPath);
            require_once 'Crypt/RSA.php';
        }
        
        $rsa = new \Crypt_RSA();
        $rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
        $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
        $rsa->loadKey("-----BEGIN PUBLIC KEY-----\nMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCBMUfDGyQbPrNjBpZVkQ6Kabt"
                      . "4fo9W0fRlA7XKdbo2eg9P8i+TfBfeYBd6hlu/DlMnDL8AdElXKakNONT5B4y1/M15cBySmn1gDPLYs/u9rZrjmGL"
                      . "NZX/4sOomISUFihnDl2+3hGMrrGRQyuW78kY/ZJBpdqqybAJolJTe1J4R2wIDAQAB\n-----END PUBLIC KEY-----");
        
        //return $key === hex2bin('1111'); // for testing
        
        return @$rsa->verify(self::getShopDomain(), $key);
    }
}
