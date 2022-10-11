<?php
/*
 * Plugin Name:         LearnDash Drip Topic Group - Backend
 * Description:         This plugin will create a Drip concept for Topic on Backend
 * Author:              #
 * Author URI:          #
 * Plugin URI:          #
 * Text Domain:         group-drip-topic
 * Domain Path:         /languages
 * License:             GPLv3
 * License URI:         #
 * Version:             1.0.0
 * Requires at least:   5.0
 * Requires PHP:        7.0
 */

// All Class instance are store in Global Variable $drip_topic_group
global $drip_topic_group_backend;

$file_name = 'src/classes/addon-drip-topic-by-group-backend.php';
if ( file_exists( dirname( __FILE__ ) . '/' . $file_name ) ) {
	include_once $file_name;
}

