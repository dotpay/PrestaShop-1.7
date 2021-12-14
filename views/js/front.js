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
 function checkRequired() {
    var requiredInputs = $.merge($(
		'#checkout-payment-step .dotpay-one-channel input[required][type=checkbox]'
    ).not(
        '[name=payment-option]'
    ), $(
        '#conditions-to-approve input[required][type=checkbox]'
    ));
    var unchecked = 0;
	requiredInputs.each(function(){
		if($(this).prop('checked') == false) {
			++unchecked;
		}
	});
	return unchecked > 0;
}



	jQueryCodeBLIKregex = function(){
		var $regexblik=/^([0-9]{6})$/;
		if ($('input.dotpay_blik_code').val().match($regexblik)) {
			return true;
		}else{
			return false;
		}
	};


function checkBlikCode() {
    if($('.dotpay_blik_code').parents('.payment-form').parent().css('display') != 'block') {
        return false;
    }

    var value = $('input[name="blik_code"]').val();
    return !(value.length == 6 && !isNaN(parseInt(value)) && parseInt(value) == value);

}


function checkOrderConfirmButton() {
    $('#payment-confirmation button').attr('disabled', checkRequired() || checkBlikCode() );
}

//get variable from url


function getURLParameter(name) {
    return decodeURI(
        (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
    );
}


var renewpay = getURLParameter('renew');

var dp_empty_blik_code = '<p class="alert alert-warning" id="dotpay_empty_blik_code"><strong>Brak kodu BLIK!</strong><br> Dla wybranej metody płatności należy uzupełnić 6-cyfrowy kod BLIK z aplikacji bankowej.</p><br>';



function checkSelectedBylaw2() {
    var unchecked = $("#agreement_bylaw > input[type=checkbox]:checked").length;
        return unchecked > 2;
}


$(document).ready(function(){

    setTimeout(function(){  

        var requiredInputs = $('#checkout-payment-step input[required]').not('[name=payment-option]');
        requiredInputs.change(function(e){

            if(!checkSelectedBylaw2()){  
                console.log('%cNOT selected accept PayPro S.A. Regulations of Payments','background:red;color:#fff')
				$('#payment-confirmation button').attr("disabled", "disabled");
            }
            checkOrderConfirmButton();
            //e.stopPropagation();
        });


        $('input[name=payment-option]').change(function(){
            $('.agreements input[required]').prop('checked', true);
        });

        $("input.dotpay_blik_code").bind('keyup paste keydown', function(e) {
            if (/\D/g.test(this.value))
                {
                    this.value = this.value.replace(/\D/g, '');
                }
            });

        $("input.dotpay_blik_code").attr("pattern", "[0-9]{6}");
        $("input.dotpay_blik_code").attr("maxlength", "6");


		$('input.dotpay_blik_code').on('keypress keydown keyup',function(){
            	
			var value_blik = $('input[name="blik_code"]').val();
			if(value_blik.length == 6 && !isNaN(parseInt(value_blik))){
					console.log('BLIK code is ok :) ');
					$('#dotpay_empty_blik_code').remove();
					$('input.dotpay_blik_code').css({"background-color":"#d3f2d2","border-color":"#3a9112"});
			}else{
					console.log('BLIK code is not ok! ');
					$('input.dotpay_blik_code').css({"background-color":"#ffc9c9","border-color":"#f22"});
					if( ($('#dotpay_empty_blik_code').length < 1) && ($('input.dotpay_blik_code').parents('.payment-form').parent().css('display') == 'block') ) {
						$('#payment-confirmation button').before(dp_empty_blik_code);
					}
			}	
			
			checkOrderConfirmButton();
			
        });
		
	//check if main conditions changes	

		$('#payment-confirmation button').attr("disabled", "disabled");

		$('input[id^="conditions_to_approve"]').change(function(){
			var main_conditions = $('input[id^="conditions_to_approve"]:checked').length;
			
			if(main_conditions > 0 ){
				$(document).ready(function(){
					setTimeout(function(){   
										if(!checkSelectedBylaw2()){  
											console.log('%cSelect first PayPro S.A. Regulations of Payments !','background:red;color:#fff');
								            $('#payment-confirmation button').attr("disabled", "disabled");
							            }
										
										//if blik method enabled
										if($('input.dotpay_blik_code').parents('.payment-form').parent().css('display') == 'block') 
											{
												if(!jQueryCodeBLIKregex()){
													console.log('%cNo blik code or incomplete','background: #cfcfcf; color: brown;');
													$('#payment-confirmation button').attr("disabled", "disabled");
														if(jQuery('#dotpay_empty_blik_code').length < 1) {
															$('#payment-confirmation button').before(dp_empty_blik_code);
														}
														$('input.dotpay_blik_code').on('keypress keydown keyup',function(){
															
															if($('input.dotpay_blik_code').val().length < 1) {
																console.log('%cNo blik code enter','background: #cfcfcf; color: brown;');
															}
															
												
														});

												}

											}

					 }, 200);
										
										
										
										
				});
			}  

		});


		},500);
		
   setTimeout(function(){  
    //only for renew payment
    if(renewpay == 1 ){
        $("h5.aeuc_scart").attr('href', '').css({'cursor': 'pointer', 'pointer-events' : 'none'});
        $( "div.cart-summary-products" ).find( "ul.media-list > li.media div.media-left a" ).attr('href', '').css({'cursor': 'pointer', 'pointer-events' : 'none'});
        $("div.block-promo ").remove();
        $( "div.order-confirmation-table" ).find( "div.details a" ).attr('href', '').css({'cursor': 'pointer', 'pointer-events' : 'none'});
    }

    }, 500);


});  

