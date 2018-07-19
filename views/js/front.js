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
 * @copyright Dotpay sp. z o.o.
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
function checkRequired() {
    var requiredInputs = $('#checkout-payment-step input[required]').not('[name=payment-option]');
    var unchecked = 0;
        requiredInputs.each(function(){
            if($(this).prop('checked') == false) {
                ++unchecked;
            }
        });
        return unchecked > 0;
}

function checkBlikCode() {
    if($('.dotpay_blik_code').parents('.payment-form').parent().css('display') != 'block') {
        return false;
    }

    var value = $('input[name="blik_code"]').val();
    return !(value.length == 6 && !isNaN(parseInt(value)) && parseInt(value) == value);
}

function checkOrderConfirmButton() {
    $('#payment-confirmation button').attr('disabled', checkRequired() || checkBlikCode());
}

$(document).ready(function(){
    var requiredInputs = $('#checkout-payment-step input[required]').not('[name=payment-option]');
    requiredInputs.change(function(e){
        checkOrderConfirmButton();
        e.stopPropagation();
    });

    $('input[name=payment-option]').change(function(){
        $('.agreements input[required]').prop('checked', true);
    });

    $("input.dotpay_blik_code").keypress(function(event) {
      return /\d/.test(String.fromCharCode(event.keyCode));
    });
    $("input.dotpay_blik_code").attr("pattern", "[0-9]{6}");
    $("input.dotpay_blik_code").attr("maxlength", "6");


    $('input.dotpay_blik_code').change(function(){
        checkOrderConfirmButton();
    });
});
