{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA < contact@prestashop.com >
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
    <div class="dotpay-config">
        <h3>{l s='Information' mod='dotpay'}</h3>
        <a href="https://www.przelewy24.pl/aktualnosci/kolejny-etap-integracji-przelewy24-i-dotpay-zakonczenie-kwestii-formalnych" target="_blank" title="www.przelewy24.pl"><img src="{$moduleDir}views/img/p24_logo.png" height="50px" border="0" /></a> | <a href="http://www.dotpay.pl" target="_blank" title="www.dotpay.pl"><img src="{$moduleDir}views/img/dotpay_logo85.png" height="50px" border="0" /></a>
        <br>
        <div class="alert alert-info">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <br />
        <p style="color: #1662b3">{l s='Leading online payment operators on the Polish market:' mod='dotpay'} <b>Przelewy24</b> {l s='and' mod='dotpay'} <b>Dotpay</b> {l s='now work together.' mod='dotpay'}</p>
        <p style="color: #1662b3">{l s='The target platform thanks to which we will provide the best possible infrastructure, products and services will be Przelewy24.' mod='dotpay'}</p>
        <p style="color: #1662b3;font-weight: bold;">{l s='Therefore, in this version of the payment module, you will notice changes in the name and logos.' mod='dotpay'}</p>
        <br />
        <br />
        <h2>{l s='Check manual before configuration:'  mod='dotpay'}<a href="https://github.com/dotpay/{$repositoryName}/releases/download/v{$moduleVersion}/Dotpay_PrestaShop_module-manual_{l s='en'  mod='dotpay'}.pdf" Title="{l s='Get manual for this module' mod='dotpay'}" target="_blank"> {l s='download manual' mod='dotpay'}</a></h2>
    </div>

        {if $confOK}
            <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Module is active. ' mod='dotpay'}</h2>
                <br />
                    {if $SellerIDName !== false || $P24SellerIDName !== false}
                    <p style="color: #1f4622; font-size: 1.3em;">{l s='The module has been configured for the account:' mod='dotpay'} <span style="color: #018c0b; font-weight: bold;"><br>{if $P24SellerIDName !== false}<span style="color:#555";>P24:</span> {$P24SellerIDName} (ID #{$P24SellerID}){else}{$SellerIDName} (ID #{$SellerID}){/if}</span>
                        <em style="color: #627a82; font-weight: normal;"> &#8594; 
                        {if $testMode}{l s='testing environment' mod='dotpay'} {else} {l s='production environment' mod='dotpay'}{/if}
                        </em>
                        </p><br /><br />
                    {/if}

                {if $P24Migrated === true} 
                    {if $testMode == false}
                        <p><b style="color: #7fc8e6;">{l s='Your account is ready to work in P24' mod='dotpay'}</b></p><br>
                    {/if} 
                    {if $P24SellerIDName !== false}
                        <p><b style="color: #065a30;">{l s='Your account is ready to work in P24' mod='dotpay'}</b></p><br>                  
                    {/if}    
                {else}
                    {if $testMode == false && $P24SellerIDName === false}
                        <p><b style="color: #5ecfeb;">{l s='If your account has already been transferred to the new Przelewy24 panel, be sure to select the option below:' mod='dotpay'} <span style="background: #ffe199;"> 🧑‍💻 {l s='My account has already been migrated from Dotpay to Przelewy24' mod='dotpay'}.</span></b> <em>({l s='not for sandbox accounts' mod='dotpay'})</em></p><br>                       
                    {/if}    
                {/if}

                    {if $P24Migrated !== true  && $P24SellerIDName === false} 
                        <p style="color: #472424;"><i>{l s='If you do not recive payment information, please check URLC configuration in your Dotpay user panel. Check also if your shop sees Dotpay IP address properly.' mod='dotpay'}</i></p>
                    {/if}    
            </div>

            {if $P24SellerIDName !== false && $P24Migrated !== true}
                <div class="alert alert-info">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Your account is ready to work in P24' mod='dotpay'}</h2>
                    <br />
                    <p><b style="color: #2e489c;font-size: 1.1rem;">{l s='Your Dotpay account has been migrated to the Przelewy24 panel.' mod='dotpay'} </b><br> <em>({l s='not for sandbox accounts' mod='dotpay'})</em></p><br> 
                    <br />
                </div>
            {/if}


        {else}

                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Module is not active. Please check your configuration.' mod='dotpay'}</h2>
                    <br />
                    <p style="color: #555;"><b>{l s='ID and PIN can be found in Dotpay panel in Settings in the top bar. ID number is a 6-digit string after # in a "Shop" column.' mod='dotpay'}</b></p>
                    <br />
                </div>
        {/if}

        {if $testMode}
            <div class="alert alert-warning">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Module is in TEST mode. All payment information is fake!' mod='dotpay'}</h2>
                <br />
                <p><b style="color: #721616;">{l s='If you want to run payments in production, change the switch settings below:' mod='dotpay'} <span style="background: #ffe199;"> 🧪 {l s='Test mode' mod='dotpay'}.</span></b></p><br>  
            </div>
        {/if}

        {if $oldVersion !== false}
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Prestashop version not supported!' mod='dotpay'}</h2>
                <br />
                <p style="color: #a82830;"><b>{l s='Please update your PrestaShop installation to the latest version if you want to use the newest features!' mod='dotpay'}</b><br><br>{l s='Your version of Prestashop:' mod='dotpay'} {$CurrentVersion} </p>
            </div>
        {/if}

        {if ($testSellerId === false && $P24Migrated === false) || ($P24testSellerId === false  && $P24Migrated === true)}
				
             {if $errorCodeID === false || $P24errorCodeID === false }
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                     {if $P24SellerID !== false && $P24testSellerId === false && $P24Migrated === true}
                        <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Your seller ID is incorrect.' mod='dotpay'} (P24 ID: {$P24SellerID})</h2>  
                    {else}
                        <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Your seller ID is incorrect.' mod='dotpay'} (ID: {$SellerID})</h2>
                    {/if}        
                    <br />
                    <p style="color: #555;"><b>{l s='Please check your ID and Test mode settings.' mod='dotpay'}</b><br>
                    {if $P24Migrated === true}<b style="color: #b30000;">{l s='You have indicated in the configuration that your Dotpay account is already migrated to P24. Change settings and check again.' mod='dotpay'}</b>                    
                    {/if}
                    </p>
                    <br />
                </div>
            {else}
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Problem with Your seller ID !' mod='dotpay'} #{$SellerID}</h2>

                     {if ( is_numeric($SellerID) && (float)(int)$SellerID === (float)$SellerID && $SellerID|count_characters:false == 6) || (is_numeric($SellerID) && (float)(int)$P24SellerID === (float)$P24SellerID && $P24SellerID|count_characters:false == 6) } 
                       
                        <br />
                        {if $P24SellerID !== false}
                            <h2 style="color: #a25d5d;"><b>error_code: [{$P24errorCodeID.error_code}]</b></h2><br />
                            <h3 style="color: #b31a1a;max-width: 80%;padding: 20px;width: max-content;"><b>{$P24errorCodeID.detail}</b></h3>
                        {else}
                            <h2 style="color: #a25d5d;"><b>error_code: [{$errorCodeID.error_code}]</b></h2><br />
                            <h3 style="color: #b31a1a;max-width: 80%;padding: 20px;width: max-content;"><b>{$errorCodeID.detail}</b></h3>

                        {/if}
                        {if $errorCodeID.error_code == "UNKNOWN_ACCOUNT" || $P24errorCodeID.error_code == "UNKNOWN_ACCOUNT"}        
                        <p style="color: #555;"><b>{l s='Please check your ID and Test mode settings.' mod='dotpay'}</b></p><br />
                        <p style="color: #b31a1a;">
                            {if $testMode}{l s='Module is in TEST mode. If you entered a production account ID, set below test mode for' mod='dotpay'} 
                                <b>{l s='NO' mod='dotpay'}</b>.
                            {/if}</p><br>
                        {/if}
                    <br />
                     
                     {else} 
                     {if $SellerID|count_characters:false != 6 || $P24SellerID|count_characters:false != 6}
                     <p style="color: #b31a1a;"><b>{l s='ID number is a 6-digit string. You have entered:' mod='dotpay'}  <em>{if $P24SellerID !== false}{$P24SellerID|count_characters:false}{else}{$SellerID|count_characters:false}{/if}</em></b>.</p><br />
                     {/if}
                        {l s='Your seller ID is incorrect.' mod='dotpay'}
                     {/if}
					
                </div>
            {/if}
        {/if}
		

		
		
        {if $testApiAccount }
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Your username or password for API is incorrect.' mod='dotpay'}</h2>
                <br />
                <p style="color: #555;"><b>{l s='Please check your API configuration.' mod='dotpay'}</b></p>
                <br />
            </div>
        {/if}
        {if $testSellerPin }
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Your PIN is incorrect.' mod='dotpay'}</h2>
                <br />
                <p style="color: #555;"><b>{l s='Please type correct PIN. Until it payments will not be accepted.' mod='dotpay'}</b></p>
                <br />
            </div>
        {/if}
        {if $testCorrectSellerForApi }
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Error with API data' mod='dotpay'}</h2>
                <br />
                <p style="color: #555;"><b>{l s='Your given API data is not correct for the given ID. Please check it.' mod='dotpay'}</b></p>
                <br />
            </div>
        {/if}
        {if $universalErrorMessage != false}
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Error with module settings' mod='dotpay'}</h2>
                <br />
                <p style="color: #555;"><b>{$universalErrorMessage|escape:'htmlall':'UTF-8'}</b></p>
                <br />
            </div>
        {/if}

    
    </div>
</div>

<div class="panel">
    <div class="dotpay-config-state">
    <h3>{l s='Updates' mod='dotpay'}</h3>
    <h4>
	{l s='Version of this module is: ' mod='dotpay'}<strong>{$moduleVersion|escape:'htmlall':'UTF-8'}</strong>{if $canNotCheckPlugin === false } <em>({l s='official last:' mod='dotpay'}  <a href="https://github.com/dotpay/{$repositoryName}/releases/latest" target="_blank">v{$moduleVersionGH|escape:'htmlall':'UTF-8'}</a>)</em>.{/if}<br>
	</h4>
  {if $canNotCheckPlugin}
        <div class="alert alert-warning">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Can not check the update' mod='dotpay'}</h2>
            <br />
            <p style="color: #555;">
                {l s='You can manually check the latest version' mod='dotpay'}
                <a href="https://github.com/dotpay/{$repositoryName}/releases/latest" target="_blank">{l s='on this page' mod='dotpay'}</a>.
            </p>
        </div>
    {elseif $obsoletePlugin}
        <div class="alert alert-warning">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Your plug is out of date!' mod='dotpay'}</h2>
            <br />
            <p style="color: #555;">
                {l s='You can download the latest version from' mod='dotpay'} Github: 
                <a href="https://github.com/dotpay/{$repositoryName}/releases/latest" target="_blank">v{$moduleVersionGH|escape:'htmlall':'UTF-8'}</a>.
            </p>
        </div>
    {else}
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Your module is up to date.' mod='dotpay'}</h2>
            <br />
            <p style="color: #555;">
                {l s='This gives you the guarantee of security and the ability to use the latest solutions offered by Dotpay.' mod='dotpay'}
            </p>
        </div>
    {/if}
        {if $badPhpVersion}
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h2 style="margin-left: 10px; margin-top: 0px;">{l s='Your PHP version is obsolete:' mod='dotpay'}&nbsp;{$phpVersion|escape:'htmlall':'UTF-8'}</h2>
                <br />
                <p style="color: #555;"><b>{l s='This plugin might work incorrectly. Please update your PHP version to at least' mod='dotpay'}&nbsp;{$minorPhpVersion|escape:'htmlall':'UTF-8'}</b></p>
                <br />
            </div>
        {/if}

    </div>
</div>
<p id="scroll-to-save">
    <i class="icon icon-arrow-down"></i>&nbsp;{l s='Go to Save' mod='dotpay'}
</p>
<div class="channel-control-template">
    <select class="channel-chooser">
        <option value="">{l s='Choose a channel...' mod='dotpay'}</option>
        {foreach from=$availableChannels item=channel}
            <option value="{$channel->getId()}" data-logo="{$channel->getLogo()}">{$channel->getName()}</option>
        {/foreach}
    </select>
    <button class="channel-chooser-remove" type="button"><i class="icon-remove"></i>&nbsp;{l s='Remove this channel' mod='dotpay'}</button>
    <img src="" class="chosen-channel-logo empty-channel-logo" />
</div>
{literal}
<script type="text/javascript">
    var badID = '{/literal}{$badIdMessage|escape:'htmlall':'UTF-8'}{literal}';
    var badPIN = '{/literal}{$badPinMessage|escape:'htmlall':'UTF-8'}{literal}';
    var valueLowerThanZero = '{/literal}{$valueLowerThanZero|escape:'htmlall':'UTF-8'}{literal}';

    function setFieldsForRenew() {
        if($('.renew-enable-option input[name="DP_RENEW"]:checked').val()=='1') {
            $('.renew-option').parents('.form-group').show();
        } else {
            $('.renew-option').parents('.form-group').hide();
        }
    }

    function setFieldsForFCC() {
        if($('.fcc-enable-option input[name="DP_FCC"]:checked').val()=='1') {
            $('.fcc-option').parents('.form-group').show();
        } else {
            $('.fcc-option').parents('.form-group').hide();
        }
    }

    function setFieldsForSurCh() {
        if($('.surcharge-enable-option input[name="DP_SURCHARGE"]:checked').val()=='1') {
            $('.surcharge-option').parents('.form-group').show();
        } else {
            $('.surcharge-option').parents('.form-group').hide();
        }
    }

    function setFieldsForExCh() {
        if($('.excharge-enable-option input[name="DP_EXCHARGE"]:checked').val()=='1') {
            $('.excharge-option').parents('.form-group').show();
        } else {
            $('.excharge-option').parents('.form-group').hide();
        }
    }

    function setFieldsForDiscount() {
        if($('.discount-enable-option input[name="DP_REDUCT_SHIP"]:checked').val()=='1') {
            $('.reduct-option').parents('.form-group').show();
        } else {
            $('.reduct-option').parents('.form-group').hide();
        }
    }

    function disableSubmit(mode) {
        $("#module_form_submit_btn").prop("disabled", mode);
    }

    function prepareValidation() {
        $('.form-group').find('.col-lg-9').append('<span class="errorMessage"></span>');
    }

    function setError(obj, message) {
        obj.parents('.form-group').find('.errorMessage').html(message);
    }

    function validateId(idElem, empty) {
        var idLength = idElem.val().length;
        if(empty===true && idLength === 0) {
            return 0;
        }
        if(idLength!=6 || isNaN(idElem.val() % 1)) {
            setError(idElem, badID);
            return 1;
        } else {
            setError(idElem, '');
            return 0;
        }
    }

    function validatePin(pinElem, empty) {
        var pinLength = pinElem.val().length;
        if(empty===true && pinLength === 0) {
            return true;
        }
        if(pinLength>32 || pinLength<16){
            setError(pinElem, badPIN);
            return 1;
        } else {
            setError(pinElem, '');
            return 0;
        }
    }

    function PINvisibleEye() {

         $("i#eyelook").click(function() {

             $('i#eyelook').toggleClass("icon-eye-slash icon-eye");
             var input = $('input#DP_PIN');
             if (input.attr("type") == "password") {
                 input.attr("type", "text");
                  $('i#eyelook').attr('style', 'color : #97224b; cursor : zoom-out;');
             } else {
                 input.attr("type", "password");
                 $('i#eyelook').attr('style', 'color : #2eacce; cursor : zoom-in;');
             }
         });
    }

    function validateLTZ(obj) {
        if(parseFloat(obj.val())<0) {
            setError(obj, valueLowerThanZero);
            return 1;
        } else {
            setError(obj, '');
            return 0;
        }
    }

    function validateGUI(check) {
        if(check == undefined)
            var check = 0;
        check += validateId($('#DP_ID'));
        check += validatePin($('#DP_PIN'));
        if($('.fcc-enable-option input[name="DP_FCC"]:checked').val()=='1') {
            check += validateId($('#DP_FCC_ID'), check);
            check += validatePin($('#DP_FCC_PIN'), check);
        }
        check += validateLTZ($('#DP_RENEW_DAYS'));
        check += validateLTZ($('#DP_EX_AMOUNT'));
        check += validateLTZ($('#DP_EX_PERC'));
        check += validateLTZ($('#DP_RS_AMOUNT'));
        check += validateLTZ($('#DP_RS_PERC'));
        if(check > 0)
            disableSubmit(true);
        else
            disableSubmit(false);
    }

    function setVisibilityForAdvancedMode() {
        if($('[name^="DP_ADV_MODE"]:checked').val() == '1')
            $('#advanced-settings').css('display','block');
        else
            $('#advanced-settings').css('display','none');
    }

    $(document).ready(function(){
        $('.password-field').attr('type', 'password');
        $('.lastInSection').parents('.form-group').after('<hr />');

        $('input#DP_PIN').attr("type", "password");
        $('#DP_TEST_MODE_on').parents('div.form-group').attr('style', '');
        console.log('test off1');

        //if configuration is incorrect
        {/literal}
        {if $testMode === false && $errorCodeID === false && $P24Migrated === true}
            {literal}
                $('#p24_migrated').parents('div.form-group').attr('style', 'border : 0.3rem solid #ff7b92; border-radius: 12px;');
                console.log('migrated1 on');
            {/literal}            
        {else}
            {literal}
                $('#p24_migrated').parents('div.form-group').attr('style', 'border : 0.3rem solid #c0def0; border-radius: 12px;');
                console.log('migrated1 off');
            {/literal}   
        {/if} 
        {if $testMode === true}
            {literal}
                $('#DP_TEST_MODE_on').parents('div.form-group').attr('style', 'border : 0.3rem solid #ff7b92; border-radius: 12px;');
                console.log('test on');
            {/literal}            
        {else}
            {literal}
                $('#DP_TEST_MODE_on').parents('div.form-group').attr('style', '');
                console.log('test off2');
            {/literal}  
        {/if}    
        {literal}




        $('<div id="advanced-settings"></div>').insertAfter($('.advanced-mode-switch').parents('.form-group'));
        $('#advanced-settings').nextAll().detach().appendTo('#advanced-settings');
        $('<hr style="height: 2px; background-color: #2eacce;" />').prependTo('#advanced-settings');
        $('[name^="DP_ADV_MODE"]').change(setVisibilityForAdvancedMode);
        PINvisibleEye();
        setVisibilityForAdvancedMode();
        setFieldsForDiscount();
        setFieldsForSurCh();
        setFieldsForExCh();
        setFieldsForFCC();
        setFieldsForRenew();



        //remove spaces from PIN input

            $("input#DP_PIN").bind('keyup paste keydown', function(e) {
              $(this).val(function(_, v){
                  return v.replace(/\s+/g, '');
              });
          });

          $("input#DP_FCC_PIN").bind('keyup paste keydown', function(e) {
              $(this).val(function(_, v){
                  return v.replace(/\s+/g, '');
              });
          });


          // remove spaces from ID input

           $("input#DP_ID").attr("pattern", "[0-9]{4,6}");
           $("input#DP_ID").attr("maxlength", "6");
           $("input#DP_ID").bind('keyup paste keydown', function(e) {
              if (/\D/g.test(this.value)) {
                  // Filter non-digits from input value.
                  this.value = this.value.replace(/\D/g, '');
                  }
              });

          $("input#DP_FCC_ID").attr("pattern", "[0-9]{4,6}");
          $("input#DP_FCC_ID").attr("maxlength", "6");
          $("input#DP_FCC_ID").bind('keyup paste keydown', function(e) {
             if (/\D/g.test(this.value)) {
                 // Filter non-digits from input value.
                 this.value = this.value.replace(/\D/g, '');
                 }
          });

          //currency
          $("input#DP_WIDGET_CURR").attr("pattern", "^([A-Z]{3}?\,?)+([A-Z]{3})$");

          $('input#DP_WIDGET_CURR').bind('keyup blur', function () {
              $(this).val($(this).val().replace(/[^A-Z,]/g, ''))
          });


          $("input#DP_FCC_CURR").attr("pattern", "^([A-Z]{3}?\,?)+([A-Z]{3})$");

          $('input#DP_FCC_CURR').bind('keyup blur', function () {
              $(this).val($(this).val().replace(/[^A-Z,]/g, ''))
          });


          $("input#DP_RENEW_DAYS").attr("pattern", "[0-9]{0,3}");
          $("input#DP_RENEW_DAYS").bind('keyup paste keydown', function(e) {
             if (/\D/g.test(this.value)) {
                 // Filter non-digits from input value.
                 this.value = this.value.replace(/\D/g, '');
             }
             });

           //username and password

           
          $("input#DP_USERNAME").bind('keyup paste keydown', function(e) {
              $(this).val(function(_, v){
                  return v.replace(/\s+/g, '');
              });
          });

          
          $("input#DP_PASSWORD").bind('keyup paste keydown', function(e) {
              $(this).val(function(_, v){
                  return v.replace(/\s+/g, '');
              });
          }); 


          $("input#DP_EX_AMOUNT").attr("pattern", "^0$|^0\.(0)([1-9])$|^0\.(([1-9])(\d)?)$|^([1-9])((\.\d{1,2})?)$|^((?!0)(\d){1,5})((\.\d{1,2})?)$|^(1(\d{5})(.\d{1,2})?)$|^(200000(.[0]{1,2})?)$");    
          $("input#DP_SUR_AMOUNT").attr("pattern", "^0$|^0\.(0)([1-9])$|^0\.(([1-9])(\d)?)$|^([1-9])((\.\d{1,2})?)$|^((?!0)(\d){1,5})((\.\d{1,2})?)$|^(1(\d{5})(.\d{1,2})?)$|^(200000(.[0]{1,2})?)$"); 
          $("input#DP_RS_AMOUNT").attr("pattern", "^0$|^0\.(0)([1-9])$|^0\.(([1-9])(\d)?)$|^([1-9])((\.\d{1,2})?)$|^((?!0)(\d){1,5})((\.\d{1,2})?)$|^(1(\d{5})(.\d{1,2})?)$|^(200000(.[0]{1,2})?)$"); 

          $("input#DP_SUR_PERC").attr("pattern", "^0$|^0\.(0)([1-9])$|^0\.(([1-9])(\d)?)$|^([1-9])((\.\d{1,2})?)$|^((?!0)(\d){1,2})((\.\d{1,2})?)$|^(100(.[0]{1,2})?)$"); 
          $("input#DP_EX_PERC").attr("pattern", "^0$|^0\.(0)([1-9])$|^0\.(([1-9])(\d)?)$|^([1-9])((\.\d{1,2})?)$|^((?!0)(\d){1,2})((\.\d{1,2})?)$|^(100(.[0]{1,2})?)$"); 
          $("input#DP_RS_PERC").attr("pattern", "^0$|^0\.(0)([1-9])$|^0\.(([1-9])(\d)?)$|^([1-9])((\.\d{1,2})?)$|^((?!0)(\d){1,2})((\.\d{1,2})?)$|^(100(.[0]{1,2})?)$"); 
          
          $('input#DP_EX_AMOUNT').bind('keyup blur paste', function () {
                    var valid = /^\d{0,6}(\.\d{0,2})?$/.test( this.value.replace('.', '') ),
                        val = this.value.replace('.', '');
                    if( !valid ) {
                        this.value = val.substring(0, val.length-1);
                    } else if( val.length > 2 ) {
                        this.value = val.substring(0,val.length-2)+"."+val.substring(val.length-2);
                    }
          });
          $('input#DP_SUR_AMOUNT').bind('keyup blur paste', function () {
                    var valid = /^\d{0,6}(\.\d{0,2})?$/.test( this.value.replace('.', '') ),
                        val = this.value.replace('.', '');
                    if( !valid ) {
                        this.value = val.substring(0, val.length-1);
                    } else if( val.length > 2 ) {
                        this.value = val.substring(0,val.length-2)+"."+val.substring(val.length-2);
                    }
          });
          $('input#DP_RS_AMOUNT').bind('keyup blur paste', function () {
                    var valid = /^\d{0,6}(\.\d{0,2})?$/.test( this.value.replace('.', '') ),
                        val = this.value.replace('.', '');
                    if( !valid ) {
                        this.value = val.substring(0, val.length-1);
                    } else if( val.length > 2 ) {
                        this.value = val.substring(0,val.length-2)+"."+val.substring(val.length-2);
                    }
          });


        $('input#DP_SUR_PERC').on("keypress keyup blur",function (event) {
                    var valid = /^\d{0,6}(\.\d{0,2})?$/.test( this.value.replace('.', '') ),
                        val = this.value.replace('.', '');
                    if( !valid ) {
                        this.value = val.substring(0, val.length-1);
                    } else if( val.length > 2 ) {
                        this.value = val.substring(0,val.length-2)+"."+val.substring(val.length-2);
                    }
                });

        $('input#DP_EX_PERC').on("keypress keyup blur",function (event) {
                    var valid = /^\d{0,6}(\.\d{0,2})?$/.test( this.value.replace('.', '') ),
                        val = this.value.replace('.', '');
                    if( !valid ) {
                        this.value = val.substring(0, val.length-1);
                    } else if( val.length > 2 ) {
                        this.value = val.substring(0,val.length-2)+"."+val.substring(val.length-2);
                    }
                });

        $('input#DP_RS_PERC').on("keypress keyup blur",function (event) {
                    var valid = /^\d{0,6}(\.\d{0,2})?$/.test( this.value.replace('.', '') ),
                        val = this.value.replace('.', '');
                    if( !valid ) {
                        this.value = val.substring(0, val.length-1);
                    } else if( val.length > 2 ) {
                        this.value = val.substring(0,val.length-2)+"."+val.substring(val.length-2);
                    }
                });


        prepareValidation();
        var check = validateId($('#DP_ID'), true) + validatePin($('#DP_PIN'), true);
        if(check)
            disableSubmit(true);

        $('.renew-enable-option input[name="DP_RENEW"]').change(function(){
            setFieldsForRenew();
            validateGUI();
        });

        $('.fcc-enable-option input[name="DP_FCC"]').change(function(){
            setFieldsForFCC();
            validateGUI();
        });

        $('.surcharge-enable-option input[name="DP_SURCHARGE"]').change(function(){
            setFieldsForSurCh();
        });

        $('.excharge-enable-option input[name="DP_EXCHARGE"]').change(function(){
            setFieldsForExCh();
        });

        $('.discount-enable-option input[name="DP_REDUCT_SHIP"]').change(function(){
            setFieldsForDiscount();
        });

        $('.validate-gui').change(function(){
            validateGUI();
        });

        $.dpChannelChooser();

    });
</script>
{/literal}
