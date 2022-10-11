jQuery( document ).ready(function() {

	jQuery( ".ld-table-list-items .ld-table-list-item" ).each(function( index ) {
		var get_topic_id = jQuery(this).attr("id").match(/\d+$/)[0];
		jQuery( ".ld-table-list-item-"+get_topic_id ).appendTo( jQuery( "#ld-table-list-item-"+get_topic_id ) );
		var count = jQuery(".ld-table-list-item-"+get_topic_id).length;

		if ( count > 1 ) {
			jQuery(".ld-table-list-item-"+get_topic_id).hide();
			jQuery(".ld-table-list-item-"+get_topic_id+":eq(0)").show();
		}
		if( jQuery( ".ld-table-list-items #ld-table-list-item-"+get_topic_id + " span" ).hasClass( "ld-status" ) ){
			jQuery( "#ld-table-list-item-"+get_topic_id ).css({"cursor": "default", "pointer-events": "none"});
		}

	});

	jQuery(document).on('change', 'select#group_name_id', function(event) {
    	var selected_grp_id = jQuery(this).val();
    	var params =  {"selected_grp_id":selected_grp_id,action:"load_tab_ajax"}

		jQuery.post(topicdrip.ajaxurl,params,function(data){

			if(data){
		        jQuery(".scheduling-management-tab").empty().append(data);
		    }else{
		        jQuery(".scheduling-management-tab").empty().append("No data Found");
		    }

		});
    });

	jQuery(document).on('change', 'select#group_name_id', function(event) {
    	var selected_grp_id = jQuery(this).val();
    	var params =  {"selected_grp_id":selected_grp_id,action:"load_course_ajax"}

		jQuery.post(topicdrip.ajaxurl,params,function(data){

			if(data){
		        jQuery(".course_name_section").empty().append(data);
		        jQuery('.data_insert_success').hide();
		        jQuery(".topic_drip_setting_table").hide();
		    }else{
		        jQuery(".course_name_section").empty().append("No data Found");
		        jQuery('.data_insert_success').hide();
		        jQuery(".topic_drip_setting_table").hide();
		    }

		});
    });

    jQuery(document).on('change', 'select#course_name_id', function(event) {
    	var selected_grp_id = jQuery('#group_name_id').val();
    	var selected_course_id = jQuery(this).val();
    	var params =  {"selected_course_id":selected_course_id,"selected_grp_id":selected_grp_id,action:"load_lesson_ajax"}

		jQuery.post(topicdrip.ajaxurl,params,function(data){

			if(data){

				jQuery(".topic_drip_setting_table").show();
		        jQuery(".topic_drip_setting_table").empty().append(data);

		        jQuery('.topic_drip_date_setting').datetimepicker({
			        timeFormat: "hh:mm tt",
			        dateFormat: 'dd-mm-yy'
				});

				jQuery('.data_insert_success').hide();

		    }else{
		    	jQuery(".topic_drip_setting_table").show();
		        jQuery(".topic_drip_setting_table").empty().append("No data Found");
		        jQuery('.data_insert_success').hide();
		    }

		});
    });
   
    jQuery(document).on('submit','#save_topic_drip_meta_data', function (e) {
		var this_var = jQuery(this);
        var form_data = this_var.serialize();
        var group_name_id = jQuery('#group_name_id').val();

        jQuery('.data_insert_success').hide();

        jQuery.ajax({
	        type: "post",
	        url:topicdrip.ajaxurl,
	        data: form_data+'&group_name_id='+group_name_id+'&action=insert_form_data',
	        success: function(html) {
	            jQuery('.data_insert_success').show();
	            //jQuery('.data_insert_success').delay(2000).fadeOut();
	        },
	        error: function (jXHR, textStatus, errorThrown) {
	        	jQuery('.data_insert_success').hide();
	            alert(errorThrown);
	        }
    	});

    	e.preventDefault();

    });

	jQuery(document).on('change', 'select#group_name_id', function(event) {
    	var selected_grp_id = jQuery(this).val();
    	var params =  {"selected_grp_id":selected_grp_id,action:"load_course_ajax_course"}

		jQuery.post(topicdrip.ajaxurl,params,function(data){

			if(data){
		        jQuery(".course_name_section_course").empty().append(data);
				jQuery('.data_insert_success_course').hide();
		    }else{
		        jQuery(".course_name_section_course").empty().append("No data Found");
		        jQuery('.data_insert_success_course').hide();
		    }

		});
    });

    jQuery(document).on('click', '.topic_drip_remove_course', function(event) {
    	event.preventDefault();

    	var remove_course_id = jQuery('#topic_drip_assigned_course').val();
    	var total_assigned_id = jQuery("select#topic_drip_assigned_course option").map(function() {return jQuery(this).val();}).get();
    	var total_all_id = jQuery("select#topic_drip_all_course option").map(function() {return jQuery(this).val();}).get();
    	
    	var params =  {"remove_course_id":remove_course_id,"total_assigned_id":total_assigned_id,"total_all_id":total_all_id,action:"remove_course_ajax"}

		jQuery.post(topicdrip.ajaxurl,params,function(data){

			if(data){
		        jQuery("#replace_assigned_and_all_section").empty().append(data);
		        jQuery('.data_insert_success_course').hide();
		    }else{
		        jQuery("#replace_assigned_and_all_section").remove();
		        jQuery('.data_insert_success_course').hide();
		    }

		});
    });

    jQuery(document).on('click', '.topic_drip_add_course', function(event) {
    	event.preventDefault();

    	var total_assigned_id = jQuery("select#topic_drip_assigned_course option").map(function() {return jQuery(this).val();}).get();
    	var total_all_id = jQuery("select#topic_drip_all_course option").map(function() {return jQuery(this).val();}).get();
    	var add_course_id = jQuery('#topic_drip_all_course').val();

    	var params =  {"add_course_id":add_course_id,"total_assigned_id":total_assigned_id,"total_all_id":total_all_id,action:"add_course_ajax"}

		jQuery.post(topicdrip.ajaxurl,params,function(data){

			if(data){
		        jQuery("#replace_assigned_and_all_section").empty().append(data);
		        jQuery('.data_insert_success_course').hide();
		        
		    }else{
		        jQuery("#replace_assigned_and_all_section").remove();
		        jQuery('.data_insert_success_course').hide();
		       
		    }

		});
    });

    jQuery(document).on('submit','#save_topic_drip_meta_data_course', function (e) {

		var this_var = jQuery(this);
        var form_data = this_var.serialize();
        var group_name_id_course = jQuery('#group_name_id').val();

        jQuery('.data_insert_success_course').hide();

        jQuery.ajax({
	        type: "post",
	        url:topicdrip.ajaxurl,
	        data: form_data+'&group_name_id_course='+group_name_id_course+'&action=insert_course_data',
	        success: function(html) {
	            jQuery('.data_insert_success_course').show();
	            //jQuery('.data_insert_success_course').delay(2000).fadeOut();
	        },
	        error: function (jXHR, textStatus, errorThrown) {
	        	jQuery('.data_insert_success_course').hide();
	            alert(errorThrown);
	        }
    	});

    	e.preventDefault();

    });

    jQuery(document).on('change', 'select#group_name_id', function(event) {

    	var selected_grp_id = jQuery(this).val();
    	var params =  {"selected_grp_id":selected_grp_id,action:"load_user_ajax_user"}

		jQuery.post(topicdrip.ajaxurl,params,function(data){

			if(data){
		        jQuery(".course_name_section_user").empty().append(data);
				jQuery('.data_insert_success_user').hide();
		    }else{
		        jQuery(".course_name_section_user").empty().append("No data Found");
		        jQuery('.data_insert_success_user').hide();
		    }

		});
    });

    jQuery(document).on('click', '.topic_drip_remove_user', function(event) {
    	event.preventDefault();

    	var remove_user_id = jQuery('#topic_drip_assigned_user').val();
    	var total_assigned_id = jQuery("select#topic_drip_assigned_user option").map(function() {return jQuery(this).val();}).get();
    	var total_all_id = jQuery("select#topic_drip_all_user option").map(function() {return jQuery(this).val();}).get();
    	
    	var params =  {"remove_user_id":remove_user_id,"total_assigned_id":total_assigned_id,"total_all_id":total_all_id,action:"remove_user_ajax"}

		jQuery.post(topicdrip.ajaxurl,params,function(data){

			if(data){
		        jQuery("#replace_assigned_and_all_section_user").empty().append(data);
		        jQuery('.data_insert_success_user').hide();
		    }else{
		        jQuery("#replace_assigned_and_all_section_user").remove();
		        jQuery('.data_insert_success_user').hide();
		       
		    }

		});
    });

    jQuery(document).on('click', '.topic_drip_remove_leader', function(event) {
    	event.preventDefault();

    	var remove_leader_id = jQuery('#topic_drip_assigned_leader').val();
    	var total_assigned_id_leader = jQuery("select#topic_drip_assigned_leader option").map(function() {return jQuery(this).val();}).get();
    	var total_all_id_leader = jQuery("select#topic_drip_all_leader option").map(function() {return jQuery(this).val();}).get();
    	
    	var params =  {"remove_leader_id":remove_leader_id,"total_assigned_id_leader":total_assigned_id_leader,"total_all_id_leader":total_all_id_leader,action:"remove_leader_ajax"}

		jQuery.post(topicdrip.ajaxurl,params,function(data){

			if(data){
		        jQuery("#replace_assigned_and_all_section_leader").empty().append(data);
		        jQuery('.data_insert_success_user').hide();
		    }else{
		        jQuery("#replace_assigned_and_all_section_leader").remove();
		        jQuery('.data_insert_success_user').hide();
		       
		    }

		});
    });

    jQuery(document).on('click', '.topic_drip_add_user', function(event) {
    	event.preventDefault();

    	var total_assigned_id = jQuery("select#topic_drip_assigned_user option").map(function() {return jQuery(this).val();}).get();
    	var total_all_id = jQuery("select#topic_drip_all_user option").map(function() {return jQuery(this).val();}).get();
    	var add_user_id = jQuery('#topic_drip_all_user').val();

    	var params =  {"add_user_id":add_user_id,"total_assigned_id":total_assigned_id,"total_all_id":total_all_id,action:"add_user_ajax"}

		jQuery.post(topicdrip.ajaxurl,params,function(data){

			if(data){
		        jQuery("#replace_assigned_and_all_section_user").empty().append(data);
		        jQuery('.data_insert_success_user').hide();
		        
		    }else{
		        jQuery("#replace_assigned_and_all_section_user").remove();
		        jQuery('.data_insert_success_user').hide();
		       
		    }

		});
    });

	jQuery(document).on('click', '.topic_drip_add_leader', function(event) {
    	event.preventDefault();

    	var total_assigned_id_leader = jQuery("select#topic_drip_assigned_leader option").map(function() {return jQuery(this).val();}).get();
    	var total_all_id_leader = jQuery("select#topic_drip_all_leader option").map(function() {return jQuery(this).val();}).get();
    	var add_leader_id = jQuery('#topic_drip_all_leader').val();

    	var params =  {"add_leader_id":add_leader_id,"total_assigned_id_leader":total_assigned_id_leader,"total_all_id_leader":total_all_id_leader,action:"add_leader_ajax"}

		jQuery.post(topicdrip.ajaxurl,params,function(data){

			if(data){
		        jQuery("#replace_assigned_and_all_section_leader").empty().append(data);
		        jQuery('.data_insert_success_user').hide();
		    }else{
		        jQuery("#replace_assigned_and_all_section_leader").remove();
		        jQuery('.data_insert_success_user').hide();
		    }

		});
	});

	jQuery(document).on('submit','#save_topic_drip_meta_data_user', function (e) {
		var this_var = jQuery(this);
        var form_data = this_var.serialize();
        var group_name_id_user = jQuery('#group_name_id').val();

        jQuery('.data_insert_success_user').hide();

        jQuery.ajax({
	        type: "post",
	        url:topicdrip.ajaxurl,
	        data: form_data+'&group_name_id_user='+group_name_id_user+'&action=insert_user_data',
	        success: function(html) {
	            jQuery('.data_insert_success_user').show();
	            //jQuery('.data_insert_success_user').delay(2000).fadeOut();
	        },
	        error: function (jXHR, textStatus, errorThrown) {
	        	jQuery('.data_insert_success_user').hide();
	            alert(errorThrown);
	        }
    	});

	    e.preventDefault();

    });

});