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
<script type="text/javascript" src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/js/clipboard.min.js"></script>

<style>
    .manual-img{
        border: 1px solid #BBBBBB;
        padding: 5px;
        border-radius: 3px;
        max-width: 700px;
        height: auto;
        display: block;
    }
    #azleasyssl-config h2{
        margin-top: 0;
    }
    #azl-ssl-modal .modal-body{
        font-size: 16px;
    }
    #azl-console, .azl-error-details, #azl-step-2-info{
        font-size: 15px;
        padding: 10px;
        background: #FFE2E4;
        color: #D27C82;
        border-left: solid 3px #eab3b7;
        border-radius: 3px;
        margin-top: 10px;
        margin-bottom: 10px;
    }
    #azl-step-2-info{
        border-left: solid 3px #4ac7e0;
        color: #1e94ab;
        background: #DCF4F9;
    }
    #azl-console h2{
        margin-top: 0;
    }
    .btn-clipboard{
        margin-top: 5px;
    }
    .dynamic-row{
        margin-bottom: 5px;
    }
    .azl-checked, .azl-not-checked{
        margin-top: 5px;
        display: block;
    }
    #azl-ssl-modal h2{
        margin-top: 0;
    }
    .ar-blocked{
        position: relative;
    }
    .ar-loading{
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        z-index: 1000;
        background: rgba(255,255,255,0.5);
    }
    .ar-loading .ar-loading-inner{
        position: absolute;
        width: 180px;
        height: 49px;
        padding: 15px 15px 15px 40px;
        border-radius: 2px;
        box-shadow: 0 0 6px rgba(0, 0, 0, 0.4);
        border: 1px solid #AAAAAA;
        margin: auto;
        top: 0;
        bottom: 0;
        right: 0;
        left: 0;
        background: url('{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/ring-alt.gif') no-repeat 6px 8px scroll  #FFFFFF;
    }
</style>

<div class="row" id="azleasyssl-config">
    <div class="col-lg-2 col-md-3">
        <div class="list-group azleasysslTabs">
            <a class="list-group-item {if empty($active_tab) or $active_tab != 'AzlEasysslGeneralConfig'}active{/if}" data-tab="0" id="azleasyssl-tab-0" data-target="azleasyssl-general" href="#">
                <i class="icon-cog"></i> {l s='Generate SSL certificate' mod='azleasyssl'}
            </a>
            <a class="list-group-item {if !$issued}hidden{/if}" data-tab="1" id="azleasyssl-tab-1" data-target="azleasyssl-issued" href="#">
                <i class="icon-cog"></i> {l s='Issued SSL certificate' mod='azleasyssl'}
            </a>
            <a class="list-group-item {if !$issued}hidden{/if} {if $active_tab == 'AzlEasysslGeneralConfig'}active{/if}" data-tab="2" id="azleasyssl-tab-2" data-target="azleasyssl-shop" href="#">
                <i class="icon-cog"></i> {l s='Shop configuration' mod='azleasyssl'}
            </a>
            <a class="list-group-item" data-tab="3" id="azleasyssl-tab-3" data-target="azleasyssl-manual" href="#">
                <i class="icon-info"></i> {l s='Installation instructions' mod='azleasyssl'}
            </a>
            <a class="list-group-item" data-tab="10" id="azleasyssl-tab-10" data-target="azleasyssl-about" href="#">
                <i class="icon-info"></i> {l s='About' mod='azleasyssl'}
            </a>
        </div>
    </div>
    <div class="col-lg-10 col-md-9" id="azleasyssl-config-tabs">
        {include file="./_partials/general.tpl"}
        {include file="./_partials/issued.tpl"}
        {include file="./_partials/shop.tpl"}
        {include file="./_partials/manual.tpl"}
        {include file="./_partials/about.tpl"}
    </div>
</div>
{include file="./_partials/modal.tpl"}

<script>
    var $cl = null;
    window.addEventListener('load', function(){
        $cl = new ClipboardJS('.btn-clipboard');
        $cl.on('success', function(e) {
            showSuccessMessage('Coppied to clipboard');
            e.clearSelection();
        });
        $cl.on('error', function(e) {
            showErrorMessage('Error copying content. Please use Ctrl+C to copy');
        });
        $(document).on('click', '.azl-add', function(){
            azlCloneRow($('.dynamic-container .row:first-child'));
        });
        $(document).on('click', '.azl-remove', function(){
            azlRemoveRow($(this).parent().parent());
        });
        $('.azl-manual li a').click(function(){
            var target = $(this).data('target');
            $('#azleasyssl-tab-3').trigger('click');
            $(target).tab('show');
        });
        $(".azleasysslTabs a").click(function(e){
            e.preventDefault();
            if ($(this).data('target') == 'azleasyssl-issued') {
                azlViewIssuedSSL();
                return false;
            }
            $(".azleasysslTabs .active").removeClass('active');
            $(this).addClass('active');
            $('#azleasyssl-config .azleasyssl-config-panel').addClass('hidden');
            $('#' + $(this).data('target')).removeClass('hidden');
            $('#azleasysslActiveTab').remove();
            $('#azleasysslActiveTab').val($(this).data('tab'));
        });
        $('.azleasysslTabs .active').trigger('click');
    });
    var azlAjaxUrl = '{$link->getAdminLink('AdminAzlEasyssl')}'; {* URL generated by Link object, no escape necessary. Escaping will break functionality *}
    var azlViewIssued = false;
    var azlCheckInterval = null;
    var azlCurrentStep = 1;
    
    function azlBlockUI(selector, text){
        if (text == null){
            text = '{l s='Loading...' mod='azleasyssl'}';
        }
        $(selector).addClass('ar-blocked');
        $(selector).find('.ar-loading').remove();
        $(selector).append('<div class="ar-loading"><div class="ar-loading-inner">' + text + '</div></div>');
    };
    
    function azlUnblockUI(selector){
        $(selector).find('.ar-loading').remove();
        $(selector).removeClass('ar-blocked');
    };
    
    function azlRemoveRow(row){
        $(row).remove();
    }
    
    function azlCloneRow(row){
        var $row = $(row).clone();
        $row.attr('data-index', $('.dynamic-row').length + 1);
        $row.find('input').val('');
        $row.find('.azl-remove').removeClass('hidden');
        $row.find('.azl-checked').addClass('hidden');
        $row.find('.azl-not-checked').addClass('hidden');
        $('.dynamic-container').append($row);
    }
    
    function azlIsDomainsChecked(){
        if ($('.dynamic-row[data-status="pending"]').length == 0){
            clearInterval(azlCheckInterval);
        }else{
            console.log('pending');
            return false;
        }
        if ($('.dynamic-row[data-status="error"]').length){
            console.log('has errors');
            $('#azl-domains-error').removeClass('hidden');
            azlUnblockUI('#azl-ssl-modal .modal-content');
            return false;
        }
        var domains = [];
        $('.dynamic-row').each(function(){
            domains.push($(this).find('input').val());
        });
        $.ajax({
            type: 'POST',
            url: azlAjaxUrl,
            dataType: 'json',
            data: {
                controller : 'AdminAzlEasyssl',
                action : 'saveDomains',
                ajax : true,
                domain: domains
            },
            success: function(data){
                $('#azl-step-2-info').html(data.content).removeClass('hidden');
                azlStep2();
                azlUnblockUI('#azl-ssl-modal .modal-content');
            }
        }).fail(function(){
            azlUnblockUI('#azl-ssl-modal .modal-content');
        });
    }
    
    function azlCheckDomains(){
        azlCheckInterval = setInterval(function(){
            azlIsDomainsChecked();
        }, 500);
        $('.dynamic-row .azl-checked, .dynamic-row .azl-not-checked').addClass('hidden');
        $('.dynamic-row').attr('data-status', 'pending').removeClass('row-success').removeClass('row-error');
        $('#azl-domains-error').addClass('hidden');
        azlBlockUI('#azl-ssl-modal .modal-content', '{l s='Checking domains...' mod='azleasyssl'}');
        $('.dynamic-row').each(function(){
            var domain = $(this).find('input').val();
            var $row = $(this);
            $.ajax({
                type: 'POST',
                url: azlAjaxUrl,
                dataType: 'json',
                data: {
                    controller : 'AdminAzlEasyssl',
                    action : 'checkDomain',
                    baseUrl: '{$ajaxUrl nofilter}',{* URL generated by Link object, no escape necessary. Escaping will break functionality *}
                    ajax : true,
                    domain: domain
                },
                success: function(data){
                    if (data.success){
                        $row.addClass('row-success').attr('data-status', 'success');
                        $row.removeClass('row-error');
                        $row.find('.azl-checked').removeClass('hidden');
                        $row.find('.azl-not-checked').addClass('hidden');
                    }else{
                        $row.attr('data-status', 'error');
                        $row.addClass('row-error');
                        $row.removeClass('row-success');
                        $row.find('.azl-not-checked').removeClass('hidden');
                        $row.find('.azl-checked').addClass('hidden');
                    }
                }
            }).fail(function(){
                $row.attr('data-status', 'error');
                $row.addClass('row-error');
                $row.removeClass('row-success');
                $row.find('.azl-not-checked').removeClass('hidden');
                $row.find('.azl-checked').addClass('hidden');
            });
        });
    }
    
    function azlContinue(){
        if (azlCurrentStep == 1){
            return azlCheckDomains();
        }
        if (azlCurrentStep == 2){
            return azlPrepare();
        }
        if (azlCurrentStep == 3){
            return azlIssueCert();
        }
    }
    
    function azlBack(){
        if (azlCurrentStep == 1){
            return azlStep1();
        }
        if (azlCurrentStep == 2){
            return azlStep1();
        }
        if (azlCurrentStep == 3){
            return azlStep2();
        }
    }
    
    function azlStep1(){
        azlCurrentStep = 1;
        $('#azl-back-btn').addClass('hidden');
        $('#azl-next-btn').removeClass('hidden');
        $('#azl-download').addClass('hidden');
        $('#azl-restart').addClass('hidden');
        $('#azl-step-1').removeClass('hidden');
        $('#azl-step-2').addClass('hidden');
        $('#azl-step-3').addClass('hidden');
        $('#azl-ssl-modal').modal('show');
    }
    
    function azlStep2(){
        azlCurrentStep = 2;
        $('#azl-back-btn').removeClass('hidden');
        $('#azl-step-1').addClass('hidden');
        $('#azl-step-2').removeClass('hidden');
        $('#azl-step-3').addClass('hidden');
        $('#azl-setup').removeClass('text-success');
        $('#azl-setup').removeClass('text-danger');
        $('#azl-setup').addClass('text-muted');
        
        $('#azl-gen-cert').removeClass('text-success');
        $('#azl-gen-cert').removeClass('text-danger');
        $('#azl-gen-cert').addClass('text-muted');
        
        $('#azl-download-cert').removeClass('text-success');
        $('#azl-download-cert').removeClass('text-danger');
        $('#azl-download-cert').addClass('text-muted');
        $('#azl-ssl-modal').modal('show');
    }
    
    function azlStep3(){
        azlCurrentStep = 3;
        $('#azl-step-1').addClass('hidden');
        $('#azl-step-2').addClass('hidden');
        $('#azl-step-3').removeClass('hidden');
        $('#azl-ssl-modal').modal('show');
    }
    
    function azlPrepare(){
        azlBlockUI('#azl-ssl-modal .modal-content');
        $('#azl-console').addClass('hidden');
        $('#azl-issued-certificate').addClass('hidden');
        azlViewIssued = false;
        $('#azl-issued').addClass('hidden');
        
        $('#azl-step-3').addClass('hidden');
        $('#azl-step-2').removeClass('hidden');
        $('#azl-setup').removeClass('text-success');
        $('#azl-setup').removeClass('text-danger');
        $('#azl-setup').addClass('text-muted');
        
        $('#azl-gen-cert').removeClass('text-success');
        $('#azl-gen-cert').removeClass('text-danger');
        $('#azl-gen-cert').addClass('text-muted');
        
        $('#azl-download-cert').removeClass('text-success');
        $('#azl-download-cert').removeClass('text-danger');
        $('#azl-download-cert').addClass('text-muted');
        
        $('#azl-restart').addClass('hidden');
        $('#azl-download').addClass('hidden');
        $('#azl-setup').removeClass('text-muted');
        
        $.ajax({
            type: 'POST',
            url: azlAjaxUrl,
            dataType: 'json',
            data: {
                controller : 'AdminAzlEasyssl',
                action : 'newChallenge',
                ajax : true,
                email: $('#azl-email').val()
            },
            success: function(data)
            {
                if (data.success){
                    $('#azl-setup').addClass('text-success');
                    $('#azl-next-btn').removeClass('hidden');
                    azlIssueCert(data);
                }else{
                    azlUnblockUI('#azl-ssl-modal .modal-content');
                    showErrorMessage('Error');
                    azlDisplayErrors(data);
                    $('#azl-setup').addClass('text-danger');
                    $('#azl-restart').removeClass('hidden');
                    $('#azl-download').addClass('hidden');
                    $('#azl-next-btn').addClass('hidden');
                }
            }
        }).fail(function(){
            $('#azl-next-btn').addClass('hidden');
            azlUnblockUI('#azl-ssl-modal .modal-content');
            showErrorMessage('Error here');
            $('#azl-setup').addClass('text-danger');
        });
    }
    
    function azlViewIssuedSSL()
    {
        $(".azleasysslTabs .active").removeClass('active');
        $('#azleasyssl-tab-1').addClass('active');
        $('#azleasyssl-config .azleasyssl-config-panel').addClass('hidden');
        $('#' + $('#azleasyssl-tab-1').data('target')).removeClass('hidden');
        $('#azleasysslActiveTab').remove();
        $('#azleasysslActiveTab').val($('#azleasyssl-tab-1').data('tab'));
        $('#azl-issued-certificate').removeClass('hidden');
        azlBlockUI('#azleasyssl-issued');
        $.ajax({
            type: 'POST',
            url: azlAjaxUrl,
            dataType: 'json',
            data: {
                controller : 'AdminAzlEasyssl',
                action : 'issued',
                ajax : true
            },
            success: function(data)
            {
                if (data.success){
                    $('#azl-issued-cert').text(data.certificate);
                    $('#azl-issued-private').text(data.private_key);
                    $('#azl-issued-chain').text(data.chain);
                    $('#azl-issued-public').text(data.public_key);
                    $('#azl-issued-fullchain').text(data.fullchain_certificate);
                    $('#azl-issued-csr').text(data.last_csr);
                    $('#azl-issued-download').attr('href', data.zip);
                    
                    azlUnblockUI('#azleasyssl-issued');
                }else{
                    showErrorMessage('Error loading certificate');
                    azlUnblockUI('#azleasyssl-issued');
                }
            }
        }).fail(function(){
            showErrorMessage('Error here');
            azlUnblockUI('#azleasyssl-issued');
        });
    }
    
    function azlIssueCert(data){
        $('#azl-gen-cert').removeClass('text-muted');
        azlBlockUI('#azl-ssl-modal .modal-content');
        $.ajax({
            type: 'POST',
            url: azlAjaxUrl,
            dataType: 'json',
            data: {
                controller : 'AdminAzlEasyssl',
                action : 'issue',
                ajax : true,
                data: data
            },
            success: function(data)
            {
                if (data.success){
                    $('#azl-gen-cert').addClass('text-success');
                    $('#azl-download-cert').removeClass('text-muted');
                    $('#cert').text(data.data.certificate);
                    $('#private').text(data.data.private_key);
                    $('#chain').text(data.data.chain);
                    $('#public').text(data.data.public_key);
                    $('#fullchain').text(data.data.fullchain_certificate);
                    $('#csr').text(data.last_csr);
                    $('#azl-download').attr('href', data.data.zip);
                    setTimeout(function(){
                        $('#azl-download-cert').addClass('text-success');
                        $('#azl-step-3').removeClass('hidden');
                        $('#azl-step-2').addClass('hidden');
                        $('#azl-restart').addClass('hidden');
                        $('#azl-download').removeClass('hidden');
                        $('#azl-back-btn').addClass('hidden');
                        $('#azl-next-btn').addClass('hidden');
                        $('#azl-issued').removeClass('hidden');
                        $('#azleasyssl-tab-1').removeClass('hidden');
                        $('#azleasyssl-tab-2').removeClass('hidden');
                        azlUnblockUI('#azl-ssl-modal .modal-content');
                    }, 1000);
                }else{
                    azlUnblockUI('#azl-ssl-modal .modal-content');
                    showErrorMessage(data.error_message);
                    azlDisplayErrors(data);
                    $('#azl-gen-cert').addClass('text-danger');
                    $('#azl-next-btn').addClass('hidden');
                    $('#azl-restart').removeClass('hidden');
                    $('#azl-download').addClass('hidden');
                }
            }
        }).fail(function(){
            azlUnblockUI('#azl-ssl-modal .modal-content');
            showErrorMessage('Error here');
            $('#azl-gen-cert').addClass('text-danger');
            $('#azl-next-btn').addClass('hidden');
            $('#azl-restart').removeClass('hidden');
            $('#azl-download').addClass('hidden');
        });
    }
    
    function azlDisplayErrors(data){
        if (data.details){
            var details = [];
            $.each(data.details, function(index){
                details.push('<b>' + index + ':</b> ' + data.details[index]);
            });
            $('#azl-console .inner').html(details.join('<br/>'));
            $('#azl-console').removeClass('hidden');
        }
    }
</script>