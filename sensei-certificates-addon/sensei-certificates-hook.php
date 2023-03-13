<?php

// Added custom metabox in certificate template page : backend.
add_action( 'add_meta_boxes', 'certificate_templates_custom_meta_boxes' );
function certificate_templates_custom_meta_boxes() {

	add_meta_box(
		'sensei-certificate-custom-data',
		__( 'Custom Certificate Field', 'sensei-certificates-addon' ),
		'certificate_template_custom_data_meta_box',
		'certificate_template',
		'normal',
		'low'
	);
}


// Added custom field inside custom metabox in certificate template page : backend.
function certificate_template_custom_data_meta_box( $post ) {

	global $woocommerce, $woothemes_sensei_certificate_templates;

	wp_nonce_field( 'certificates_save_data', 'certificates_meta_nonce' );

	$woothemes_sensei_certificate_templates->populate_object( $post->ID );

	$default_fonts   = array(
		'Helvetica' => 'Helvetica',
		'Courier'   => 'Courier',
		'Times'     => 'Times',
	);
	$available_fonts = array_merge( array( '' => '' ), $default_fonts ); ?>

	<style type="text/css">
		#misc-publishing-actions { display:none; }
		#edit-slug-box { display:none }
		.imgareaselect-outer { cursor: crosshair; }
	</style>
	<div id="certificate_options" class="panel certificate_templates_options_panel">
		<div class="options_group">
			<?php

			// Custom Message.
			echo '<div class="options_group">';
				certificate_templates_wp_position_picker(
					array(
						'id'          => 'certificate_custom_message_pos',
						'label'       => __( 'Message Position Custom', 'sensei-certificates-addon' ),
						'value'       => implode( ',', $woothemes_sensei_certificate_templates->get_field_position( 'certificate_custom_message' ) ),
						'description' => __( 'Optional position of the Certificate Message', 'sensei-certificates-addon' ),
					)
				);
				certificates_wp_hidden_input(
					array(
						'id'    => '_certificate_custom_message_pos',
						'class' => 'field_pos',
						'value' => implode( ',', $woothemes_sensei_certificate_templates->get_field_position( 'certificate_custom_message' ) ),
					)
				);
				certificate_templates_wp_font_select(
					array(
						'id'      => '_certificate_custom_message',
						'label'   => __( 'Font Custom', 'sensei-certificates-addon' ),
						'options' => $available_fonts,
					)
				);
				certificates_wp_text_input(
					array(
						'id'    => '_certificate_custom_message_font_color',
						'label' => __( 'Font color Custom', 'sensei-certificates-addon' ),
						'value' => isset( $woothemes_sensei_certificate_templates->certificate_template_fields['certificate_custom_message']['font']['color'] ) ? $woothemes_sensei_certificate_templates->certificate_template_fields['certificate_custom_message']['font']['color'] : '',
						'class' => 'colorpick',
					)
				);
				certificates_wp_textarea_input(
					array(
						'class'       => 'medium',
						'id'          => '_certificate_custom_message_text',
						'label'       => __( 'Message Text Custom', 'sensei-certificates-addon' ),
						'description' => __( 'Text to display in the message area.', 'sensei-certificates-addon' ),
						'placeholder' => __( 'You can use fields like : {{learner}}, {{course_title}}, {{completion_date}}, {{course_place}} OR any BuddyPress fields like : {{Name}}' ),
						'value'       => isset( $woothemes_sensei_certificate_templates->certificate_template_fields['certificate_custom_message']['text'] ) ? $woothemes_sensei_certificate_templates->certificate_template_fields['certificate_custom_message']['text'] : '',
					)
				);
			echo '</div>';

			?>
		</div>
	</div>
	<?php
}


// Saving custom field data in postmeta in database : backend.
add_action( 'sensei_process_certificate_template_meta', 'certificate_templates_process_meta_custom', 20, 2 );
function certificate_templates_process_meta_custom( $post_id, $post ) {

	if (
		empty( $_POST['certificates_meta_nonce'] )
		|| ! wp_verify_nonce( wp_unslash( $_POST['certificates_meta_nonce'] ), 'certificates_save_data' )
	) {
		return;
	}

	$font_color  = ! empty( $_POST['_certificate_font_color'] ) ? sanitize_text_field( wp_unslash( $_POST['_certificate_font_color'] ) ) : '#000000'; // Provide a default.
	$font_size   = ! empty( $_POST['_certificate_font_size'] ) ? intval( $_POST['_certificate_font_size'] ) : 11;
	$font_family = ! empty( $_POST['_certificate_font_family'] ) ? sanitize_text_field( wp_unslash( $_POST['_certificate_font_family'] ) ) : '';

	update_post_meta( $post_id, '_certificate_font_color', $font_color );
	update_post_meta( $post_id, '_certificate_font_size', $font_size );
	update_post_meta( $post_id, '_certificate_font_family', $font_family );
	update_post_meta(
		$post_id,
		'_certificate_font_style',
		( isset( $_POST['_certificate_font_style_b'] ) && 'yes' == $_POST['_certificate_font_style_b'] ? 'B' : '' ) .
														( isset( $_POST['_certificate_font_style_i'] ) && 'yes' == $_POST['_certificate_font_style_i'] ? 'I' : '' ) .
														( isset( $_POST['_certificate_font_style_c'] ) && 'yes' == $_POST['_certificate_font_style_c'] ? 'C' : '' ) .
														( isset( $_POST['_certificate_font_style_o'] ) && 'yes' == $_POST['_certificate_font_style_o'] ? 'O' : '' )
	);

	$fields = array();
	foreach ( array( '_certificate_heading', '_certificate_message', '_certificate_custom_message', '_certificate_course', '_certificate_completion', '_certificate_place' ) as $i => $field_name ) {

		$field = array(
			'type'     => 'property',
			'font'     => array(
				'family' => '',
				'size'   => '',
				'style'  => '',
				'color'  => '',
			),
			'position' => array(),
			'order'    => $i,
		);

		if ( ! empty( $_POST[ $field_name . '_pos' ] ) ) {

			$position = explode( ',', wp_unslash( $_POST[ $field_name . '_pos' ] ) );
			$position = array_map( 'intval', $position );

			$field['position'] = array(
				'x1'     => $position[0],
				'y1'     => $position[1],
				'width'  => $position[2],
				'height' => $position[3],
			);
		}

		if ( ! empty( $_POST[ $field_name . '_text' ] ) ) {
			$field['text'] = sanitize_textarea_field( wp_unslash( $_POST[ $field_name . '_text' ] ) );
		}

		if ( ! empty( $_POST[ $field_name . '_font_family' ] ) ) {
			$field['font']['family'] = sanitize_text_field( wp_unslash( $_POST[ $field_name . '_font_family' ] ) );
		}
		if ( ! empty( $_POST[ $field_name . '_font_size' ] ) ) {
			$field['font']['size'] = intval( $_POST[ $field_name . '_font_size' ] );
		}
		if ( isset( $_POST[ $field_name . '_font_style_b' ] ) ) {
			$field['font']['style'] = 'B';
		}
		if ( isset( $_POST[ $field_name . '_font_style_i' ] ) ) {
			$field['font']['style'] .= 'I';
		}
		if ( isset( $_POST[ $field_name . '_font_style_c' ] ) ) {
			$field['font']['style'] .= 'C';
		}
		if ( isset( $_POST[ $field_name . '_font_style_o' ] ) ) {
			$field['font']['style'] .= 'O';
		}
		if ( isset( $_POST[ $field_name . '_font_color' ] ) ) {
			$field['font']['color'] = sanitize_text_field( wp_unslash( $_POST[ $field_name . '_font_color' ] ) );
		}

		$fields[ ltrim( $field_name, '_' ) ] = $field;

	}

	update_post_meta( $post_id, '_certificate_template_fields', $fields );

}
