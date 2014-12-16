<?php
/**
 * This class manages all functionality with our Capture theme.
 */
class Capture {
	const CAPTURE_VERSION = '1.1.7';

	private static $instance; // Keep track of the instance

	/**
	 * Function used to create instance of class.
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) )
			self::$instance = new Capture;

		return self::$instance;
	}


	/**
	 * This function sets up all of the actions and filters on instance
	 */
	function __construct() {
		add_action( 'after_switch_theme', array( $this, 'after_switch_theme' ) ); // Flush rewrite rules on activation
		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 20 ); // Register image sizes
		add_action( 'widgets_init', array( $this, 'widgets_init' ), 20 ); // Unregister sidebars and alter Primary Sidebar output
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) ); // Add Meta Boxes
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) ); // Used to enqueue editor styles based on post type
		add_action( 'wp_head', array( $this, 'wp_head' ), 1 ); // Add <meta> tags to <head> section
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) ); // Enqueue all stylesheets (Main Stylesheet, Fonts, etc...)
		add_action( 'the_post', array( $this, 'the_post' ) ); // Remove Default Jetpack Share Buttons
		add_filter( 'the_content', array( $this, 'the_content' ) ); // Prepend Jetpack Share Buttons
		add_action( 'wp_footer', array( $this, 'wp_footer' ) ); // Responsive navigation functionality

		// Theme Customizer
		add_action( 'customize_register', array( $this, 'customize_register' ), 20 ); // Switch background properties to use refresh transport method
		add_action( 'customize_controls_print_styles', array( $this, 'customize_controls_print_styles' ), 20 ); // Customizer Styles
		add_filter( 'theme_mod_content_color', array( $this, 'theme_mod_content_color' ) ); // Set the default content color

		// Capture Slideshow
		add_action( 'wp_ajax_capture_slideshow', array( $this, 'wp_ajax_capture_slideshow' ) ); // Ajax/Backbone Requests
		add_action( 'wp_ajax_nopriv_capture_slideshow', array( $this, 'wp_ajax_capture_slideshow' ) ); // Ajax/Backbone Requests
		add_action( 'init', array( $this, 'init' ) ); // URL Endpoint
		add_filter( 'request', array( $this, 'request' ) ); // Modify request for URL Endpoint (Above)

		// Capture Theme Hooks
		add_action( 'capture_post_footer_right', array( $this, 'capture_post_footer_right' ) ); // Output Jetpack Share Buttons

		// Gravity Forms
		add_filter( 'gform_field_input', array( $this, 'gform_field_input' ), 10, 5 ); // Add placholder to newsletter form
		add_filter( 'gform_confirmation', array( $this, 'gform_confirmation' ), 10, 4 ); // Change confirmation message on newsletter form

		// WooCommerce
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 ); // Remove default WooCommerce content wrapper
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 ); // Remove default WooCommerce content wrapper
		add_action( 'woocommerce_before_main_content', array( $this, 'woocommerce_before_main_content' ) ); // Add Capture WooCommerce content wrapper
		add_action( 'woocommerce_after_main_content', array( $this, 'woocommerce_after_main_content' ) ); // Add Capture WooCommerce content wrapper
		add_filter( 'woocommerce_product_settings', array( $this, 'woocommerce_product_settings' ) ); // Adjust default WooCommerce product settings
		add_filter( 'loop_shop_per_page', array( $this, 'loop_shop_per_page' ), 20 ); // Adjust number of items displayed on a catalog page
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 ); // Remove default WooCommerce related products
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'woocommerce_after_single_product_summary' ), 20 ); // Add WooCommerce related products (3x3)
	}


	/************************************************************************************
	 *    Functions to correspond with actions above (attempting to keep same order)    *
	 ************************************************************************************/

	/**
	 * This function flushes the rewrite rules to ensure the endpoint registered below functions properly.
	 */
	function after_switch_theme() {
		flush_rewrite_rules();
	}

	/**
	 * This function adds images sizes to WordPress.
	 */
	function after_setup_theme() {
		global $content_width;

		/**
		 * Set the Content Width for embedded items.
		 */
		if ( ! isset( $content_width ) )
			$content_width = 1106;

		add_image_size( 'capture-1200x500', 1200, 500, true ); // Used for featured images on blog page
		add_image_size( 'capture-1200x9999', 1200, 9999, false ); // Used for featured images on single posts and pages

		// Remove top & footer navigation areas which are registered in SDS Core (Theme Options)
		unregister_nav_menu( 'top_nav' );
		unregister_nav_menu( 'footer_nav' );

		// WooCommerce Support
		add_theme_support( 'woocommerce' );

		// Change default core markup for search form, comment form, and comments, etc... to HTML5
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list'
		) );

		// Custom Background (color/image)
		add_theme_support( 'custom-background', array(
			'default-color' => '#ffffff'
		) );

		// Theme textdomain
		load_theme_textdomain( 'capture', get_template_directory() . '/languages' );
	}

	/**
	 * This function unregisters extra sidebars that are not used in this theme. It also modifies
	 * the global $wp_registered_sidebars to change the output of the Primary Sidebar for the "tuck" effect (CSS).
	 */
	function widgets_init() {
		global $wp_registered_sidebars;

		// Primary Sidebar
		if ( isset( $wp_registered_sidebars['primary-sidebar'] ) ) {
			$wp_registered_sidebars['primary-sidebar']['before_widget'] .= '<section class="widget-container">';
			$wp_registered_sidebars['primary-sidebar']['after_widget'] .= '</section>';
		}

		// Unregister unused sidebars which are registered in SDS Core
		unregister_sidebar( 'front-page-slider-sidebar' );
		unregister_sidebar( 'front-page-sidebar' );
		unregister_sidebar( 'secondary-sidebar' );
		unregister_sidebar( 'footer-sidebar' );
	}

	/**
	 * This function runs when meta boxes are added.
	 */
	function add_meta_boxes() {
		// Post types
		$post_types = get_post_types(
			array(
				'public' => true,
				'_builtin' => false
			)
		);
		$post_types[] = 'post';
		$post_types[] = 'page';

		// Add the metabox for each type
		foreach ( $post_types as $type ) {
			add_meta_box(
				'capture-us-metabox',
				__( 'Layout Settings', 'capture' ),
				array( $this, 'capture_us_metabox' ),
				$type,
				'side',
				'default'
			);
		}
	}

	/**
	 * This function renders a metabox.
	 */
	function capture_us_metabox( $post ) {
		// Get the post type label
		$post_type = get_post_type_object( $post->post_type );
		$label = ( isset( $post_type->labels->singular_name ) ) ? $post_type->labels->singular_name : __( 'Post' );

		echo '<p class="howto">';
		printf(
			__( 'Looking to configure a unique layout for this %1$s? %2$s.', 'capture' ),
			esc_html( strtolower( $label ) ),
			sprintf(
				'<a href="%1$s" target="_blank">Upgrade to Pro</a>',
				esc_url( sds_get_pro_link( 'metabox-layout-settings' ) )
			)
		);
		echo '</p>';
	}

	/**
	 * This function adds editor styles based on post type, before TinyMCE is initialized.
	 * It will also enqueue the correct color scheme stylesheet to better match front-end display.
	 */
	function pre_get_posts() {
		global $sds_theme_options, $post;

		$protocol = is_ssl() ? 'https' : 'http';

		// Admin only and if we have a post
		if ( is_admin() && ! empty( $post ) ) {
			add_editor_style( 'css/editor-style.css' );

			// Add correct color scheme if selected
			if ( function_exists( 'sds_color_schemes' ) && ! empty( $sds_theme_options['color_scheme'] ) && $sds_theme_options['color_scheme'] !== 'default' ) {
				$color_schemes = sds_color_schemes();
				add_editor_style( 'css/' . $color_schemes[$sds_theme_options['color_scheme']]['stylesheet'] );
			}

			// Open Sans & Oswald Web Fonts (include only if a web font is not selected in Theme Options)
			if ( ! function_exists( 'sds_web_fonts' ) || empty( $sds_theme_options['web_font'] ) )
				add_editor_style( $protocol . '://fonts.googleapis.com/css?family=Open+Sans|Oswald' ); // Google WebFonts (Open Sans & Oswald)
		}
	}

	/**
	 * This function adds <meta> tags to the <head> element.
	 */
	function wp_head() {
	?>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<?php
	}

	/**
	 * This function enqueues all styles and scripts (Main Stylesheet, Fonts, etc...).
	 * Stylesheets can be conditionally included if needed
	 */
	function wp_enqueue_scripts() {
		global $sds_theme_options, $post;

		$is_mobile = wp_is_mobile(); // Determine if user is on a mobile device
		$protocol = is_ssl() ? 'https' : 'http'; // Determine current protocol

		// Capture (main stylesheet)
		wp_enqueue_style( 'capture', get_template_directory_uri() . '/style.css', false, self::CAPTURE_VERSION );

		// Enqueue the child theme stylesheet only if a child theme is active
		if ( is_child_theme() )
			wp_enqueue_style( 'capture-child', get_stylesheet_uri(), array( 'capture' ), self::CAPTURE_VERSION );

		// Damion, Open Sans & Oswald Web Fonts (include only if a web font is not selected in Theme Options)
		if ( ! function_exists( 'sds_web_fonts' ) || empty( $sds_theme_options['web_font'] ) )
			wp_enqueue_style( 'damion-open-sans-oswald-web-fonts', $protocol . '://fonts.googleapis.com/css?family=Damion|Open+Sans|Oswald', false, self::CAPTURE_VERSION ); // Google WebFonts (Damion, Open Sans & Oswald)

		// Font Awesome
		wp_enqueue_style( 'font-awesome-css-min', get_template_directory_uri() . '/includes/css/font-awesome.min.css' );

		// Ensure jQuery is loaded on the front end for our footer script (@see wp_footer() below)
		wp_enqueue_script( 'jquery' );

		// If we're not on a single attachment page
		if ( ! is_attachment() ) {
			// ImagesLoaded
			wp_enqueue_script( 'imagesloaded-min', get_template_directory_uri() . '/js/imagesloaded.min.js', array( 'jquery' ), self::CAPTURE_VERSION, true );

			// Hammer
			if ( $is_mobile )
				wp_enqueue_script( 'hammer-min', get_template_directory_uri() . '/js/hammer.min.js', false, self::CAPTURE_VERSION, true );

			// Capture Slideshow
			wp_enqueue_script( 'capture-slideshow-min', get_template_directory_uri() . '/js/capture-slideshow.min.js', ( $is_mobile ) ? array( 'jquery', 'backbone', 'hammer-min' ) : array( 'jquery', 'backbone' ), self::CAPTURE_VERSION, true );
			wp_localize_script( 'capture-slideshow-min', 'capture', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'capture_slideshow' ),
				'permalinks' => ( get_option( 'permalink_structure' ) ) ? true : false,
				'is_archive' => ( is_archive() || is_home() || is_search() ) ? true : false,
				'is_mobile' => ( $is_mobile ) ? true : false
			) );
		}
	}

	/**
	 * This function removes the default Jetpack Share Buttons output on single posts and
	 * posts shown on the home, archive, and search page templates.
	 */
	function the_post( $post ) {
		if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'sharedaddy' ) && $post->post_type === 'post' && ( is_single() || is_home() || is_archive() || is_search() ) ) {
			remove_filter( 'the_content', 'sharing_display', 19 );
			remove_filter( 'the_excerpt', 'sharing_display', 19 );
		}
	}

	/**
	 * This function prepends Jetpack Share Buttons to single post content.
	 */
	function the_content( $content ) {
		if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'sharedaddy' ) && is_single() )
			$content = sharing_display() . $content;

		return $content;
	}

	/**
	 * This function outputs the necessary Javascript for the responsive menus and Capture Slideshow.
	 */
	function wp_footer() {
		global $post;
	?>
		<script type="text/javascript">
			// <![CDATA[
				jQuery( function( $ ) {
					// Mobile Nav
					$( '.mobile-nav-button' ).on( 'touch click', function ( e ) {
						e.stopPropagation();
						$( '.mobile-nav-button, .mobile-nav, body' ).toggleClass( 'open' );
					} );

					$( '.mobile-nav-close-button' ).on( 'touch click', function ( e ) {
						e.stopPropagation();
						$( '.mobile-nav-button, .mobile-nav, body' ).removeClass( 'open' );
					} );

					$( document ).on( 'touch click', function() {
						$( '.mobile-nav-button, .mobile-nav, body' ).removeClass( 'open' );
					} );

					<?php if ( is_single() && has_post_thumbnail() ) : // Single Posts with Featured Images ?>
						$( '.post-image-full-link', '.post' ).click( function( e ) {
							e.preventDefault();

							$( '#capture-slideshow' ).captureSlideshow(); // Capture Slideshow
						} );
					<?php endif; ?>
				} );

				<?php if ( is_single() && has_post_thumbnail() && get_query_var( 'lightbox' ) ) : // Determine if the request contains lightbox query and enable slideshow if so ?>
					jQuery( window ).load( function() {
						jQuery( '#capture-slideshow' ).captureSlideshow( {
							lightbox: true
						} );
					} );
				<?php endif; ?>
			// ]]>
		</script>
	<?php

		// Mobile menu styling for logged in users with the admin bar
		if ( is_admin_bar_showing() ) :
	?>
			<style type="text/css">
				.top-bar, .mobile-menu {
					top: 46px;
				}
			</style>
	<?php
		endif;
	}


	/********************
	 * Theme Customizer *
	 ********************/

	/**
	 * This function sets background-color and background-image settings in Theme Customizer to use
	 * the refresh method instead of postMessage.
	 */
	function customize_register( $wp_customize ) {
		if ( get_theme_support( 'custom-background', 'wp-head-callback' ) === '_custom_background_cb' ) {
			foreach ( array( 'color', 'image' ) as $prop ) {
				$wp_customize->get_setting( 'background_' . $prop )->transport = 'refresh';
			}
		}

		$wp_customize->add_section( 'capture_us', array(
			'title' => __( 'Upgrade Capture', 'capture' ),
			'priority' => 1
		) );

		$wp_customize->add_setting(
			'capture_us', // IDs can have nested array keys
			array(
				'default' => false,
				'type' => 'capture_us',
				'sanitize_callback' => 'sanitize_text_field'
			)
		);

		$wp_customize->add_control(
			new WP_Customize_US_Control(
				$wp_customize,
				'capture_us',
				array(
					'content'  => sprintf(
						__( '<strong>Premium support</strong>, more Customizer options, color schemes, web fonts, and more! %s.', 'capture' ),
						sprintf(
							'<a href="%1$s" target="_blank">%2$s</a>',
							esc_url( sds_get_pro_link( 'customizer' ) ),
							__( 'Upgrade to Pro', 'capture' )
						)
					),
					'section' => 'capture_us',
				)
			)
		);

		$wp_customize->get_section( 'colors' )->description = sprintf(
			__( 'Looking for more color customizations? %s.', 'capture' ),
			sprintf(
				'<a href="%1$s" target="_blank">%2$s</a>',
				esc_url( sds_get_pro_link( 'customizer-colors' ) ),
				__( 'Upgrade to Pro', 'capture' )
			)
		);
	}

	/**
	 * This function is run when the Theme Customizer is printing styles.
	 */
	function customize_controls_print_styles() {
	?>
		<style type="text/css">
			#accordion-section-capture_us .accordion-section-title,
			#customize-theme-controls #accordion-section-capture_us .accordion-section-title:focus,
			#customize-theme-controls #accordion-section-capture_us .accordion-section-title:hover,
			#customize-theme-controls #accordion-section-capture_us .control-section.open .accordion-section-title,
			#customize-theme-controls #accordion-section-capture_us:hover .accordion-section-title,
			#accordion-section-capture_us .accordion-section-title:active {
				background: #444;
				color: #fff;
			}

			#accordion-section-capture_us .accordion-section-title:after,
			#customize-theme-controls #accordion-section-capture_us .accordion-section-title:focus::after,
			#customize-theme-controls #accordion-section-capture_us .accordion-section-title:hover::after,
			#customize-theme-controls #accordion-section-capture_us.open .accordion-section-title::after,
			#customize-theme-controls #accordion-section-capture_us:hover .accordion-section-title::after {
				color: #fff;
			}
		</style>
	<?php
	}

	/**
	 * This function sets the default color for the content area in the Theme Customizer.
	 */
	function theme_mod_content_color( $color ) {
		// Return the current color if set
		if ( $color )
			return $color;

		// Return the selected color scheme content color if set
		if ( $selected_color_scheme = sds_get_color_scheme() )
			return $selected_color_scheme['content_color'];

		// Load all color schemes for this theme
		$color_schemes = sds_color_schemes();

		// Return the default color scheme content color
		return $color_schemes['default']['content_color'];
	}


	/**
	 * This function handles Ajax/Backbone requests for the Capture Slideshow
	 */
	function wp_ajax_capture_slideshow() {
		global $wpdb;

		// Make sure we have a valid request
		check_ajax_referer( 'capture_slideshow', 'nonce' );

		// Determine protocol
		$protocol = is_ssl() ? 'https' : 'http';

		// Select two previous, the current, and two next posts with featured images
		$current_post_id = ( isset( $_REQUEST['current_post_id'] ) ) ? abs( ( int ) $_REQUEST['current_post_id'] ) : false;
		$initial_post_id = ( isset( $_REQUEST['initial_post_id'] ) ) ? abs( ( int ) $_REQUEST['initial_post_id'] ) : false;
		$num_next_prev_posts = ( isset( $_REQUEST['num_next_prev_posts'] ) && ( int ) $_REQUEST['num_next_prev_posts'] <= 5 ) ? abs( ( int ) $_REQUEST['num_next_prev_posts'] ) : 2;

		if ( $current_post_id && $initial_post_id && $num_next_prev_posts ) {
			$capture_slideshow_posts = $wpdb->get_results( "( SELECT ID, post_title, post_date FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) WHERE post_date < ( SELECT post_date FROM $wpdb->posts WHERE ID = $current_post_id ) AND ( $wpdb->posts.post_type = 'post' ) AND ( $wpdb->posts.post_status = 'publish' ) AND ( $wpdb->postmeta.meta_key = '_thumbnail_id' ) GROUP BY $wpdb->posts.ID ORDER BY post_date DESC LIMIT $num_next_prev_posts ) UNION ( SELECT ID, post_title, post_date FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) WHERE ID = $current_post_id OR post_date > ( SELECT post_date AS current_post_date FROM $wpdb->posts WHERE ID = $current_post_id ) AND ( $wpdb->posts.post_type = 'post' ) AND ( $wpdb->posts.post_status = 'publish' ) AND ( $wpdb->postmeta.meta_key = '_thumbnail_id' ) GROUP BY $wpdb->posts.ID ORDER BY post_date LIMIT " . ( $num_next_prev_posts + 1 ) . " )" );

			// Build Backbone Models (if we have posts)
			if ( ! empty( $capture_slideshow_posts ) && is_array( $capture_slideshow_posts ) ) {
				$models = array();
				$current_image_index = 0;

				// Ensure we have an array ordered by post date DESC
				usort( $capture_slideshow_posts, array( $this, 'sort_post_date_desc' ) );

				foreach( $capture_slideshow_posts as $post ) {
					$featured_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );

					// Capture Slideshow Model
					$model = array(
						'img' => array(
							'src' => $featured_image[0],
							'width' => $featured_image[1],
							'height' => $featured_image[2]
						),
						'post' => array(
							'id' => $post->ID,
							'title' => $post->post_title,
							'date' => sprintf( __( 'Posted On %1$s', 'capture' ) , get_the_time( 'F jS, Y', $post->ID ) ),
							'timestamp' => strtotime( $post->post_date ),
							'permalink' => str_replace( $protocol . '://' . $_SERVER['SERVER_NAME'], '', get_permalink( $post->ID ) ) // Just return the URI
						),
					);

					// Append current post details to Capture Slideshow Model
					if ( ( int ) $post->ID === $current_post_id ) {
						$model['current_image'] = true;
						$current_image_index = count( $models );
					}

					// Append initial post details to Capture Slideshow Model
					if ( ( int ) $post->ID === $initial_post_id )
						$model['initial_image'] = true;

					$models[] = $model; // Append current model to model group
				}

				// Check to see if there are no more previous/next images
				if ( count( $models ) < ( $num_next_prev_posts * 2 + 1 ) ) {
					// No more previous images (current image is not in the middle)
					if ( $current_image_index < $num_next_prev_posts )
						$models[0]['no_more_images'] = true;

					// No more next images
					if( ( count( $models ) - $current_image_index - 1 ) < $num_next_prev_posts )
						$models[( count( $models ) - 1 )]['no_more_images'] = true;
				}

				// Check the database if necessary just to make sure
				if ( count( $models ) <= ( $num_next_prev_posts * 2 + 1 ) ) {
					if ( ! isset( $models[0]['no_more_images'] ) && ! ( $no_more_images = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) WHERE $wpdb->posts.post_date > ( SELECT post_date FROM $wpdb->posts WHERE ID = " . $models[0]['post']['id'] . " ) AND ( $wpdb->posts.post_type = 'post' ) AND ( $wpdb->posts.post_status = 'publish' ) AND ( $wpdb->postmeta.meta_key = '_thumbnail_id' )" ) ) )
						$models[0]['no_more_images'] = true;
					if ( ! isset( $models[( count( $models ) - 1 )]['no_more_images'] ) && ! ( $no_more_images = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) WHERE post_date < ( SELECT post_date FROM $wpdb->posts WHERE ID = " . $models[( count( $models ) - 1 )]['post']['id'] . " ) AND ( $wpdb->posts.post_type = 'post' ) AND ( $wpdb->posts.post_status = 'publish' ) AND ( $wpdb->postmeta.meta_key = '_thumbnail_id' )" ) ) )
						$models[( count( $models ) - 1 )]['no_more_images'] = true;
				}

				echo json_encode( $models ); // Return models to collection
			}
		}

		exit;
	}

	/**
	 * This function adds a URL Endpoint for the Capture Slideshow lightbox.
	 */
	function init() {
		add_rewrite_endpoint( 'lightbox', EP_PERMALINK ); // http://example.com/permalink/lightbox/
	}

	/**
	 * This function modifies the request to ensure the above URL Endpoint functions as we desire.
	 */
	 function request( $request ) {
		// Check to make sure our endpoint is set
		if( isset( $request['lightbox'] ) )
			$request['lightbox'] = true;

		return $request;
	}


	/***********************
	 * Capture Theme Hooks *
	 ***********************/

	/**
	 * This function outputs Jetpack Share Buttons in post footers.
	 */
	function capture_post_footer_right( $post ) {
		if( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'sharedaddy' ) && $post->post_type === 'post' )
			echo sharing_display();
	}


	/*****************
	 * Gravity Forms *
	 *****************/

	/**
	 * This function adds the HTML5 placeholder attribute to forms with a CSS class of the following:
	 * .mc-gravity, .mc_gravity, .mc-newsletter, .mc_newsletter classes
	 */
	function gform_field_input( $input, $field, $value, $lead_id, $form_id ) {
		$form_meta = RGFormsModel::get_form_meta( $form_id ); // Get form meta

		// Ensure we have at least one CSS class
		if ( isset( $form_meta['cssClass'] ) ) {
			$form_css_classes = explode( ' ', $form_meta['cssClass'] );

			// Ensure the current form has one of our supported classes and alter the field accordingly if we're not on admin
			if ( ! is_admin() && array_intersect( $form_css_classes, array( 'mc-gravity', 'mc_gravity', 'mc-newsletter', 'mc_newsletter' ) ) )
				$input = '<div class="ginput_container"><input name="input_' . $field['id'] . '" id="input_' . $form_id . '_' . $field['id'] . '" type="text" value="" class="large" placeholder="' . $field['label'] . '" /></div>';
		}

		return $input;
	}

	/**
	 * This function alters the confirmation message on forms with a CSS class of the following:
	 * .mc-gravity, .mc_gravity, .mc-newsletter, .mc_newsletter classes
	 */
	function gform_confirmation( $confirmation, $form, $lead, $ajax ) {
		// Ensure we have at least one CSS class
		if ( isset( $form['cssClass'] ) ) {
			$form_css_classes = explode( ' ', $form['cssClass'] );

			// Confirmation message is set and form has one of our supported classes (alter the confirmation accordingly)
			if ( $form['confirmation']['type'] === 'message' && array_intersect( $form_css_classes, array( 'mc-gravity', 'mc_gravity', 'mc-newsletter', 'mc_newsletter' ) ) )
				$confirmation = '<div class="mc-gravity-confirmation mc_gravity-confirmation mc-newsletter-confirmation mc_newsletter-confirmation">' . $confirmation . '</div>';
		}

		return $confirmation;
	}


	/***************
	 * WooCommerce *
	 ***************/

	/**
	 * This function alters the default WooCommerce content wrapper starting element.
	 */
	function woocommerce_before_main_content(){
	?>
		<section class="woocommerce woo-commerce post">
			<section class="post-container">
				<article class="post-content cf">
	<?php
	}

	/**
	 * This function alters the default WooCommerce content wrapper ending element.
	 */
	function woocommerce_after_main_content(){
	?>
				</article>
			</section>
		</section>
	<?php
	}


	/**
	 * This function adjusts the default WooCommerce Product settings.
	 */
	function woocommerce_product_settings( $settings ) {
		if ( is_array( $settings ) )
			foreach( $settings as &$setting )
				// Adjust the default value of the Catalog image size
				if( $setting['id'] === 'shop_catalog_image_size' )
					$setting['default']['width'] = $setting['default']['height'] = 300;

		return $settings;
	}

	/**
	 * This function changes the number of products output on the Catalog page.
	 */
	function loop_shop_per_page( $num_items ) {
		return 12;
	}

	/**
	 * This function changes the number of related products displayed on a single product page.
	 */
	function woocommerce_after_single_product_summary() {
		woocommerce_related_products( array(
			'posts_per_page' => 3,
			'columns' => 3
		) );
	}


	/**********************
	 * Internal Functions *
	 **********************/

	/**
	 * This function sorts an array of $post objects by post date in descending order.
	 */
	function sort_post_date_desc( $a, $b ) {
		return strnatcmp( strtotime( $b->post_date ), strtotime( $a->post_date ) );
	}
}


function CaptureInstance() {
	return Capture::instance();
}

// Starts Capture
CaptureInstance();