<?php
/*
 * Plugin Name:         Sensei Certificates Addon
 * Description:         This plugin will create custom fields to be displayed in Sensei Certificates
 * Author:              #
 * Author URI:          https://wisdmlabs.com
 * Plugin URI:          https://wisdmlabs.com
 * Text Domain:         sensei-certificates-addon
 * Domain Path:         /languages
 * License:             GPLv3
 * License URI:         #
 * Version:             1.0.0
 * Requires at least:   5.0
 * Requires PHP:        7.0
 */

// Check if sensei certificates plugin is active or not , If not die.
register_activation_hook( __FILE__, 'check_sensei_certificates_plugin_active' );
function check_sensei_certificates_plugin_active() {
	if ( ! is_plugin_active( 'sensei-certificates/woothemes-sensei-certificates.php' ) and current_user_can( 'activate_plugins' ) ) {
		wp_die( 'Sorry, but this plugin requires the sensei-certificates to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>' );
	}
}

// Removing "sensei_certificates_before_pdf_output" hook from main plugin
add_action( 'after_setup_theme', 'remove_hook' );  
function remove_hook() {
	remove_filters_for_anonymous_class( 'sensei_certificates_before_pdf_output', 'WooThemes_Sensei_Certificates', 'certificate_text' ,10 ,2 );
}

function remove_filters_for_anonymous_class( $hook_name = '', $class_name ='', $method_name = '', $priority = 0 ) {
	global $wp_filter;

	if ( ! isset($wp_filter[$hook_name][$priority]) || ! is_array($wp_filter[$hook_name][$priority]) )
		return false;

	foreach( (array) $wp_filter[$hook_name][$priority] as $unique_id => $filter_array ) {

		if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {

			if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) == $class_name && $filter_array['function'][1] == $method_name ) {
				unset( $wp_filter[$hook_name]->callbacks[$priority][$unique_id] );
			}
		}
		
	}
	return true;
}


// Adding custom field variable to sensei certificates plugin's JS.
add_action( 'admin_enqueue_scripts', 'sensei_certificate_template_admin_enqueue_scripts_custom', 999 );
function sensei_certificate_template_admin_enqueue_scripts_custom() {

	global $post, $woothemes_sensei_certificates, $wp_version;

	$screen = get_current_screen();

	if ( 'certificate_template' == $screen->id ) {

		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_media();

		wp_enqueue_script( 'imgareaselect' );
		wp_enqueue_style( 'imgareaselect' );

	}

	if ( in_array( $screen->id, array( 'certificate_template' ) ) ) {

		$sensei_certificate_templates_params = array(
			'primary_image_width'  => '',
			'primary_image_height' => '',
		);

		if ( 'certificate_template' == $screen->id ) {

			$attachment = null;
			$image_ids  = get_post_meta( $post->ID, '_image_ids', true );

			if ( is_array( $image_ids ) && isset( $image_ids[0] ) && $image_ids[0] ) {
				if ( is_numeric( $image_ids[0] ) ) {
					$attachment = wp_get_attachment_metadata( $image_ids[0] );
				}
			}

			$sensei_certificate_templates_params = array(
				'_certificate_heading_pos'    => __( 'Heading', 'sensei-certificates-addon' ),
				'_certificate_message_pos'    => __( 'Message', 'sensei-certificates-addon' ),
				'_certificate_custom_message_pos'    => __( 'Custom Message', 'sensei-certificates-addon' ),
				'_certificate_course_pos'     => __( 'Course', 'sensei-certificates-addon' ),
				'_certificate_completion_pos' => __( 'Completion Date', 'sensei-certificates-addon' ),
				'_certificate_place_pos'      => __( 'Place', 'sensei-certificates-addon' ),
				'done_label'                  => __( 'Done', 'sensei-certificates-addon' ),
				'set_position_label'          => __( 'Set Position', 'sensei-certificates-addon' ),
				'post_id'                     => $post->ID,
				'primary_image_width'         => isset( $attachment['width'] ) && $attachment['width'] ? $attachment['width'] : '0',
				'primary_image_height'        => isset( $attachment['height'] ) && $attachment['height'] ? $attachment['height'] : '0',
			);

		}

		wp_enqueue_script( 'sensei_certificate_templates_admin', $woothemes_sensei_certificates->plugin_url . 'assets/dist/js/admin.js', array( 'jquery' ) );
		wp_localize_script( 'sensei_certificate_templates_admin', 'sensei_certificate_templates_params', $sensei_certificate_templates_params );

		wp_enqueue_style( 'sensei_certificate_templates_admin_styles', $woothemes_sensei_certificates->plugin_url . 'assets/dist/css/admin.css' );

	}

	if ( in_array( $screen->id, array( 'course' ) ) ) {

		wp_enqueue_script( 'sensei_course_certificate_templates_admin', $woothemes_sensei_certificates->plugin_url . 'assets/dist/js/course.js', array( 'jquery' ) );

	}

}

// Include hook file.
include 'sensei-certificates-hook.php';

// Include class file to show data of custom field inside certificate PDF.
$file_name = 'class-woothemes-sensei-certificates-custom.php';
include_once dirname( __FILE__ ) . '/' . $file_name;
