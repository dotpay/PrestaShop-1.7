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
(function($) {
    var defaults = {
        channelsContainerClass: "dotpay-channels-selection",
        channelChangeClass: "channel-selected-change",
        selectedChannelContainerClass: "selectedChannelContainer",
        messageContainerClass: "selected-channel-message",
        collapsibleWidgetTitleClass: "collapsibleWidgetTitle",
        widgetContainerClass: "dotpay-widget-container"
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
        }
        return this;
    }
    function connectEventToWidget() {
        $('.channel-container').on('click', function(e) {
            $('.channel-input', this).prop('checked', true);
            var id = $(this).find('.channel-input').val();
            if(id == undefined)
                return false;
            var container = copyChannelContainer(id);
            $('.'+settings.selectedChannelContainerClass+' div').remove();
            container.insertBefore($('.'+settings.selectedChannelContainerClass+' hr'));
            toggleWidgetView();
            e.preventDefault();
        });
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
    }, 400);
});
