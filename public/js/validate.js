// TODO: Fix file

get_glob = function(elem)
{
    var glob= new Array();
    var named= elem.attr('id');
    var arr= named.split('_');
    var classname= arr[0];
    obj_id= arr[1];

    if ($('#hidden_'+named).length)
        val= $('#hidden_'+named).val();
    else
        val= elem.val();

    glob.push({ url: window.location.href.replace(/http\:\/\/[^\/.]+/, ''), obj_id: obj_id, classname: classname, field: arr[2], fullname: named, id: elem.attr('id'), value: val });
    return glob;
}

check_message_exists = function(error_trap, message)
{
    var messageExists = false;
    $.each((error_trap).children('div'), function(){
        if ( $(this).text() == message )
        {
            messageExists = $(this);
            return false; 
        }
    });
    return messageExists;
}

change_valid_status = function(responseObject)
{
    if ( responseObject == null || responseObject == 'null' ) return false;

    /*
    <div id="error_trap_Meter_<?=$m->id?>"></div>
    <div class="error_trap_Meter"></div>
    <div id="error_trap_isPrimary"></div>
    finally, just add to the field 
    <div id='error_trap'></div>
    */

    $.each(responseObject, function(field_selector, message) {
        var id_parts= get_view_id_parts(field_selector);
        var error_trap = {
            'property':'#error_trap_' + id_parts['object'] + '_' + id_parts['field'],
            'id':      '#error_trap_' + id_parts['object'] + '_' + id_parts['id'],
            'obj':     '.error_trap_' + id_parts['object'],
            'field':   '#trapped_' + field_selector, // special case for being next to the input!
            'generic': '#error_trap'
        };

        $.each(error_trap, function(type, et_selector) {
            if ( type == 'field' )
            {
                if ( !$('#' + field_selector).length )
                {
                    return true;
                }
            }
            else
            {
                if ( !$(et_selector).length )
                {
                    return true;
                }
            }

            // Remove message
            if ( message == 'null' )
            {
                $('#'+field_selector).css('background', 'white');
                if (type == 'field')
                {
                    $(et_selector).remove();
                }		    
                else 
                {
                    if ( $('#' + field_selector).length )
                    {
                        var toRemove = $('#'+field_selector).closest(et_selector).children('#trapped_'+field_selector);
                    }
                    else
                    {
                        var toRemove = $(et_selector).children('#trapped_'+field_selector);
                    }

                    var parentTrap = toRemove.parent();
                    var originalMessage = toRemove.text(); 
                    toRemove.remove();
                    var errorExists = check_message_exists(parentTrap, originalMessage);
                    if ( errorExists ) 
                    {
                        errorExists.css('display', 'block');
                    }
                }
            }
            else // display message
            {
                $('#'+field_selector).css('background', '#FE5F5F');
                if ( type == 'field' )
                {
                    // Append the error after the input
                    if ( !$('#trapped_'+field_selector).length )
                    {
                        $('#'+field_selector).parent().append('<div id="trapped_'+field_selector+'">'+message+'</div>');
                    }
                }
                else
                {
                    // Find actual error trap, and add the message
                    if ( !$('#trapped_'+field_selector).length )
                    {
                        var style = ' display: none;';
                        if ( $('#' + field_selector).length )
                        {
                            if ( !check_message_exists($('#'+field_selector).closest(et_selector), message) )
                            {
                                style = ' display: block;';
                            }
                            var trapSelector = $('#'+field_selector).closest(et_selector);
                        }
                        else
                        {
                            if ( !check_message_exists($(et_selector), message) )
                            {
                                style = ' display: block;';
                            }
                            var trapSelector = $(et_selector);
                        }

                        trapSelector.prepend('<div class="error_msg" style="' + style + '" id="trapped_'+field_selector+'">'+message+'</div>');

                    }
                }
            }

            // Exit, no sense in continuing the loop
            return false;
        });
    });
}

validate_fields = function(frm)
{
    frm.find("input[type='checkbox'],input[name*='data'],select[name*='data']").each(function() {
        if ( $(this).hasClass('no_change_upstream'))
        {
            return true;
        }
        $(this).change(function() {
            $.ajax({
                type: 'POST',
                url: '/ApplicationController/validate',
                data: { data: JSON.stringify(get_glob($(this))) },
                success: function(data){
                    change_valid_status(data);
                },
                dataType: "json"
            });
        });
    });
}

$(document).ready(function() {
    // validation entry point:
    $("body").find('form').each(function() {
        var fields= $(this).find("input[name*='data']");
        if ($(this).html() != null && (fields.length > 0)) 
        {            
            validate_fields($(this));
        }
    });
});
