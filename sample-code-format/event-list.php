<?php
/**
 * Template Name: Event List
 *
 * If the user has selected a static page for their homepage, this is what will
 * appear.
 * Learn more: https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Accume_Partners
 * @since 1.0
 * @version 1.0
 */


wp_enqueue_style( 'accumepartners-events' );

get_header();
$style = '';
?>


	<div class="banner-image">
		<?php
		if ( has_post_thumbnail( get_the_ID() ) )
			echo get_the_post_thumbnail( get_the_ID(), 'full' );
		else
			echo '<img src="' . get_stylesheet_directory_uri() . '/assets/images/services-bg.jpg" alt="services" />';
		?>

		<div class="container ab-center flex flex-justify-center flex-align-center">
			<?php
				echo '<h1 class="banner-title">' . ( get_field( 'banner_title' ) ? get_field('banner_title') : get_the_title() ) . '</h1>';
			?>
		</div>
	</div>
	<div class="box skew-clock main-content-box">
		<div class="skew-rev-clock">
			<div class="container entry-content">
                <div class="flex">
                    <div class="event-left-side">
                        <?php
                            while( have_posts() ): the_post();
                                the_content();
                            endwhile;
                        ?>

        				<div class="event-section">

        					<?php
							// $current_date = current_time('mysql');

        					 $today = date('Ymd');
        					/*echo $today;*/

        					
        					$per_page_display = (
        						!empty( get_field( 'display_per_page', get_the_ID() ) )
        						? get_field( 'display_per_page', get_the_ID() )
        						: 5
        					);
							$args_event = array (
        					    'post_type' => 'event',
        					    'posts_per_page' => $per_page_display,
        					    'post_status' => 'publish',
        					    'meta_key' => 'start_date',
								'orderby' => 'meta_value',
								'order' => 'ASC',
								'meta_query' => array(
									array(
										'key' => 'start_date',
										'value' => $today,
										'compare' => '>',
									),
								),

        					);

        					$data_event = new WP_Query($args_event);
        					//echo "<pre>"; print_r($career_data);
							
							$count_posts = $data_event->found_posts;
							
							
        				    if ( $data_event->have_posts() ) { ?>
        					<div class="event event-content-section">
        						<?php
        						//$i = 0;
        						while ( $data_event->have_posts() ) {
        							$data_event->the_post();

        							$start_date = get_field('start_date', false, false);
        							$start_event_date = new DateTime($start_date);

        							$end_date = get_field('end_date', false, false);
        							$end_event_date = new DateTime($end_date);
        							//echo '<i class="pointer" id="'.$i.'"></i>';
        							echo '<div class="event-content-block">'.

        								'<div class="event-post-date">'.
										// get_the_date('M d, Y') .
											$start_event_date->format('M d, Y') .
										'</div>'.

        								(
        									get_the_title()
        									? '<div class="event-title">'. get_the_title() .'</div>'
        									: ''
        								) .
        								(
        									get_the_excerpt()
        									? '<div class="event-excerpt">'. get_the_excerpt() .'</div>'
        									: ''
        								) .

        								'<div class="event-details" style="display:none">'.
                                            '<div class="event-info flex flex-justify-start">'.
        								    (
        										!empty($start_date)
        										? '<div class="event-date">'. $start_event_date->format('M d, Y') .
                                                    (
                                                        !empty( $end_date )
                                                        ? ' <span>To</span> '.$end_event_date->format('M d, Y')
                                                        : ''
                                                    ) .
                                                    '</div>'
        										: ''
        								    ) .
        								    (
        										get_field('event_address')
        										? '<div class="event-address">'. get_field('event_address') .'</div>'
        										: ''
        									) .
                                            '</div>'.
        									(
        										get_the_content()
        										? '<div class="event-content">'. apply_filters( 'the_content', get_the_content() ) . '</div>'
        										: ''
        									) .
        								'</div>'.
        								'<a href="#" class="show-more">View Details <i class="fa fa-angle-down" aria-hidden="true"></i></a>' .
            							'<a href="#" class="show-less" style="display:none">Less Details <i class="fa fa-angle-up" aria-hidden="true"></i></a>' .
        							'</div>';

        							?>


        						<?php //$i++;
        						} ?>

        					</div>
        					<?php
        					wp_reset_postdata();
        				} else {
							echo '<p>No Event found.</p>';
						}
        				?>
        				</div>
						<?php if( $count_posts > $per_page_display ) { ?>
        				<div id="more_posts_event">Load More</div>
						<?php } ?>
						
                    </div>
                    <div class="event-right-side">
                        <a href="<?php echo site_url(); ?>/events" class="event-tab current"> Upcoming Events </a>
                        <a href="<?php echo site_url(); ?>/webinars" class="event-tab"> Webinars </a>
                    </div>
                </div>
			</div>
		</div>
	</div>

<?php
get_template_part( 'coffee', 'bar' );
?>
<script type="text/javascript">

jQuery(document).ready(function() {

    jQuery(document).on('click', '.event-content-block .show-more', function(){

    	jQuery('.event-content-block').removeClass('view_all');
		jQuery('.show-more').hide().siblings('.event-excerpt').show();
		jQuery('.show-less').hide();
		jQuery('.show-more').siblings('.event-details').hide().siblings('.show-more').show();


		jQuery(this).parents('.event-content-block').addClass('view_all');
    	jQuery(this).hide().siblings('.event-excerpt').hide();
		jQuery(this).siblings('.event-details').show().siblings('.show-less').show();

		return false;

    });
    jQuery(document).on('click', '.event-content-block .show-less', function(){

			if( jQuery(this).parents('.event-content-block').hasClass('view_all') ) {
				jQuery(this).parents('.event-content-block').removeClass('view_all');
		    	jQuery(this).hide().siblings('.event-excerpt').show();
				jQuery(this).siblings('.event-details').hide().siblings('.show-more').show();
			}

		return false;
    });

});


var page = 1;
var total_post = '<?php echo $count_posts; ?>';
//alert(total_post);
var number_of_post = '<?php echo $per_page_display;?>';

function load_posts_event() {
	/*var aa = jQuery(".pointer:last").attr('id');
    alert(aa);*/

	var params =  {"offset":(page * number_of_post),"number_of_post":number_of_post,"total_post":total_post,action:"more_post_ajax"}

	jQuery.post("<?php echo home_url(); ?>/wp-admin/admin-ajax.php",params,function(data){

        page++;
        var mydata = data.split('||');
        
            if(mydata[0]){
            	jQuery(".event-section").append(mydata[0]);
            	jQuery("#more_posts_event").show();
           	} else {
                jQuery("#more_posts_event").hide();
            }
			
			if( !mydata[1] || mydata[1] == '0' ) {
				jQuery("#more_posts_event").remove();
			}
    });
}

jQuery(document).ready(function(){
	jQuery("#more_posts_event").on("click",function(){
		jQuery("#more_posts_event").hide();
        load_posts_event();
	});
});

</script>

<?php
get_footer();
