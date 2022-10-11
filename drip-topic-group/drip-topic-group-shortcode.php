<?php 

add_shortcode( 'topic_group_drip', 'topic_group_drip_func' );
function topic_group_drip_func( $atts ) {
	
	if ( learndash_is_group_leader_user( get_current_user_id() ) ) { ?>

		<div class="container">

	        <?php 

			$grp_id = array();
			$group_ids = learndash_get_administrators_group_ids( get_current_user_id() );

			if ( ! empty( $group_ids ) && is_array( $group_ids ) ) {
				foreach ( $group_ids as $group_id ) {
					$grp_id[] = $group_id;
				}
			} 

			if ( ! empty( $grp_id ) && is_array( $grp_id ) ) { ?>

				<div class="group_name_section" id="group_name_section">
					<label class="font-weight-bold display-4" for="group_name_id"><?php echo __( 'Select Group : ','drip-group-topic' ); ?></label>
					<select name="group_name_id" id="group_name_id">
						<option value=""><?php echo __( 'Select Group','drip-group-topic' ); ?></option>
						<?php 
						foreach ( $grp_id as $grp_id_key ) { ?>
							<option value="<?php echo $grp_id_key; ?>"><?php echo get_the_title( $grp_id_key ); ?></option>
						<?php } ?>
					</select>
				</div>

				<ul class="nav nav-pills scheduling-management-tab"></ul>

				<div class="tab-content">

					<div class="tab-pane fade in active" id="set-scheduling">
						<form method="post" class="save_topic_drip_meta_data" id="save_topic_drip_meta_data" action="">
							<div class="course_name_section" id="course_name_section"></div><br/><br/>
							<div class="container topic_drip_setting_table" id="topic_drip_setting_table"></div>
						</form>
					</div>

					<div class="tab-pane fade" id="set-course">
						<form method="post" class="save_topic_drip_meta_data_course" id="save_topic_drip_meta_data_course" action="">
							<div class="course_name_section_course" id="course_name_section_course"></div><br/><br/>
						</form>
					</div>

					<div class="tab-pane fade" id="set-user">
						<form method="post" class="save_topic_drip_meta_data_user" id="save_topic_drip_meta_data_user" action="">
							<div class="course_name_section_user" id="course_name_section_user"></div><br/><br/>
						</form>
					</div>

				</div>

			<?php } ?>

		</div>
		
	<?php } 

}