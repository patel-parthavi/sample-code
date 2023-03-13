jQuery( document ).ready(
	function() {

		jQuery( '.topic_drip_date_setting' ).datetimepicker(
			{
				timeFormat: "hh:mm tt",
				dateFormat: 'dd-mm-yy'
			}
		);

		jQuery( document ).on(
			'click',
			'#topic_drip_all_date_save_backend',
			function (e) {

				var topicid               = jQuery( this ).data( "topicid" );
				var groupid               = jQuery( this ).data( "groupid" );
				var topic_drip_group_date = jQuery( '.topic_drip_date_setting_' + groupid ).val();
				jQuery( '#data_insert_success_' + groupid ).hide();

				jQuery.ajax(
					{
						type: "post",
						url:topicdripbackend.ajaxurl,
						data: {action: "insert_form_data_backend", topic_drip_group_date : topic_drip_group_date, topicid : topicid, groupid : groupid },
						success: function(html) {
							jQuery( '#data_insert_success_' + groupid ).show();
							// jQuery('.data_insert_success').delay(2000).fadeOut();
						},
						error: function (jXHR, textStatus, errorThrown) {
							jQuery( '#data_insert_success_' + groupid ).hide();
							alert( errorThrown );
						}
					}
				);

				e.preventDefault();

			}
		);

		jQuery( ".ld-table-list-items .ld-table-list-item" ).each(
			function( index ) {
				var get_topic_id = jQuery( this ).attr( "id" ).match( /\d+$/ )[0];
				jQuery( ".ld-table-list-item-" + get_topic_id ).appendTo( jQuery( "#ld-table-list-item-" + get_topic_id ) );
				if ( jQuery( ".ld-table-list-items #ld-table-list-item-" + get_topic_id + " span" ).hasClass( "ld-status" ) ) {
					jQuery( "#ld-table-list-item-" + get_topic_id ).css( {"cursor": "default", "pointer-events": "none"} );
				}
			}
		);

	}
);
