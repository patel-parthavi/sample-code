<?php
/*
 * Plugin Name:         LearnDash Drip Topic Group - Frontend
 * Description:         This plugin will create a Drip concept for Topic on Frontend
 * Author:              #
 * Author URI:          #
 * Plugin URI:          #
 * Text Domain:         drip-group-topic
 * Domain Path:         /languages
 * License:             GPLv3
 * License URI:         #
 * Version:             1.0.0
 * Requires at least:   5.0
 * Requires PHP:        7.0
 */


/**
 * Register and Enqueue Styles and Scripts.
 */
add_action( 'wp_enqueue_scripts', 'drip_topic_style_script' );
function drip_topic_style_script() {

	// datepicker JS.
	wp_enqueue_script( 'jquery-ui-datepicker' );

	// date-time picker JS.
	wp_enqueue_script( 'jquery-datetimepicker-js', plugins_url() . '/drip-topic-group/assets/js/jquery-ui-timepicker-addon.js', array('jquery-ui-datepicker'), '1.6.3', true ); 

	// topic drip custom JS.
	wp_enqueue_script( 'topic-drip-custom-js', plugins_url() . '/drip-topic-group/assets/js/topic-drip-custom.js', array('jquery'), '1.0', true ); 

	// define admin AJAX URL.
	wp_localize_script( 'topic-drip-custom-js', 'topicdrip', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' )
	));

	// bootstrap JS.
	wp_enqueue_script( 'bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js', array('jquery'), '4.0.0', true );
	
	// datepicker CSS.
	wp_enqueue_style( 'jquery-datepicker-css', plugins_url() . '/drip-topic-group/assets/css/jquery-ui.css', array(), '1.12.1' ); 

	// date-time picker CSS.
	wp_enqueue_style( 'jquery-datetimepicker-css', plugins_url() . '/drip-topic-group/assets/css/jquery-ui-timepicker-addon.css', array(), '1.6.3' ); 
	
	// topic drip custom CSS.
	wp_enqueue_style( 'topic-drip-custom-css', plugins_url() . '/drip-topic-group/assets/css/topic-drip-custom.css', array(), '1.0' ); 

	// bootstrap CSS.
	wp_enqueue_style( 'bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css', array(), '4.0.0' ); 

}

// include shortcode and hook file.
include 'drip-topic-group-hook.php';
include 'drip-topic-group-shortcode.php';





