jQuery( document ).ready(
	function() {

		jQuery( ".ld-table-list-items .ld-table-list-item" ).each(
			function( index ) {
				var get_topic_id = jQuery( this ).attr( "id" ).match( /\d+$/ )[0];
				jQuery( ".ld-table-list-item-" + get_topic_id ).appendTo( jQuery( "#ld-table-list-item-" + get_topic_id ) );
				var count = jQuery( ".ld-table-list-item-" + get_topic_id ).length;
				if ( count > 1 ) {
					jQuery( ".ld-table-list-item-" + get_topic_id ).hide();
					jQuery( ".ld-table-list-item-" + get_topic_id + ":eq(0)" ).show();
				}
				if ( jQuery( ".ld-table-list-items #ld-table-list-item-" + get_topic_id + " span" ).hasClass( "ld-status" ) ) {
					jQuery( "#ld-table-list-item-" + get_topic_id ).css( {"cursor": "default", "pointer-events": "none"} );
				}
			}
		);

	}
);
