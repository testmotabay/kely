<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\ServiceApi;

class ServiceApiExeption extends \Exception
{
    
    protected $apiErrorCode;
    
    
    
    public function __construct($message, $apiErrorCode = '000')
    {
        $this->apiErrorCode = (string)$apiErrorCode;
        parent::__construct($message, (int)$apiErrorCode);
    }
    
    
    
    /**
     * @return string
     */
    public function __toString()
    {
        return __CLASS__ . ": [{$this->apiErrorCode}]: {$this->message}\n";
    }
    
    
    
    
    /**
     * @return string
     */
    public function getApiErrorCode()
    {
        return $this->apiErrorCode;
    }
}
