jQuery( document ).ready(function() {
	jQuery( ".ld-table-list-items .ld-table-list-item" ).each(function( index ) {
		var get_topic_id = jQuery(this).attr("id").match(/\d+$/)[0];
		jQuery( ".ld-table-list-item-"+get_topic_id ).appendTo( jQuery( "#ld-table-list-item-"+get_topic_id ) );
		if( jQuery( ".ld-table-list-items #ld-table-list-item-"+get_topic_id + " span" ).hasClass( "ld-status" ) ){
			jQuery( "#ld-table-list-item-"+get_topic_id ).css({"cursor": "default", "pointer-events": "none"});
		}
	});
});