<?php
/*
 * Plugin Name:         LearnDash Gravity Form Addon
 * Description:         This plugin will allow to add gravity form in course and lesson
 * Author:              #
 * Author URI:          #
 * Plugin URI:          #
 * Text Domain:         ld-gravityform-addon
 * Domain Path:         /languages
 * License:             GPLv3
 * License URI:         #
 * Version:             1.0.0
 * Requires at least:   5.0
 * Requires PHP:        7.0
 */


/**
 * Register and Enqueue Styles and Scripts - backend.
 */
add_action( 'admin_enqueue_scripts', 'ld_gravityform_style_script_backend' );
function ld_gravityform_style_script_backend() {

	// ld-gravityform custom JS - backend.
	wp_enqueue_script( 'ld-gravityform-custom-back-js', plugins_url() . '/ld-gform-addon/assets/js/ld-gform-custom-back.js', array('jquery'), '1.0', true ); 

	// datatable JS - backend.
    wp_enqueue_script( 'datatable-js','https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js', array('jquery'), '1.10.23', true);

    // datatable buttons JS - backend.
    wp_enqueue_script( 'datatable-buttons-js','https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js', array('jquery'), '1.6.5', true); 

    // datatable jszip JS - backend.
    wp_enqueue_script( 'datatable-jszip-js','https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js', array('jquery'), '3.1.3', true);

    // datatable pdfmake JS - backend.
    wp_enqueue_script( 'datatable-pdfmake-js','https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js', array('jquery'), '0.1.53', true);


    // datatable vfs JS - backend.
    wp_enqueue_script( 'datatable-vfs-js','https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js', array('jquery'), '0.1.53', true);

    // datatable buttons html5 JS - backend.
    wp_enqueue_script( 'datatable-buttons-html5-js','https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js', array('jquery'), '1.6.5', true);

    // datatable buttons print JS - backend.
    wp_enqueue_script( 'datatable-buttons-print-js','https://cdn.datatables.net/buttons/1.6.5/js/buttons.print.min.js', array('jquery'), '1.6.5', true);


	// define admin AJAX URL.
	wp_localize_script( 'ld-gravityform-custom-back-js', 'ldgravityform', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' )
	));

	// ld-gravityform custom CSS - backend.
	wp_enqueue_style( 'ld-gravityform-custom-back-css', plugins_url() . '/ld-gform-addon/assets/css/ld-gform-custom-back.css', array(), '1.0' ); 

	// datatable CSS - backend.
	wp_enqueue_style( 'datatable-css','https://cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css', array(), '1.10.23'  );

	// datatable buttons CSS - backend.
	wp_enqueue_style( 'datatable-css','https://cdn.datatables.net/buttons/1.6.5/css/buttons.dataTables.min.css', array(), '1.6.5'  );
}

/**
 * Register and Enqueue Styles and Scripts - frontend.
 */
add_action( 'wp_enqueue_scripts', 'ld_gravityform_style_script_frontend' );
function ld_gravityform_style_script_frontend() {

	// ld-gravityform custom JS - frontend.
	wp_enqueue_script( 'ld-gravityform-custom-front-js', plugins_url() . '/ld-gform-addon/assets/js/ld-gform-custom-front.js', array('jquery'), '1.0', true ); 

	
	// ld-gravityform custom CSS - frontend.
	wp_enqueue_style( 'ld-gravityform-custom-front-css', plugins_url() . '/ld-gform-addon/assets/css/ld-gform-custom-front.css', array(), '1.0' ); 
}

// include hook file.
include 'ld-gform-hook.php';
