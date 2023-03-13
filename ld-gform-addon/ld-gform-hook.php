<?php
/*
* Create gravity form selection Metabox for Post type "sfwd-courses" and "sfwd-lessons"
*/
function gravity_form_selection_metabox() {
	add_meta_box( 'gravity_form_selection_id', 'Select Gravity Form', 'select_gravity_form', array( 'sfwd-courses', 'sfwd-lessons' ), 'side', 'low' );
} 

add_action( 'add_meta_boxes', 'gravity_form_selection_metabox' );
	function select_gravity_form( $post ) { 
		$gravity_forms = GFAPI::get_forms(); ?>

    	<label class="" for="gravity_form_selection"><?php echo __( 'Select Form : ','ld-gravityform-addon' ); ?></label>
		<select name="gravity_form_selection" id="gravity_form_selection">
			<option value=""><?php echo __( 'Select Form','ld-gravityform-addon' ); ?></option>
		 	<?php 

		 	$selected_gravity_form = get_post_meta( $post->ID, 'selected_gravity_form', true );

		 	foreach ( $gravity_forms as $gravity_forms_name) { 

		 	if ( !empty( $selected_gravity_form ) && $selected_gravity_form == $gravity_forms_name['id'] ) {
		 		$selected = 'selected';
		 	} else {
		 		$selected = '';
		 	} ?>

		 	<option <?php  echo $selected; ?> value="<?php echo $gravity_forms_name['id']; ?>"><?php echo $gravity_forms_name['title']; ?></option>
		 	<?php } ?>
		</select>

<?php 
}

add_action('save_post', 'save_selected_gravity_form_name');
function save_selected_gravity_form_name( $post_id ) {
	if ( isset( $_POST['gravity_form_selection'] ) ) {        
    	update_post_meta( $post_id, 'selected_gravity_form', $_POST['gravity_form_selection'] );      
  	}
}

/**
 * Create submenu page to display User Feedback.
 */ 
add_action( 'admin_menu', 'create_feedback_submenu' );
function create_feedback_submenu() {
	add_submenu_page('learndash-lms', 
	    'User Feedback', 
	    'User Feedback', 
	    'manage_options', 
	    'user_feedback_data', 
	    'user_feedback_data_for_post'
	); 
}

/**
 * Create course/lesson dropdown on User Feedback page.
 */ 
function user_feedback_data_for_post() { 

	$course_ids = array();
	$lesson_ids = array();

	$posts_courses = ld_course_list( array( 'array' => true ) );

	foreach ( $posts_courses as $posts_courses_key => $posts_courses_value ) { 

		$course_ids[] = $posts_courses_value->ID;
		$posts_lesson =  learndash_get_lesson_list( $posts_courses_value->ID, array( 'num' => 0 ) );

		foreach ( $posts_lesson as $posts_lesson_key => $posts_lesson_value ) {
			$lesson_ids[] = $posts_lesson_value->ID;
		}

	}
	
	$course_lesson_array = array_merge( $course_ids,$lesson_ids ); 

	if ( ! empty( $course_lesson_array ) && is_array( $course_lesson_array ) ) { ?>

		<div id="feedback-post-selection-section" class="feedback-post-selection-section">

			<h2 class="feedback_title" style="font-size: 25px;" align="center"><?php echo __( 'Select Course/Lesson to see Report', 'ld-gravityform-addon' ); ?></h2>
			<label class="" for="gravity_form_exists_post"><?php echo __( 'Select Course/Lesson : ','ld-gravityform-addon' ); ?></label>
			<select name="gravity_form_exists_post" id="gravity_form_exists_post">
			 	<?php 

			 	foreach ( $course_lesson_array as $course_lesson_array_key ) {
			 		
			 		$gravity_form_exist = get_post_meta( $course_lesson_array_key, 'selected_gravity_form', true );

			 		if ( ! empty( $gravity_form_exist ) ) { ?>
			 			<option value="<?php echo $course_lesson_array_key; ?>"><?php echo get_the_title( $course_lesson_array_key ); ?></option>
			 		<?php } 
			 	} ?>
			</select>

		</div>
		<br/><br/><br/>
		<div class="feedback_report_from_users" id="feedback_report_from_users"></div>
	<?php
	}
}

/**
 * callback fuction to show feedback report data of users
 */
add_action('wp_ajax_nopriv_get_gravity_form_data_ajax', 'get_gravity_form_data_ajax');
add_action('wp_ajax_get_gravity_form_data_ajax', 'get_gravity_form_data_ajax');
function get_gravity_form_data_ajax() {
	global $wpdb;

	$get_form_id = array();
	$new_data = array();

	$selected_post_id = $_POST['selected_post_id'];
	$post_type_name = get_post_type( $selected_post_id );

	if ( $post_type_name == 'sfwd-courses' ) {
		$lesson_list =  learndash_get_lesson_list( $selected_post_id, array( 'num' => 0 ) );
		$lesson_list_reverse = array_reverse( $lesson_list );
		$selected_post_id_new = $lesson_list_reverse[0]->ID;
	} else {
		$selected_post_id_new = $selected_post_id;
	}

	$selected_post_form_id = get_post_meta( $selected_post_id, 'selected_gravity_form', true );

	$check_form_entry_id = $wpdb->get_results ( "SELECT entry_id FROM " . $wpdb->prefix . "gf_entry_meta WHERE meta_value = $selected_post_id_new" );

	if ( ! empty( $check_form_entry_id ) && is_array( $check_form_entry_id ) ) {
		foreach ( $check_form_entry_id as $check_form_entry_id_key ) {
			$check_form_entry_all_id[] = $check_form_entry_id_key->entry_id;
		}
	}

	foreach ( $check_form_entry_all_id as $check_form_entry_all_id_key => $check_form_entry_all_id_value ) {
		
		$form_entries[] = GFAPI::get_entry( $check_form_entry_all_id_value );
	}

	if ( ! empty( $form_entries ) && is_array( $form_entries ) ) { ?>

		<table class="user_feedback_details wp-list-table widefat fixed striped table-view-list" id="user_feedback_details">

			<thead>

				<tr>

					<td class=""><?php echo __( 'Display Name','ld-gravityform-addon' ); ?></td>


					<?php 
					if ( ! empty( $selected_post_form_id ) ) {

						$gravity_forms_fields = GFAPI::get_form( $selected_post_form_id );
						foreach ( $gravity_forms_fields['fields'] as $gravity_forms_fields_key => $gravity_forms_fields_value ) { 
							if ( $gravity_forms_fields_value->type != "hidden" ) { 
								$all_fields[] = $gravity_forms_fields_value->id; ?>
								<td class=""><?php echo $gravity_forms_fields_value->label; ?></td>
							<?php 
							}
						}
					} ?>

				</tr>

			</thead>

			<tbody>

				<?php	

				foreach ( $form_entries as $form_entries_key => $form_entries_value ) { ?>

					<tr>

						<?php 
						if ( ! empty( $form_entries_value['created_by'] ) ) { 
							$user_display_name = get_user_by( 'id', $form_entries_value['created_by'] ); ?>
							<td class=""><?php echo $user_display_name->display_name; ?></td>
						<?php } else { ?>
							<td class=""></td>
						<?php }

						foreach ( $all_fields as $all_fields_key=> $all_fields_value ) { ?>
							<td class=""><?php echo $form_entries_value[$all_fields_value]; ?></td>
						<?php } ?>

					</tr>

					<?php
				} ?>

			</tbody>

		</table>

	<?php
	}
	
	die;
}

/**
 * Display shortcode on lesson page
 */
add_filter( 'the_content', 'lesson_page_detect' );
function lesson_page_detect($content) {

	if ( get_post_type( get_the_ID() ) == 'sfwd-lessons' ) {

		$referer = $_SERVER['HTTP_REFERER'];

		$get_parent_id  = learndash_get_setting( get_the_ID(), 'course' );
		$get_lesson_list =  learndash_get_lesson_list( $get_parent_id, array( 'num' => 0 ) );
		$get_lesson_list_reverse = array_reverse( $get_lesson_list );
		$get_last_lesson_id = $get_lesson_list_reverse[0]->ID; 

		if (  $get_last_lesson_id == get_the_ID() ) {

			if ( strpos( $referer,'quizzes' ) !== false ) {

				$selected_gravity_form = get_post_meta( $get_parent_id, 'selected_gravity_form', true );
				$content .= '<div id="quiz_section_gravity_form" class="quiz_section_gravity_form">';
				$content .= '[gravityform id="' . $selected_gravity_form . '"]';
				$content .= '<div>';
				
			} else {

				$selected_gravity_form = get_post_meta( $get_parent_id, 'selected_gravity_form', true );
				if ( ! empty( $selected_gravity_form ) ) {
					$content .= '[gravityform id="' . $selected_gravity_form . '"]';
				}

			}
			  	
		} else {

			if ( strpos( $referer,'quizzes' ) !== false ) {

				$selected_gravity_form = get_post_meta( get_the_ID(), 'selected_gravity_form', true );
				$content .= '<div id="quiz_section_gravity_form" class="quiz_section_gravity_form">';
				$content .= '[gravityform id="' . $selected_gravity_form . '"]';
				$content .= '<div>';

			} else {

				$selected_gravity_form = get_post_meta( get_the_ID(), 'selected_gravity_form', true );
				if ( ! empty( $selected_gravity_form ) ) {
					$content .= '[gravityform id="' . $selected_gravity_form . '"]';
				}

			}
		}

	}

	return $content;
}

/**
 * Redirect to lesson page after quiz complete
 */
add_filter( "learndash_completion_redirect", function( $link, $post_id ) {
	$get_parent_id  = learndash_get_setting( $post_id, 'lesson' );
	$link= get_permalink( $get_parent_id);
	return $link;
}, 5, 2);

/**
 * Redirect to next lesson after form submit
 */
add_filter( 'gform_confirmation', 'custom_confirmation', 10, 4 );
function custom_confirmation( $confirmation, $form, $entry, $ajax ) {
	global $wpdb;

    $referer_url = $_SERVER['HTTP_REFERER'];
    $get_last_part = basename( $referer_url );

    $get_post_id = $wpdb->get_var("SELECT `id` FROM " . $wpdb->prefix . "posts WHERE post_name = '" . $get_last_part . "'");
    $get_course_id  = learndash_get_setting( $get_post_id, 'course' );
    $get_all_lesson_list =  learndash_get_lesson_list( $get_course_id, array( 'num' => 0 ) );

	$last_key = end( array_keys( $get_all_lesson_list ) );

	foreach ( $get_all_lesson_list as $get_all_lesson_list_key => $get_all_lesson_list_value ) {
		if ( $get_post_id == $get_all_lesson_list_value->ID  ) {
			
			if ( $get_all_lesson_list_key == $last_key  ) {
				/*$confirmation_redirect_url = get_permalink( $get_course_id );
				learndash_lesson_topics_completed($get_all_lesson_list_value->ID,true);
				$confirmation = array( 'redirect' => $confirmation_redirect_url );*/
			} else {
				$new_key = $get_all_lesson_list_key + 1;
				$confirmation_redirect_url = get_permalink( $get_all_lesson_list[$new_key]->ID );
				learndash_lesson_topics_completed($get_all_lesson_list_value->ID,true);
				$confirmation = array( 'redirect' => $confirmation_redirect_url  );
			}
		}
	}

	return $confirmation;
}