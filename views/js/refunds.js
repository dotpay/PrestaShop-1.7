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

/**
 * Update details of payment which is assocoated with a possible return
 * @param {string} payment Payment number
 * @returns {undefined}
 */
function updateDotpayReturnDetails(payment) {
    $.post(
        window.refundConfig.returnUrl+"&ajax=1",
        {"payment": payment, "order": window.refundConfig.orderId},
        function(response) {
            var payment = JSON.parse(response);
            var form = $('#dotpayDetailsPaymentPanel form');
            form.find('input[name=amount]').val(payment.sum_of_payments).data('maxvalue', payment.sum_of_payments);
            form.find('#return-currency').html(payment.currency);
            form.find('input[name=description]').val(payment.description);
            if(payment.sum_of_payments === 0.0)
                form.find('input[type=submit]').attr('disabled', true);
            else
                form.find('input[type=submit]').attr('disabled', false);
            $('#dotpay-return-payment').attr('disabled', $('#dotpay-return-payment option').length < 2);
        }
    );
}
$(document).ready(function(){
    if (window.refundConfig != undefined) {
        $('#dotpay-return-payment').change(function(){
            updateDotpayReturnDetails($(this).val());
        });
        $('.dotpay-return-amount').change(function(){
            var obj = $(this);
            if(obj.val()==='')
                return true;
            var value = parseFloat(obj.val().replace(',','.'));
            if(value > parseFloat(obj.data('maxvalue')))
                obj.val(obj.data('maxvalue'));
            else if(value <= 0.0)
                obj.val('0.01');
        });
        updateDotpayReturnDetails($('#dotpay-return-payment').val());
        $('#dotpay-refund-send').click(function(){
            $('#dotpay-return-payment').attr('disabled', false);
        });
    }
});