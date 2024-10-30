jQuery(document).ready(function($){
    var is_locked = false;
    var html_classes;
    
    
    settings_page();
    
    jQuery(document).keydown(function(e) {
        if(e.which == 81 && e.ctrlKey) {
            // ctrl+q pressed
            lock();
        }
    });
    
    jQuery('form input:text texarea').keydown(function(e){
        if(e.which == 81 && e.ctrlKey) {
            // ctrl+q pressed
            lock();
        }
    });
    
    jQuery("#wp-admin-bar-wp-lock-button").click(function(e){
        e.preventDefault();
        lock();
    });
    
    jQuery(".single-palette input[type=radio]").on("click", function(){
        jQuery(".img_container input[type=checkbox]").each(function(){
            jQuery(this).removeAttr("checked");
        })
    });
    
    jQuery(".img_container input[type=checkbox]").on("click", function(){
        jQuery(".single-palette input[type=radio]").each(function(){
            jQuery(this).removeAttr("checked");
        })
    });
    
    jQuery(".vegas-background").each(function(){
        jQuery(this).css('opacity', '1');
    });
    
    if (typeof wp == "object") {
        wp.heartbeat.interval( 'fast' );
    }
    
    jQuery(document).on( 'heartbeat-send', function( e, data ) {
        data['wp_lock_system'] = 'check';
    });
    
    jQuery(document).on( 'heartbeat-tick', function( event, data ) {
        if ( data.hasOwnProperty( 'is_locked' ) || data.hasOwnProperty( 'lock_now' ) || data.hasOwnProperty( 'lock_idle' )) {
            if(data['is_locked'] == true && is_locked == false){
                lock();
            }
            else if(data['lock_now'] == true && is_locked == false){
                lock();
                if (jQuery("#auto_lock_status").length == 1) {
                    jQuery("#auto_lock_status").removeAttr("checked");
                    jQuery('#lock-datetimepicker').attr("disabled", "disabled");
                }
            }
            else if (data['lock_idle'] == true && is_locked == false) {
                lock();
            }
            else if(data['is_locked'] == false && is_locked){
                if (typeof direct_loding == "undefined") {
                    jQuery("#wpadminbar").slideDown("fast");
                    jQuery("html").attr("class", html_classes);
                    jQuery("#lock-mask").remove();
                    jQuery("#lock-screen-cont").remove();
                    is_locked = false;
                    destroy_vegas();
                    jQuery("body").removeClass("lockoverflow");
                }
                else{
                    reload();
                }
            }
        }
    });
    
    
    
    if (typeof direct_loding != "undefined" && direct_loding) {
        set_date();
        set_time();
        handle_events();
        destroy_vegas();
        vagase_slideshow();
        get_me_some_value();
    }
    
    function lock() {
        jQuery.ajax({
            url: lock_admin_url+'admin-ajax.php?action=lock_me',
            type: 'post',
            success: function(response){
                is_locked = true;
                if(jQuery("#lock-mask").length == 0){
                    jQuery("body").append("<div id='lock-mask'/>");
                    jQuery("body").append("<div id='lock-screen-cont'/>");
                }
                jQuery("body").addClass("lockoverflow");
                jQuery("#lock-screen-cont").html(response);
                jQuery("#wpadminbar").slideUp("fast");
                
                html_classes = jQuery("html").attr("class");
                jQuery("html").removeClass();
                
                set_date();
                set_time();
                handle_events();
                destroy_vegas();
                vagase_slideshow();
            }
        });
    }
    
    function set_date() {
        var now = new Date();
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        jQuery("#lock-date-time p").html(now.getDate() + " "+ months[now.getMonth()] + " " + now.getFullYear());
    }
    
    function set_time() {
        var now = new Date();
        jQuery("#lock-date-time h4").html(get_digit(now.getHours()) + ":" + get_digit(now.getMinutes()));
        
        if(is_locked)
            setTimeout(set_time);
    }
    
    function get_digit(digit) {
        return ( digit > 9 ) ? digit : "0"+digit;
    }
    
    function handle_events() {
        jQuery("#unlock-password").focus();
        jQuery("#unlock-password").unbind("keydown");
        jQuery("#unlock-password").on("keydown", function(){
            if (jQuery(this).hasClass("error")) {
                jQuery(this).removeClass("error");
            }
        })
        jQuery(".btn-unlock").unbind("click");
        jQuery(".btn-unlock").click(function(){
            jQuery(this).parent("form").submit();
        });
        
        jQuery("#wp-unlock-form").unbind("submit");
        jQuery("#wp-unlock-form").on("submit", function(e){
            e.preventDefault();
            jQuery("#unlock-password").removeClass("error");
            if(jQuery("#unlock-password").val().trim() == ""){
                jQuery("#unlock-password").attr("placeholder", "Please enter your password");
                jQuery("#unlock-password").addClass("error");
                return false;
            }
            jQuery.ajax({
                url: lock_admin_url+'admin-ajax.php?action=unlock_me',
                type: 'post',
                data: {userpasswd: jQuery("#unlock-password").val()},
                success: function(response){
                    if(response == "true"){
                        if (typeof direct_loding == "undefined") {
                            jQuery("body").removeClass("lockoverflow");
                            jQuery("#wpadminbar").slideDown("fast");
                            jQuery("html").attr("class", html_classes);
                            jQuery("#lock-mask").remove();
                            jQuery("#lock-screen-cont").remove();
                            is_locked = false;
                            destroy_vegas();
                        }
                        else{
                            reload();
                        }
                    }
                    else{
                        jQuery("#unlock-password").val("");
                        jQuery("#unlock-password").attr("placeholder", "Invalid password");
                        jQuery("#unlock-password").addClass("error");
                    }
                }
            });
            return false;
        })
    }
    
    function get_me_some_value() {
        jQuery.ajax({
            url: lock_admin_url+'admin-ajax.php?action=locked_getmesomething',
            type: 'post',
            success: function(response){
                response = jQuery.parseJSON(response);
                if (typeof response.is_locked != "undefined" && response.is_locked == false) {
                    reload()
                }
                else{
                    setTimeout(get_me_some_value, 9000);
                }
            }
        });
    }
    
    function reload() {
        var url = window.location.href;
        var filename = url.substring(url.lastIndexOf('/')+1);
        if ( filename == "admin.php" || filename == "admin.php?" ) {
            window.location.href = lock_admin_url;
        }
        else{
            window.location.reload();
        }
    }
    
    
    function vagase_slideshow() {
        if (typeof lock_bgs == "object" && lock_bgs.length > 0) {
            $.vegas('slideshow', {
                backgrounds:lock_bgs,
                walk:function(step) {
                    console.log(step);
                }
            })('overlay');
        }
    }
    
    function destroy_vegas() {
        $.vegas('destroy');
    }
    
    var custom_uploader;
    
    function settings_page() {
        enable_or_disable_datepicker();
        load_datetime_picker();
        enable_disable_idle_timeout();
        enable_media_uploader();
    }
    
    function enable_or_disable_datepicker() {
        if (jQuery("#auto_lock_status").length == 1) {
            if (jQuery("#auto_lock_status").attr("checked") == "checked") {
                $('#lock-datetimepicker').removeAttr("disabled");
            }
            else{
                $('#lock-datetimepicker').attr("disabled", "disabled");
            }
            
            jQuery("#auto_lock_status").on("click", function(){
                if(jQuery(this).attr("checked") == "checked") {
                    $('#lock-datetimepicker').removeAttr("disabled");
                }
                else{
                    $('#lock-datetimepicker').attr("disabled", "disabled");
                }
            });
        }
    }
    
    function enable_disable_idle_timeout() {
        if (jQuery("#auto_idle_lock_status").length == 1) {
            if (jQuery("#auto_idle_lock_status").attr("checked") == "checked") {
                $('#auto_idle_lock_timeout').removeAttr("disabled");
            }
            else{
                $('#auto_idle_lock_timeout').attr("disabled", "disabled");
            }
            
            jQuery("#auto_idle_lock_status").on("click", function(){
                if(jQuery(this).attr("checked") == "checked") {
                    $('#auto_idle_lock_timeout').removeAttr("disabled");
                }
                else{
                    $('#auto_idle_lock_timeout').attr("disabled", "disabled");
                }
            });
        }
    }
    
    function load_datetime_picker() {
        if ($('#lock-datetimepicker').length == 1) {
            var value;
            if ($('#lock-datetimepicker').attr("data-selected") != '') {
                value = $('#lock-datetimepicker').attr("data-selected");
            }
            else{
                var after_ten_minutes = new Date();
                after_ten_minutes.setMinutes(after_ten_minutes.getMinutes() + 10);
                value = after_ten_minutes.getDate() + '-' + (after_ten_minutes.getMonth() + 1) + '-' + after_ten_minutes.getFullYear() + ' ' + after_ten_minutes.getHours() + ':' + after_ten_minutes.getMinutes();
            }
            
            $('#lock-datetimepicker').datetimepicker({
                minDate: 0,
                format:'d-m-Y H:i',
                formatDate:'d-m-Y',
                value: value,
                step:10
            });
            
            
        }
    }
    
    function enable_media_uploader() {
        jQuery("#btn-lock-upload").click(function(e){
            e.preventDefault();
            
            if (custom_uploader) {
                custom_uploader.open();
            }
            //Extend the wp.media object
            custom_uploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose Image',
                button: {
                    text: 'Choose Image'
                },
                multiple: false
            });
            
            custom_uploader.open();
            
            custom_uploader.on('select', function() {
                attachment = custom_uploader.state().get('selection').first().toJSON();
                
                var total_divs = jQuery("#lock-media-images .img_container").length;
                total_divs++;
                
                var new_img = '<div class="img_container">'
                    new_img += '<input type="checkbox" name="wp-lock-screen-bg[]" id="wp-lock-screen-bg-'+total_divs+'" value="'+attachment.url+'" checked="checked" />'
                    new_img += '<label for="wp-lock-screen-bg-'+total_divs+'">'
                        new_img += '<img src="'+attachment.url+'"  />';
                    new_img += '</label>'
                new_img += '</div>';
                
                jQuery("#lock-media-images .lock-dynamic-images").append(new_img);
                
                if (jQuery("#lock-media-images .nothing-found").length > 0) {
                    jQuery("#lock-media-images .nothing-found").remove();
                }
            });
        });
    }
});