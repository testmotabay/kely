<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\Api;

use AdScale\Helpers\Logger;
use Tools;

class ApiRequest
{
    
    private $ch;
    
    
    
    /**
     * Init curl session
     *
     * @param array $options An array specifying which options to set and their values.
     * The keys should be valid curl_setopt constants or
     * their integer equivalents.
     */
    public function init($options)
    {
        $this->ch = curl_init();
        
        $options_defaults = [
            CURLOPT_URL            => '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_CONNECTTIMEOUT => 12,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Accept-Charset: charset=utf-8',
                'Content-Type: application/json',
            ],
        ];
        
        $options = array_replace($options_defaults, $options);
        
        curl_setopt_array($this->ch, $options);
    }
    
    
    
    
    /**
     * Make curl request
     *
     * @return array  ['header','body','curl_error','http_code','last_url']
     */
    public function exec()
    {
        $response   = curl_exec($this->ch);
        $curl_error = curl_error($this->ch);
        $result     = [
            'headers_raw' => '',
            //'headers'     => '',
            'body_raw'    => '',
            'body'        => '',
            'curl_error'  => '',
            'http_code'   => '',
            'last_url'    => '',
        ];
        if ($response === false || $curl_error !== '') {
            $result['curl_error'] = $curl_error;
            
            return $result;
        }
        
        // headers
        $header_size           = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $result['headers_raw'] = Tools::substr($response, 0, $header_size);
        /*
        $headers_arr = explode( "\r\n", $result['headers_raw'] );
        $headers_arr = array_filter( $headers_arr );
        $headers     = [];
        
        foreach ( $headers_arr as $header_raw_row ) {
            $header_arr = explode( ' ', $header_raw_row );
            $header_arr = array_filter( $header_arr );
            $headers[]  = $header_arr;
        }
        
        $result['headers'] = $headers;
        */
        // body
        $result['body_raw'] = Tools::substr($response, $header_size);
        $result['body']     = self::mayBeJson($result['body_raw']);
        
        // code
        $result['http_code'] = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        
        // url
        $result['last_url'] = curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);
        
        Logger::log($result, 'response >>', 'api_request');
        
        return $result;
    }
    
    
    
    
    /**
     * @param $string
     * @param bool $return_decoded
     *
     * @return mixed
     */
    public static function mayBeJson($string, $return_decoded = true)
    {
        $may_be_json = json_decode($string, true);
        if ($return_decoded) {
            return (json_last_error() === JSON_ERROR_NONE) ? $may_be_json : $string;
        }
        
        return json_last_error() === JSON_ERROR_NONE;
    }
}
