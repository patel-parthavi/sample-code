<?php
/**
 * Accume Partners functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Accume_Partners
 * @since 1.0
 */

/**
 * Accume Partners only works in WordPress 4.7 or later.
 */
if ( version_compare( $GLOBALS['wp_version'], '4.7-alpha', '<' ) ) {
	require get_template_directory() . '/inc/back-compat.php';
	return;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function accumepartners_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed at WordPress.org. See: https://translate.wordpress.org/projects/wp-themes/accumepartners
	 * If you're building a theme based on Accume Partners, use a find and replace
	 * to change 'accumepartners' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'accumepartners' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );
	add_post_type_support( 'page', 'excerpt' );

	add_image_size( 'accumepartners-featured-image', 2000, 1200, true );

	add_image_size( 'accumepartners-thumbnail-avatar', 100, 100, true );

	// Set the default content width.
	$GLOBALS['content_width'] = 525;

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'top'    => __( 'Top Menu', 'accumepartners' ),
		'quick-links' => __( 'Quick Links Menu', 'accumepartners' ),
		'social' => __( 'Social Links Menu', 'accumepartners' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	// Add theme support for Custom Logo.
	add_theme_support( 'custom-logo', array(
		'width'       => 736,
		'height'      => 76,
		'flex-width'  => true,
	) );

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/*
	 * This theme styles the visual editor to resemble the theme style,
	 * specifically font, colors, and column width.
 	 */
	add_editor_style( array( 'assets/css/editor-style.css', accumepartners_fonts_url() ) );

}
add_action( 'after_setup_theme', 'accumepartners_setup' );

/**
 * Register custom fonts.
 */
function accumepartners_fonts_url() {
	return 'https://fonts.googleapis.com/css?family=Josefin+Sans:100,100i,300,300i,400,400i,600,600i,700,700i|Poppins:100,100i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i';
}

/**
 * Add preconnect for Google Fonts.
 *
 * @since Accume Partners 1.0
 *
 * @param array  $urls           URLs to print for resource hints.
 * @param string $relation_type  The relation type the URLs are printed.
 * @return array $urls           URLs to print for resource hints.
 */
function accumepartners_resource_hints( $urls, $relation_type ) {
	if ( wp_style_is( 'accumepartners-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'accumepartners_resource_hints', 10, 2 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function accumepartners_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Blog Sidebar', 'accumepartners' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Add widgets here to appear in your sidebar on blog posts and archive pages.', 'accumepartners' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
	register_sidebar( array(
		'name'          => __( 'Newsletter Listing', 'accumepartners' ),
		'id'            => 'sidebar-4',
		'description'   => __( 'Add widgets here to appear in your footer.', 'accumepartners' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h5 class="widget-title">',
		'after_title'   => '</h5>',
	) );
}
add_action( 'widgets_init', 'accumepartners_widgets_init' );

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with ... and
 * a 'Continue reading' link.
 *
 * @since Accume Partners 1.0
 *
 * @param string $link Link to single post/page.
 * @return string 'Continue reading' link prepended with an ellipsis.
 */
function accumepartners_excerpt_more( $link ) {
	if ( is_admin() ) {
		return $link;
	}

	return ' &hellip; ';
}
add_filter( 'excerpt_more', 'accumepartners_excerpt_more' );

/**
 * Handles JavaScript detection.
 *
 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
 *
 * @since Accume Partners 1.0
 */
function accumepartners_javascript_detection() {
	echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}
add_action( 'wp_head', 'accumepartners_javascript_detection', 0 );

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function accumepartners_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", get_bloginfo( 'pingback_url' ) );
	}
}
add_action( 'wp_head', 'accumepartners_pingback_header' );


add_action( 'init', 'action__init' );
function action__init(){

	wp_register_style( 'accumepartners-service', get_theme_file_uri( '/assets/css/service.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-leadership', get_theme_file_uri( '/assets/css/leadership.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-locations', get_theme_file_uri( '/assets/css/locations.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-about', get_theme_file_uri( '/assets/css/about.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-values', get_theme_file_uri( '/assets/css/our-values.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-announcements', get_theme_file_uri( '/assets/css/announcements.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-industries-list', get_theme_file_uri( '/assets/css/industries-list.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-industry-detail', get_theme_file_uri( '/assets/css/industry-detail.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-contact-us', get_theme_file_uri( '/assets/css/contact-us.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-insights', get_theme_file_uri( '/assets/css/insights.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-careers', get_theme_file_uri( '/assets/css/careers.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-whitepaper-list', get_theme_file_uri( '/assets/css/whitepaper-list.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-newsletter-list', get_theme_file_uri( '/assets/css/newsletter-list.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-whitepaper', get_theme_file_uri( '/assets/css/whitepaper.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-case-study', get_theme_file_uri( '/assets/css/case-study.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-newsletter-listing', get_theme_file_uri( '/assets/css/newsletter-listing.css' ), array( 'accumepartners-style' ), '1.0' );
    wp_register_style( 'accumepartners-events', get_theme_file_uri( '/assets/css/events.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-webinars', get_theme_file_uri( '/assets/css/webinars.css' ), array( 'accumepartners-style' ), '1.0' );
	wp_register_style( 'accumepartners-collateral', get_theme_file_uri( '/assets/css/collateral.css' ), array( 'accumepartners-style' ), '1.0' );


	wp_register_script( 'google-map-api', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDa4yrm9eTZP84-Fc6TY3cbT_JSf27wSdg', array('jquery'), 'init' );
	wp_register_script( 'infobox', get_theme_file_uri( '/assets/js/infobox.js' ), array('google-map-api'), 'init' );
	wp_register_script( 'cookie', get_theme_file_uri( '/assets/js/jquery.cookie.js' ), array('jquery'), '1.0' );
	wp_register_script( 'jquery-ui', get_theme_file_uri( '/assets/js/jquery-ui.js' ), array('jquery'), '1.0' );

}

function inline_scripts() {
	ob_start();
	?>
	jQuery( document ).ready( function() { 
		setTimeout( function() {
			jQuery('.banner-image img').fadeIn(300); 
		}, 500 );
	});
	<?php
	return ob_get_clean();

}

/**
 * Enqueue scripts and styles.
 */
function accumepartners_scripts() {
	// Add custom fonts, used in the main stylesheet.
	wp_enqueue_style( 'accumepartners-fonts', accumepartners_fonts_url(), array(), null );
	wp_enqueue_style( 'fontawesome',get_theme_file_uri( '/assets/fonts/font-awesome.min.css' ) , array(), '4.7.0'  );
	wp_enqueue_style( 'accume-fonts',get_theme_file_uri( '/assets/css/styles.min.css' ) , array(), '1.0'  );
	wp_enqueue_style( 'animate', get_theme_file_uri( '/assets/css/animate.css' ), array(), '3.6.2' );
	wp_enqueue_style( 'mCustomScrollbar', get_theme_file_uri( '/assets/css/jquery.mCustomScrollbar.css' ), array(), '3.1.13' );
	wp_enqueue_style( 'fancybox', get_theme_file_uri( '/assets/css/jquery.fancybox.css' ), array(), '3.3.5' );



	// Theme stylesheet.
	wp_enqueue_style( 'accumepartners-style', get_stylesheet_uri() );
	wp_enqueue_style( 'accumepartners-design', get_theme_file_uri( '/assets/css/design.css' ), array('accumepartners-style'), '1.0' );




	// Load the dark colorscheme.
	if ( 'dark' === get_theme_mod( 'colorscheme', 'light' ) || is_customize_preview() ) {
		wp_enqueue_style( 'accumepartners-colors-dark', get_theme_file_uri( '/assets/css/colors-dark.css' ), array( 'accumepartners-style' ), '1.0' );
	}

	// Load the html5 shiv.
	wp_enqueue_script( 'html5', get_theme_file_uri( '/assets/js/html5.js' ), array(), '3.7.3' );
	wp_script_add_data( 'html5', 'conditional', 'lt IE 9' );
	
	wp_enqueue_script( 'slick', get_theme_file_uri( '/assets/js/slick.min.js' ), array('jquery'), '1.6.0' );
	wp_enqueue_script( 'fancybox', get_theme_file_uri( '/assets/js/jquery.fancybox.js' ), array('jquery'), '3.3.5' );
	wp_enqueue_script( 'wow', get_theme_file_uri( '/assets/js/wow.min.js' ), array('jquery'), '1.3.0' );
	wp_add_inline_script( 'wow', 'new WOW({mobile:false}).init();' );
	wp_enqueue_script( 'waypoints', get_theme_file_uri( '/assets/js/waypoints.min.js' ), array('jquery'), '1.6.2' );
	wp_enqueue_script( 'counter', get_theme_file_uri( '/assets/js/jquery.counterup.min.js' ), array('jquery'), '1.0' );




	wp_enqueue_script( 'mCustomScrollbar', get_theme_file_uri( '/assets/js/jquery.mCustomScrollbar.concat.min.js' ), array('jquery'), '3.1.13' );

	// wp_enqueue_script( 'accumepartners-skip-link-focus-fix', get_theme_file_uri( '/assets/js/skip-link-focus-fix.js' ), array(), '1.0', true );



	wp_enqueue_script( 'accumepartners-global', get_theme_file_uri( '/assets/js/global.js' ), array( 'jquery' ), '1.0', true );
	wp_add_inline_script( 'accumepartners-global', inline_scripts() );

	wp_enqueue_script( 'jquery-scrollto', get_theme_file_uri( '/assets/js/jquery.scrollTo.js' ), array( 'jquery' ), '2.1.2', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'accumepartners_scripts' );

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for content images.
 *
 * @since Accume Partners 1.0
 *
 * @param string $sizes A source size value for use in a 'sizes' attribute.
 * @param array  $size  Image size. Accepts an array of width and height
 *                      values in pixels (in that order).
 * @return string A source size value for use in a content image 'sizes' attribute.
 */
function accumepartners_content_image_sizes_attr( $sizes, $size ) {
	$width = $size[0];

	if ( 740 <= $width ) {
		$sizes = '(max-width: 706px) 89vw, (max-width: 767px) 82vw, 740px';
	}

	if ( is_active_sidebar( 'sidebar-1' ) || is_archive() || is_search() || is_home() || is_page() ) {
		if ( ! ( is_page() && 'one-column' === get_theme_mod( 'page_options' ) ) && 767 <= $width ) {
			 $sizes = '(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px';
		}
	}

	return $sizes;
}
// add_filter( 'wp_calculate_image_sizes', 'accumepartners_content_image_sizes_attr', 10, 2 );

/**
 * Filter the `sizes` value in the header image markup.
 *
 * @since Accume Partners 1.0
 *
 * @param string $html   The HTML image tag markup being filtered.
 * @param object $header The custom header object returned by 'get_custom_header()'.
 * @param array  $attr   Array of the attributes for the image tag.
 * @return string The filtered header image HTML.
 */
function accumepartners_header_image_tag( $html, $header, $attr ) {
	if ( isset( $attr['sizes'] ) ) {
		$html = str_replace( $attr['sizes'], '100vw', $html );
	}
	return $html;
}
add_filter( 'get_header_image_tag', 'accumepartners_header_image_tag', 10, 3 );

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for post thumbnails.
 *
 * @since Accume Partners 1.0
 *
 * @param array $attr       Attributes for the image markup.
 * @param int   $attachment Image attachment ID.
 * @param array $size       Registered image size or flat array of height and width dimensions.
 * @return array The filtered attributes for the image markup.
 */
// function accumepartners_post_thumbnail_sizes_attr( $attr, $attachment, $size ) {
// 	if ( is_archive() || is_search() || is_home() ) {
// 		$attr['sizes'] = '(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px';
// 	} else {
// 		$attr['sizes'] = '100vw';
// 	}
// 
// 	return $attr;
// }
// add_filter( 'wp_get_attachment_image_attributes', 'accumepartners_post_thumbnail_sizes_attr', 10, 3 );


/**
 * Use front-page.php when Front page displays is set to a static page.
 *
 * @since Accume Partners 1.0
 *
 * @param string $template front-page.php.
 *
 * @return string The template to be used: blank if is_home() is true (defaults to index.php), else $template.
 */
function accumepartners_front_page_template( $template ) {
	return is_home() ? '' : $template;
}
add_filter( 'frontpage_template',  'accumepartners_front_page_template' );

/**
 * Modifies tag cloud widget arguments to display all tags in the same font size
 * and use list format for better accessibility.
 *
 * @since Accume Partners 1.4
 *
 * @param array $args Arguments for tag cloud widget.
 * @return array The filtered arguments for tag cloud widget.
 */
function accumepartners_widget_tag_cloud_args( $args ) {
	$args['largest']  = 1;
	$args['smallest'] = 1;
	$args['unit']     = 'em';
	$args['format']   = 'list';

	return $args;
}
add_filter( 'widget_tag_cloud_args', 'accumepartners_widget_tag_cloud_args' );

/**
 * Implement the Custom Header feature.
 */
// require get_parent_theme_file_path( '/inc/custom-header.php' );

/**
 * Custom template tags for this theme.
 */
require get_parent_theme_file_path( '/inc/template-tags.php' );

/**
 * Additional features to allow styling of the templates.
 */
require get_parent_theme_file_path( '/inc/template-functions.php' );

/**
 * Customizer additions.
 */
require get_parent_theme_file_path( '/inc/customizer.php' );

/**
 * SVG icons functions and filters.
 */
// require get_parent_theme_file_path( '/inc/icon-functions.php' );



add_action( 'save_post', 'action_save_post', 10, 3 );
function action_save_post( $post_id ) {
	if( 'contact-us.php' != get_page_template_slug( $post_id ) )
		return;


	$add1 = get_field( 'address_box', $post_id );
	if ( !empty( $add1 ) ) {
		$findadd = str_replace(" ","+",$add1);
		$map_url11 = str_replace("<br+/>","+",$findadd);
		$url = "http://maps.google.com/maps/api/geocode/json?address=".$map_url11."&sensor=false";
		$response = file_get_contents($url);
		$response = json_decode($response, true);
		//print_r($response);
		$lat = $response['results'][0]['geometry']['location']['lat'];
		$long = $response['results'][0]['geometry']['location']['lng'];

		if ( !$lat && !$long ) {
			add_filter( 'redirect_post_location', 'add_notice_query_var', 999 );
		}
		else {
			update_post_meta( $post_id, 'latitude', $lat );
			update_post_meta( $post_id, 'longitude', $long );
		}
	}

}

function add_notice_query_var( $location ) {
	remove_filter( 'redirect_post_location', 'add_notice_query_var', 999 );
	return add_query_arg( array( 'map-address' => '1' ), $location );
}

add_action( 'admin_notices', 'my_error_notice' );
function my_error_notice() {

	if ( ! isset( $_GET['map-address'] ) )
		return;

	echo '<div class="error notice">
		<p>' . __( 'Please check address and update page again to dislay map.', 'accumepartners' ) . '</p>
	</div>';
}


// announcements ajax function
add_action('wp_ajax_data_fetch_announcements' , 'data_fetch_announcements');
add_action('wp_ajax_nopriv_data_fetch_announcements','data_fetch_announcements');

function data_fetch_announcements() {
	$args = array(
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'order'   => 'ASC',
		's' => esc_attr( $_REQUEST['keyword'] ),
		'post_type' => 'announcements',
		'orderby'        => 'menu_order',
	);

	if (
		isset( $_REQUEST[ 'original' ] )
		&& !empty( $_REQUEST[ 'original' ] )
	){
		$data = stripslashes( $_REQUEST[ 'original' ] );
		$args = json_decode( $data, true );
	}

    $the_query = new WP_Query( $args );

    if( $the_query->have_posts() ) {
		echo '<div class="announcements announcements-content-section">';

		$ac =1;
		while ( $the_query->have_posts() ) {
			$the_query->the_post();

			echo '<div class="announcements-accordion wow fadeInUp">'.
				'<input id="accord-' . $ac . '" name="accordion" type="radio" />' .
				'<div class="accordion-title">'.
					'<label for="accord-' . $ac . '">'. get_the_title() . '<i class="fa fa-angle-down" aria-hidden="true"></i></label>' .
				'</div>' .
				'<div class="acc-cont">' .
					'<div class="Announcement-info flex flex-justify-start">' .
						(
							get_the_date()
							? '<div class="announcements-date">'. get_the_date('F d, Y') . '</div>'
							: ''
						) .
						(
							get_the_time()
							? '<div class="announcements-time">'. get_the_time() .' '. __( 'Estern Daylight Time', 'accumepartners' ). '</div>'
							: ''
						) .
					'</div>' .
					(
						get_the_content()
						? '<div class="announcements-accordion-content">'. apply_filters( 'the_content', get_the_content() ) . '</div>'
						: ''
					) .
				'</div>' .
			'</div>';


			$ac++;
		}

		wp_reset_postdata();
	}
	else {
		echo '<div class="announcements announcements-content-section wow fadeInUp">' .
			'<div class="announcements-accordion nodatafound">'.
				'<div class="accordion-title">'.
					'<label>No data found</label>' .
				'</div>' .
			'</div>' .
		'</div>';
	}
	die();
}

// Twitter feed shortcode

function shortcode_twitter() {

	$tweet = '';

		if( get_field( 'tweets' ) ) {
			$tweet .= '<div class="tweet-blocks">';

			while ( have_rows('tweets') ) {
				the_row();

				$tweet .= '<div class="tweet-item flex flex-align-center">' .
					'<div class="left-tweet">' .
					 (
						get_sub_field( 'tweet_title' )
						? '<h4>' .
							( get_sub_field( 'twitter_url' ) ? '<a href="' .  esc_url( 'twitter_url' ) . '" target="_blank">' : '' ) .
								get_sub_field( 'tweet_title' )  .
							( get_sub_field( 'twitter_url' ) ? '</a>' : '' ) .
						'</h4>'
						: ''
					) .
					'<a class="tweet-this" href="' . esc_url( get_sub_field( 'twitter_url' ) ) . '" target="_blank"><i class="fa fa-twitter" aria-hidden="true"></i> Tweet this</a>' .
					'</div>' .
					'<div class="right-tweet">' .
						get_sub_field( 'tweet_content' ) .
					'</div>' .
				'</div>';
			}

			$tweet .= '</div>';
		}

	return $tweet;
}
add_shortcode('twitter', 'shortcode_twitter');



function myprefix_button_shortcode( $atts, $content = null ) {

	// Extract shortcode attributes
	extract( shortcode_atts( array(
		'url'    => '',
		'title'  => '',
		'target' => '',
		'text'   => '',
		'class'  => 'light',
	), $atts ) );

	$content = $text ? $text : $content;

	if ( $url ) {
		$link_attr = array(
			'href'   => esc_url( $url ),
			'title'  => esc_attr( $title ),
			'target' => ( 'blank' == $target ) ? '_blank' : '',
			'class'  => 'cta-button ' . esc_attr( $class ),
		);
		$link_attrs_str = '';
		foreach ( $link_attr as $key => $val ) {

			if ( $val )
				$link_attrs_str .= ' ' . $key . '="' . $val . '"';
		}
		return wpautop( '<a ' . $link_attrs_str . '><span>' . do_shortcode( $content ) . '</span></a>' );
	}
	else {
		return wpautop( '<span class="cta-button"><span>' . do_shortcode( $content ) . '</span></span>' );
	}
}
add_shortcode( 'button', 'myprefix_button_shortcode' );


/*download pdf button*/
function myprefix_downloadbutton_shortcode( $atts, $content = null ) {

	// Extract shortcode attributes
	extract( shortcode_atts( array(
		'url'    => '',
		'title'  => '',
		'target' => '',
		'text'   => '',
		'class'  => '',
	), $atts ) );

	$content = $text ? $text : $content;

	if ( $url ) {
		$link_attr = array(
			'href'   => esc_url( $url ),
			'title'  => esc_attr( $title ),
			'target' => ( 'blank' == $target ) ? '_blank' : '',
			//'class'  => 'dwnload-cta-button ' . esc_attr( $class ),
		);
		$link_attrs_str = '';
		foreach ( $link_attr as $key => $val ) {

			if ( $val )
				$link_attrs_str .= ' ' . $key . '="' . $val . '"';
		}

		return wpautop( '<span class="dwnload-cta-button"><strong>' . do_shortcode( $content ) . '</strong><a ' . $link_attrs_str . ' class="dwnloadicon"><img class="hovereffect" src="'. get_stylesheet_directory_uri() .'/assets/images/pdf-download-icon-hov.png" alt=""/><img class="normaleffect" src="'. get_stylesheet_directory_uri(). '/assets/images/pdf-download-icon.png" alt=""/></a></span>' );
	}
	else {
		return wpautop( '<span class="dwnload-cta-button"><strong>' . do_shortcode( $content ) . '</strong><span class="dwnloadicon"><img class="hovereffect" src="'. get_stylesheet_directory_uri() .'/assets/images/pdf-download-icon-hov.png" alt=""/><img class="normaleffect" src="'. get_stylesheet_directory_uri(). '/assets/images/pdf-download-icon.png" alt=""/></span></span>' );
	}
}
add_shortcode( 'downloadbutton', 'myprefix_downloadbutton_shortcode' );
/*download pdf button*/

function more_post_ajax(){
  //echo "<pre>"; print_r($_POST);
 //$lastpost = $_POST['lastpost'];
 $offset = $_POST['offset'];
 $np = $_POST['number_of_post'];
 $total_post = $_POST['total_post'];

 header("Content-Type: text/html");
 $today = date('Ymd');

	$args_event = array (
	    'post_type' => 'event',
	    'posts_per_page' => $np,
	    'post_status' => 'publish',
		'offset' => $offset,
	    // 'meta_key' => 'start_date',
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
	    //'offset' => $_POST['lastpost'] + 1
	);

	$data_event = new WP_Query($args_event);
	$cnt = $data_event->post_count;

	$count = $offset+$np;
    $count_new = 0;

	if( $count < $total_post ) {
		$count_new = 1;
	}

		if ( $data_event->have_posts() ) {
			?>
			<div class="event event-content-section">
				<?php
				while ( $data_event->have_posts() ) {
					$data_event->the_post();

					$start_date = get_field('start_date', false, false);
					$start_event_date = new DateTime($start_date);

					$end_date = get_field('end_date', false, false);
					$end_event_date = new DateTime($end_date);

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
				<?php  }  ?>

			</div>
			<?php
		}
		echo "||". $count_new;

        wp_reset_postdata();


    die();
}

add_action('wp_ajax_nopriv_more_post_ajax', 'more_post_ajax');
add_action('wp_ajax_more_post_ajax', 'more_post_ajax');

/*webinar*/
function more_webinar_ajax(){

 $offset = $_POST['offset'];
 $np = $_POST['number_of_post'];
 $total_post = $_POST['total_post'];

 header("Content-Type: text/html");

	$args_event = array (
	    'post_type' => 'webinar',
	    'posts_per_page' => $np,
		'post_status' => 'publish',
		'orderby' => 'menu_order',
		'order' => 'ASC',
		'offset' => $offset
	);

	$data_event = new WP_Query($args_event);
	$cnt = $data_event->post_count;

	//echo "<pre>"; print_r($data_event);

	$count = $offset+$cnt;
    $count_new = 0;

    if($count < $total_post)
      {
        $count_new = 1;
      }

		if ( $data_event->have_posts() ) { ?>
			<div class="event event-content-section">
				<?php
				while ( $data_event->have_posts() ) {
					$data_event->the_post();

					echo '<div class="event-content-block">'.

					    (
							get_the_title()
							? '<div class="event-title">'. get_the_title() .'</div>'
							: ''
						) .
						(
							get_the_content()
							? '<div class="event-content">'. apply_filters( 'the_content', get_the_content() ) . '</div>'
							: ''
						) .

					'</div>';

					?>
				<?php  }  ?>

			</div>
			<?php
		}
		echo "||". $count_new;

        wp_reset_postdata();


    die();
}

add_action('wp_ajax_nopriv_more_webinar_ajax', 'more_webinar_ajax');
add_action('wp_ajax_more_webinar_ajax', 'more_webinar_ajax');


function cptui_register_my_cpts() {

	/**
	 * Post Type: Services.
	 */

	$labels = array(
		"name" => __( "Services", "accumepartners" ),
		"singular_name" => __( "Service", "accumepartners" ),
	);

	$args = array(
		"label" => __( "Services", "accumepartners" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "service", "with_front" => true ),
		"query_var" => true,
		"supports" => array( "title", "editor", "thumbnail", "excerpt" ),
	);

	register_post_type( "service", $args );

	/**
	 * Post Type: Leaderships.
	 */

	$labels = array(
		"name" => __( "Leaderships", "accumepartners" ),
		"singular_name" => __( "Leadership", "accumepartners" ),
	);

	$args = array(
		"label" => __( "Leaderships", "accumepartners" ),
		"labels" => $labels,
		"description" => "",
		"public" => false,
		"publicly_queryable" => false,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "leadership", "with_front" => true ),
		"query_var" => true,
		"supports" => array( "title", "editor", "thumbnail", "excerpt" ),
	);

	register_post_type( "leadership", $args );

	/**
	 * Post Type: Announcements.
	 */

	$labels = array(
		"name" => __( "Announcements", "accumepartners" ),
		"singular_name" => __( "Announcement", "accumepartners" ),
	);

	$args = array(
		"label" => __( "Announcements", "accumepartners" ),
		"labels" => $labels,
		"description" => "",
		"public" => false,
		"publicly_queryable" => false,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "announcement", "with_front" => true ),
		"query_var" => true,
		"supports" => array( "title", "editor", "thumbnail" ),
	);

	register_post_type( "announcements", $args );

	/**
	 * Post Type: Career.
	 */

	$labels = array(
		"name" => __( "Career", "accumepartners" ),
		"singular_name" => __( "Career", "accumepartners" ),
	);

	$args = array(
		"label" => __( "Career", "accumepartners" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => true,
		"rewrite" => array( "slug" => "career", "with_front" => true ),
		"query_var" => true,
		"supports" => array( "title", "editor", "thumbnail" ),
		"taxonomies" => array( "career_type" ),
	);

	register_post_type( "career", $args );

	/**
	 * Post Type: Events.
	 */

	$labels = array(
		"name" => __( "Events", "accumepartners" ),
		"singular_name" => __( "Event", "accumepartners" ),
	);

	$args = array(
		"label" => __( "Events", "accumepartners" ),
		"labels" => $labels,
		"description" => "",
		"public" => false,
		"publicly_queryable" => false,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "event", "with_front" => true ),
		"query_var" => true,
		"supports" => array( "title", "editor", "thumbnail", "excerpt" ),
	);

	register_post_type( "event", $args );

	/**
	 * Post Type: Newsletter.
	 */
	
	/*
	$labels = array(
		"name" => __( "Newsletter", "accumepartners" ),
		"singular_name" => __( "Newsletter", "accumepartners" ),
	);

	$args = array(
		"label" => __( "Newsletter", "accumepartners" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => true,
		"rewrite" => array( "slug" => "newsletter", "with_front" => true ),
		"query_var" => true,
		"supports" => array( "title", "editor", "excerpt" ),
		"taxonomies" => array( "newsletter_type" ),
	);

	register_post_type( "newsletter", $args );
	*/

	/**
	 * Post Type: White Pappers.
	 */

	$labels = array(
		"name" => __( "White Pappers", "accumepartners" ),
		"singular_name" => __( "White Papper", "accumepartners" ),
	);

	$args = array(
		"label" => __( "White Pappers", "accumepartners" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => true,
		"rewrite" => array( "slug" => "white-paper", "with_front" => true ),
		"query_var" => true,
		"supports" => array( "title", "editor", "thumbnail", "excerpt" ),
	);

	register_post_type( "white_paper", $args );

	/**
	 * Post Type: Webinars.
	 */

	$labels = array(
		"name" => __( "Webinars", "accumepartners" ),
		"singular_name" => __( "Webinar", "accumepartners" ),
	);

	$args = array(
		"label" => __( "Webinars", "accumepartners" ),
		"labels" => $labels,
		"description" => "",
		"public" => false,
		"publicly_queryable" => false,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "webinar", "with_front" => true ),
		"query_var" => true,
		"supports" => array( "title", "editor", "thumbnail", "excerpt" ),
	);

	register_post_type( "webinar", $args );
}

add_action( 'init', 'cptui_register_my_cpts' );

function cptui_register_my_taxes() {

	/**
	 * Taxonomy: Service Type.
	 */

	$labels = array(
		"name" => __( "Service Type", "accumepartners" ),
		"singular_name" => __( "Service Type", "accumepartners" ),
	);

	$args = array(
		"label" => __( "Service Type", "accumepartners" ),
		"labels" => $labels,
		"public" => true,
		"hierarchical" => true,
		"label" => "Service Type",
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => false,
		"query_var" => true,
		"rewrite" => array( 'slug' => 'service-type', 'with_front' => true,  'hierarchical' => true, ),
		"show_admin_column" => true,
		"show_in_rest" => false,
		"rest_base" => "service_type",
		"show_in_quick_edit" => true,
	);
	register_taxonomy( "service_type", array( "service" ), $args );

	/**
	 * Taxonomy: Newletter Type.
	 */

	$labels = array(
		"name" => __( "Newletter Type", "accumepartners" ),
		"singular_name" => __( "Newletter Type", "accumepartners" ),
	);

	$args = array(
		"label" => __( "Newletter Type", "accumepartners" ),
		"labels" => $labels,
		"public" => true,
		"hierarchical" => true,
		"label" => "Newletter Type",
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => array( 'slug' => 'newsletter-type', 'with_front' => true, ),
		"show_admin_column" => true,
		"show_in_rest" => true,
		"rest_base" => "newsletter_type",
		"show_in_quick_edit" => true,
	);
	register_taxonomy( "newsletter_type", array( "newsletter" ), $args );

	/**
	 * Taxonomy: Career Type.
	 */

	$labels = array(
		"name" => __( "Career Type", "accumepartners" ),
		"singular_name" => __( "Career Type", "accumepartners" ),
	);

	$args = array(
		"label" => __( "Career Type", "accumepartners" ),
		"labels" => $labels,
		"public" => true,
		"hierarchical" => true,
		"label" => "Career Type",
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => false,
		"query_var" => true,
		"rewrite" => array( 'slug' => 'career-type', 'with_front' => true,  'hierarchical' => true, ),
		"show_admin_column" => true,
		"show_in_rest" => false,
		"rest_base" => "career_type",
		"show_in_quick_edit" => true,
	);
	register_taxonomy( "career_type", array( "career" ), $args );
}

add_action( 'init', 'cptui_register_my_taxes' );

add_action( 'wp_head', 'action_wp_head' );
function action_wp_head() {
	if ( is_singular( 'newsletter' ) )
		echo '<meta name="robots" content="noindex, follow">';

}
add_action( 'template_redirect', 'action__template_redirect' );
function action__template_redirect(){
	if ( is_singular( 'newsletter' ) ) {
		wp_redirect( home_url( '/newsletters/' ), 301 );
	   die;
	}
}

