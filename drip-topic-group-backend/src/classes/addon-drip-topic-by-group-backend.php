<?php

namespace drip_topic_group_backend;

use uncanny_learndash_toolkit as toolkit;
use DateTime;
use DateTimeZone;
use Exception;
use LearnDash_Settings_Section;
use LearnDash_Theme_Register;
use SFWD_LMS;
use WP_Error;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class AddonDripTopicByGroupBackend
 *
 * @package drip_topic_group_backend
 */
class AddonDripTopicByGroupBackend {

	public static $learndash_post_types = array( 'sfwd-topic' );


	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'run_frontend_hooks' ) );

	}

	/*
	 * Initialize frontend actions and filters
	 */
	public static function run_frontend_hooks() {

		if ( true === self::dependants_exist() ) {

			// Call AJAX on click of save button , to save the date
			add_action( 'wp_ajax_nopriv_insert_form_data_backend', array( __CLASS__, 'insert_form_data_backend' ) );
			add_action( 'wp_ajax_insert_form_data_backend', array( __CLASS__, 'insert_form_data_backend' ) );

			// enqueue plugin's custom JS/CSS for backend
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts_func' ) );

			// enqueue plugin's custom JS/CSS for frontend
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'action__enqueue_scripts' ) );

			// Displaying "Available on" message for Single Topic page
			add_filter( 'learndash_content', array( __CLASS__, 'topic_visible_after' ), 10, 2 );

			// Displaying "Available on" message for Topic on course page
			add_filter( 'learndash_lesson_attributes', array( __CLASS__, 'filter__learndash_lesson_attributes' ), 10, 2 );

			include dirname( dirname( __FILE__ ) ) . '/includes/metabox-topic-group-drip-settings-addon.php';
			add_filter( 'learndash_header_tab_menu', array( __CLASS__, 'learndash_header_tab_menu_custom' ), 999, 3 );
		}

	}


	// Call AJAX on click of save button , to save the date
	public function insert_form_data_backend() {

		$topic_drip_group_date = $_POST['topic_drip_group_date'];
		$topicid               = $_POST['topicid'];
		$groupid               = $_POST['groupid'];

		if ( ! empty( $topic_drip_group_date ) ) {
			update_post_meta( $topicid, 'topic_drip_post_available_on_' . $groupid, strtotime( $topic_drip_group_date ) );
		} else {
			update_post_meta( $topicid, 'topic_drip_post_available_on_' . $groupid, '' );

		}

		die();
	}


	/**
	 * Here we call Lesson filter and pass Topic attributes
	 * Displaying "Available on" message for Topic on course page
	 *
	 * @return array of $attributes
	 */
	public function filter__learndash_lesson_attributes( $attributes, $lesson ) {

		$get_course_id = learndash_get_course_id( $lesson['post']->ID, true );
		$posts_topic   = learndash_get_topic_list( $lesson['post']->ID, $get_course_id );

		if ( ! empty( $posts_topic ) && is_array( $posts_topic ) ) {

			foreach ( $posts_topic as $posts_topic_key => $posts_topic_value ) {

				$user_groups = learndash_get_users_group_ids( get_current_user_id() );

				if ( ! empty( $user_groups ) ) {

					foreach ( $user_groups as $user_groups_key => $user_groups_value ) {

						$topic_access_from = get_post_meta( $posts_topic_value->ID, 'topic_drip_post_available_on_' . $user_groups_value, true );

						if ( $topic_access_from > time() ) {

							$new_date = date( 'F d, Y h:i A', $topic_access_from );

							$attributes[] = array(
								'label' => sprintf(
									esc_html_x( 'Available on %s', 'Available on date label', 'group-drip-topic' ),
									$new_date
								),
								'class' => "ld-status-waiting ld-tertiary-background ld-table-list-item-$posts_topic_value->ID",
								'icon'  => 'ld-icon-calendar',
							);

						}
					}
				}
			}
		}

		return $attributes;

	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean || string Return either true or name of function or plugin
	 */
	public static function dependants_exist() {

		/* Checks for LearnDash */
		global $learndash_post_types;
		if ( ! isset( $learndash_post_types ) ) {
			return 'Plugin: LearnDash';
		}

		// Return true if no dependency or dependency is available
		return true;

	}


	/**
	 * @param $date
	 *
	 * @return array|false|int
	 */
	public static function reformat_date( $date ) {

		if ( is_array( $date ) ) {
			if ( isset( $date['aa'] ) ) {
				$date['aa'] = intval( $date['aa'] );
			} else {
				$date['aa'] = 0;
			}

			if ( isset( $date['mm'] ) ) {
				$date['mm'] = intval( $date['mm'] );
			} else {
				$date['mm'] = 0;
			}

			if ( isset( $date['jj'] ) ) {
				$date['jj'] = intval( $date['jj'] );
			} else {
				$date['jj'] = 0;
			}

			if ( isset( $date['hh'] ) ) {
				$date['hh'] = intval( $date['hh'] );
			} else {
				$date['hh'] = 0;
			}

			if ( isset( $date['mn'] ) ) {
				$date['mn'] = intval( $date['mn'] );
			} else {
				$date['mn'] = 0;
			}

			if ( ( ! empty( $date['aa'] ) ) && ( ! empty( $date['mm'] ) ) && ( ! empty( $date['jj'] ) ) ) {

				$date_string = sprintf( '%04d-%02d-%02d %02d:%02d:00', intval( $date['aa'] ), intval( $date['mm'] ), intval( $date['jj'] ), intval( $date['hh'] ), intval( $date['mn'] ) );
				$gmt_offset  = get_option( 'gmt_offset' );
				if ( empty( $gmt_offset ) ) {
					$gmt_offset = 0;
				}

				// get ms difference for time offset from GMT
				// could be +ve or -ve depending on timezone
				// If GMT offset is +ve, subtract from time to get time in GMT since user is ahead of GMT
				// If GMT offset is -ve, add time to get GMT time since user is behind GMT
				// -1 is the logic to add/subtract offset time to implement above two line logic
				$offset      = ( $gmt_offset * ( 60 * 60 ) ) * - 1; // MS difference for time offset
				$return_time = (int) strtotime( $date_string ) + $offset;

				return $return_time;
			} else {
				return 0;
			}
		} else {
			return $date;
		}
	}

	/**
	 * @param $timestamp
	 *
	 * @return bool
	 */
	public static function is_timestamp( $timestamp ) {
		if ( is_numeric( $timestamp ) && strtotime( date( 'd-m-Y H:i:s', $timestamp ) ) === (int) $timestamp ) {
			return $timestamp;
		} else {
			return false;
		}
	}


	/**
	 *
	 */
	public static function admin_enqueue_scripts_func() {

		global $post;

		if ( 'sfwd-topic' === $post->post_type ) {

			wp_enqueue_style( 'dataTables-backend', 'https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css' );
			wp_enqueue_script( 'dataTables-backend', 'https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js', array( 'jquery' ), '1.0.0', true );

			// datepicker JS
			wp_enqueue_script( 'jquery-ui-datepicker' );

			// date-time picker JS
			wp_enqueue_script( 'jquery-datetimepicker-js-backend', plugins_url() . '/drip-topic-group/assets/js/jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker' ), '1.6.3', true );

			// Custom backend JS
			wp_enqueue_script( 'topic-drip-custom-js-backend', plugins_url( '/drip-topic-group-backend/assets/js/topic-drip-custom-back.js' ), array( 'jquery' ), '1.0.0', true );

			// Define AJAX URL
			wp_localize_script(
				'topic-drip-custom-js-backend',
				'topicdripbackend',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
				)
			);

			// datepicker CSS
			wp_enqueue_style( 'jquery-datepicker-css-backend', plugins_url() . '/drip-topic-group/assets/css/jquery-ui.css', array(), '1.12.1' );

			// date-time picker CSS
			wp_enqueue_style( 'jquery-datetimepicker-css-backend', plugins_url() . '/drip-topic-group/assets/css/jquery-ui-timepicker-addon.css', array(), '1.6.3' );

			// Custom backend CSS
			wp_enqueue_style( 'group-drip-topic-css-backend', plugins_url( '/drip-topic-group-backend/assets/css/topic-drip-custom-back.css' ), array(), '1.0.0' );

		}
	}

	/**
	 * enqueue plugin's custom JS/CSS
	 */
	public static function action__enqueue_scripts() {

		// Custom frontend CSS
		wp_enqueue_style( 'group-drip-topic-css-frontend', plugins_url( '/drip-topic-group-backend/assets/css/topic-drip-custom-front.css' ), array(), '1.0.0' );

		// Custom backend CSS
		wp_enqueue_script( 'topic-drip-custom-js-frontend', plugins_url( '/drip-topic-group-backend/assets/js/topic-drip-custom-front.js' ), array( 'jquery' ), '1.0.0', true );

	}

	/**
	 * @param $tabs
	 * @param $menu_tab_key
	 * @param $screen_post_type
	 *
	 * @return mixed
	 */
	public static function learndash_header_tab_menu_custom( $tabs, $menu_tab_key, $screen_post_type ) {

		if ( $tabs ) {
			foreach ( $tabs as $k => $tab ) {
				if ( 'sfwd-topic-settings' === $tab['id'] ) {
					$tabs[ $k ]['metaboxes'][] = 'learndash-topic-group-drip-settings';
					break;
				}
			}
		}

		return $tabs;
	}

	/**
	 * @param $time
	 *
	 * @return int
	 */
	public static function adjust_for_timezone_difference( $time ) {
		$gmt_offset = get_option( 'gmt_offset' );
		if ( empty( $gmt_offset ) ) {
			$gmt_offset = 0;
		}
		// get ms difference for time offset from GMT
		// could be +ve of -ve depending on timezone
		$offset = $gmt_offset * ( 60 * 60 );

		return (int) $time + $offset;
	}


	/**
	 * Displaying "Available on" message for Single Topic page
	 */
	public static function topic_visible_after( $content, $post ) {

		if ( 'sfwd-topic' === $post->post_type ) {

			$user_groups = learndash_get_users_group_ids( get_current_user_id() );

			if ( ! empty( $user_groups ) ) {

				foreach ( $user_groups as $user_groups_key => $user_groups_value ) {

					$topic_access_from = get_post_meta( $post->ID, 'topic_drip_post_available_on_' . $user_groups_value, true );

					// $default_course_id = learndash_get_course_id( $post->ID, true );

					if ( $topic_access_from > time() ) {

						$content = SFWD_LMS::get_template(
							'learndash_course_lesson_not_available',
							array(
								'user_id'                 => get_current_user_id(),
								'lesson_id'               => $post->ID,
								'lesson_access_from_int'  => $topic_access_from,
								'lesson_access_from_date' => learndash_adjust_date_time_display( $topic_access_from ),
								'context'                 => 'lesson',
							),
							false
						);

						if ( $content ) {
							return $content;
						} else {
							return '<div class=\'notavailable_message\'>' . array( $content, $post, $topic_access_from ) . '</div>';
						}
					}
				}
			}
		}

		return $content;

	}

}

$obj = new AddonDripTopicByGroupBackend();
