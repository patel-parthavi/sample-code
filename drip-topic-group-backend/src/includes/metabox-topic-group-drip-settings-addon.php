<?php

if ( ( class_exists( 'LearnDash_Settings_Metabox' ) ) && ( ! class_exists( 'Metabox_Topic_Group_Drip_Settings_Addon_Backend' ) ) ) {

	/**
	 * Class Metabox_Topic_Group_Drip_Settings_Addon_Backend
	 */
	class Metabox_Topic_Group_Drip_Settings_Addon_Backend extends \LearnDash_Settings_Metabox {


		/**
		 * Public constructor for class
		 */
		public function __construct() {

			// What screen ID are we showing on.
			$this->settings_screen_id = 'sfwd-topic';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_metabox_key = 'learndash-topic-group-drip-settings';

			// Section label/header.
			$this->settings_section_label = sprintf(
			// translators: placeholder: topic.
				esc_html_x( '%s Group Drip Settings', 'placeholder: Topic', 'learndash' ),
				learndash_get_custom_label( 'topic' )
			);

			$this->settings_section_description = sprintf(
			// translators: placeholder: topic.
				esc_html_x( 'Controls the timing and way %s can be accessed.', 'placeholder: topic', 'learndash' ),
				learndash_get_custom_label_lower( 'topic' )
			);

			add_filter(
				'learndash_metabox_save_fields_' . $this->settings_metabox_key,
				array(
					$this,
					'filter_saved_fields',
				),
				30,
				3
			);

			// Map internal settings field ID to legacy field ID.
			$this->settings_fields_map = array(
				'drip_visible_after_specific_date' => 'drip_visible_after_specific_date',
			);

			parent::__construct();
		}

		/**
		 * Initialize the metabox settings values.
		 */
		public function load_settings_values() {
			parent::load_settings_values();

			if ( true === $this->settings_values_loaded ) {

			}
		}

		/**
		 * Initialize the metabox settings fields.
		 */
		public function load_settings_fields() {

			global $post;
			$groups = get_posts(
				array(
					'post_type'      => 'groups',
					'posts_per_page' => 999,
					'post_status'    => 'publish',
					'orderby'        => 'title',
					'order'          => 'ASC',
				)
			);

			// If any group is not exists, this option will be disabled
			if ( ! $groups ) {
				return '';
			}

			if ( ! isset( $_GET['course_id'] ) ) {

				$ltopic_courses = learndash_get_courses_for_step( $post->ID );
				if ( 'yes' === LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Courses_Builder', 'shared_steps' )
					 && (
						 ( isset( $ltopic_courses['primary'] ) && ! empty( $ltopic_courses['primary'] ) )
						 || ( isset( $ltopic_courses['primary'] ) && ! empty( $ltopic_courses['secondary'] ) )
					 )
				) {
					echo sprintf(
						_x( 'Please select a %s to manage group drip dates.zz', 'Course  Label', 'group-drip-topic' ),
						LearnDash_Custom_Label::get_label( 'course' )
					);
					echo '<br />';
					echo $this->get_course_switch_html( 0 );
				} else {
					$topic_course_id = learndash_get_course_id( $post );

					if ( ! empty( $topic_course_id ) ) {
						$item_url = get_edit_post_link( $post->ID );
						$item_url = add_query_arg( 'course_id', $topic_course_id, $item_url );
						$item_url = add_query_arg( 'currentTab', 'sfwd-topic-settings', $item_url );

						echo "<a href='$item_url'>";
						echo esc_attr__( 'Please click here to set group drip dates.', 'group-drip-topic' );
						echo '</a>';
					} else {
						echo sprintf(
							_x( 'Drip dates can only be added if the %1$s is associated with a %2$s.', 'Course  Label', 'group-drip-topic' ),
							LearnDash_Custom_Label::get_label( 'topic' ),
							LearnDash_Custom_Label::get_label( 'course' )
						);
						echo '<br />';
						echo sprintf(
							_x( 'This %1$s has no %2$s associations.', 'Course  Label', 'group-drip-topic' ),
							LearnDash_Custom_Label::get_label( 'topic' ),
							LearnDash_Custom_Label::get_label( 'course' )
						);

					}
				}

				return '';
			}

			$note   = sprintf(
				_x( 'Note: A drip date set for a %1$s/Group persists across all %2$s. You cannot set different drip dates for the same %1$s/Group in different %2$s.', '1 topic and 2 Course  Label', 'group-drip-topic' ),
				LearnDash_Custom_Label::get_label( 'topic' ),
				LearnDash_Custom_Label::get_label( 'courses' )
			);
			$reload = esc_attr__( 'Note: Page reloads after Save/Remove date', 'group-drip-topic' );
			ob_start();

			?>
			<script>
				function uoAddSwitcher() {
					jQuery('<div style="clear:both;padding: 15px 0;"><?php echo $note; ?></div>').insertBefore("#uo-ld-group-drip");
					jQuery('<div style="clear:both; font-weight:bold; padding: 15px 0; text-align:right;"><?php echo $reload; ?></div>').insertAfter("#uo-ld-group-drip");
					jQuery('<div style="display:inline;"><?php echo $this->get_course_switch_html( $_GET['course_id'] ); ?></div>').appendTo("#uo-ld-group-drip_filter");
					jQuery(".dataTables_filter label").addClass("pull-right");
				};
			</script>

			<?php
			if ( 'yes' == LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Courses_Builder', 'shared_steps' ) ) {
				?>
					<div class="select_coourse_dropdown_shared">
							<?php echo $this->get_course_switch_html( $_GET['course_id'] ); ?>
					</div>
				<?php
			}

			?>

			<table id="uo-ld-group-drip" class="striped celled table backend-topic-drip-table" style="width:100%">
				<thead>
				<tr>
					<th><?php esc_attr_e( 'LearnDash group', 'group-drip-topic' ); ?></th>
					<th><?php esc_attr_e( 'Drip date', 'group-drip-topic' ); ?></th>
					<th><?php esc_attr_e( 'Action', 'group-drip-topic' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $groups as $group ) {
					if ( $group && is_object( $group ) ) {

						$group_has_course_access = learndash_group_has_course( $group->ID, $_GET['course_id'] );
						if ( ! $group_has_course_access ) {
							continue;
						}
						$date = get_post_meta( $post->ID, 'topic_drip_post_available_on_' . $group->ID, true );
						// Add tha ( date ) after group name on selection if exists
						if ( $date ) {
							if ( is_array( $date ) ) {
								$date   = \drip_topic_group_backend\AddonDripTopicByGroupBackend::reformat_date( $date );
								$u_date = $date;
								$date   = learndash_adjust_date_time_display( $date );
							}

							if ( \drip_topic_group_backend\AddonDripTopicByGroupBackend::is_timestamp( $date ) ) {
								$u_date = $date;
								$date   = \drip_topic_group_backend\AddonDripTopicByGroupBackend::adjust_for_timezone_difference( $date );
							}
						}
						?>
						<tr class="uo-ld-group-drip-row" data-group="<?php echo $group->ID; ?>"
							data-post="<?php echo $post->ID; ?>"
							data-sort="<?php echo $u_date; ?>>"
						>
							<td><?php echo $group->post_title; ?></td>
							<td>
								<?php
									$date = date( 'd-m-Y h:i A', $date );
								?>
								
								<div class="ld_date_selector">
									<input value="<?php echo $date; ?>" type="text" name="topic_drip_setting_<?php echo $group->ID; ?>]" class="topic_drip_date_setting topic_drip_date_setting_<?php echo $group->ID; ?>" id="topic_drip_date_setting_<?php echo $group->ID; ?>" />
								</div>
							</td>
							
							<td class="uo-ld-group-drip-row__actions">
								<input data-groupid = "<?php echo $group->ID; ?>" data-topicid="<?php echo $post->ID; ?>" value="Save" type="submit" name="topic_drip_date_save" id="topic_drip_all_date_save_backend" class="button-primary topic_drip_all_date_save_backend" />
								<div style="display: none;" id="data_insert_success_<?php echo $group->ID; ?>" class="data_insert_success_<?php echo $group->ID; ?>">
									<h4 class="text-center" style="color: green;"><?php echo __( 'Data inserted successfully', 'group-drip-topic' ); ?></h4>
								</div>
								
							
							</td>
						</tr>
						<?php
					}
				}
				?>
				</tbody>
			</table>
			

			<?php
			echo ob_get_clean();
		}

		public function get_course_switch_html( $course_post_id ) {

			global $post;

			if ( 'yes' !== LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Courses_Builder', 'shared_steps' ) ) {
				$course_name = get_the_title( $course_post_id );

				return '<div style="display: inline-block;padding: 0 20px 0 20px;margin-top: 8px;">' .
					sprintf(
						esc_attr_x( '%1$s: %2$s', 'Course  Label', 'learndash' ),
						LearnDash_Custom_Label::get_label( 'course' ),
						$course_name
					) .
					   '</div>';
			}

			$cb_courses = learndash_get_courses_for_step( $post->ID );

			$item_url = get_edit_post_link( $post->ID );
			$html     = '';

			$html .= '<label style="padding-right:15px;">' . sprintf( esc_html_x( 'Switch %s', 'placeholder: Course', 'learndash' ), LearnDash_Custom_Label::get_label( 'Course' ) ) . ': ';
			$html .= '<select onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">';
			$html .= '<option value="">' . sprintf( esc_html_x( 'Select a %s', 'placeholder: Course', 'learndash' ), LearnDash_Custom_Label::get_label( 'Course' ) ) . '</option>';

			foreach ( $cb_courses as $course_key => $course_set ) {

				foreach ( $course_set as $course_id => $course_title ) {

					$item_url = add_query_arg( 'course_id', $course_id, $item_url );
					$item_url = add_query_arg( 'currentTab', 'sfwd-topic-settings', $item_url );

					$selected = '';

					if ( $course_id == $course_post_id ) {
						$selected = ' selected="selected" ';
					}

					$html .= '<option ' . $selected . 'value="' . $item_url . '" >' . get_the_title( $course_id ) . '</option>';

				}
			}
			$html .= '</select></label>';

			return $html;
		}

		/**
		 * Filter settings values for metabox before save to database.
		 *
		 * @param array  $settings_values      Array of settings values.
		 * @param string $settings_metabox_key Metabox key.
		 * @param string $settings_screen_id   Screen ID.
		 *
		 * @return array $settings_values.
		 */
		public function filter_saved_fields( $settings_values = array(), $settings_metabox_key = '', $settings_screen_id = '' ) {

			return $settings_values;
		}

		// End of functions.
	}

	add_filter(
		'learndash_post_settings_metaboxes_init_' . learndash_get_post_type_slug( 'topic' ),
		function ( $metaboxes = array() ) {
			if ( ( ! isset( $metaboxes['Metabox_Topic_Group_Drip_Settings_Addon_Backend'] ) ) ) {
				$metaboxes['Metabox_Topic_Group_Drip_Settings_Addon_Backend'] = \Metabox_Topic_Group_Drip_Settings_Addon_Backend::add_metabox_instance();
			}

			return $metaboxes;
		},
		999,
		1
	);

}
