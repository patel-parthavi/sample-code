<?php

/* Hooks related to Scheduling Date start */

//callback fuction to insert scheduling data in database , when click on submit , in scheduling tab.
add_action('wp_ajax_nopriv_insert_form_data', 'insert_form_data');
add_action('wp_ajax_insert_form_data', 'insert_form_data');
function insert_form_data() {
	if ( ! empty ( $_POST["topic_drip_setting"] ) && is_array( $_POST["topic_drip_setting"] ) ) {
		foreach ( $_POST["topic_drip_setting"] as $topic_drip_setting_key => $topic_drip_setting_value ) {
			if ( ! empty( $topic_drip_setting_value ) ) {
				update_post_meta( $topic_drip_setting_key, 'topic_drip_post_available_on_' . $_POST["group_name_id"], strtotime( $topic_drip_setting_value ) );
			} else {
				update_post_meta( $topic_drip_setting_key, 'topic_drip_post_available_on_' . $_POST["group_name_id"], '' );
			}
		}
	}
	die();
}

// callback fucntion to show course drop down , when group is selected , in scheduling tab.
add_action('wp_ajax_nopriv_load_course_ajax', 'load_course_ajax');
add_action('wp_ajax_load_course_ajax', 'load_course_ajax');
function load_course_ajax() {
	$selected_grp_id = $_POST['selected_grp_id'];
	$group_course_ids = learndash_group_enrolled_courses( $selected_grp_id ); 

	if ( ! empty( $group_course_ids ) && is_array( $group_course_ids ) ) { ?>

	 	<label class="font-weight-bold display-4" for="course_name_id"><?php echo __( 'Select Course : ','drip-group-topic' ); ?></label>
		<select name="course_name_id" id="course_name_id">
			<option value=""><?php echo __( 'Select Course','drip-group-topic' ); ?></option>
		 	<?php 
		 	foreach ( $group_course_ids as $group_course_id ) { ?>
		 		<option value="<?php echo $group_course_id; ?>"><?php echo get_the_title( $group_course_id ); ?></option>
		 	<?php } ?>
		</select>

	<?php 
	}

	die();
}

// callback fucntion to show lesson , when course is selected , in scheduling tab.
add_action('wp_ajax_nopriv_load_lesson_ajax', 'load_lesson_ajax');
add_action('wp_ajax_load_lesson_ajax', 'load_lesson_ajax');
function load_lesson_ajax() {

	$selected_course_id = $_POST['selected_course_id'];
	$selected_grp_id = $_POST['selected_grp_id'];

	$posts_lesson =  learndash_get_lesson_list( $selected_course_id, array( 'num' => 0 ) );

	if ( ! empty( $posts_lesson ) && is_array( $posts_lesson ) ) { ?>

		<div class="row">
			<div class="font-weight-bold display-4 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4"><?php echo __( 'Lesson', 'drip-group-topic' ); ?></div>
			<div class="font-weight-bold display-4 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4"><?php echo __( 'Topic', 'drip-group-topic' ); ?></div>
			<div class="font-weight-bold display-4 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4"><?php echo __( 'Set Date', 'drip-group-topic' ); ?></div>
		</div>

		<?php 
	 	foreach ( $posts_lesson as $posts_lesson_key => $posts_lesson_value ) { ?>
	 		<div class="row">
				<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
					<?php echo $posts_lesson_value->post_title; ?>
				</div>
			</div>

			<?php 
			$posts_topic = learndash_get_topic_list( $posts_lesson_value->ID, $selected_course_id );
			if ( ! empty( $posts_topic ) && is_array( $posts_topic ) ) { 
				foreach ( $posts_topic as $posts_topic_key => $posts_topic_value ) {  
					$topic_access_from = get_post_meta( $posts_topic_value->ID, 'topic_drip_post_available_on_' . $selected_grp_id, true );
					if ( !empty($topic_access_from)) {
						$new_date = date( 'd-m-Y h:i A', $topic_access_from );
					} else {
						$new_date = '';
					} ?>
					<div class="row">
						<div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4"></div>
						<div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
							<?php echo $posts_topic_value->post_title; ?>
						</div>
						<div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
							<input value="<?php echo $new_date; ?>" type="text" name="topic_drip_setting[<?php echo $posts_topic_value->ID; ?>]" class="topic_drip_date_setting" id="topic_drip_date_setting_<?php echo $posts_topic_value->ID; ?>" />
						</div>
					</div>  
				<?php }  
			}

		} ?>

		<div class="row">
			<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
				<div class="text-center topic_drip_save_button">
					<br/><br/><input value="Submit" type="submit" name="topic_drip_date_save" id="topic_drip_all_date_save" class="button-primary topic_drip_all_date_save">
				</div>
			</div>
		</div> 

		<div class="row">
			<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
				<div id="data_insert_success" class="data_insert_success">
					<h4 class="text-center" style="color: green;"><?php echo __( "Data inserted successfully", "" ); ?></h4>
				</div>
			</div>
		</div>  

	<?php }
	
	die();
}

/* Hooks related to Scheduling Date end */


/* Hooks related to "Available on" Notice start */

//callback function to show Available on notice , on course page front-end.
add_filter( 'learndash_lesson_attributes', 'topic_drip_learndash_lesson_attributes', 10, 2 );
function topic_drip_learndash_lesson_attributes( $attributes, $lesson ) {
	$get_course_id = learndash_get_course_id( $lesson['post']->ID, true ); 
	$posts_topic = learndash_get_topic_list( $lesson['post']->ID, $get_course_id );

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
								esc_html_x( 'Available on %s', 'Available on date label', 'drip-group-topic' ),
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

//callback function to show Available on notice , on topic single page front-end.
add_filter( 'learndash_content', 'topic_drip_lesson_visible_after', 10, 2 );
function topic_drip_lesson_visible_after( $content, $post ) {
	if ( 'sfwd-topic' === $post->post_type ) {
		$user_groups = learndash_get_users_group_ids( get_current_user_id() );
		if ( ! empty( $user_groups ) ) {
			foreach ( $user_groups as $user_groups_key => $user_groups_value ) {
				$topic_access_from = get_post_meta( $post->ID, 'topic_drip_post_available_on_' . $user_groups_value, true );
				//$default_course_id = learndash_get_course_id( $post->ID, true );

				if ( $topic_access_from > time() ) {
					$content   = SFWD_LMS::get_template(
						'learndash_course_lesson_not_available',
						array(
							'user_id'                 => get_current_user_id(),
							'lesson_id'               => $post->ID,
							'lesson_access_from_int'  => $topic_access_from,
							'lesson_access_from_date' => learndash_adjust_date_time_display ( $topic_access_from ),
							'context'                 => 'lesson',
						), false
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

/* Hooks related to "Available on" Notice end */


/* Hooks related to course start */

// callback fuction to show course in multi select box , when group selected , in courses tab.
add_action('wp_ajax_nopriv_load_course_ajax_course', 'load_course_ajax_course');
add_action('wp_ajax_load_course_ajax_course', 'load_course_ajax_course');
function load_course_ajax_course() {
	$not_enrolled_course_ids = array();
	$selected_grp_id = $_POST['selected_grp_id'];
	$group_course_ids = learndash_group_enrolled_courses( $selected_grp_id ); 

	$args_course = array(
	    'posts_per_page'   => -1,
	    'post_type'        => 'sfwd-courses',
	    'post_status'    => 'publish',
	);

	$course_not_enrolled = get_posts( $args_course ); 
	if ( ! empty( $course_not_enrolled ) ) {
		foreach ( $course_not_enrolled as $course_not_enrolled_key => $course_not_enrolled_value ) {
			$not_enrolled_course_ids[] = $course_not_enrolled_value->ID;
		}
	} 

	if ( empty( $group_course_ids ) ) {
		$not_enrolled_course = $not_enrolled_course_ids;
	} else {
		$not_enrolled_course = array_diff( $not_enrolled_course_ids, $group_course_ids );
	} ?>

	<div class="row">
		<div class="font-weight-bold display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4"><?php echo __( 'All Course', 'drip-group-topic' ); ?></div>
		<div class="font-weight-bold display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4"><?php echo __( 'Action', 'drip-group-topic' ); ?></div>
		<div class="font-weight-bold display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4"><?php echo __( 'Assigned Course', 'drip-group-topic' ); ?></div>
	</div>

 	
	<div class="row" id="replace_assigned_and_all_section">

 		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
	 		<select name="topic_drip_all_course[]" id="topic_drip_all_course" multiple>
				<option value="0"><?php echo __( 'Select Course','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $not_enrolled_course as $not_enrolled_course_id ) { 
					if (  $not_enrolled_course_id != 0 ) { ?>
						<option value="<?php echo $not_enrolled_course_id ?>"><?php echo get_the_title( $not_enrolled_course_id ); ?></option>
					<?php } 
				} ?>
			</select>
 		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<a href="" id="topic_drip_add_course" class="topic_drip_add_course"><?php echo __( 'Add', 'drip-group-topic' ); ?></a> 
			/
			<a href="" id="topic_drip_remove_course" class="topic_drip_remove_course"><?php echo __( 'Remove', 'drip-group-topic' ); ?></a>
		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<select name="topic_drip_assigned_course[]" id="topic_drip_assigned_course" multiple>
				<option value="0"><?php echo __( 'Select Course','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $group_course_ids as $group_course_id ) { 
					if (  $group_course_id != 0 ) { ?>
						<option value="<?php echo $group_course_id ?>"><?php echo get_the_title( $group_course_id ); ?></option>
					<?php } 
				} ?>
			</select>	
		</div>

	</div>

	<div class="row">
		<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
			<div class="text-center topic_drip_save_button_course">
				<br/><br/><input value="Submit" type="submit" name="topic_drip_date_save_course" id="topic_drip_all_date_save_course" class="button-primary topic_drip_all_date_save_course">
			</div>
		</div>
	</div> 

	<div class="row">
		<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
			<div id="data_insert_success_course" class="data_insert_success_course">
				<h4 class="text-center" style="color: green;"><?php echo __( "Data inserted successfully", "" ); ?></h4>
			</div>
		</div>
	</div>  

	<?php

	die();
}

// callback fuction to remove course from multi select box , when click on remove button , in courses tab.
add_action('wp_ajax_nopriv_remove_course_ajax', 'remove_course_ajax');
add_action('wp_ajax_remove_course_ajax', 'remove_course_ajax');

function remove_course_ajax() {
	$remove_course_id_old = $_POST['remove_course_id'];
	$total_assigned_id = $_POST['total_assigned_id'];
	$total_all_id = $_POST['total_all_id'];

	$remove_course_id = array_merge( $remove_course_id_old, $total_all_id );
	$after_remove = array_diff( $total_assigned_id, $remove_course_id_old );


	if ( ! empty( $remove_course_id ) ) { ?>

 		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<select name="topic_drip_all_course[]" id="topic_drip_all_course" multiple>
				<option value="0"><?php echo __( 'Select Course','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $remove_course_id as $remove_group_course_id ) { 
					if (  $remove_group_course_id != 0 ) { ?>
						<option selected value="<?php echo $remove_group_course_id ?>"><?php echo get_the_title( $remove_group_course_id ); ?></option>
					<?php } 
				} ?>
			</select>	
		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<a href="" id="topic_drip_add_course" class="topic_drip_add_course"><?php echo __( 'Add', 'drip-group-topic' ); ?></a> 
			/
			<a href="" id="topic_drip_remove_course" class="topic_drip_remove_course"><?php echo __( 'Remove', 'drip-group-topic' ); ?></a>
		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<select name="topic_drip_assigned_course[]" id="topic_drip_assigned_course" multiple>
				<option value="0"><?php echo __( 'Select Course','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $after_remove as $after_remove_group_course_id ) { 
					if (  $after_remove_group_course_id != 0 ) { ?>
						<option selected value="<?php echo $after_remove_group_course_id ?>"><?php echo get_the_title( $after_remove_group_course_id ); ?></option>
					<?php } 
				} ?>
			</select>	
		</div>

		<?php
	}

	die();
}


// callback fuction to add course in multi select box on , when click on add buttoon , in courses tab.
add_action('wp_ajax_nopriv_add_course_ajax', 'add_course_ajax');
add_action('wp_ajax_add_course_ajax', 'add_course_ajax');

function add_course_ajax() {

	$add_course_id = $_POST['add_course_id'];
	$total_assigned_id = $_POST['total_assigned_id'];
	$total_all_id = $_POST['total_all_id'];

	$after_add = array_diff( $total_all_id, $add_course_id );
	$after_assigned = array_merge( $total_assigned_id, $add_course_id );
	

	if ( ! empty( $add_course_id ) ) { ?>

 		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<select name="topic_drip_all_course[]" id="topic_drip_all_course" multiple>
				<option value="0"><?php echo __( 'Select Course','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $after_add as $after_add_group_course_id ) { 
					if (  $after_add_group_course_id != 0 ) { ?>
						<option selected value="<?php echo $after_add_group_course_id ?>"><?php echo get_the_title( $after_add_group_course_id ); ?></option>
					<?php }
				} ?>
			</select>	
		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<a href="" id="topic_drip_add_course" class="topic_drip_add_course"><?php echo __( 'Add', 'drip-group-topic' ); ?></a> 
			/
			<a href="" id="topic_drip_remove_course" class="topic_drip_remove_course"><?php echo __( 'Remove', 'drip-group-topic' ); ?></a>
		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<select name="topic_drip_assigned_course[]" id="topic_drip_assigned_course" multiple>
				<option value="0"><?php echo __( 'Select Course','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $after_assigned as $after_assigned_group_course_id ) { 
					if (  $after_assigned_group_course_id != 0 ) { ?>
						<option selected value="<?php echo $after_assigned_group_course_id ?>"><?php echo get_the_title( $after_assigned_group_course_id ); ?></option>
					<?php }
				} 
				?>
			</select>	
		</div>
		 	
		<?php
	}

	die();
}

// callback function to insert course data in database , when click on submit , in courses tab.
add_action('wp_ajax_nopriv_insert_course_data', 'insert_course_data');
add_action('wp_ajax_insert_course_data', 'insert_course_data');

function insert_course_data() {
	$group_name_id_course = $_POST['group_name_id_course'];
	$get_course_id = array();

	if ( ! empty ( $_POST["topic_drip_assigned_course"] ) && is_array( $_POST["topic_drip_assigned_course"] ) ) {
		foreach ( $_POST["topic_drip_assigned_course"] as $total_assigned_id_key ) {
			if (  $total_assigned_id_key != 0 ) {
				$get_course_id[] = $total_assigned_id_key;
			}
		}
		learndash_set_group_enrolled_courses( $group_name_id_course, $get_course_id );
	}

	if ( ! empty ( $_POST["topic_drip_all_course"] ) && is_array( $_POST["topic_drip_all_course"] ) ) {
		foreach ( $_POST["topic_drip_all_course"] as $total_all_id_key ) {
			if (  $total_all_id_key != 0 ) { 
				ld_update_course_group_access( $total_all_id_key, $group_name_id_course, true );
			}
		} 
	}

	die();
}

/* Hooks related to course end */


/* Hooks related to user and group leader start */

// callback fuction to show user and group leader in multi select box , when group selected , on users tab.
add_action('wp_ajax_nopriv_load_user_ajax_user', 'load_user_ajax_user');
add_action('wp_ajax_load_user_ajax_user', 'load_user_ajax_user');

function load_user_ajax_user() {
	$selected_grp_id = $_POST['selected_grp_id'];
	$group_user_list_array = array();

	$args_user = [
	    'role__not_in' => ['administrator'],
	    'fields' => 'all',
	];

	$group_user_list_data = get_users( $args_user ) ; 

	foreach ( $group_user_list_data as $group_user_list_id ) {
		if (  $group_user_list_id->ID != 0 ) { 
			$group_user_list_array[] = $group_user_list_id->ID;
		}
	}
	
	$assigned_group_user_list = learndash_get_groups_user_ids( $selected_grp_id ); 
	$group_user_list = array_diff( $group_user_list_array, $assigned_group_user_list );
	
	?>

	<div class="row">
		<div class="font-weight-bold display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4"><?php echo __( 'All User', 'drip-group-topic' ); ?></div>
		<div class="font-weight-bold display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4"><?php echo __( 'Action', 'drip-group-topic' ); ?></div>
		<div class="font-weight-bold display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4"><?php echo __( 'Assigned User', 'drip-group-topic' ); ?></div>
	</div>

	 	
	<div class="row" id="replace_assigned_and_all_section_user">

 		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
	 		<select name="topic_drip_all_user[]" id="topic_drip_all_user" multiple>
				<option value="0"><?php echo __( 'Select User','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $group_user_list as $group_user_list_id ) { 
					if (  $group_user_list_id != 0 ) { 
						$user_name_all = get_user_by( 'id', $group_user_list_id );

						?>
						<option value="<?php echo $group_user_list_id ; ?>"><?php echo $user_name_all->display_name; ?></option>
					<?php } 
				} ?>
			</select>
 		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<a href="" id="topic_drip_add_user" class="topic_drip_add_user"><?php echo __( 'Add', 'drip-group-topic' ); ?></a> 
			/
			<a href="" id="topic_drip_remove_user" class="topic_drip_remove_user"><?php echo __( 'Remove', 'drip-group-topic' ); ?></a>
		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<select name="topic_drip_assigned_user[]" id="topic_drip_assigned_user" multiple>
				<option value="0"><?php echo __( 'Select User','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $assigned_group_user_list as $assigned_group_user ) { 
					if (  $assigned_group_user != 0 ) { 
						$user_name_assigned = get_user_by( 'id', $assigned_group_user );
						?>
						<option value="<?php echo $assigned_group_user ; ?>"><?php echo $user_name_assigned->display_name; ?></option>
					<?php } 
				} ?>
			</select>
		</div>

	</div>

	<?php
	$group_leader_list_array = array();
	$args_leader_user = [
	    'role__in' => ['administrator','group_leader'],
	    'fields' => 'all',
		];

	$group_leader_list_data = get_users( $args_leader_user ) ; 

	foreach ( $group_leader_list_data as $group_user_list_id ) {
		if (  $group_user_list_id->ID != 0 ) { 
			$group_leader_list_array[] = $group_user_list_id->ID;
		}
	}
	
	$assigned_group_leader_list = learndash_get_groups_administrator_ids( $selected_grp_id ); 
	$group_leader_list = array_diff( $group_leader_list_array, $assigned_group_leader_list );
	?>

	<div class="row">
		<div class="font-weight-bold display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4"><?php echo __( 'All Leaader', 'drip-group-topic' ); ?></div>
		<div class="font-weight-bold display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4"><?php echo __( 'Action', 'drip-group-topic' ); ?></div>
		<div class="font-weight-bold display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4"><?php echo __( 'Assigned Leader', 'drip-group-topic' ); ?></div>
	</div>

	<div class="row" id="replace_assigned_and_all_section_leader">

 		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
	 		<select name="topic_drip_all_leader[]" id="topic_drip_all_leader" multiple>
				<option value="0"><?php echo __( 'Select Leader','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $group_leader_list as $group_leader_user_list_id ) { 
					if (  $group_leader_user_list_id != 0 ) { 

						$leader_name_all = get_user_by( 'id', $group_leader_user_list_id );
						?>
						<option value="<?php echo $group_leader_user_list_id ; ?>"><?php echo $leader_name_all->display_name; ?></option>
					<?php } 
				} ?>
			</select>
 		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<a href="" id="topic_drip_add_leader" class="topic_drip_add_leader"><?php echo __( 'Add', 'drip-group-topic' ); ?></a> 
			/
			<a href="" id="topic_drip_remove_leader" class="topic_drip_remove_leader"><?php echo __( 'Remove', 'drip-group-topic' ); ?></a>
		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<select name="topic_drip_assigned_leader[]" id="topic_drip_assigned_leader" multiple>
				<option value="0"><?php echo __( 'Select Leader','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $assigned_group_leader_list as $assigned_group_user_leader ) { 
					if (  $assigned_group_user_leader != 0 ) { 
						$leader_name_assigned = get_user_by( 'id', $assigned_group_user_leader );
						?>
						<option value="<?php echo $assigned_group_user_leader ; ?>"><?php echo $leader_name_assigned->display_name; ?></option>
					<?php } 
				} ?>
			</select>
		</div>

	</div>

	<div class="row">
		<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
			<div class="text-center topic_drip_save_button_user">
				<br/><br/><input value="Submit" type="submit" name="topic_drip_date_save_user" id="topic_drip_all_date_save_user" class="button-primary topic_drip_all_date_save_user">
			</div>
		</div>
	</div> 

	<div class="row">
		<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
			<div id="data_insert_success_user" class="data_insert_success_user">
				<h4 class="text-center" style="color: green;"><?php echo __( "Data inserted successfully", "" ); ?></h4>
			</div>
		</div>
	</div>  

	<?php

	die();
}

// callback fuction to remove user from multi select box , when click on remove button , on users tab..
add_action('wp_ajax_nopriv_remove_user_ajax', 'remove_user_ajax');
add_action('wp_ajax_remove_user_ajax', 'remove_user_ajax');

function remove_user_ajax() {
	$remove_user_id_old = $_POST['remove_user_id'];
	$total_assigned_id = $_POST['total_assigned_id'];
	$total_all_id = $_POST['total_all_id'];
	
	$remove_user_id = array_merge( $remove_user_id_old, $total_all_id );
	$after_remove = array_diff( $total_assigned_id, $remove_user_id_old );


	if ( ! empty( $remove_user_id ) ) { ?>

 		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<select name="topic_drip_all_user[]" id="topic_drip_all_user" multiple>
				<option value="0"><?php echo __( 'Select User','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $remove_user_id as $group_user_list_id ) { 
					if (  $group_user_list_id != 0 ) { 
						$user_name_all_remove = get_user_by( 'id', $group_user_list_id );

						?>
						<option selected value="<?php echo $group_user_list_id ; ?>"><?php echo $user_name_all_remove->display_name; ?></option>
					<?php } 
				} ?>
			</select>	
		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<a href="" id="topic_drip_add_user" class="topic_drip_add_user"><?php echo __( 'Add', 'drip-group-topic' ); ?></a> 
			/
			<a href="" id="topic_drip_remove_user" class="topic_drip_remove_user"><?php echo __( 'Remove', 'drip-group-topic' ); ?></a>
		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<select name="topic_drip_assigned_user[]" id="topic_drip_assigned_user" multiple>
				<option value="0"><?php echo __( 'Select User','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $after_remove as $assigned_group_user ) { 
					if (  $assigned_group_user != 0 ) { 
						$user_name_assigned_remove = get_user_by( 'id', $assigned_group_user );
						?>
						<option selected value="<?php echo $assigned_group_user ; ?>"><?php echo $user_name_assigned_remove->display_name; ?></option>
					<?php } 
				} ?>
			</select>
		</div>

	<?php } 

	die();
}


// callback fuction to remove group leader from multi select box , when click on remove button , on users tab.
add_action('wp_ajax_nopriv_remove_leader_ajax', 'remove_leader_ajax');
add_action('wp_ajax_remove_leader_ajax', 'remove_leader_ajax');

function remove_leader_ajax() {

	$remove_leader_id_old = $_POST['remove_leader_id'];
	$total_assigned_id_leader = $_POST['total_assigned_id_leader'];
	$total_all_id_leader = $_POST['total_all_id_leader'];
	
	$remove_leader_id = array_merge( $remove_leader_id_old, $total_all_id_leader );
	$after_remove_leader = array_diff( $total_assigned_id_leader, $remove_leader_id_old );

	if ( ! empty( $remove_leader_id ) ) { ?>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
	 		<select name="topic_drip_all_leader[]" id="topic_drip_all_leader" multiple>
				<option value="0"><?php echo __( 'Select Leader','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $remove_leader_id as $group_leader_user_list_id ) { 
					if (  $group_leader_user_list_id != 0 ) { 
						$leader_name_all_remove = get_user_by( 'id', $group_leader_user_list_id );

						?>
						<option selected value="<?php echo $group_leader_user_list_id ; ?>"><?php echo $leader_name_all_remove->display_name; ?></option>
					<?php } 
				} ?>
			</select>
		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<a href="" id="topic_drip_add_leader" class="topic_drip_add_leader"><?php echo __( 'Add', 'drip-group-topic' ); ?></a> 
			/
			<a href="" id="topic_drip_remove_leader" class="topic_drip_remove_leader"><?php echo __( 'Remove', 'drip-group-topic' ); ?></a>
		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<select name="topic_drip_assigned_leader[]" id="topic_drip_assigned_leader" multiple>
				<option value="0"><?php echo __( 'Select Leader','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $after_remove_leader as $assigned_group_user_leader ) { 
					if (  $assigned_group_user_leader != 0 ) { 
						$leader_name_assigned_remove = get_user_by( 'id', $assigned_group_user_leader );
						?>
						<option selected value="<?php echo $assigned_group_user_leader ; ?>"><?php echo $leader_name_assigned_remove->display_name; ?></option>
					<?php } 
				} ?>
			</select>
		</div>

		<?php
	}

	die();
}

// Callback fuction to add user in multi select box , when click on Add button , on users tab.
add_action('wp_ajax_nopriv_add_user_ajax', 'add_user_ajax');
add_action('wp_ajax_add_user_ajax', 'add_user_ajax');
function add_user_ajax() {
	$add_user_id = $_POST['add_user_id'];
	$total_assigned_id = $_POST['total_assigned_id'];
	$total_all_id = $_POST['total_all_id'];

	$after_add_user = array_diff( $total_all_id, $add_user_id );
	$after_assigned_user = array_merge( $total_assigned_id, $add_user_id );

	if ( ! empty( $add_user_id ) ) { ?>

 		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<select name="topic_drip_all_user[]" id="topic_drip_all_user" multiple>
				<option value="0"><?php echo __( 'Select User','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $after_add_user as $group_user_list_id ) { 
					if (  $group_user_list_id != 0 ) { 
						$user_name_all_remove = get_user_by( 'id', $group_user_list_id );

						?>
						<option selected value="<?php echo $group_user_list_id ; ?>"><?php echo $user_name_all_remove->display_name; ?></option>
					<?php } 
				} ?>
			</select>	
		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<a href="" id="topic_drip_add_user" class="topic_drip_add_user"><?php echo __( 'Add', 'drip-group-topic' ); ?></a> 
			/
			<a href="" id="topic_drip_remove_user" class="topic_drip_remove_user"><?php echo __( 'Remove', 'drip-group-topic' ); ?></a>
		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<select name="topic_drip_assigned_user[]" id="topic_drip_assigned_user" multiple>
				<option value="0"><?php echo __( 'Select User','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $after_assigned_user as $assigned_group_user ) { 
					if (  $assigned_group_user != 0 ) { 
						$user_name_assigned_remove = get_user_by( 'id', $assigned_group_user );
						?>
						<option selected value="<?php echo $assigned_group_user ; ?>"><?php echo $user_name_assigned_remove->display_name; ?></option>
					<?php } 
				} ?>
			</select>
		</div>

	<?php } 

	die();
}

// Callback fuction to add group leader in multi select box , when click on Add button , in users tab.
add_action('wp_ajax_nopriv_add_leader_ajax', 'add_leader_ajax');
add_action('wp_ajax_add_leader_ajax', 'add_leader_ajax');
function add_leader_ajax() {
	$add_leader_id = $_POST['add_leader_id'];
	$total_assigned_id_leader = $_POST['total_assigned_id_leader'];
	$total_all_id_leader = $_POST['total_all_id_leader'];

	$after_add_leader = array_diff( $total_all_id_leader, $add_leader_id );
	$after_assigned_leader = array_merge( $total_assigned_id_leader, $add_leader_id );

	if ( ! empty( $add_leader_id ) ) { ?>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
	 		<select name="topic_drip_all_leader[]" id="topic_drip_all_leader" multiple>
				<option value="0"><?php echo __( 'Select Leader','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $after_add_leader as $group_leader_user_list_id ) { 
					if (  $group_leader_user_list_id != 0 ) { 
						$leader_name_all_remove = get_user_by( 'id', $group_leader_user_list_id );

						?>
						<option selected value="<?php echo $group_leader_user_list_id ; ?>"><?php echo $leader_name_all_remove->display_name; ?></option>
					<?php } 
				} ?>
			</select>
			</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<a href="" id="topic_drip_add_leader" class="topic_drip_add_leader"><?php echo __( 'Add', 'drip-group-topic' ); ?></a> 
			/
			<a href="" id="topic_drip_remove_leader" class="topic_drip_remove_leader"><?php echo __( 'Remove', 'drip-group-topic' ); ?></a>
		</div>

		<div class="display-6 col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
			<select name="topic_drip_assigned_leader[]" id="topic_drip_assigned_leader" multiple>
				<option value="0"><?php echo __( 'Select Leader','drip-group-topic' ); ?></option>
				<?php 
				foreach ( $after_assigned_leader as $assigned_group_user_leader ) { 
					if (  $assigned_group_user_leader != 0 ) { 
						$leader_name_assigned_remove = get_user_by( 'id', $assigned_group_user_leader );
						?>
						<option selected value="<?php echo $assigned_group_user_leader ; ?>"><?php echo $leader_name_assigned_remove->display_name; ?></option>
					<?php } 
				} ?>
			</select>
		</div>

		<?php
	}

	die();
}

// callback function to insert user data in database , when click on submit , in users tab.
add_action('wp_ajax_nopriv_insert_user_data', 'insert_user_data');
add_action('wp_ajax_insert_user_data', 'insert_user_data');
function insert_user_data() {
	$group_name_id_user = $_POST['group_name_id_user'];
	$total_assigned_id_array = array();
	$total_assigned_id_key_array = array();

	if ( ! empty ( $_POST["topic_drip_all_user"] ) && is_array( $_POST["topic_drip_all_user"] ) ) {
		foreach ( $_POST["topic_drip_all_user"] as $total_all_id_key ) {
			if (  $total_all_id_key != 0 ) { 
				ld_update_group_access( $total_all_id_key, $group_name_id_user,true );
			}
		} 
	}

	if ( ! empty ( $_POST["topic_drip_assigned_user"] ) && is_array( $_POST["topic_drip_assigned_user"] ) ) {
		foreach ( $_POST["topic_drip_assigned_user"] as $total_assigned_id_key ) {
			if (  $total_assigned_id_key != 0 ) {
				$total_assigned_id_array[] = $total_assigned_id_key;
			}
		}
		learndash_set_groups_users( $group_name_id_user, $total_assigned_id_array  );
	}


	if ( ! empty ( $_POST["topic_drip_all_leader"] ) && is_array( $_POST["topic_drip_all_leader"] ) ) {
		foreach ( $_POST["topic_drip_all_leader"] as $total_all_id_key ) {
			if (  $total_all_id_key != 0 ) {
				ld_update_leader_group_access( $total_all_id_key, $group_name_id_user,true );
			}
		} 
	}

	if ( ! empty ( $_POST["topic_drip_assigned_leader"] ) && is_array( $_POST["topic_drip_assigned_leader"] ) ) {
		foreach ( $_POST["topic_drip_assigned_leader"] as $total_assigned_id_key ) {
			if (  $total_assigned_id_key != 0 ) {
				$total_assigned_id_key_array[] = $total_assigned_id_key;
			}
		}
		learndash_set_groups_administrators( $group_name_id_user, $total_assigned_id_key_array );
	}

	die();
}

/* Hooks related to user and group leader end */

// callback fuction to show TAB , on change of group dropdown.
add_action('wp_ajax_nopriv_load_tab_ajax', 'load_tab_ajax');
add_action('wp_ajax_load_tab_ajax', 'load_tab_ajax');
function load_tab_ajax() {
	$selected_grp_id = $_POST['selected_grp_id'];

	if ( !empty( $selected_grp_id ) ) { ?>
        <li class="active">
        	<a href="#set-user" data-toggle="pill"><?php echo __( 'Users','drip-group-topic' ); ?></a>
        </li>
        <li>
        	<a href="#set-course" data-toggle="pill"><?php echo __( 'Courses','drip-group-topic' ); ?></a>
        </li>
        <li>
        	<a href="#set-scheduling" data-toggle="pill"><?php echo __( 'Scheduling','drip-group-topic' ); ?></a>
        </li>

	<?php }
	die();
}