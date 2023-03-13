<?php

/**
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 * @link        #
 * @since       1.0.0
 * @package     leandash-addon
 * Plugin Name: LearnDash Add-On 
 * Plugin URI:  #
 * Description: This plugin used for learndash and woocommerce customization.
 * Version:     1.1.0
 * Author:      Wisdmlabs
 * Author URI:  #
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: leandash-addon
 */

error_reporting( 0 );
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'LD_ADDON' ) ) {
	define( 'LD_ADDON', '1.1.0' );
}
if ( ! defined( 'LD_ADDON_URL' ) ) {
	define( 'LD_ADDON_URL', plugins_url( '/', __FILE__ ) );
}
if ( ! defined( 'LD_ADDON_PATH' ) ) {
	define( 'LD_ADDON_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'LD_ADDON_BASENAME' ) ) {
	define( 'LD_ADDON_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'LD_ADDON_FILEPATH' ) ) {
	define( 'LD_ADDON_FILEPATH', __FILE__ );
}
if ( ! defined( 'LD_ADDON_SLUG' ) ) {
	define( 'LD_ADDON_SLUG', 'leandash-addon' );
}
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */





// Order deletion command : Start

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    function delete_order_by_command ( $args, $assoc_args ) {
	    if ( $assoc_args['product_id'] && $assoc_args['start_date'] && $assoc_args['end_date'] ) {
	        $get_order_delete_status = delete_user_order( $assoc_args['product_id'], $assoc_args['start_date'], $assoc_args['end_date'] );
	        WP_CLI::success( "\n Deleted order id : $get_order_delete_status[0] \n Order id with more than 1 product not deleted : $get_order_delete_status[1]" );
	    }else{
	        WP_CLI::error( "args missing" );
	    } 
    }
    WP_CLI::add_command( 'wc order_deletion_tool', 'delete_order_by_command' );
}

function delete_user_order( $product_id, $start_date, $end_date ) {
	global $wpdb;

	$clear_timestamp  = strtotime( $start_date );
	$clear_timestamp_end  = strtotime( $end_date );

	$get_orders = $wpdb->get_col(
		
		"
        SELECT order_items.order_id
        FROM {$wpdb->prefix}woocommerce_order_items as order_items
        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
        LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
        WHERE posts.post_type = 'shop_order'
        AND posts.post_date BETWEEN '" . date( 'Y-m-d', $clear_timestamp ) . " 00:00:00' AND '" . date( 'Y-m-d', $clear_timestamp_end ) . " 23:59:59'
        AND order_items.order_item_type = 'line_item'
        AND order_item_meta.meta_key = '_product_id'
        AND order_item_meta.meta_value = '$product_id'
    	"
	);

	foreach ( $get_orders as $get_order_id ) {

		$order_data = wc_get_order( $get_order_id );
		$product_count = $order_data->get_item_count();

		if ( $product_count > 1 ) {
			$not_to_delete_order[] = '#' . $get_order_id;
		} else {
			$to_delete_order[] = '#' . $get_order_id;
			wp_delete_post( $get_order_id, true );
		}

	}

	$to_delete_order_implode = implode( ",",$to_delete_order );
	$not_to_delete_order_implode = implode( ",",$not_to_delete_order );

	return array( $to_delete_order_implode , $not_to_delete_order_implode );

}

// Order deletion command : End