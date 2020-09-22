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

<div class="modal fade" id="azl-ssl-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="modal-title" style="font-size: 18px;" id="myModalLabel">{l s='Generating SSL certificate' mod='azleasyssl'}</div>
            </div>
            <div class="modal-body">
                <div id="azl-step-1">
                    <h2>{l s='Step 1: determine domains' mod='azleasyssl'}</h2>
                    <div id="azl-domains-error" class="azl-error-details hidden">
                        {l s='Some of domains has errors. Please check all domains are correct and module is active for all domains listed below.' mod='azleasyssl'}
                    </div>
                    <div class="dynamic-container">
                        {foreach from=$domains key=k item=domain}
                            <div class="row dynamic-row" data-status="pending" data-index="{$k|intval + 1}">
                                <div class="col-sm-1 col-sm-offset-3 text-right">
                                    <span class="azl-checked text-success hidden" title="{l s='Domain answer correct' mod='azleasyssl'}"><i class="icon icon-check"></i></span>
                                    <span class="azl-not-checked text-danger hidden" title="{l s='Domain answer error' mod='azleasyssl'}"><i class="icon icon-close"></i></span>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" value="{$domain|escape:'htmlall':'UTF-8'}" name="domains" class="form-control" />
                                </div>
                                <div class="col-sm-2">
                                    <button type="button" class="btn btn-danger azl-remove {if $k == 0}hidden{/if}"><i class="icon icon-minus"></i></button>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                    <p class="text-center">
                        <button type="button" class="btn btn-link azl-add"><i class="icon icon-plus"></i> {l s='Add one more domain' mod='azleasyssl'}</button>
                    </p>
                </div>
                <div id="azl-step-2" class="hidden">
                    <h2>{l s='Step 2: generating ssl certificate' mod='azleasyssl'}</h2>
                    <p id="azl-step-2-info"></p>
                    <div class="row text-muted" id="azl-setup">
                        <div class="col-sm-4 text-right">
                            <i class="icon-check-circle"></i>
                        </div>
                        <div class="col-sm-6">
                            {l s='Setting up Let\'s encrypt account' mod='azleasyssl'}
                        </div>
                    </div>
                    <div class="row text-muted" id="azl-gen-cert">
                        <div class="col-sm-4 text-right">
                            <i class="icon-check-circle"></i>
                        </div>
                        <div class="col-sm-6">
                            {l s='Generating SSL certificate' mod='azleasyssl'}
                        </div>
                    </div>
                    <div class="row text-muted" id="azl-download-cert">
                        <div class="col-sm-4 text-right">
                            <i class="icon-check-circle"></i>
                        </div>
                        <div class="col-sm-6">
                            {l s='Downloading SSL certificate' mod='azleasyssl'}
                        </div>
                    </div>
                </div>
                <div id="azl-console" class="hidden">
                    <h2>{l s='Error details' mod='azleasyssl'}</h2>
                    <div class="inner">
                        
                    </div>
                </div>
                <div id="azl-step-3" class="hidden">
                    <div class="form-group">
                        <label>{l s='Certificate (cert.pem)' mod='azleasyssl'}</label>
                        <textarea class="form-control" id="cert" rows="8" readonly=""></textarea>
                        <button class="btn btn-default btn-clipboard btn-block" data-clipboard-target="#cert" type="button"><i class="icon icon-clipboard"></i> {l s='Copy to clipboard' mod='azleasyssl'}</button>
                    </div>
                    <div class="form-group">
                        <label>{l s='Private KEY' mod='azleasyssl'}</label>
                        <textarea class="form-control" id="private" rows="8" readonly=""></textarea>
                        <button class="btn btn-default btn-clipboard btn-block" data-clipboard-target="#private" type="button"><i class="icon icon-clipboard"></i> {l s='Copy to clipboard' mod='azleasyssl'}</button>
                    </div>
                    <div class="form-group">
                        <label>{l s='Chain' mod='azleasyssl'}</label>
                        <textarea class="form-control" id="chain" rows="8" readonly=""></textarea>
                        <button class="btn btn-default btn-clipboard btn-block" data-clipboard-target="#chain" type="button"><i class="icon icon-clipboard"></i> {l s='Copy to clipboard' mod='azleasyssl'}</button>
                    </div>
                    <div class="form-group">
                        <label>{l s='Fullchain' mod='azleasyssl'}</label>
                        <textarea class="form-control" id="fullchain" rows="8" readonly=""></textarea>
                        <button class="btn btn-default btn-clipboard btn-block" data-clipboard-target="#fullchain" type="button"><i class="icon icon-clipboard"></i> {l s='Copy to clipboard' mod='azleasyssl'}</button>
                    </div>
                    
                    <div class="form-group">
                        <label>{l s='Public KEY' mod='azleasyssl'}</label>
                        <textarea class="form-control" id="public" rows="8" readonly=""></textarea>
                        <button class="btn btn-default btn-clipboard btn-block" data-clipboard-target="#public" type="button"><i class="icon icon-clipboard"></i> {l s='Copy to clipboard' mod='azleasyssl'}</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{l s='Close' mod='azleasyssl'}</button>
                <button type="button" class="btn btn-default" onclick="azlBack();" id="azl-back-btn">{l s='Back' mod='azleasyssl'}</button>
                <button type="button" class="btn btn-success" onclick="azlContinue();" id="azl-next-btn">{l s='Next' mod='azleasyssl'}</button>
                <button type="button" class="btn btn-danger hidden" id="azl-restart" onclick="azlPrepare(); return false;">{l s='Try again' mod='azleasyssl'}</button>
                <a href="#" id="azl-download" target="_blank" class="btn btn-primary hidden"><i class="icon icon-download"></i> {l s='Download ZIP' mod='azleasyssl'}</a>
            </div>
        </div>
    </div>
</div>