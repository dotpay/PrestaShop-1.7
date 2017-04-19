(function($) {
    
    var defaults = {
        chooserTemplate: "channel-control-template",
        channelListInput: "chosen-channel-list",
        channelLogo: "chosen-channel-logo",
        chooserElement: "channel-control",
        chooserSelect: "channel-chooser",
        chooserRemove: "channel-chooser-remove",
        chooserAddChannel: "add-new-channel"
    };
    
    var channelList;
    var canBeUpdated = true;
    var settings = {};
    
    $.dpChannelChooser = function(options) {
        settings = $.extend( {}, defaults, options );
        channelList = $('.'+settings.channelListInput).css('display', 'none').val().split(',').reverse();
        $('.'+settings.chooserSelect).change(onChangeSelectedChannel);
        $('.'+settings.chooserRemove).click(onRemoveSelectedChannel);
        $('#'+settings.chooserAddChannel).click(onAddNewChannel);
        initializeSettings();
    };
    
    function initializeSettings() {
        var channelAdd = $('.'+settings.channelListInput);
        canBeUpdated = false;
        for(var i in channelList) {
            if (isNaN(parseInt(channelList[i])) === false) {
                channelAdd.after(createSelect(channelList[i]));
            }
        }
        canBeUpdated = true;
        updateValues();
    }
    
    function createSelect(id) {
        var copy = $('.'+settings.chooserTemplate).clone(true);
        copy.removeClass(settings.chooserTemplate).addClass(settings.chooserElement);
        copy.find('option').attr('selected', false);
        if (id !== '') {
            copy.find('option[value='+id+']').attr('selected', true);
            copy.find('select').val(id).change();
        }
        return copy;
    }
    
    function onChangeSelectedChannel(e) {
        var changed = $(e.target);
        changed.siblings('.'+settings.channelLogo).attr('src', changed.find('option:selected').data('logo')).removeClass('empty-channel-logo');
        var firstOption = changed.find('option:first');
        if(!firstOption.val()) {
            firstOption.remove();
        }
        updateValues();
    }
    
    function onRemoveSelectedChannel(e) {
        e.stopPropagation();
        e.preventDefault();
        $(e.target).parents('.'+settings.chooserElement).remove();
        updateValues();
        return false;
    }
    
    function onAddNewChannel(e) {
        e.stopPropagation();
        e.preventDefault();
        var newSelect = createSelect('');
        var setValues = $('.'+settings.channelListInput).val().split(',');
        for(var i in setValues) {
            newSelect.find('option[value='+setValues[i]+']').attr('disabled','true');
        }
        var element = $('.'+settings.chooserElement+':last');
        if (element.length === 0) {
            element = $('.'+settings.channelListInput);
        }
        element.after(newSelect);
        return false;
    }
    
    function getSetValues() {
        var setValues = [];
        $('.'+settings.chooserElement+' option').removeAttr('disabled');
        var select = $('.'+settings.chooserElement+' select');
        select.each(function(){
            if($(this).val()) {
                setValues[setValues.length] = $(this).val();
            }
        });
        select.each(function(){
            if($(this).val()) {
                for(var i in setValues) {
                    if ($(this).val() !== setValues[i]) {
                        $(this).find('option[value='+setValues[i]+']').attr('disabled','true');
                    }
                }
            }
        });
        return setValues;
    }
    
    function updateValues() {
        if (!canBeUpdated) {
            return false;
        }
        var setValues = getSetValues();
        $('.'+settings.channelListInput).val(setValues.join(','));
    }
})(jQuery);