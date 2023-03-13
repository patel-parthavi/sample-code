<?php

namespace drip_topic_group;

use uncanny_pro_toolkit;
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
 * Class AddonDripTopicByGroup
 * @package drip_topic_group
 */
class AddonDripTopicByGroup {

	public static $learndash_post_types = array( 'sfwd-topic' );

	public static $access_metabox_key = 'learndash-topic-access-settings';

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'run_frontend_hooks' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'update_drip_date_topic_data' ) );
	}

	/*
	 * Initialize frontend actions and filters
	 */
	public static function run_frontend_hooks() {
		if ( true === self::dependants_exist() ) {

			self::run_backwards_compatibility_update();

			add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_enqueue_scripts_func' ] );

			// enqueue plugin's custom JS/CSS
			add_action( 'wp_enqueue_scripts', [ __CLASS__, 'action__enqueue_scripts' ] );

			// Legacy - group access settings
			add_filter( 'learndash_post_args', array( __CLASS__, 'add_group_access_to_post_args_legacy' ) );

			// 3.0+ - Save custom topic settings field
			add_filter( 'learndash_metabox_save_fields', array( __CLASS__, 'save_topic_custom_meta' ), 60, 3 );

			# Change again when the option is called on "Edit topic" page
			add_filter( 'sfwd-lessons_display_settings', array( __CLASS__, 'change_topic_setting' ) );

			# Change shortcodes and hooks to show the topic because there is no hooking point to control it, so I change entire screen
			add_action( 'after_setup_theme', array( __CLASS__, 'change_hooks_and_shortcodes' ), 1 );

			#Convert String DateTime to UnixTimeStamp
			add_action( 'admin_init', array( __CLASS__, 'reformat_date_to_unix' ), 999 );

			///Add filter for LD Notifications
			add_filter( 'ld_lesson_access_from', array( __CLASS__, 'ld_topic_access_from_func' ), 99999, 3 );


			// Displaying "Available on" message for Topic
			add_filter( 'learndash_lesson_attributes', array( __CLASS__, 'filter__learndash_lesson_attributes' ), 10, 2 );



			include( dirname( dirname( __FILE__ ) ) . '/includes/metabox-topic-group-drip-settings-addon.php' );
			add_filter( 'learndash_header_tab_menu', [ __CLASS__, 'learndash_header_tab_menu_custom' ], 999, 3 );
		}

	}



	/**
	 * Here we call topic filter and pass Topic attributes
	 * Displaying "Available on" message for Topic
	 *
	 * @return array of $attributes
	 *
	 */
	public function filter__learndash_lesson_attributes( $attributes, $lesson ) {

		$user = wp_get_current_user();
		
		$args_topic = array(
		    'posts_per_page'   => -1,
		    'post_type'        => 'sfwd-topic',
		    'post_status'    => 'publish',
		    'meta_query'    => array(
		        array(
		            'key'       => 'lesson_id',
		            'value'     => $lesson['post']->ID,
		            'compare'   => '=',
		        ),
		    ),
		);

		$posts_topic = get_posts( $args_topic );

		foreach ( $posts_topic as $posts_topic_key => $posts_topic_value ) {

			$topic_access_from = self::get_topic_access_from( $posts_topic_value->ID, $user->ID );

			if ( in_array( 'administrator', $user->roles ) ) {
				continue;
			}
			
			if ( 'Available' === (string) $topic_access_from || empty ( $topic_access_from ) ) {
				continue;
			}

			if ( $topic_access_from > time() ) { 

				$new_date = date( 'F d, Y H:i A', $topic_access_from );

				$attributes[] = array(
						'label' => sprintf(
						// translators: placeholders: Date when topic will be available
							esc_html_x( 'Available on %s', 'Available on date label', 'topic-drip-group' ),
							$new_date
						),
						'class' => "ld-status-waiting ld-tertiary-background ld-table-list-item-$posts_topic_value->ID",
						'icon'  => 'ld-icon-calendar',
					);

			}

		}
		
		return $attributes;

	}




	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean || string Return either true or name of function or plugin
	 *
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

	public static function run_backwards_compatibility_update() {


		// script only run once, check option
		$run_once = get_option( 'uo_drip_compatibility_topic', 'no' );


		if ( 'yes' !== $run_once ) {

			// Get all topic post types
			$post_list = get_posts( array(
				'numberposts' => - 1,
				'post_type'   => 'sfwd-topic',
			) );

			// loop through post types
			foreach ( $post_list as $post ) {

				// script done, only run once, set option
				$all_other_users_date = get_post_meta( $post->ID, stripslashes( __CLASS__ ) . '-all', true );

				if ( $all_other_users_date ) {

					// We need to change the data to a timestamp if a array is returned
					if ( is_array( $all_other_users_date ) ) {
						$all_other_users_date = self::reformat_date( $all_other_users_date );
						$all_other_users_date = learndash_adjust_date_time_display( $all_other_users_date );
					}

					if ( self::is_timestamp( $all_other_users_date ) ) {

						// Get the topic options
						$original_option = get_post_meta( $post->ID, '_sfwd-topic', true );

						// Set the native drip date to the all users drip date since that will be the default and we are not using the -all users custom meta data anymore
						$original_option['sfwd-topic_visible_after_specific_date'] = $all_other_users_date;

						delete_post_meta( $post->ID, stripslashes( __CLASS__ ) . '-all' );
						update_post_meta( $post->ID, '_sfwd-topic', $original_option );
					}
				}
			}

			// script done, only run once, set option
			update_option( 'uo_drip_compatibility_topic', 'yes', true );
		}
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

				//get ms difference for time offset from GMT
				//could be +ve or -ve depending on timezone
				//If GMT offset is +ve, subtract from time to get time in GMT since user is ahead of GMT
				//If GMT offset is -ve, add time to get GMT time since user is behind GMT
				//-1 is the logic to add/subtract offset time to implement above two line logic
				$offset      = ( $gmt_offset * ( 60 * 60 ) ) * - 1; //MS difference for time offset
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
	 * Get $_POST data of Drip Topic Date
	 *
	 */
	public function update_drip_date_topic_data() {
		$data = $_POST;
		self::update_drip_date_topic( $data );
	}

	/**
	 * @param $data
	 *
	 */
	public static function update_drip_date_topic( $data ) {
		// validate inputs
		$group_id = absint( $data['group_id'] );
		$post_id  = absint( $data['post_id'] );
		$action   = $data['action'];
		if ( ! empty( $group_id ) && ! empty( $post_id ) ) {
			if ( 'remove' === $action ) {
				delete_post_meta( $post_id, stripslashes( __CLASS__ ) . '-' . $group_id );
				self::unset_notifications( $post_id );

			} else {

				$month  = absint( $data['month'] );
				$day    = absint( $data['day'] );
				$year   = absint( $data['year'] );
				$hour   = absint( $data['hour'] );
				$minute = absint( $data['minute'] );

				if ( 0 === $month || 0 === $day || 0 === $year ) {
					wp_send_json_error( [
							'success' => false,
							'message' => 'invalid date',
						]
					);
				}

				// Format to test agianst
				$format = 'Y-m-d H:i:s';

				// Add leading zero to single digit dates
				$_month  = str_pad( (string) $month, 2, '0', STR_PAD_LEFT );
				$_day    = str_pad( (string) $day, 2, '0', STR_PAD_LEFT );
				$_hour   = str_pad( (string) $hour, 2, '0', STR_PAD_LEFT );
				$_minute = str_pad( (string) $minute, 2, '0', STR_PAD_LEFT );

				$formatted_date = (string) $year . '-' . $_month . '-' . $_day . ' ' . $_hour . ':' . $_minute . ':00';
				$_date          = DateTime::createFromFormat( $format, $formatted_date );

				// Check if the date is valid
				if ( $_date && $_date->format( $format ) === $formatted_date ) {
					$complete_date = [
						'aa' => $year,
						'mm' => $month,
						'jj' => $day,
						'hh' => $hour,
						'mn' => $minute,
					];

					$date = self::reformat_date( $complete_date );

					update_post_meta( $post_id, stripslashes( __CLASS__ ) . '-' . $group_id, $date );
					self::set_notifications( $post_id );

				} else {
					
				}
			}
		}

	}

	/**
	 * @param $topic_id
	 *
	 * @throws Exception
	 */
	public static function unset_notifications( $topic_id ) {
		if ( function_exists( 'learndash_notifications_send_notifications' ) ) {
			$course_id = learndash_get_course_id( $topic_id );
			if ( 0 === (int) $course_id && isset( $_REQUEST['ld-course-switcher'] ) ) {
				preg_match( "/course_id=[^&]*/", $_REQUEST['ld-course-switcher'], $parse_query );
				if ( $parse_query ) {
					$course_id = (int) str_replace( 'course_id=', '', $parse_query[0] );
				}
			}
			$group_ids = learndash_get_course_groups( $course_id );
			$user_ids  = [];
			if ( $group_ids ) {
				foreach ( $group_ids as $group_id ) {
					$users = learndash_get_groups_user_ids( $group_id );
					if ( $users ) {
						foreach ( $users as $user_id ) {
							$user_ids[ $user_id ] = $user_id;
						}
					}
				}
			}

			if ( $user_ids ) {
				foreach ( $user_ids as $user_id => $u_id ) {
					learndash_notifications_delete_delayed_emails_by_user_id_lesson_id( $user_id, $topic_id );
					delete_user_meta( $user_id, 'ld_sent_notification_topic_available_' . $topic_id );
					delete_user_meta( $user_id, 'uo_ld_sent_notification_topic_available_' . $topic_id );
				}
			}
		}
	}

	/**
	 * @param $topic_id
	 *
	 * @throws Exception
	 */
	public static function set_notifications( $topic_id ) {
		if ( function_exists( 'learndash_notifications_send_notifications' ) ) {
			$course_id = learndash_get_course_id( $topic_id );
			//Logic for course builder
			if ( 0 === (int) $course_id && ( isset( $_REQUEST['course_id'] ) ) && ( ! empty( $_REQUEST['course_id'] ) ) ) {
				$course_id = intval( $_GET['course_id'] );
			}

			if ( 0 === (int) $course_id && isset( $_REQUEST['ld-course-switcher'] ) ) {
				preg_match( "/course_id=[^&]*/", $_REQUEST['ld-course-switcher'], $parse_query );
				if ( $parse_query ) {
					$course_id = (int) str_replace( 'course_id=', '', $parse_query[0] );
				}
			}

			$group_ids = learndash_get_course_groups( $course_id );
			$user_ids  = [];
			if ( $group_ids ) {
				foreach ( $group_ids as $group_id ) {
					$users = learndash_get_groups_user_ids( $group_id );
					if ( $users ) {
						foreach ( $users as $user_id ) {
							$user_ids[ $user_id ] = $user_id;
						}
					}
				}
			}

			if ( $user_ids ) {
				foreach ( $user_ids as $user_id => $u_id ) {
					learndash_notifications_delete_delayed_emails_by_user_id_lesson_id( $user_id, $topic_id );

					delete_user_meta( $user_id, 'ld_sent_notification_topic_available_' . $topic_id );
					delete_user_meta( $user_id, 'uo_ld_sent_notification_topic_available_' . $topic_id );

					$topic_access_from = self::ld_topic_access_group( $topic_id, $user_id, $course_id );
					if ( ! is_null( $topic_access_from ) ) {
						self::manually_set_notification( $user_id, $course_id, $topic_id, $topic_access_from );
						update_user_meta( $user_id, 'uo_ld_sent_notification_topic_available_' . $topic_id, $topic_access_from );
					}
				}
			}
		}
	}

	/**
	 * @param      $topic_id
	 * @param      $user_id
	 *
	 * @param bool $course_id
	 *
	 * @return bool|mixed|string
	 * @throws Exception
	 */
	private static function ld_topic_access_group( $topic_id, $user_id, $course_id = false, $access_from = '' ) {
		if ( false === $course_id ) {
			$course_id = learndash_get_course_id( $topic_id );
		}
		$user_groups = learndash_get_users_group_ids( $user_id );

		//No group found, assumption: Available
		if ( empty( $user_groups ) ) {
			$default = get_post_meta( $topic_id, stripslashes( __CLASS__ ) . '-all', true );
			if ( ! empty( $default ) ) {
				if ( ! self::is_timestamp( $default ) ) {
					return strtotime( $default );
				}

				return $default;
			} else {

				return $access_from;
			}
		}

		$group_dates = array();
		foreach ( $user_groups as $group_id ) {
			$date = get_post_meta( $topic_id, stripslashes( __CLASS__ ) . '-' . $group_id, true );
			if ( ! empty( $date ) ) {
				if ( self::is_timestamp( $date ) ) {
					$group_dates[ $group_id ] = $date;
				} else {
					$group_dates[ $group_id ] = strtotime( $date );
				}
			}
		}

		//Array contains Group Dates!
		asort( $group_dates );
		$gmt_date_time = new DateTime();
		$gmt_date_time->setTimezone( new DateTimeZone( 'GMT' ) );
		$time_now = strtotime( $gmt_date_time->format( 'Y-m-d H:i:s' ) );
		$return   = false;
		if ( ! empty( $group_dates ) ) {
			foreach ( $user_groups as $group_id ) {

				if ( ! empty( $group_dates[ $group_id ] ) && learndash_group_has_course( $group_id, $course_id ) ) {

					if ( absint( $time_now ) < absint( $group_dates[ $group_id ] ) ) {
						$return = false;
					} elseif ( absint( $time_now ) >= absint( $group_dates[ $group_id ] ) ) {

						$ld_access = self::ld_topic_access_from_inherited_from_ld( $topic_id, $user_id, $course_id );
						if ( self::is_timestamp( $ld_access ) ) {
							$return = $ld_access;
						} else {
							$return = 'Available';
						}
					}
				}
			}
		} else {
			//No Group Dates found
			$default = get_post_meta( $topic_id, stripslashes( __CLASS__ ) . '-all', true );
			if ( ! empty( $default ) ) {
				if ( ! self::is_timestamp( $default ) ) {
					return strtotime( $default );
				}

				return $default;
			}

			$ld_access = self::ld_topic_access_from_inherited_from_ld( $topic_id, $user_id, $course_id );
			if ( self::is_timestamp( $ld_access ) ) {
				$return = $ld_access;
			} else {
				$return = 'Available';
			}
		}

		if ( false === $return ) {
			foreach ( $group_dates as $group_id => $date ) {
				if ( learndash_group_has_course( $group_id, $course_id ) ) {
					return $date;
				}
			}
		}

		return $return;
	}

	/**
	 * Get timestamp of when user has access to topic
	 *
	 * @param int $topic_id
	 * @param int $user_id
	 *
	 * @return int  timestamp
	 * @since 2.1.0
	 *
	 */
	public static function ld_topic_access_from_inherited_from_ld( $topic_id, $user_id, $course_id = null ) {
		$return = null;

		if ( is_null( $course_id ) ) {
			$course_id = learndash_get_course_id( $topic_id );
		}

		$courses_access_from = ld_course_access_from( $course_id, $user_id );
		if ( empty( $courses_access_from ) ) {
			$courses_access_from = learndash_user_group_enrolled_to_course_from( $user_id, $course_id );
		}

		$visible_after = learndash_get_setting( $topic_id, 'visible_after' );
		if ( $visible_after > 0 ) {

			// Adjust the Course acces from by the number of days. Use abs() to ensure no negative days.
			$topic_access_from = $courses_access_from + abs( $visible_after ) * 24 * 60 * 60;
			$topic_access_from = apply_filters( 'ld_topic_access_from__visible_after', $topic_access_from, $topic_id, $user_id );

			$current_timestamp = time();
			if ( $current_timestamp < $topic_access_from ) {
				$return = $topic_access_from;
			}

		} else {
			$visible_after_specific_date = learndash_get_setting( $topic_id, 'visible_after_specific_date' );
			if ( ! empty( $visible_after_specific_date ) ) {
				if ( ! is_numeric( $visible_after_specific_date ) ) {
					// If we a non-numberic value like a date stamp Y-m-d hh:mm:ss we want to convert it to a GMT timestamp
					$visible_after_specific_date = learndash_get_timestamp_from_date_string( $visible_after_specific_date, true );
				}

				$current_time = time();

				if ( $current_time < $visible_after_specific_date ) {
					$return = apply_filters( 'ld_topic_access_from__visible_after_specific_date', $visible_after_specific_date, $topic_id, $user_id );
				}
			}
		}

		return $return;
	}


	# Change the shortcode

	/**
	 * @param $user_id
	 * @param $course_id
	 * @param $topic_id
	 * @param $topic_access_from
	 */
	public static function manually_set_notification( $user_id, $course_id, $topic_id, $topic_access_from ) {
		$notifications = learndash_notifications_get_notifications( 'lesson_available' );
		foreach ( $notifications as $n ) {
			self::insert_delayed_notification( $n, $user_id, $course_id, $topic_id, $topic_access_from );
		}
	}

	/**
	 * @param $notification
	 * @param $user_id
	 * @param $course_id
	 * @param $topic_id
	 * @param $topic_access_from
	 */
	public static function insert_delayed_notification( $notification, $user_id, $course_id, $topic_id, $topic_access_from ) {

		// Get recipient
		$recipients = learndash_notifications_get_recipients( $notification->ID );

		// If notification doesn't have recipient, exit
		if ( empty( $recipients ) ) {
			return;
		}

		// Get recipients emails
		$emails = learndash_notifications_get_recipients_emails( $recipients, $user_id, $course_id );

		global $ld_notifications_shortcode_data;
		$ld_notifications_shortcode_data = array(
			'user_id'         => $user_id,
			'course_id'       => $course_id,
			'lesson_id'       => $topic_id,
			'topic_id'        => $topic_id,
			'assignment_id'   => null,
			'quiz_id'         => null,
			'question_id'     => null,
			'notification_id' => $notification->ID,
			'group_id'        => null,
		);

		$shortcode_data = $ld_notifications_shortcode_data;
		$bcc            = learndash_notifications_get_bcc( $notification->ID );
		//$update_where   = [];

		/**
		 * Action hook before sending out notification or save it to database
		 *
		 * @param array $shortcode_data Notification trigger data that trigger this notification sending
		 */
		do_action( 'learndash_notification_before_send', $shortcode_data );

		if ( isset( $topic_access_from ) && $topic_access_from > time() ) {

			$sent_on = $topic_access_from;

			$data = array(
				'title'          => do_shortcode( $notification->post_title ),
				'message'        => $notification->post_content,
				'recipient'      => maybe_serialize( $emails ),
				'shortcode_data' => maybe_serialize( $shortcode_data ),
				'sent_on'        => $sent_on,
				'bcc'            => maybe_serialize( $bcc ),
			);

			global $wpdb;
			$wpdb->query( "INSERT INTO {$wpdb->prefix}ld_notifications_delayed_emails (title, message, recipient, shortcode_data, sent_on, bcc)	VALUES ( '{$notification->post_title}', '{$notification->post_content}', '" . maybe_serialize( $emails ) . "', '" . maybe_serialize( $shortcode_data ) . "', '{$sent_on}', '" . maybe_serialize( $bcc ) . "')" );
		}

	}

	/**
	 *
	 */
	public static function admin_enqueue_scripts_func() {
		global $post;
		if ( 'sfwd-topic' === $post->post_type ) {
			wp_enqueue_style( 'dataTables', 'https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css' );
			wp_enqueue_script( 'dataTables', 'https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js', array( 'jquery' ), '1.0.0', true );
		}
	}

	/**
	 * enqueue plugin's custom JS/CSS
	*/
	public static function action__enqueue_scripts() {
		wp_enqueue_style( 'group-drip-topic-css', plugins_url( '/drip-topic-by-group/assets/css/group-drip-topic.css' ), array(), '1.0.0' );
		wp_enqueue_script( 'group-drip-topic-js', plugins_url( '/drip-topic-by-group/assets/js/group-drip-topic.js' ), array( 'jquery' ), '1.0.0', true );
		
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
	 * @param $access_from
	 * @param $topic_id
	 * @param $user_id
	 *
	 * @return bool|int|mixed|string
	 * @throws Exception
	 */
	public static function ld_topic_access_from_func( $access_from, $topic_id, $user_id ) {
		if ( ! is_admin() ) {
			$course_id        = learndash_get_course_id( $topic_id );
			$has_group_access = learndash_user_group_enrolled_to_course( $user_id, $course_id );
			if ( $has_group_access ) {
				if ( is_object( $topic_id ) ) {
					$topic_id = $topic_id->ID;
				}

				$group_access = self::ld_topic_access_group( $topic_id, $user_id, $course_id, $access_from );

				$access_from = '';

				if ( is_numeric( $group_access ) && $group_access >= time() ) {
					$access_from = $group_access;
				}
			}
		}

		return $access_from;
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
		//get ms difference for time offset from GMT
		//could be +ve of -ve depending on timezone
		$offset = $gmt_offset * ( 60 * 60 );

		return (int) $time + $offset;
	}


	/**
	 * Save post metadata when a post is saved.
	 *
	 * @param $settings_field_updates
	 * @param $settings_metabox_key
	 * @param $settings_screen_id
	 *
	 * @return mixed
	 */
	public static function save_topic_custom_meta( $settings_field_updates, $settings_metabox_key, $settings_screen_id ) {


		global $post;

		if ( self::$access_metabox_key === $settings_metabox_key ) {

			// - Update the post's metadata. Nonce already verified by LearnDash
			if (
				isset( $_POST['learndash-topic-access-settings'] ) &&
				isset( $_POST['learndash-topic-access-settings']['set_groups_for_dates'] )
			) {


				// if group was set, save it
				if ( isset( $_POST['learndash-topic-access-settings']['set_groups_for_dates'] ) ) {

					$group_id = $_POST['learndash-topic-access-settings']['set_groups_for_dates'];
					if ( ! empty( $group_id ) ) {

						$date = self::reformat_date( $_POST['learndash-topic-access-settings']['visible_after_specific_date'] );

						if ( 0 === $date ) {
							delete_post_meta( $post->ID, stripslashes( __CLASS__ ) . '-' . $group_id );
							self::unset_notifications( $post->ID );
						} else {
							update_post_meta( $post->ID, stripslashes( __CLASS__ ) . '-' . $group_id, $date );
							self::set_notifications( $post->ID );
						}
					}
				}
			}

			// get original options and reset it
			$original_option                                             = get_post_meta( $post->ID, '_sfwd-topic', true );
			$original_date                                               = get_post_meta( $post->ID, stripslashes( __CLASS__ ) . '-all', true );
			$original_option['sfwd-topic_set_groups_for_dates']        = '';
			$original_option['sfwd-topic_visible_after_specific_date'] = $original_date;

			update_post_meta( $post->ID, '_sfwd-topic', $original_option );
		}

		return $settings_field_updates;


	}

	/**
	 *
	 */
	public static function change_hooks_and_shortcodes() {
		# Replace the function
		remove_filter( 'learndash_content', 'topic_visible_after', 1 );
		add_filter( 'learndash_content', array( __CLASS__, 'topic_visible_after' ), 1, 2 );
		
	}


	/**
	 * @param $post_args
	 *
	 * @return array
	 */
	public static function add_group_access_to_post_args_legacy( $post_args ) {

		if ( class_exists( 'LearnDash_Theme_Register' ) ) {
			return $post_args;
		}

		// Get all groups
		if ( ! is_user_logged_in() ) {
			return $post_args;
		}

		$groups = get_posts( [
			'post_type'      => 'groups',
			'posts_per_page' => 999,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );


		// If any group is not exists, this option will be disabled
		if ( ! $groups ) {
			return $post_args;
		}

		// group_selection
		$group_selection = array(
			0     => 'Select a LearnDash Group',
			'all' => 'All Other Users',
		);

		# TODO Show only groups that have access to this topic
		# Current code is inefficient and will have issues when a lot of groups are set up
		# try recursive to get courses of the topic and then groups of the courses

		foreach ( $groups as $group ) {
			if ( $group && is_object( $group ) ) {
				$group_selection[ $group->ID ] = $group->post_title;
			}
		}

		$new_post_args = array();


		foreach ( $post_args as $key => $val ) {
			// add option on topic setting
			if ( in_array( $val['post_type'], self::$learndash_post_types, true ) ) {
				$new_post_args[ $key ]           = $val;
				$new_post_args[ $key ]['fields'] = array();

				foreach ( $post_args[ $key ]['fields'] as $key_lessons => $val_lessons ) {
					$new_post_args[ $key ]['fields'][ $key_lessons ] = $val_lessons;

					if ( 'visible_after' === $key_lessons ) {
						$new_post_args[ $key ]['fields']['set_groups_for_dates'] = array(
							'name'            => 'LearnDash Group',
							'type'            => 'select',
							'help_text'       => 'Choose a group for a custom drip date',
							'initial_options' => $group_selection,
						);
					}
				}
			} else {
				$new_post_args[ $key ] = $val;
			}
		}

		return $new_post_args;
	}

	# Change the template as one in template dir of this plugin

	/**
	 * @param $setting
	 *
	 * @return mixed
	 */
	public static function change_topic_setting( $setting ) {
		// Get the post which are modifying
		global $post;

		foreach ( $setting['sfwd-topic_set_groups_for_dates']['initial_options'] as $group_id => &$group_name ) {

			if ( ! $group_id ) {
				continue;
			}
			$date = get_post_meta( $post->ID, stripslashes( __CLASS__ ) . '-' . $group_id, true );
			// Add tha ( date ) after group name on selection if exists

			if ( $date ) {
				if ( is_array( $date ) ) {
					$date = self::reformat_date( $date );
					$date = learndash_adjust_date_time_display( $date );
				}
				if ( self::is_timestamp( $date ) ) {
					$date_format = get_option( 'date_format' );
					$time_format = get_option( 'time_format' );
					$date        = self::adjust_for_timezone_difference( $date );
					$date        = date_i18n( "$date_format $time_format", $date );
				}
				$group_name = $group_name . ' &mdash; (' . $date . ')';
			}
		}

		return $setting;
	}




	/**
	 * @param $content
	 * @param $post
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function topic_visible_after( $content, $post ) {
		if ( empty( $post->post_type ) ) {
			return $content;
		}

		$user = wp_get_current_user();
		if ( in_array( 'administrator', $user->roles ) ) {
			return $content;
		}

		$uncanny_active_classes = get_option( 'uncanny_toolkit_active_classes', '' );
		if ( ! empty( $uncanny_active_classes ) ) {
			if ( key_exists( 'uncanny_pro_toolkit\GroupLeaderAccess', $uncanny_active_classes ) ) {
				$course_id         = learndash_get_course_id( $post->ID );
				$get_course_groups = learndash_get_course_groups( $course_id );
				$groups_of_leader  = learndash_get_administrators_group_ids( $user->ID );
				$matching          = array_intersect( $groups_of_leader, $get_course_groups );
				if ( in_array( 'group_leader', $user->roles ) && ! empty( $matching ) ) {
					return $content;
				}
			}
		}


		if ( 'sfwd-topic' === (string) $post->post_type ) {
			$topic_id = $post->ID;
		} elseif ( 'sfwd-quiz' === (string) $post->post_type || 'sfwd-lessons' === (string) $post->post_type ) {
			if ( 'yes' === LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Courses_Builder', 'shared_steps' ) ) {
				$course_id = learndash_get_course_id( $post );
				$topic_id = learndash_course_get_single_parent_step( $course_id, $post->ID );
			} else {
				$topic_id = learndash_get_setting( $post, 'lesson' );
			}
		} else {
			return $content;
		}

		if ( empty( $topic_id ) ) {
			return $content;
		}
		// Compare Two of Dates and return minimum value
		$topic_access_from = self::get_topic_access_from( $topic_id, $user->ID );
		if ( 'Available' === (string) $topic_access_from || empty ( $topic_access_from ) ) {
			return $content;
		}

		if ( $topic_access_from > time() ) {

			$course_id = learndash_get_course_id( $topic_id );
			$content   = SFWD_LMS::get_template(
				'learndash_course_lesson_not_available',
				array(
					'user_id'                 => $user->ID,
					'course_id'               => $course_id,
					'lesson_id'               => $topic_id,
					'lesson_access_from_int'  => $topic_access_from,
					'lesson_access_from_date' => learndash_adjust_date_time_display( $topic_access_from ),
					'context'                 => 'lesson',
				), false
			);

			if ( $content ) {
				return $content;
			} else {
				$content     = self::learndash_topic_available_from_text( $content, get_post( $topic_id ), $topic_access_from ) . '<br><br>';
				$course_link = get_permalink( $course_id );
				$content     .= '<a href="' . esc_url( $course_link ) . '">' . esc_html__( 'Return to Course Overview', 'uncanny-pro-toolkit' ) . '</a>';

				return '<div class=\'notavailable_message\'>' . apply_filters( 'learndash_topic_available_from_text', $content, $post, $topic_access_from ) . '</div>';
			}
		}

		return $content;
	}

	/**
	 * @param $topic_id
	 * @param $user_id
	 *
	 * @return bool|int|mixed|string
	 * @throws Exception
	 */
	public static function get_topic_access_from( $topic_id, $user_id ) {
		$topic_access_from = ld_lesson_access_from( $topic_id, $user_id );
		// Check Group Access As Well
		$topic_access_group = self::ld_topic_access_group( $topic_id, $user_id );
		$return              = 'Available';
		if ( ! empty( $topic_access_group ) && 'Available' !== $topic_access_group ) {
			if ( $topic_access_group > time() ) {
				$return = $topic_access_group;
			}
		}

		// Compare Two of Them without null, and return maximum value
		if ( ! empty( $topic_access_from ) ) {
			$return = $topic_access_from;
		}

		return $return;
	}

	/**
	 * @param $message
	 * @param $post
	 * @param $topic_access_from_int
	 *
	 * @return bool|int|mixed|string
	 */
	public static function learndash_topic_available_from_text( $message, $post, $topic_access_from_int ) {

		if ( ! is_admin() ) {
			if ( is_object( $post ) && isset( $post->post_type ) && 'sfwd-topic' === $post->post_type ) {
				if ( is_numeric( $topic_access_from_int ) && $topic_access_from_int >= time() ) {
					$access_from = $topic_access_from_int;
					$date_format = get_option( 'date_format' );
					$time_format = get_option( 'time_format' );
					$date        = self::adjust_for_timezone_difference( $access_from );
					$date        = date_i18n( "$date_format $time_format", $date );

					$message = sprintf( wp_kses_post( esc_attr__( '<span class="ld-display-label">Available on:</span> <span class="ld-display-date">%s</span>', 'topic-drip-group' ) ), $date );
				}
			}
		}

		return $message;
	}

	/**
	 *
	 */
	public static function reformat_date_to_unix() {
		if ( 'no' === get_option( 'group_drip_date_modified_to_unix_topic', 'no' ) ) {
			global $wpdb;
			$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->postmeta} WHERE meta_key LIKE '" . stripslashes( __CLASS__ ) . "%'" );
			// If any group is not exists, this option will be disabled
			if ( ! empty( $groups ) ) {
				// group_selection
				foreach ( $groups as $group ) {
					$post_id      = $group->post_id;
					$key          = $group->meta_key;
					$current_date = $group->meta_value;
					if ( ! empty( $current_date ) && 0 !== $current_date ) {
						if ( false === self::is_timestamp( $current_date ) ) {
							//attempt to convert to unix timestamp
							if ( is_array( maybe_unserialize( $current_date ) ) ) {
								$date_format  = get_option( 'date_format' );
								$time_format  = get_option( 'time_format' );
								$current_date = date( "$date_format $time_format", self::reformat_date( $current_date ) );
							}
							$unix_time = self::attempt_to_unix( $current_date );
							if ( false !== $unix_time ) {
								//DateTime was able to convert it to unix time, all good
								update_post_meta( $post_id, $key, $unix_time );
								$bak = str_replace( stripslashes( __CLASS__ ), 'bak-AddonDripTopicByGroup', $key );
								update_post_meta( $post_id, $bak, $current_date ); //keep a backup, Just-in-case
							}
						}
					}
				}
			}
			update_option( 'group_drip_date_modified_to_unix_topic', 'yes' );
		}
	}

	/**
	 * @param $date
	 *
	 * @return bool
	 */
	public static function attempt_to_unix( $date ) {
		try {
			$date = new DateTime( $date );

			return $date->getTimestamp();
		} catch ( Exception $e ) {
			return false;
		}
	}

}

$obj = new AddonDripTopicByGroup();