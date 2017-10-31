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
$(document).ready(function(){
    $("#scroll-to-save").hide().css('margin-left', ($('#module_form').width()-200)+'px');
    $(function () {
        $(window).scroll(function () {
            if ($(this).scrollTop() < $('#module_form').height()-80) {
                $('#scroll-to-save').fadeIn('slow');
            } else {
                $('#scroll-to-save').fadeOut('slow');
            }
        });

        $('#scroll-to-save').click(function () {
            $('body,html').animate({
                scrollTop: $('#module_form_submit_btn').offset().top
            }, 800);
            return false;
        });
    });
});