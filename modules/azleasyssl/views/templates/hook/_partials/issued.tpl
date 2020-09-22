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

<div class="azleasyssl-config-panel hidden" id="azleasyssl-issued">
    <div class="bootstrap panel form-horizontal hidden" id="azl-issued-certificate">
        <h3><i class="icon-cogs"></i> {l s='Issued certificate' mod='azleasyssl'}</h3>
        <div class="form-wrapper">
            <div class="row form-group">
                <div class="col-lg-6 col-sm-12">
                    <div class="">
                        <h2>{l s='How to install certificate' mod='azleasyssl'}</h2>
                        <ul class="azl-manual">
                            <li>
                                <a href="#" data-target="#cpanel-manual">{l s='CPanel manual' mod='azleasyssl'}</a>
                            </li>
                            <li>
                                <a href="#" data-target="#plesk-manual">{l s='Plesk manual' mod='azleasyssl'}</a>
                            </li>
                            <li>
                                <a href="#" data-target="#nginx-manual">{l s='Nginx manual' mod='azleasyssl'}</a>
                            </li>
                            <li>
                                <a href="#" data-target="#apache-manual">{l s='Apache2 manual' mod='azleasyssl'}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-lg-6 col-sm-12">
                    <div class="">
                        <label>{l s='Certificate (cert.pem)' mod='azleasyssl'}</label>
                        <textarea class="form-control" id="azl-issued-cert" rows="8" readonly=""></textarea>
                        <button class="btn btn-default btn-clipboard btn-block" data-clipboard-target="#azl-issued-cert" type="button"><i class="icon icon-clipboard"></i> {l s='Copy to clipboard' mod='azleasyssl'}</button>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-12">
                    <div class="">
                        <label>{l s='Private KEY' mod='azleasyssl'}</label>
                        <textarea class="form-control" id="azl-issued-private" rows="8" readonly=""></textarea>
                        <button class="btn btn-default btn-clipboard btn-block" data-clipboard-target="#azl-issued-private" type="button"><i class="icon icon-clipboard"></i> {l s='Copy to clipboard' mod='azleasyssl'}</button>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-lg-6 col-sm-12">
                    <div class="">
                        <label>{l s='Chain' mod='azleasyssl'}</label>
                        <textarea class="form-control" id="azl-issued-chain" rows="8" readonly=""></textarea>
                        <button class="btn btn-default btn-clipboard btn-block" data-clipboard-target="#azl-issued-chain" type="button"><i class="icon icon-clipboard"></i> {l s='Copy to clipboard' mod='azleasyssl'}</button>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-12">
                    <div class="">
                        <label>{l s='Fullchain' mod='azleasyssl'}</label>
                        <textarea class="form-control" id="azl-issued-fullchain" rows="8" readonly=""></textarea>
                        <button class="btn btn-default btn-clipboard btn-block" data-clipboard-target="#azl-issued-fullchain" type="button"><i class="icon icon-clipboard"></i> {l s='Copy to clipboard' mod='azleasyssl'}</button>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-lg-6 col-sm-12">
                    <div class="">
                        <label>{l s='Public KEY' mod='azleasyssl'}</label>
                        <textarea class="form-control" id="azl-issued-public" rows="8" readonly=""></textarea>
                        <button class="btn btn-default btn-clipboard btn-block" data-clipboard-target="#azl-issued-public" type="button"><i class="icon icon-clipboard"></i> {l s='Copy to clipboard' mod='azleasyssl'}</button>
                    </div>
                </div>
            </div>
            <a href="" class="btn btn-success btn-block" id="azl-issued-download">
                <i class="icon icon-download"></i> {l s='Download ZIP' mod='azleasyssl'}
            </a>
        </div>
    </div>
</div>