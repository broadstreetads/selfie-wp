jQuery(function($){
    
    var needRefresh = false;
    
    /**
    * Check a response fromt he server to see if the call was successful (uses
    *  success flag, not HTTP error codes)
    */
    function isSuccessful(raw_json)
    {
        o = eval('(' + raw_json + ')');
        return o.success == true;
    }

    /**
    * Show and fade away a 'saved' message next to a checkbox with the given id
    */
    function markSaved(span_id)
    {
        jQuery(span_id).show().delay(500).fadeOut();
    }
    
    $('#business_enabled').click(function() {
        needRefresh = true;
    });
    
    $('#one-click-signup').click(function(e) {
        e.preventDefault();
        var email = prompt('Please confirm your email address:', window.admin_email);
        
        if(!email) return false;
        
        $.post(ajaxurl, {action: 'register', email: email}, function(response) {
            if(response.success)
            {
                location.reload();
            }
            else
            {
                alert('There was an error creating a an account! Do you already have an account? If not, try again.');
            }
        }, 'json');
    });
    
    $('#save').click(function() {
        
        var network_id = $('#network').val();
        
        jQuery.post(ajaxurl, {
             action: 'save_settings', 
             api_key: $('#api_key').val(),
             network_id: network_id
            }, 
            function(response) {
                if(response.success)
                {
                    markSaved('#save-success');
                    $('#network').empty();

                    if(response.key_valid) {
                        $('#key-invalid').hide().removeClass('visible');;
                        $('#key-valid').fadeIn().addClass('visible');
                        var opt;
                        
                        for(var i in response.networks) {
                            opt = $('<option>')
                                    .text(response.networks[i].name)
                                    .attr('value', response.networks[i].id);
                                    
                            if(network_id == response.networks[i].id)
                                opt.attr('selected', 'selected');
                                    
                            $('#network').append(opt);
                        }

                    } else {
                        $('#network').append($('<option value="-1">Enter a valid token above</option>'));
                        $('#key-valid').hide().removeClass('visible');
                        $('#key-invalid').fadeIn().addClass('visible');
                    }
                    
                    if(needRefresh) {
                        location.reload();
                    }
                }
            },
        'json');
    });
    
   
    
    function showUpdateDetails() {
        var type = $('#bs_update_source').val();
        
        $('#bs_source_details').children().hide();
        $('#bs_source_' + type + '_detail').show();
    }
    
    $('#bs_source_details').children().hide();
    showUpdateDetails();    
    window.remove_image = function(e) {
        e.preventDefault();
        el = $(e.target);
        
        if(confirm('Are you sure?'))
        {
            el = $(el);
            el.parents('li').remove();
        }
        
        window.rewrite_image_names();
    };    
});