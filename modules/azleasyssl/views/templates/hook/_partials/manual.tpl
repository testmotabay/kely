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
<div class="azleasyssl-config-panel hidden" id="azleasyssl-manual" style="font-size: 15px;">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-info"></i> {l s='Installation instructions' mod='azleasyssl'}
        </div>
        <div class="form-wrapper">
            <h2>{l s='How to get SSL certificate' mod='azleasyssl'}</h2>
            <ol>
                <li>
                    {l s='Navigate to "Generate SSL certificate" section on the left menu of the module' mod='azleasyssl'}
                </li>
                <li>
                    {l s='Enter your email address to "Email" field (by default will be filled current employee email) for receiving notifications from LetsEntrypt.' mod='azleasyssl'}
                    {l s='LetsEntrypt will send the first notice at 20 days before your certificate expires, and more notices at 10 days and 1 day before it expires.' mod='azleasyssl'}
                </li>
                <li>
                    {l s='Click "Issue SSL certificate"' mod='azleasyssl'}
                    <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/00000326.png" class="manual-img" style="margin-bottom: 10px;" alt="" />
                </li>
                <li>
                    {l s='Enter all needed domains.' mod='azleasyssl'}
                    {l s='If you using multistore, you need to enable module for all stores and add each store domain to the list.' mod='azleasyssl'}
                    {l s='Click "Next" button.' mod='azleasyssl'}
                    <ol>
                        <li>
                            {l s='If all domains are validated you will see next step.' mod='azleasyssl'}
                        </li>
                        <li>
                            {l s='If some domains not validated - remove them from list.' mod='azleasyssl'}
                        </li>
                    </ol>
                    <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/subdomain.jpg" class="manual-img" style="margin-bottom: 10px;" alt="{l s='Subdomains' mod='azleasyssl'}" />
                </li>
                <li>
                    {l s='On second step click "Next" button to issue SSL certificate for listed domains.' mod='azleasyssl'}
                </li>
                <li>
                    {l s='Finaly you will see issued certificate. Later you can see issued certificate in newly appeared Issued SSL certificate section' mod='azleasyssl'}
                    <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/certificates.png" class="manual-img" style="margin-bottom: 10px;" alt="" />
                </li>
                <li>
                    {l s='Next you need to install certificate to your server or hosting panel. Please read next manual section.' mod='azleasyssl'}
                </li>
            </ol>
            
            <hr/>
            <h2>{l s='How to install SSL certificate' mod='azleasyssl'}</h2>
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#cpanel" id="cpanel-manual" data-toggle="tab">{l s='CPanel' mod='azleasyssl'}</a>
                </li>
                <li>
                    <a href="#plesk" id="plesk-manual" data-toggle="tab">{l s='Plesk' mod='azleasyssl'}</a>
                </li>
                <li>
                    <a href="#nginx" id="nginx-manual" data-toggle="tab">{l s='Nginx' mod='azleasyssl'}</a>
                </li>
                <li>
                    <a href="#apache" id="apache-manual" data-toggle="tab">{l s='Apache2' mod='azleasyssl'}</a>
                </li>
                <li>
                    <a href="#other" id="apache-manual" data-toggle="tab">{l s='Other Hosting Panels' mod='azleasyssl'}</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="cpanel">
                    <ol>
                        <li>
                            {l s='Login to your cPanel' mod='azleasyssl'}
                        </li>
                        <li>
                            {l s='Navigate to "Security" section in your hosting cPanel, and click on "SSL/TLS" link' mod='azleasyssl'}
                            <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/cpanel-1.jpg" class="manual-img" style="margin-bottom: 10px;" alt="{l s='CPanel 1' mod='azleasyssl'}" />
                        </li>
                        <li>
                            {l s='On landed page click on "Manage SSL sites"' mod='azleasyssl'}
                            <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/cpanel-2.jpg" class="manual-img" style="margin-bottom: 10px;" alt="{l s='CPanel 1' mod='azleasyssl'}" />
                        </li>
                        <li>
                            {l s='Select domain from "Domain" dropdown' mod='azleasyssl'}
                        </li>
                        <li>
                            {l s='Copy value from "Certificate (cert.pem)" field of module to "Certificate: (CRT)" field in cPanel' mod='azleasyssl'}
                        </li>
                        <li>
                            {l s='Copy value from "Private KEY" field of module to "Private Key (KEY)" field in cPanel' mod='azleasyssl'}
                        </li>
                        <li>
                            {l s='Click "Install Certificate" in cPanel' mod='azleasyssl'}
                        </li>
                    </ol>
                </div>
                <div class="tab-pane" id="plesk">
                    <ol>
                        <li>{l s='Log in to your Plesk Panel' mod='azleasyssl'}</li>
                        <li>{l s='Select the "Websites & Domains" tab and click on the domain you need to install an SSL certificate for.' mod='azleasyssl'}</li>
                        <li>
                            {l s='Choose the "Secure Your Sites" option' mod='azleasyssl'}<br/>
                            <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/plesk1.jpg" class="manual-img" style="margin-bottom: 10px;" alt="{l s='Plesk 1' mod='azleasyssl'}" />
                        </li>
                        <li>
                            {l s='Click Add SSL Certificate on the left top' mod='azleasyssl'}<br/>
                            <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/plesk2.jpg" class="manual-img" style="margin-bottom: 10px;" alt="{l s='Plesk 2' mod='azleasyssl'}" />
                        </li>
                        <li>
                            {l s='Browse downloaded certificates to upload or just paste the domain certificate in plain text into the corresponding fields and click on "Send Text".' mod='azleasyssl'}<br/>
                            <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/plesk3.jpg" class="manual-img" style="margin-bottom: 10px;" alt="{l s='Plesk 3' mod='azleasyssl'}" />
                        </li>
                    </ol>
                    
                </div>
                <div class="tab-pane" id="nginx">
                    <br/>
                    <p class="alert alert-warning">
                        {l s='To install SSL certificate on nginx server you need access to edit nginx virtual host and restart nginx server.' mod='azleasyssl'}
                    </p>
                    <ol>
                        <li>
                            {l s='Upload zip file with ssl certificate to your server.' mod='azleasyssl'}
                        </li>
                        <li>
                            {l s='Unzip archive.' mod='azleasyssl'}
                        </li>
                        <li>
                            {l s='Add following lines to your nginx virtual host config:' mod='azleasyssl'}
<pre>server{
    ...
    listen 443 ssl;
    ssl on;
    ssl_certificate /path/to/cert/fullchain.crt;
    ssl_certificate_key /path/to/cert/private.pem;
    ssl_trusted_certificate /path/to/cert/chain.crt;
    ...
}</pre>
                        </li>
                        <li>{l s='Restart nginx server.' mod='azleasyssl'}</li>
                    </ol>
                </div>
                <div class="tab-pane" id="apache">
                    <br/>
                    <p class="alert alert-warning">
                        {l s='To install SSL certificate on apache server you need access to edit apache virtual host and restart apache server.' mod='azleasyssl'}
                    </p>
                    <ol>
                        <li>
                            {l s='Upload zip file with ssl certificate to your server.' mod='azleasyssl'}
                        </li>
                        <li>
                            {l s='Unzip archive.' mod='azleasyssl'}
                        </li>
                        <li>
                            {l s='Add following lines to your apache virtual host config:' mod='azleasyssl'}
<pre>&lt;VirtualHost *:443&gt;
    ...
    SSLEngine on
    SSLCertificateFile /path/to/cert/certificate.pem
    SSLCertificateKeyFile /path/to/cert/private.pem
    SSLCertificateChainFile /path/to/cert/chain.crt
    ...
&lt;/VirtualHost&gt;</pre>
                        </li>
                        <li>{l s='Enable apache module mod_ssl. You can use command "a2enmod ssl".' mod='azleasyssl'}</li>
                        <li>{l s='Restart apache server.' mod='azleasyssl'}</li>
                    </ol>
                </div>
                <div class="tab-pane" id="other">
                    <p>
                        {l s='If your hosting panel is not Plesk or cPanel. Look for a section for SSL where you can insert public and private key for your domain name.' mod='azleasyssl'}
                        {l s='In case you won\'t find, please send generated certificates to your hosting provider with request to add them for your Domain name.' mod='azleasyssl'}
                    </p>
                </div>
            </div>
            <p>
                {l s='Last step is configure your shop for using HTTPS connection.' mod='azleasyssl'}
            </p>
            <hr/>
            <h2>{l s='Configure your shop' mod='azleasyssl'}</h2>
            <div class="alert alert-danger">
                {l s='Please do following settings only when you have installed SSL certificate on your server or hosting.' mod='azleasyssl'}
            </div>
            <p>
                {l s='Enable options "Enable SSL" and "Enable SSL on all pages" to use secure connection.' mod='azleasyssl'} 
                {l s='You will be able use HTTP or HTTPS connection.' mod='azleasyssl'}
            </p>
            <p>
                {l s='Enable options "Force SSL" and "Redirect from HTTP to HTTPS" to force redirect from HTTP to HTTPS connection.' mod='azleasyssl'}
            </p>
        </div>
    </div>
</div>