<?php

// Created class to display custom fields data inside certificate PDF
class WooThemes_Sensei_Certificates_Custom {

	public function __construct() {

		add_action( 'sensei_certificates_before_pdf_output', array( $this, 'action__sensei_certificates_before_pdf_output' ), 20, 2 );
	}

	public function action__sensei_certificates_before_pdf_output( $pdf_certificate, $fpdf ) {

		$show_border    = apply_filters( 'woothemes_sensei_certificates_show_border', 0 );
		$start_position = 200;

		$args = array(
			'post_type'  => 'certificate',
			'meta_key'   => 'certificate_hash',
			'meta_value' => $pdf_certificate->hash,
		);

		$query          = new WP_Query( $args );
		$certificate_id = 0;

		if ( $query->have_posts() ) {

			$query->the_post();
			$certificate_id = $query->posts[0]->ID;

		}

		wp_reset_query();

		if ( 0 < intval( $certificate_id ) ) {

			$user_id      = get_post_meta( $certificate_id, 'learner_id', true );
			$student      = get_userdata( $user_id );
			$student_name = $student->display_name;
			$fname        = $student->first_name;
			$lname        = $student->last_name;

			if ( '' != $fname && '' != $lname ) {
				$student_name = $fname . ' ' . $lname;
			}

			$course_id       = get_post_meta( $certificate_id, 'course_id', true );
			$course_title    = get_post_field( 'post_title', $course_id );
			$course_end      = Sensei_Utils::sensei_check_for_activity(
				array(
					'post_id' => intval( $course_id ),
					'user_id' => intval( $user_id ),
					'type'    => 'sensei_course_status',
				),
				true
			);
			$course_end_date = $course_end->comment_date;

			$certificate_template_id = get_post_meta( $course_id, '_course_certificate_template', true );

			$certificate_template_custom_fields = get_post_custom( $certificate_template_id );

			$load_data = array(
				'certificate_font_style'      => array(),
				'certificate_font_color'      => array(),
				'certificate_font_size'       => array(),
				'certificate_font_family'     => array(),
				'image_ids'                   => array(),
				'certificate_template_fields' => array(),
			);

			if ( bp_has_profile( array( 'user_id' => $user_id ) ) ) {

				while ( bp_profile_groups() ) {
					bp_the_profile_group();

					if ( bp_profile_group_has_fields() ) {

						while ( bp_profile_fields() ) {
							bp_the_profile_field();

							if ( bp_field_has_data() ) {

								$get_bp_field_name[] = bp_get_the_profile_field_name();
								$get_bp_field_value[] = bp_get_the_profile_field_value();

							}
						}
					}
				}
			}

			$get_bp_field = array_combine( $get_bp_field_name, $get_bp_field_value );

			$get_user_info = get_userdata( $user_id );
			$get_user_info_meta = get_user_meta( $user_id );


			foreach ( $load_data as $key => $default ) {

				$this->$key = ( isset( $certificate_template_custom_fields[ '_' . $key ][0] ) && '' !== $certificate_template_custom_fields[ '_' . $key ][0] ) ? ( is_array( $default ) ? maybe_unserialize( $certificate_template_custom_fields[ '_' . $key ][0] ) : $certificate_template_custom_fields[ '_' . $key ][0] ) : $default;

			}

			if ( isset( $this->certificate_font_color ) && '' != $this->certificate_font_color ) {
				$pdf_certificate->certificate_pdf_data['font_color'] = $this->certificate_font_color; }
			if ( isset( $this->certificate_font_size ) && '' != $this->certificate_font_size ) {
				$pdf_certificate->certificate_pdf_data['font_size'] = $this->certificate_font_size; }
			if ( isset( $this->certificate_font_family ) && '' != $this->certificate_font_family ) {
				$pdf_certificate->certificate_pdf_data['font_family'] = $this->certificate_font_family; }
			if ( isset( $this->certificate_font_style ) && '' != $this->certificate_font_style ) {
				$pdf_certificate->certificate_pdf_data['font_style'] = $this->certificate_font_style; }

			$date = Woothemes_Sensei_Certificates_Utils::get_certificate_formatted_date( $course_end_date );

			$certificate_heading = __( 'Certificate of Completion', 'sensei-certificates-addon' );

			if ( isset( $this->certificate_template_fields['certificate_heading']['text'] ) && '' != $this->certificate_template_fields['certificate_heading']['text'] ) {

				$certificate_heading = $this->certificate_template_fields['certificate_heading']['text'];
				$certificate_heading = str_replace( array( '{{learner}}', '{{course_title}}', '{{completion_date}}', '{{course_place}}' ), array( $student_name, $course_title, $date, get_bloginfo( 'name' ) ), $certificate_heading );
				foreach ( $get_bp_field as $get_bp_field_key => $get_bp_field_value ) {
					$certificate_heading = str_replace( '{{BP-' . $get_bp_field_key . '}}', strip_tags( $get_bp_field_value ), $certificate_heading );
				}
				foreach ( $get_user_info->data as $get_user_info_key => $get_user_info_value ) {
					if ( $get_user_info_key == "user_pass" ) {
						continue;
					}
					$certificate_heading = str_replace( '{{usermeta-' . $get_user_info_key . '}}', strip_tags( $get_user_info_value ), $certificate_heading );
				}
				foreach ( $get_user_info_meta as $get_user_info_meta_key => $get_user_info_meta_value ) {
					if ( $get_user_info_meta_key == "rich_editing" || $get_user_info_meta_key == "syntax_highlighting" || $get_user_info_meta_key == "comment_shortcuts" || $get_user_info_meta_key == "admin_color" || $get_user_info_meta_key == "use_ssl" || $get_user_info_meta_key == "show_admin_bar_front" || $get_user_info_meta_key == "bp_xprofile_visibility_levels" || $get_user_info_meta_key == "session_tokens" || $get_user_info_meta_key == "wp_capabilities" || strpos( $get_user_info_meta_key, 'wp_sensei_course_enrolment_') === 0 || $get_user_info_meta_key == "wp_sensei_enrolment_providers_state"  ) {
						continue;
					}
					$certificate_heading = str_replace( '{{usermeta-' . $get_user_info_meta_key . '}}', strip_tags( $get_user_info_meta_value[0] ), $certificate_heading );
				}
			}

			$certificate_message = __( 'This is to certify that', 'sensei-certificates-addon' ) . " \r\n\r\n" . $student_name . " \r\n\r\n" . __( 'has completed the course', 'sensei-certificates-addon' );

			if ( isset( $this->certificate_template_fields['certificate_message']['text'] ) && '' != $this->certificate_template_fields['certificate_message']['text'] ) {

				$certificate_message = $this->certificate_template_fields['certificate_message']['text'];
				$certificate_message = str_replace( array( '{{learner}}', '{{course_title}}', '{{completion_date}}', '{{course_place}}' ), array( $student_name, $course_title, $date, get_bloginfo( 'name' ) ), $certificate_message );
				foreach ( $get_bp_field as $get_bp_field_key => $get_bp_field_value ) {
					$certificate_message = str_replace( '{{BP-' . $get_bp_field_key . '}}', strip_tags( $get_bp_field_value ), $certificate_message );
				}
				foreach ( $get_user_info->data as $get_user_info_key => $get_user_info_value ) {
					if ( $get_user_info_key == "user_pass" ) {
						continue;
					}
					$certificate_message = str_replace( '{{usermeta-' . $get_user_info_key . '}}', strip_tags( $get_user_info_value ), $certificate_message );
				}
				foreach ( $get_user_info_meta as $get_user_info_meta_key => $get_user_info_meta_value ) {
					if ( $get_user_info_meta_key == "rich_editing" || $get_user_info_meta_key == "syntax_highlighting" || $get_user_info_meta_key == "comment_shortcuts" || $get_user_info_meta_key == "admin_color" || $get_user_info_meta_key == "use_ssl" || $get_user_info_meta_key == "show_admin_bar_front" || $get_user_info_meta_key == "bp_xprofile_visibility_levels" || $get_user_info_meta_key == "session_tokens" || $get_user_info_meta_key == "wp_capabilities" || strpos( $get_user_info_meta_key, 'wp_sensei_course_enrolment_') === 0 || $get_user_info_meta_key == "wp_sensei_enrolment_providers_state"  ) {
						continue;
					}
					$certificate_message = str_replace( '{{usermeta-' . $get_user_info_meta_key . '}}', strip_tags( $get_user_info_meta_value[0] ), $certificate_message );
				}
			}

			$certificate_custom_message = '';
			if ( isset( $this->certificate_template_fields['certificate_custom_message']['text'] ) && '' != $this->certificate_template_fields['certificate_custom_message']['text'] ) {

				$certificate_custom_message = $this->certificate_template_fields['certificate_custom_message']['text'];
				$certificate_custom_message = str_replace( array( '{{learner}}', '{{course_title}}', '{{completion_date}}', '{{course_place}}' ), array( $student_name, $course_title, $date, get_bloginfo( 'name' ) ), $certificate_custom_message );
				foreach ( $get_bp_field as $get_bp_field_key => $get_bp_field_value ) {
					$certificate_custom_message = str_replace( '{{BP-' . $get_bp_field_key . '}}', strip_tags( $get_bp_field_value ), $certificate_custom_message );
				}
				foreach ( $get_user_info->data as $get_user_info_key => $get_user_info_value ) {
					if ( $get_user_info_key == "user_pass" ) {
						continue;
					}
					$certificate_custom_message = str_replace( '{{usermeta-' . $get_user_info_key . '}}', strip_tags( $get_user_info_value ), $certificate_custom_message );
				}
				foreach ( $get_user_info_meta as $get_user_info_meta_key => $get_user_info_meta_value ) {
					if ( $get_user_info_meta_key == "rich_editing" || $get_user_info_meta_key == "syntax_highlighting" || $get_user_info_meta_key == "comment_shortcuts" || $get_user_info_meta_key == "admin_color" || $get_user_info_meta_key == "use_ssl" || $get_user_info_meta_key == "show_admin_bar_front" || $get_user_info_meta_key == "bp_xprofile_visibility_levels" || $get_user_info_meta_key == "session_tokens" || $get_user_info_meta_key == "wp_capabilities" || strpos( $get_user_info_meta_key, 'wp_sensei_course_enrolment_') === 0 || $get_user_info_meta_key == "wp_sensei_enrolment_providers_state"  ) {
						continue;
					}
					$certificate_custom_message = str_replace( '{{usermeta-' . $get_user_info_meta_key . '}}', strip_tags( $get_user_info_meta_value[0] ), $certificate_custom_message );
				}
			}

			$certificate_course = $course_title;
			if ( isset( $this->certificate_template_fields['certificate_course']['text'] ) && '' != $this->certificate_template_fields['certificate_course']['text'] ) {

				$certificate_course = $this->certificate_template_fields['certificate_course']['text'];
				$certificate_course = str_replace( array( '{{learner}}', '{{course_title}}', '{{completion_date}}', '{{course_place}}' ), array( $student_name, $course_title, $date, get_bloginfo( 'name' ) ), $certificate_course );
				foreach ( $get_bp_field as $get_bp_field_key => $get_bp_field_value ) {
					$certificate_course = str_replace( '{{BP-' . $get_bp_field_key . '}}', strip_tags( $get_bp_field_value ), $certificate_course );
				}
				foreach ( $get_user_info->data as $get_user_info_key => $get_user_info_value ) {
					if ( $get_user_info_key == "user_pass" ) {
						continue;
					}
					$certificate_course = str_replace( '{{usermeta-' . $get_user_info_key . '}}', strip_tags( $get_user_info_value ), $certificate_course );
				}
				foreach ( $get_user_info_meta as $get_user_info_meta_key => $get_user_info_meta_value ) {
					if ( $get_user_info_meta_key == "rich_editing" || $get_user_info_meta_key == "syntax_highlighting" || $get_user_info_meta_key == "comment_shortcuts" || $get_user_info_meta_key == "admin_color" || $get_user_info_meta_key == "use_ssl" || $get_user_info_meta_key == "show_admin_bar_front" || $get_user_info_meta_key == "bp_xprofile_visibility_levels" || $get_user_info_meta_key == "session_tokens" || $get_user_info_meta_key == "wp_capabilities" || strpos( $get_user_info_meta_key, 'wp_sensei_course_enrolment_') === 0 || $get_user_info_meta_key == "wp_sensei_enrolment_providers_state"  ) {
						continue;
					}
					$certificate_course = str_replace( '{{usermeta-' . $get_user_info_meta_key . '}}', strip_tags( $get_user_info_meta_value[0] ), $certificate_course );
				}
			}

			$certificate_completion = $date;
			if ( isset( $this->certificate_template_fields['certificate_completion']['text'] ) && '' != $this->certificate_template_fields['certificate_completion']['text'] ) {

				$certificate_completion = $this->certificate_template_fields['certificate_completion']['text'];
				$certificate_completion = str_replace( array( '{{learner}}', '{{course_title}}', '{{completion_date}}', '{{course_place}}' ), array( $student_name, $course_title, $date, get_bloginfo( 'name' ) ), $certificate_completion );
				foreach ( $get_bp_field as $get_bp_field_key => $get_bp_field_value ) {
					$certificate_completion = str_replace( '{{BP-' . $get_bp_field_key . '}}', strip_tags( $get_bp_field_value ), $certificate_completion );
				}
				foreach ( $get_user_info->data as $get_user_info_key => $get_user_info_value ) {
					if ( $get_user_info_key == "user_pass" ) {
						continue;
					}
					$certificate_completion = str_replace( '{{usermeta-' . $get_user_info_key . '}}', strip_tags( $get_user_info_value ), $certificate_completion );
				}
				foreach ( $get_user_info_meta as $get_user_info_meta_key => $get_user_info_meta_value ) {
					if ( $get_user_info_meta_key == "rich_editing" || $get_user_info_meta_key == "syntax_highlighting" || $get_user_info_meta_key == "comment_shortcuts" || $get_user_info_meta_key == "admin_color" || $get_user_info_meta_key == "use_ssl" || $get_user_info_meta_key == "show_admin_bar_front" || $get_user_info_meta_key == "bp_xprofile_visibility_levels" || $get_user_info_meta_key == "session_tokens" || $get_user_info_meta_key == "wp_capabilities" || strpos( $get_user_info_meta_key, 'wp_sensei_course_enrolment_') === 0 || $get_user_info_meta_key == "wp_sensei_enrolment_providers_state"  ) {
						continue;
					}
					$certificate_completion = str_replace( '{{usermeta-' . $get_user_info_meta_key . '}}', strip_tags( $get_user_info_meta_value[0] ), $certificate_completion );
				}
			}

			$certificate_place = sprintf( __( 'At %s', 'sensei-certificates-addon' ), get_bloginfo( 'name' ) );
			if ( isset( $this->certificate_template_fields['certificate_place']['text'] ) && '' != $this->certificate_template_fields['certificate_place']['text'] ) {

				$certificate_place = $this->certificate_template_fields['certificate_place']['text'];
				$certificate_place = str_replace( array( '{{learner}}', '{{course_title}}', '{{completion_date}}', '{{course_place}}' ), array( $student_name, $course_title, $date, get_bloginfo( 'name' ) ), $certificate_place );
				foreach ( $get_bp_field as $get_bp_field_key => $get_bp_field_value ) {
					$certificate_place = str_replace( '{{BP-' . $get_bp_field_key . '}}', strip_tags( $get_bp_field_value ), $certificate_place );
				}
				foreach ( $get_user_info->data as $get_user_info_key => $get_user_info_value ) {
					if ( $get_user_info_key == "user_pass" ) {
						continue;
					}
					$certificate_place = str_replace( '{{usermeta-' . $get_user_info_key . '}}', strip_tags( $get_user_info_value ), $certificate_place );
				}
				foreach ( $get_user_info_meta as $get_user_info_meta_key => $get_user_info_meta_value ) {
					if ( $get_user_info_meta_key == "rich_editing" || $get_user_info_meta_key == "syntax_highlighting" || $get_user_info_meta_key == "comment_shortcuts" || $get_user_info_meta_key == "admin_color" || $get_user_info_meta_key == "use_ssl" || $get_user_info_meta_key == "show_admin_bar_front" || $get_user_info_meta_key == "bp_xprofile_visibility_levels" || $get_user_info_meta_key == "session_tokens" || $get_user_info_meta_key == "wp_capabilities" || strpos( $get_user_info_meta_key, 'wp_sensei_course_enrolment_') === 0 || $get_user_info_meta_key == "wp_sensei_enrolment_providers_state"  ) {
						continue;
					}
					$certificate_place = str_replace( '{{usermeta-' . $get_user_info_meta_key . '}}', strip_tags( $get_user_info_meta_value[0] ), $certificate_place );
				}
			}
			$output_fields = array(
				'certificate_heading'    => 'text_field',
				'certificate_message'    => 'textarea_field',
				'certificate_custom_message'    => 'textarea_field',
				'certificate_course'     => 'text_field',
				'certificate_completion' => 'text_field',
				'certificate_place'      => 'text_field',
			);

			foreach ( $output_fields as $meta_key => $function_name ) {

				if ( isset( $this->certificate_template_fields[ $meta_key ]['position']['x1'] ) ) {

					$font_settings = $this->get_certificate_font_settings_custom( $meta_key );

					call_user_func_array( array( $pdf_certificate, $function_name ), array( $fpdf, $$meta_key, $show_border, array( $this->certificate_template_fields[ $meta_key ]['position']['x1'], $this->certificate_template_fields[ $meta_key ]['position']['y1'], $this->certificate_template_fields[ $meta_key ]['position']['width'], $this->certificate_template_fields[ $meta_key ]['position']['height'] ), $font_settings ) );
				}
			}
		} else {
			wp_die( esc_html__( 'The certificate you are searching for does not exist.', 'sensei-certificates-addon' ), esc_html__( 'Certificate Error', 'sensei-certificates-addon' ) );
		}
	}

	public function get_certificate_font_settings_custom( $field_key = '' ) {

		$return_array = array();

		if ( isset( $this->certificate_template_fields[ $field_key ]['font']['color'] ) && '' != $this->certificate_template_fields[ $field_key ]['font']['color'] ) {
			$return_array['font_color'] = $this->certificate_template_fields[ $field_key ]['font']['color'];
		}

		if ( isset( $this->certificate_template_fields[ $field_key ]['font']['family'] ) && '' != $this->certificate_template_fields[ $field_key ]['font']['family'] ) {
			$return_array['font_family'] = $this->certificate_template_fields[ $field_key ]['font']['family'];
		}

		if ( isset( $this->certificate_template_fields[ $field_key ]['font']['style'] ) && '' != $this->certificate_template_fields[ $field_key ]['font']['style'] ) {
			$return_array['font_style'] = $this->certificate_template_fields[ $field_key ]['font']['style'];
		}

		if ( isset( $this->certificate_template_fields[ $field_key ]['font']['size'] ) && '' != $this->certificate_template_fields[ $field_key ]['font']['size'] ) {
			$return_array['font_size'] = $this->certificate_template_fields[ $field_key ]['font']['size'];
		}

		return $return_array;

	}

}

$obj = new WooThemes_Sensei_Certificates_Custom();
