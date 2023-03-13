jQuery( document ).ready(function() {

	jQuery("#gravity_form_exists_post").val(jQuery("#gravity_form_exists_post option:first").val());
	
	var selected_post_id = jQuery("#gravity_form_exists_post").val();
	var params =  {"selected_post_id":selected_post_id,action:"get_gravity_form_data_ajax"}

	jQuery.post(ldgravityform.ajaxurl,params,function(data){
    	if(data){
	        jQuery(".feedback_report_from_users").empty().append(data);
        	jQuery('#user_feedback_details').DataTable({
		        dom: 'lBfrtip',
		        buttons: [
		            'pdf'
		        ]
		    });
	    }else{
	        jQuery(".feedback_report_from_users").empty().append("No data Found");
	    }
	});

	jQuery(document).on('change', 'select#gravity_form_exists_post', function(event) {
    	var selected_post_id = jQuery(this).val();
    	var params =  {"selected_post_id":selected_post_id,action:"get_gravity_form_data_ajax"}

    	jQuery.post(ldgravityform.ajaxurl,params,function(data){
	    	if(data){
		        jQuery(".feedback_report_from_users").empty().append(data);
	        	jQuery('#user_feedback_details').DataTable({
			        dom: 'lBfrtip',
			        buttons: [
			            'pdf'
			        ]
			    });
		    }else{
		        jQuery(".feedback_report_from_users").empty().append("No data Found");
		    }
		});

    });

});