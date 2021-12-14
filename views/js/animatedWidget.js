/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to tech@dotpay.pl so we can send you a copy immediately.
 *
 * @author    Dotpay Team <tech@dotpay.pl>
 * @copyright PayPro S.A.
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
 (function($) {
    var defaults = {
        channelsContainerClass: "dotpay-channels-selection",
        channelChangeClass: "channel-selected-change",
        selectedChannelContainerClass: "selectedChannelContainer",
        messageContainerClass: "selected-channel-message",
        collapsibleWidgetTitleClass: "collapsibleWidgetTitle",
        widgetContainerClass: "dotpay-form-widget-container"
    };

    var settings = {};



    $.dpCollapsibleWidget = function(options) {
        if(window.dotpayRegisterWidgetEvent == undefined) {
            window.dotpayRegisterWidgetEvent = true;
            settings = $.extend( {}, defaults, options );
            $('.'+settings.messageContainerClass+', .'+settings.selectedChannelContainerClass).hide();
            connectEventToWidget();
            $('.'+settings.selectedChannelContainerClass+', .'+settings.messageContainerClass).click(function(e){
                e.stopPropagation();
                e.preventDefault();
                return false;
            });
            $('.'+settings.channelChangeClass).click(onChangeSelectedChannel);
            $('div.selectedChannelContainer.channels-wrapper').click(onChangeSelectedChannel);  

        }
        return this;
    }
    function connectEventToWidget() {
        setTimeout(function(){ 
                $('.channel-container').on('click', function(e) {
                        $('.dp_channel-input', this).prop('checked', true);
                        var id = $(this).find('.dp_channel-input').val();
                        if(id == undefined)
                            return false;
                        var container = copyChannelContainer(id);
                        $('.'+settings.selectedChannelContainerClass+' div').remove();
                        container.insertBefore($('.'+settings.selectedChannelContainerClass+' hr'));
                        $('div.dotpay-channels-selection:not(:first)').remove();
                        toggleWidgetView();
                        e.preventDefault();

                        var CheckedChannel = $('div.channel-container.selected').find('img').attr('title');
                        console.log('%cSelected Payment Method: ' + CheckedChannel, 'background: #cfcfcf; color: #4c605c');

                        if(CheckedChannel.length > 1){
                            $('p#dp_NoSelected').hide();
                            if($('.dp_checkedchannel').length > 0){
                                $('span.dp_checkedchannel').html(CheckedChannel +'<br>');
                            }else {
                                $('<span class="dp_checkedchannel">'+ CheckedChannel +'<br></span>').insertBefore('.channel-selected-change');
                            }
						
						if(checkSelectedChannel() > 0 &&  checkSelectedBylaw() > 0 && checktoggleapprove() > 0){
							$('div#payment-confirmation > div > button').removeClass('disabled');
							$('#payment-confirmation button').attr("disabled", false);
						}						

                        }  else {
                            $('p#dp_NoSelected').show();
                        }
                    
                    
                });
      }, 1200);   
    }

    function copyChannelContainer(id) {
        var container = $('.'+settings.widgetContainerClass+' #'+id).parents('.channel-container').clone();
        container.find('.tooltip').remove();
        container.find('.input-container').remove();
        container.removeClass('not-online');
        return container;
    }

    function onChangeSelectedChannel(e) {
        toggleWidgetView();
        e.stopPropagation();
        e.preventDefault();
        return false;
    }

    function checkSelectedChannel() {
        var unchecked = $("input[type='radio'][class='dp_channel-input']:checked").length;
            return unchecked > 0;
    }
    function checkSelectedBylaw() {
        var unchecked = $("#agreement_bylaw > input[type=checkbox]:checked").length;
            return unchecked > 0;
    }
    
    function checktoggleapprove() {
        var unchecked = $('input[id^="conditions_to_approve"]:checked').length; 
        return unchecked > 0;

    }




    function toggleWidgetView() {
        $('.'+settings.collapsibleWidgetTitleClass+', .'+settings.selectedChannelContainerClass+' hr, .'+settings.widgetContainerClass).animate(
            {
                height: "toggle",
                opacity: "toggle"
            }, {
                duration: "slow"
            }
        );
        $('.'+settings.messageContainerClass+',.'+settings.selectedChannelContainerClass).show();
    }



})(jQuery);


$(document).ready(function(){


    setTimeout(function(){
        $.dpCollapsibleWidget();

    }, 1200);

    $('div.dotpay-channels-selection:not(:first)').remove();


});

if (typeof jQuery != 'undefined') {

       setTimeout(function(){
            jQuery(document).ready(function () {

                jQuery('div.channel-container').on("click", function () {
                                        
                    var CheckedChannel = jQuery('div.channel-container.selected').find('img').attr('title');
                    console.log('Selected: ' + CheckedChannel);
                });

                jQuery('input[name="payment-option"]').on("change", function () {
                     if (this.checked) {

                        
                        var CheckedPaymentMethod =  jQuery('input[name="payment-option"]:checked').attr('id');          
                        var DotpayMainMethodChecked = jQuery("#pay-with-"+CheckedPaymentMethod+"-form").find(".collapsibleWidgetTitle").length;
                        var IfDotpayMainMethodChecked = jQuery("#pay-with-"+CheckedPaymentMethod+"-form").is(":visible");
                        var allVisibleChannels = jQuery(".dotpay-channels-selection").find(".dp_channel-input").length;
                        console.log('Available channels in widget: '+allVisibleChannels);  

                        var CheckedPaymentMethodTitle =  jQuery('label[for="'+CheckedPaymentMethod+'"] > span').text();

                        setTimeout(function(){  
                            var IfDotpayMainMethodChecked = jQuery("#pay-with-"+CheckedPaymentMethod+"-form").is(":visible");
   
                            console.log('%cSelected Payment Method: ' + CheckedPaymentMethodTitle, 'background: #cfcfcf; color: blue;');  

                            //for blik
                            if(CheckedPaymentMethodTitle == 'BLIK' ){

                                var value_blik_code = jQuery('input[name="blik_code"]').val();
                                
                                if (!(value_blik_code.length == 6 && !isNaN(parseInt(value_blik_code)) && parseInt(value_blik_code) == value_blik_code)){
                                        jQuery('div#payment-confirmation > div > button').prop('disabled', true);
                                        // fix for PrestaShop v1.7.8:  
                                        jQuery('div#payment-confirmation > div > button').addClass('disabled');
                                        

                                        console.log('Empty blik code');
                                
                                        jQuery('input.dotpay_blik_code').keyup(function(){

                                            if(value_blik_code.length == 6 && !isNaN(parseInt(value_blik_code)) && parseInt(value_blik_code) == value_blik_code){
                                                console.log('Code blik entered.');
                                                jQuery('div#payment-confirmation > div > button').prop('disabled', false);
                                                
                                                // fix for PrestaShop v1.7.8 :   
                                                if (jQuery('div#payment-confirmation > div > button').hasClass('disabled')){
                                                    jQuery('div#payment-confirmation > div > button').removeClass('disabled');
                                                }
												
												
                                            }
                                        });
                           
                                        }
                                        jQuery('p#dp_NoSelected').hide();
                            }else{
								$('#dotpay_empty_blik_code').remove();
							}
                                        



                            if(DotpayMainMethodChecked >0 && IfDotpayMainMethodChecked == true) {

                                    console.log('%cDotpay main method is checked.','background: #cfcfcf; color: green;'); 
                                

                                    if(allVisibleChannels > 0)
                                    {
                                        jQuery('p#dp_NoSelected').show();                                          
                                        jQuery('p#dp_NoWidget').hide();
                        
                                        if(jQuery('div.selectedChannelContainer.channels-wrapper > .channel-container').length > 0){
                                            console.log('Selected channel: '+jQuery('div.selectedChannelContainer.channels-wrapper > .channel-container').length);
                                            jQuery('p#dp_NoSelected').hide();
                                        }else{
                                            jQuery('p#dp_NoSelected').show();
                                            jQuery( "#payment-confirmation > div > button" ).prop('disabled', true);

                                            // fix for PrestaShop v1.7.8: 
                                            jQuery('div#payment-confirmation > div > button').addClass('disabled'); 
                                            
                                            console.log('No payment channel selected');
                                            
                                        }
                                    
                                        jQuery.each(dotpayWidgetConfig.disabledChannels, function(index, dpDisabledCh){
                                            console.log('%cRemove from widget method: %c ' +dpDisabledCh+ ' %c, method available separately.','background: #cfcfcf; color: #ee4a37;font-weight: normal;','font-weight: bold;background: #00ffff; color: #a51c7b','background: #cfcfcf; color: #ee4a37;font-weight: normal;');
                                            jQuery( "input#" + dpDisabledCh + ".dp_channel-input" ).closest( "div[id^='dp_channel_'].channel-container" ).hide();
                                        });

                                    } else {
                                        jQuery('p#dp_NoWidget').show();
                                                                       
                                    }
                               
                                    jQuery("input[type='radio'][class='dp_channel-input']").on("change", function () {
                                        if(checkSelectedChannel() > 0 &&  checkSelectedBylaw() > 0 && checktoggleapprove > 0){
                                            console.log('All checked are selected');
                                        }else {
                                            console.log('Not all checked...');
                                        }
                                });  

                               
                                }else{
                                    jQuery('p#dp_NoSelected').hide();
                                    
                                }
                         }, 500);    

              
               
               
                        };

        });


        });


    }, 300);

}
