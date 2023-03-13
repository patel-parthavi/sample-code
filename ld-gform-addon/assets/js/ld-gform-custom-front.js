jQuery( document ).ready(function() {
 
	if(jQuery('.ld-table-list-items').length > 0 ){
    	jQuery(".gform_wrapper").hide();
    }
    if(jQuery('.ld-table-list-items .ld-status-complete').length > 0 ){
    	jQuery(".gform_wrapper").show();
    	jQuery(".ld-lesson-topic-list").hide();
    } 
    if(jQuery('.ld-status.ld-status-complete.ld-secondary-background').length > 0 ){
    	jQuery(".gform_wrapper").hide();
    	jQuery(".ld-lesson-topic-list").show();
    }
    if(jQuery('.quiz_section_gravity_form .gform_wrapper').length > 0){
    	jQuery(".gform_wrapper").show();
    	jQuery(".ld-lesson-topic-list").hide();
    }
    if(jQuery('.gform_wrapper').length > 0){
    	jQuery(".learndash_mark_complete_button").hide();
    }    

    var wrapper_id = jQuery(".gform_wrapper").attr('id');
    if ( wrapper_id ) {
    	var form_id = wrapper_id.substr(wrapper_id.lastIndexOf("_") + 1);
    
	    if ( form_id ) {
		    jQuery("#gform_submit_button_"+form_id).attr( "disabled", "disabled" );
			jQuery("form#gform_"+form_id+" input").keyup(function() {
			    var empty = false;
			    jQuery("form#gform_"+form_id+" .gfield_contains_required input").each(function() {
			        if (jQuery(this).val() == '') {
			            empty = true;
			        }
			    });

			    if (empty) {
			        jQuery("#gform_submit_button_"+form_id).attr('disabled', 'disabled'); 
			    } else {
			        jQuery("#gform_submit_button_"+form_id).removeAttr('disabled'); 
			    }
			});
		}
	}

});