{*
* 2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@azelab.com so we can send you a copy immediately.
*
*
*  @author Azelab <support@azelab.com>
*  @copyright  2017 Azelab
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*}

<div class="azleasyssl-config-panel {if $active_tab == 'AzlEasysslGeneralConfig'}hidden{/if}" id="azleasyssl-general">
    <div class="bootstrap panel form-horizontal">
        <h3><i class="icon-cogs"></i> {l s='Generate SSL certificate' mod='azleasyssl'}</h3>
        <div class="form-wrapper">
            <form action="" onsubmit="azlStep1(); return false;" method="POST">
                <div class="form-group">
                    <label class="control-label col-sm-3">{l s='Email' mod='azleasyssl'}</label>
                    <div class="col-sm-4">
                        <input type="text" value="{$email|escape:'htmlall':'UTF-8'}" name="email" required="" id="azl-email" class="control-form" />
                        <p class="help-block">
                            {l s='Enter email which you want to receive notifications from LetsEncrypt' mod='azleasyssl'}
                        </p>
                    </div>
                    <div class="col-sm-5">
                        <button class="btn btn-success" type="submit">{l s='Issue SSL certificate' mod='azleasyssl'}</button>
                        <button id="azl-issued" onclick="azlViewIssuedSSL();" class="btn btn-default {if !$issued}hidden{/if}" type="button">{l s='Show issued certificate' mod='azleasyssl'}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>