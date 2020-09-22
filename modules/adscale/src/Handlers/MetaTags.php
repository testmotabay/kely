<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\Handlers;

use AdScale\App;
use AdScale\Helpers\Helper;
use Context;

class MetaTags
{
    
    
    public static function getGoogleSiteVerificationTag()
    {
        $value = Helper::getGSVCode();
        $tag   = $value && is_string($value)
            ? "\n" . '<meta name="google-site-verification" content="' . $value . '">' . "\n"
            : '';
        
        return $tag;
    }
    
    
    
    public static function resolveGSV()
    {
        /** @var $context Context */
        
        $context = App::instance()->getContext();
        
        $gsv          = self::getGoogleSiteVerificationTag();
        $templateVars = $context->smarty->getTemplateVars();
        
        if (isset($templateVars['HOOK_HEADER']) && is_string($templateVars['HOOK_HEADER'])) {
            return $templateVars['HOOK_HEADER'] . $gsv;
        }
        
        return $gsv;
    }
}
