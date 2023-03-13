<?php
/*
 * Plugin Name:         LearnDash Drip Topic by Group
 * Description:         This plugin will create a Drip concept for Topic
 * Author:              #
 * Author URI:          #
 * Plugin URI:          #
 * Text Domain:         topic-drip-group
 * Domain Path:         /languages
 * License:             GPLv3
 * License URI:         #
 * Version:             1.0.0
 * Requires at least:   5.0
 * Requires PHP:        7.0
 */

// All Class instance are store in Global Variable $drip_topic_group
global $drip_topic_group;

$file_name = 'src/classes/addon-drip-topic-by-group.php';	
if ( file_exists( dirname( __FILE__ ) . '/' . $file_name ) ) {
	include_once $file_name;
}

