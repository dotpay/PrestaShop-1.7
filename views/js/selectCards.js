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
function performActionOC() {
    setVisibilityOcLogo();
    if(!getOcCheckedSelectCards().length)
        getOcSelectCards().attr('disabled', 'disabled');
    else
        getOcSelectCards().removeAttr('disabled');
}

function changeOcLogo() {
    if(window.positionOcLogo === 0)
        window.positionOcLogo = 360;
    else
        window.positionOcLogo = 0;
    if(getOcCheckedSelectCards().length) {
        var logo = $('.dotpay-card-logo');
        if(typeof(logo.transition) === 'function') {
            logo.transition({ rotateY: window.positionOcLogo });
        }
        logo.attr('src', logo.attr('data-card-'+getOcSelectCards().val()));
    }
}

function setVisibilityOcLogo() {
    if(getOcCheckedSelectCards().length) {
        jQuery('.dotpay-card-logo').show();
    } else {
        jQuery('.dotpay-card-logo').hide();
    }
    changeOcLogo();
}

function getOcCheckedSelectCards() {
    return jQuery('input[name=dotpay_oc_mode][value=select]:checked');
}

function getOcSelectCards() {
    return jQuery('select[name=dotpay_card_list]');
}

$(document).ready(function(){
    if(getOcSelectCards().find('option').length === 0) {
        $('.dotpay_oc_mode').hide();
        $('.dotpay_card_list').hide();
        $('input[name=dotpay_oc_mode]:last').click().prop('checked', true);
    } else {
        $('input[name=dotpay_oc_mode]:first').click().prop('checked', true);;
    }
    setVisibilityOcLogo();
    $('input[name=dotpay_oc_mode]').change(performActionOC);
    getOcSelectCards().change(changeOcLogo);
});