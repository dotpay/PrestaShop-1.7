/**
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
 *  @author    Dotpay Team <tech@dotpay.pl>
 *  @copyright Dotpay sp. z o.o.
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 */

/**
 * A part of Dotpay JS SDK for a service of checking status of payment.
 * This function needs jQuery on a shop site.
 */
function DotpayStatusChecker(parent, config) {
    config.interval = config.interval || 5;
    config.timeout = config.timeout || 120;
    config.delay = config.delay || 5;

    var setMessage = function(message, className) {
        parent.find('p').remove();
        var element = document.createElement('p');
        element.className = 'alert '+className;
        element.innerHTML = message;
        parent.prepend(element);
    };

    var setErrorMessage = function(message) {
        setMessage(message, 'alert-danger');
    };
    var setWarningMessage = function(message) {
        setMessage(message, 'alert-warning');
    };
    var setInfoMessage = function(message) {
        setMessage(message, 'alert-info');
    };
    var setSuccessMessage = function(message) {
        setMessage(message, 'alert-success');
    };

    var showLoader = function() {
        var element = document.createElement('div');
        element.className = 'loading';
        parent.append(element);
    };

    var hideLoader = function() {
        parent.find('.loading').remove();
    };

    var counter = 0;
    var counterLimit = config.timeout/config.interval;

    var getBaseMessage = function(status) {
        return config.messages.basic+"<br />"+config.messages.status+':&nbsp;<b>'+status+'</b>';
    };

    var getMessageWithStatus = function(message, status) {
        return message+"<br />"+config.messages.status+':&nbsp;<b>'+status+'</b>';
    };

    var finish = function(intervalId) {
        hideLoader();
        clearInterval(intervalId);
    };

    var isJsonString = function(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    };

    setInfoMessage(config.messages.basic);
    showLoader();
    var checkInt = setInterval(function(){
        if(counter<counterLimit)
            ++counter;
        else {
            finish(checkInt);
            setWarningMessage(config.messages.timeout);
            return;
        }
        $.get(config.target, {"orderId": config.orderId}, function(e){
            if (isJsonString(e)) {
                var data = JSON.parse(e);
            } else if(typeof(e) == 'object') {
                var data = e;
            } else {
                return;
            }

            data.code = parseInt(data.code);
            if (data.message != undefined && data.message != "") {
                var additionalMessage = "<br />"+data.message;
            } else {
                var additionalMessage = "";
            }
            switch(data.code) {
                case -1://NOT EXIST
                    setErrorMessage(config.messages.notFound+additionalMessage);
                    clearInterval(checkInt);
                    hideLoader();
                    break;
                case 0://ERROR
                    finish(checkInt);
                    setErrorMessage(getMessageWithStatus(config.messages.error, data.status)+additionalMessage);
                    break;
                case 1://PENDING
                    setInfoMessage(getBaseMessage(data.status)+"<br />"+config.messages.pending+additionalMessage);
                    break;
                case 2://SUCCESS
                    finish(checkInt);
                    setSuccessMessage(getMessageWithStatus(config.messages.success, data.status+additionalMessage));
                    setTimeout(function(){location.href=config.redirect;}, config.delay*1000);
                    break;
                case 3://TOO MANY
                    finish(checkInt);
                    setWarningMessage(getMessageWithStatus(config.messages.tooMany, data.status)+additionalMessage);
                    break;
                case 4://OTHER STATUS
                    finish(checkInt);
                    setInfoMessage(getBaseMessage(data.status)+additionalMessage);
                    break;
                default://UNKNOWN STATUS
                    finish(checkInt);
                    var message = config.messages.unknown;
                    if(data.status != undefined) {
                        message += "<br />"+config.messages.status+':&nbsp; <b> '+data.status+'</b>';
                    }
                    setErrorMessage(message+additionalMessage);
            }
        });
    }, config.interval*1000);
}
