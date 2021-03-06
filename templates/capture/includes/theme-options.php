<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * SDS Theme Options
 *
 * Description: This Class instantiates the SDS Options Panel providing themes with various options to use.
 *
 * @version 1.2.6
 */
if ( ! class_exists( 'SDS_Theme_Options' ) ) {
	global $sds_theme_options;

	class SDS_Theme_Options {
		/**
		 * @var string, Constant, Version of the class
		 */
		const VERSION = '1.2.6';


		// Private Variables

		/**
		 * @var SDS_Theme_Options, Instance of the class
		 */
		private static $instance; // Keep track of the instance

		/**
		 * @var string, Description shown on options panel
		 */
		private static $options_page_description = 'Customize your theme to the fullest extent by using the options below.'; // Options Page description shown below title


		// Public Variables

		/**
		 * @var string, Option name
		 */
		public static $option_name = 'sds_theme_options';

		/**
		 * @var array, Array of option defaults
		 */
		public $option_defaults = array();

		/**
		 * @var WP_Theme, Current theme object
		 */
		public $theme;

		/*
		 * Function used to create instance of class.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) )
				self::$instance = new SDS_Theme_Options;

			return self::$instance;
		}

		/**
		 * These functions calls and hooks are added on new instance.
		 */
		function __construct() {
			$this->option_defaults = $this->get_sds_theme_option_defaults();
			$this->theme = $this->get_parent_theme();

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) ); // Enqueue Theme Options Stylesheet
			add_action( 'admin_menu', array( $this, 'admin_menu' ) ); // Register Appearance Menu Item
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 999 ); // Add Theme Options Menu to Toolbar
			add_action( 'admin_init', array( $this, 'admin_init' ) ); // Register Settings, Settings Sections, and Settings Fields
			add_filter( 'wp_redirect', array( $this, 'wp_redirect' ) ); // Add "hash" (tab) to URL before re-direct
		}


		/**
		 * This function enqueues our theme options stylesheet, WordPress media upload scripts, and our custom upload script only on our options page in admin.
		 */
		function admin_enqueue_scripts( $hook ) {
			if ( $hook === 'appearance_page_sds-theme-options' ) {
				$protocol = is_ssl() ? 'https' : 'http';

				wp_enqueue_style( 'sds-theme-options', get_template_directory_uri() . '/includes/css/sds-theme-options.css', false, self::VERSION );

				wp_enqueue_media(); // Enqueue media scripts
				wp_enqueue_script( 'sds-theme-options', get_template_directory_uri() . '/includes/js/sds-theme-options.js', array( 'jquery' ), self::VERSION );

				// Web Fonts
				if ( function_exists( 'sds_web_fonts' ) ) {
					$google_families = $this->get_google_font_families_list();

					wp_enqueue_style( 'google-web-fonts', $protocol . '://fonts.googleapis.com/css?family=' . $google_families, false, self::VERSION );
				}
			}
		}

		/**
		 * This function adds a menu item under "Appearance" in the Dashboard.
		 */
		function admin_menu() {
			add_theme_page( __( 'Theme Options', 'capture' ), __( 'Theme Options', 'capture' ), 'edit_theme_options', 'sds-theme-options', array( $this, 'sds_theme_options_page' ) );
		}

		/**
		 * This function adds a new menu to the Toolbar under the appearance parent group on the front-end.
		 */
		function admin_bar_menu( $wp_admin_bar ) {
			// Make sure we're on the front end and that the current user can either switch_themes or edit_theme_options
			if ( ! is_admin() && ( current_user_can( 'switch_themes' ) || current_user_can( 'edit_theme_options' ) ) ) 
				$wp_admin_bar->add_menu( array(
					'parent' => 'appearance',
					'id'  => 'sds-theme-options',
					'title' => __( 'Theme Options', 'capture' ),
					'href' => admin_url( 'themes.php?page=sds-theme-options' ),
					'meta' => array(
						'class' => 'sds-theme-options'
					)
				) );
		}

		/**
		 * This function registers our setting, settings sections, and settings fields.
		 */
		function admin_init() {
			// Register Setting
			register_setting( self::$option_name, self::$option_name, array( $this, 'sds_theme_options_sanitize' ) );


			/*
			 * General Settings (belong to the sds-theme-options[general] "page", used during page render to display section in tab format)
			 */

			// Logo
			add_settings_section( 'sds_theme_options_logo_section', __( 'Upload A Logo', 'capture'), array( $this, 'sds_theme_options_logo_section' ), 'sds-theme-options[general]' );
			add_settings_field( 'sds_theme_options_logo_field', __( 'Logo:', 'capture'), array( $this, 'sds_theme_options_logo_field' ), 'sds-theme-options[general]', 'sds_theme_options_logo_section' );
			
			// Hide Tagline
			add_settings_section( 'sds_theme_options_hide_tagline_section', __( 'Show/Hide Site Tagline', 'capture'), array( $this, 'sds_theme_options_hide_tagline_section' ), 'sds-theme-options[general]' );
			add_settings_field( 'sds_theme_options_hide_tagline_field', __( 'Show or Hide Site Tagline:', 'capture'), array( $this, 'sds_theme_options_hide_tagline_field' ), 'sds-theme-options[general]', 'sds_theme_options_hide_tagline_section' );

			// Color Schemes (if specified by theme)
			if ( function_exists( 'sds_color_schemes' ) ) {
				add_settings_section( 'sds_theme_options_color_schemes_section', __( 'Color Scheme', 'capture'), array( $this, 'sds_theme_options_color_schemes_section' ), 'sds-theme-options[general]' );
				add_settings_field( 'sds_theme_options_color_schemes_field', __( 'Select A Color Scheme:', 'capture'), array( $this, 'sds_theme_options_color_schemes_field' ), 'sds-theme-options[general]', 'sds_theme_options_color_schemes_section' );
			}

			// Google Web Fonts (if specified by theme)
			if ( function_exists( 'sds_web_fonts' ) ) {
				add_settings_section( 'sds_theme_options_web_fonts_section', __( 'Web Fonts', 'capture'), array( $this, 'sds_theme_options_web_fonts_section' ), 'sds-theme-options[general]' );
				add_settings_field( 'sds_theme_options_web_fonts_field', __( 'Select A Web Font:', 'capture'), array( $this, 'sds_theme_options_web_fonts_field' ), 'sds-theme-options[general]', 'sds_theme_options_web_fonts_section' );
			}

			/*
			 * Content Layout Settings (belong to the sds-theme-options[content-layout] "page", used during page render to display section in tab format)
			 */

			if ( function_exists( 'sds_content_layouts' ) ) {
				add_settings_section( 'sds_theme_options_content_layout_section', __( 'Content Layout', 'capture'), array( $this, 'sds_theme_options_content_layout_section' ), 'sds-theme-options[content-layout]' );
				add_settings_field( 'sds_theme_options_content_layout_global_field', __( 'Global', 'capture'), array( $this, 'sds_theme_options_content_layout_global_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_front_page_field', __( 'Front Page', 'capture'), array( $this, 'sds_theme_options_content_layout_front_page_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_home_field', __( 'Home (Blog)', 'capture'), array( $this, 'sds_theme_options_content_layout_home_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_single_field', __( 'Single Post', 'capture'), array( $this, 'sds_theme_options_content_layout_single_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_page_field', __( 'Single Page', 'capture'), array( $this, 'sds_theme_options_content_layout_page_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_archive_field', __( 'Archive', 'capture'), array( $this, 'sds_theme_options_content_layout_archive_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_category_field', __( 'Category', 'capture'), array( $this, 'sds_theme_options_content_layout_category_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_tag_field', __( 'Tag', 'capture'), array( $this, 'sds_theme_options_content_layout_tag_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_404_field', __( '404 Error', 'capture'), array( $this, 'sds_theme_options_content_layout_404_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
			}


			/*
			 * Social Media Settings (belong to the sds-theme-options[social-media] "page", used during page render to display section in tab format)
			 */

 			add_settings_section( 'sds_theme_options_social_media_section', __( 'Social Media', 'capture'), array( $this, 'sds_theme_options_social_media_section' ), 'sds-theme-options[social-media]' );
			add_settings_field( 'sds_theme_options_social_media_facebook_url_field', __( 'Facebook:', 'capture'), array( $this, 'sds_theme_options_social_media_facebook_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_twitter_url_field', __( 'Twitter:', 'capture'), array( $this, 'sds_theme_options_social_media_twitter_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_linkedin_url_field', __( 'LinkedIn:', 'capture'), array( $this, 'sds_theme_options_social_media_linkedin_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_google_plus_url_field', __( 'Google+:', 'capture'), array( $this, 'sds_theme_options_social_media_google_plus_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_youtube_url_field', __( 'YouTube:', 'capture'), array( $this, 'sds_theme_options_social_media_youtube_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_vimeo_url_field', __( 'Vimeo:', 'capture'), array( $this, 'sds_theme_options_social_media_vimeo_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_instagram_url_field', __( 'Instagram:', 'capture'), array( $this, 'sds_theme_options_social_media_instagram_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_pinterest_url_field', __( 'Pinterest:', 'capture'), array( $this, 'sds_theme_options_social_media_pinterest_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_flickr_url_field', __( 'Flickr:', 'capture'), array( $this, 'sds_theme_options_social_media_flickr_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			//add_settings_field( 'sds_theme_options_social_media_yelp_url_field', __( 'Yelp:', 'capture'), array( $this, 'sds_theme_options_social_media_yelp_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_foursquare_url_field', __( 'Foursquare:', 'capture'), array( $this, 'sds_theme_options_social_media_foursquare_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_rss_url_field', __( 'RSS:', 'capture'), array( $this, 'sds_theme_options_social_media_rss_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
		}

		/**
		 * This function is the callback for the logo settings section.
		 */
		function sds_theme_options_logo_section() {
		?>
			<p>
				<?php
					$sds_logo_dimensions = apply_filters( 'sds_theme_options_logo_dimensions', '300x100' );
					printf( __( 'Upload a logo to to replace the site name. Recommended dimensions: %1$s.', 'capture' ), $sds_logo_dimensions );
				?>
			</p>
		<?php
		}

		/**
		 * This function is the callback for the logo settings field.
		 */
		function sds_theme_options_logo_field() {
			global $sds_theme_options;
		?>
			<strong><?php _e( 'Current Logo:', 'capture' ); ?></strong>
			<div class="sds-theme-options-preview sds-theme-options-logo-preview">
				<?php
					if ( isset( $sds_theme_options['logo_attachment_id'] ) && $sds_theme_options['logo_attachment_id'] ) :
						echo wp_get_attachment_image( $sds_theme_options['logo_attachment_id'], 'full' );
					else :
				?>
						<div class="description"><?php _e( 'No logo selected.', 'capture' ); ?></div>
				<?php endif; ?>
			</div>

			<input type="hidden" id="sds_theme_options_logo" class="sds-theme-options-upload-value" name="sds_theme_options[logo_attachment_id]"  value="<?php echo ( isset( $sds_theme_options['logo_attachment_id'] ) && ! empty( $sds_theme_options['logo_attachment_id'] ) ) ? esc_attr( $sds_theme_options['logo_attachment_id'] ) : false; ?>" />
			<input type="submit" id="sds_theme_options_logo_attach" class="button-primary sds-theme-options-upload" name="sds_theme_options_logo_attach"  value="<?php esc_attr_e( 'Choose Logo', 'capture' ); ?>" data-media-title="Choose A Logo" data-media-button-text="Use As Logo" />
			<?php submit_button( __( 'Remove Logo', 'capture' ), array( 'secondary', 'button-remove-logo' ), 'sds_theme_options[remove-logo]', false, ( ! isset( $sds_theme_options['logo_attachment_id'] ) || empty( $sds_theme_options['logo_attachment_id'] ) ) ? array( 'disabled' => 'disabled', 'data-init-empty' => 'true' ) : false ); ?>
		<?php
		}

		
		/**
		 * This function is the callback for the show/hide tagline settings section.
		 */
		function sds_theme_options_hide_tagline_section() {
		?>
			<p><?php _e( 'Use this option to show or hide the site tagline.', 'capture' ); ?></p>
		<?php
		}

		/**
		 * This function is the callback for the show/hide tagline settings field.
		 */
		function sds_theme_options_hide_tagline_field() {
			global $sds_theme_options;
		?>
			<div class="checkbox sds-theme-options-checkbox checkbox-show-hide-tagline" data-label-left="<?php esc_attr_e( 'Show', 'capture' ); ?>" data-label-right="<?php esc_attr_e( 'Hide', 'capture' ); ?>">
				<input type="checkbox" id="sds_theme_options_hide_tagline" name="sds_theme_options[hide_tagline]" <?php ( isset( $sds_theme_options['hide_tagline'] ) ) ? checked( $sds_theme_options['hide_tagline'] ) : checked( false ); ?> />
				<label for="sds_theme_options_hide_tagline">| | |</label>
			</div>
			<span class="description"><?php _e( 'When "show" is displayed, the tagline will be displayed on your site and vise-versa.', 'capture' ); ?></span>
		<?php
		}

		
		/**
		 * This function is the callback for the color schemes settings section.
		 */
		function sds_theme_options_color_schemes_section() {
		?>
			<p><?php _e( 'Select a color scheme to use on your site.', 'capture' ); ?></p>
		<?php
		}

		/**
		 * This function is the callback for the color schemes settings field.
		 */
		function sds_theme_options_color_schemes_field() {
			global $sds_theme_options, $wp_version;

			$color_schemes = ( function_exists( 'sds_color_schemes' ) ) ? sds_color_schemes() : array();

			if ( ! empty( $color_schemes ) ) :
		?>
			<div class="sds-theme-options-color-schemes-wrap">
				<?php if ( version_compare( $wp_version, '3.8', '<' ) ) : // Output styles to change CSS on < 3.8 ?>
					<style type="text/css">
						.sds-theme-options-color-scheme input[type=radio]:checked + .sds-theme-options-color-scheme-preview:after {
							content: '\2714';
							font-family: inherit;
							font-size: 20px;
							margin-top: -5px;
						}
					</style>
				<?php endif; ?>

				<?php foreach( $color_schemes as $name => $atts ) :	?>
					<div class="sds-theme-options-color-scheme sds-theme-options-color-scheme-<?php echo $name; ?>">
						<label>
							<?php if ( ( ! isset( $sds_theme_options['color_scheme'] ) || empty( $sds_theme_options['color_scheme'] ) ) && isset( $atts['default'] ) && $atts['default'] ) : // No color scheme selected, use default ?>
								<input type="radio" id="sds_theme_options_color_scheme_<?php echo $name; ?>" name="sds_theme_options[color_scheme]" <?php checked( true ); ?> value="<?php echo $name; ?>" />
							<?php else: ?>
								<input type="radio" id="sds_theme_options_color_scheme_<?php echo $name; ?>" name="sds_theme_options[color_scheme]" <?php ( isset( $sds_theme_options['color_scheme'] ) ) ? checked( $sds_theme_options['color_scheme'], $name ) : checked( false ); ?> value="<?php echo $name; ?>" />
							<?php endif;?>

							<?php if ( isset( $atts['preview'] ) && ! empty( $atts['preview'] ) ) : ?>
								<div class="sds-theme-options-color-scheme-preview" style="background: <?php echo $atts['preview']; ?>">&nbsp;</div>
							<?php endif; ?>

							<?php echo ( isset( $atts['label'] ) ) ? $atts['label'] : false; ?>
						</label>
					</div>
				<?php endforeach; ?>

				<?php do_action( 'sds_theme_options_upgrade_cta', 'color-schemes' ); ?>
			</div>
		<?php
			endif;
		}

		
		/**
		 * This function is the callback for the web fonts settings section.
		 */
		function sds_theme_options_web_fonts_section() {
		?>
			<p><?php _e( 'Select a Google Web Font to use on your site.', 'capture' ); ?></p>
		<?php
		}

		/**
		 * This function is the callback for the web fonts settings field.
		 */
		function sds_theme_options_web_fonts_field() {
			global $sds_theme_options, $wp_version;

			$web_fonts = ( function_exists( 'sds_web_fonts' ) ) ? sds_web_fonts() : array();

			if ( ! empty( $web_fonts ) ) :
		?>
			<div class="sds-theme-options-web-fonts-wrap">
				<?php if ( version_compare( $wp_version, '3.8', '<' ) ) : // Output styles to change CSS on < 3.8 ?>
					<style type="text/css">
						.sds-theme-options-web-font input[type=radio]:checked + .sds-theme-options-web-font-selected:before {
							content: '\2714';
							font-family: inherit;
							font-size: 20px;
							margin-top: -2px;
						}
					</style>
				<?php endif; ?>

				<div class="sds-theme-options-web-font sds-theme-options-web-font-default">
					<label>
						<input type="radio" id="sds_theme_options_web_font_default" name="sds_theme_options[web_font]" <?php ( ! isset( $sds_theme_options['web_font'] ) || empty( $sds_theme_options['web_font'] ) || $sds_theme_options['web_font'] === 'default' ) ? checked( true ) : checked( false ); ?> value="default" />
						<div class="sds-theme-options-web-font-selected">&nbsp;</div>
					</label>
					<span class="sds-theme-options-web-font-label-default"><?php _e( 'Default', 'capture' ); ?></span>
				</div>

				<?php
					foreach( $web_fonts as $name => $atts ) :
						$css_name = strtolower( str_replace( array( '+'. ':' ), '-', $name) );
				?>
						<div class="sds-theme-options-web-font sds-theme-options-web-font-<?php echo $css_name; ?>" style="<?php echo ( isset( $atts['css'] ) && ! empty( $atts['css'] ) ) ? $atts['css'] : false; ?>">
							<label>
								<input type="radio" id="sds_theme_options_web_font_name_<?php echo $css_name; ?>" name="sds_theme_options[web_font]" <?php ( isset( $sds_theme_options['web_font'] ) ) ? checked( $sds_theme_options['web_font'], $name ) : checked( false ); ?> value="<?php echo $name; ?>" />
								<div class="sds-theme-options-web-font-selected">&nbsp;</div>
							</label>
							<span class="sds-theme-options-web-font-label"><?php echo ( isset( $atts['label'] ) ) ? $atts['label'] : false; ?></span>
							<span class="sds-theme-options-web-font-preview"><?php _e( 'Grumpy wizards make toxic brew for the evil Queen and Jack.', 'capture' ); ?></span>
						</div>
				<?php
					endforeach;
				?>

				<?php do_action( 'sds_theme_options_upgrade_cta', 'web-fonts' ); ?>
			</div>
		<?php
			endif;
		}


		/**
		 * This function is the callback for the content layout settings section.
		 */
		function sds_theme_options_content_layout_section() {
		?>
			<p><?php _e( 'Control the layout of the content on your site here. Choose a global layout scheme to be used across your entire site or specifiy individual content type layout schemes by adjusting the options below.', 'capture' ); ?></p>
		<?php
		}

		/**
		 * This function is the callback for the global content layout settings field.
		 */
		function sds_theme_options_content_layout_global_field() {
			$this->content_layouts_field( 'global', 'Select a content layout that will be applied globally on your site. Select more specific content layouts below.' );
		}

		/**
		 * This function is the callback for the front page content layout settings field.
		 */
		function sds_theme_options_content_layout_front_page_field() {
			$this->content_layouts_field( 'front_page', 'Select a content layout that will be applied to the front page on your site (if selected in Settings > General).' );
		}

		/**
		 * This function is the callback for the home (blog) page content layout settings field.
		 */
		function sds_theme_options_content_layout_home_field() {
			$this->content_layouts_field( 'home', 'Select a content layout that will be applied to the blog on your site.' );
		}

		/**
		 * This function is the callback for the single post content layout settings field.
		 */
		function sds_theme_options_content_layout_single_field() {
			$this->content_layouts_field( 'single', 'Select a content layout that will be applied to single posts on your site.' );
		}

		/**
		 * This function is the callback for the single page content layout settings field.
		 */
		function sds_theme_options_content_layout_page_field() {
			$this->content_layouts_field( 'page', 'Select a content layout that will be applied to single pages on your site.' );
		}

		/**
		 * This function is the callback for the archive content layout settings field.
		 */
		function sds_theme_options_content_layout_archive_field() {
			$this->content_layouts_field( 'archive', 'Select a content layout that will be applied to archives on your site.' );
		}

		/**
		 * This function is the callback for the category content layout settings field.
		 */
		function sds_theme_options_content_layout_category_field() {
			$this->content_layouts_field( 'category', 'Select a content layout that will be applied to category archives on your site.' );
		}

		/**
		 * This function is the callback for the tag content layout settings field.
		 */
		function sds_theme_options_content_layout_tag_field() {
			$this->content_layouts_field( 'tag', 'Select a content layout that will be applied to tag archives on your site.' );
		}

		/**
		 * This function is the callback for the 404 (error) content layout settings field.
		 */
		function sds_theme_options_content_layout_404_field() {
			$this->content_layouts_field( '404', 'Select a content layout that will be applied to the 404 error page on your site.' );
		}


		/**
		 * This function is the callback for the social media settings section.
		 */
		function sds_theme_options_social_media_section() {
		?>
			<p><?php _e( 'Enter your social media links here. This section is used throughout the site to display social media links to visitors. Some themes display social media links automatically, and some only display them within the Social Media widget.', 'capture' ); ?></p>
		<?php
		}

		/**
		 * This function is the callback for the facebook url settings field.
		 */
		function sds_theme_options_social_media_facebook_url_field() {
			$this->social_media_field( 'facebook_url' );
		}

		/**
		 * This function is the callback for the twitter url settings field.
		 */
		function sds_theme_options_social_media_twitter_url_field() {
			$this->social_media_field( 'twitter_url' );
		}

		/**
		 * This function is the callback for the linkedin url settings field.
		 */
		function sds_theme_options_social_media_linkedin_url_field() {
			$this->social_media_field( 'linkedin_url' );
		}

		/**
		 * This function is the callback for the google_plus url settings field.
		 */
		function sds_theme_options_social_media_google_plus_url_field() {
			$this->social_media_field( 'google_plus_url' );
		}

		/**
		 * This function is the callback for the youtube url settings field.
		 */
		function sds_theme_options_social_media_youtube_url_field() {
			$this->social_media_field( 'youtube_url' );
		}

		/**
		 * This function is the callback for the vimeo url settings field.
		 */
		function sds_theme_options_social_media_vimeo_url_field() {
			$this->social_media_field( 'vimeo_url' );
		}

		/**
		 * This function is the callback for the instagram url settings field.
		 */
		function sds_theme_options_social_media_instagram_url_field() {
			$this->social_media_field( 'instagram_url' );
		}

		/**
		 * This function is the callback for the pinterest url settings field.
		 */
		function sds_theme_options_social_media_pinterest_url_field() {
			$this->social_media_field( 'pinterest_url' );
		}

		/**
		 * This function is the callback for the flickr url settings field.
		 */
		function sds_theme_options_social_media_flickr_url_field() {
			$this->social_media_field( 'flickr_url' );
		}

		/**
		 * This function is the callback for the yelp url settings field.
		 */
		function sds_theme_options_social_media_yelp_url_field() {
			$this->social_media_field( 'yelp_url' );
		}

		/**
		 * This function is the callback for the foursquare url settings field.
		 */
		function sds_theme_options_social_media_foursquare_url_field() {
			$this->social_media_field( 'foursquare_url' );
		}

		/**
		 * This function is the callback for the rss url settings field.
		 */
		function sds_theme_options_social_media_rss_url_field() {
			global $sds_theme_options;
		?>
			<strong><?php _e( 'Use Site RSS Feed:', 'capture' ); ?></strong>
			<div class="checkbox sds-theme-options-checkbox checkbox-social_media-rss_url-use-site-feed" data-label-left="<?php esc_attr_e( 'Yes', 'capture' ); ?>" data-label-right="<?php esc_attr_e( 'No', 'capture' ); ?>">
				<input type="checkbox" id="sds_theme_options_social_media_rss_url_use_site_feed" name="sds_theme_options[social_media][rss_url_use_site_feed]" <?php ( isset( $sds_theme_options['social_media']['rss_url_use_site_feed'] ) ) ? checked( $sds_theme_options['social_media']['rss_url_use_site_feed'] ) : checked( false ); ?> />
				<label for="sds_theme_options_social_media_rss_url_use_site_feed">| | |</label>
			</div>
			<span class="description"><?php _e( 'When "yes" is displayed, the RSS feed for your site will be used.', 'capture' ); ?></span>

			<div id="sds_theme_options_social_media_rss_url_custom">
				<strong><?php _e( 'Custom RSS Feed:', 'capture' ); ?></strong>
				<input type="text" id="sds_theme_options_social_media_rss_url" name="sds_theme_options[social_media][rss_url]" class="large-text" value="<?php echo ( isset( $sds_theme_options['social_media']['rss_url'] ) && ! empty( $sds_theme_options['social_media']['rss_url'] ) ) ? esc_attr( esc_url( $sds_theme_options['social_media']['rss_url'] ) ) : false; ?>" />
			</div>
		<?php
		}


		/**
		 * This function sanitizes input from the user when saving options.
		 */
		function sds_theme_options_sanitize( $input ) {
			// Reset to Defaults
			if ( isset( $input['reset'] ) )
				return $this->get_sds_theme_option_defaults();

			// Remove Logo
			if ( isset( $input['remove-logo'] ) ) {
				unset( $input['remove-logo'] ); // We don't want to store this value in the options array

				$input['logo_attachment_id'] = false;
			}

			// Parse arguments, replacing defaults with user input
			$input = wp_parse_args( $input, $this->get_sds_theme_option_defaults() );

			// General
			$input['logo_attachment_id'] = ( ! empty( $input['logo_attachment_id'] ) ) ? ( int ) $input['logo_attachment_id'] : '';
			$input['color_scheme'] = sanitize_text_field( $input['color_scheme'] );
			$input['web_font'] = ( ! empty( $input['web_font'] ) && $input['web_font'] !== 'default' ) ? sanitize_text_field( $input['web_font'] ) : false;
			$input['hide_tagline'] = ( $input['hide_tagline'] ) ? true : false;

			// Color Scheme (remove content/background colors if they match another color scheme's default values)
			if ( ! empty( $input['color_scheme'] ) ) {
				// Get color schemes
				$color_schemes = ( function_exists( 'sds_color_schemes' ) ) ? sds_color_schemes() : array();

				if ( ! empty( $color_schemes ) ) {
					unset( $color_schemes[$input['color_scheme']]); // Remove current color scheme

					// Get current theme mods
					$theme_mod_content_color = get_theme_mod( 'content_color' );
					$theme_mod_background_color = get_theme_mod( 'background_color' );

					// Loop through color schemes
					foreach( $color_schemes as $color_scheme_id => $color_scheme ) {
						// Check to see if the current content color theme mod matches this color scheme's default value
						if ( $color_scheme['content_color'] === $theme_mod_content_color )
							remove_theme_mod( 'content_color' );

						// Check to see if the current background color theme mod matches this color scheme's default value
						if ( isset( $color_scheme['background_color'] ) && ltrim( $color_scheme['background_color'], '#' ) === $theme_mod_background_color )
							remove_theme_mod( 'background_color' );
					}
				}
			}

			// Content Layouts
			foreach ( $input['content_layouts'] as $key => &$value )
				$value = ( $value !== 'default' ) ? sanitize_text_field( $value ) : false;

			// Social Media
			foreach ( $input['social_media'] as $key => &$value ) {
				// RSS Feed (use site feed)
				if ( $key === 'rss_url_use_site_feed' && $value ) {
					$value = true;

					$input['social_media']['rss_url'] = '';
				}
				else
					$value = esc_url( $value );
			}

			// Ensure the 'rss_url_use_site_feed' key is set in social media
			if ( ! isset( $input['social_media']['rss_url_use_site_feed'] ) )
				$input['social_media']['rss_url_use_site_feed'] = false;


			return $input;
		}


		/**
		 * This function handles the rendering of the options page.
		 */
		function sds_theme_options_page() {
			global $_wp_admin_css_colors, $wp_version;

			$user_admin_color = get_user_meta(  get_current_user_id(), 'admin_color', true );
		?>
			<div class="wrap about-wrap">
				<?php if ( isset( $_wp_admin_css_colors[$user_admin_color] ) && version_compare( $wp_version, '3.8', '>=' ) ) : // Output styles to match selected admin color scheme ?>
					<style type="text/css">
						/* Checkboxes */
						.sds-theme-options-checkbox:before {
							background: <?php echo $_wp_admin_css_colors[$user_admin_color]->colors[2]; ?>;
						}

						/* Web Fonts */
						.sds-theme-options-web-font input[type=radio]:checked + .sds-theme-options-web-font-selected:before {
							color: <?php echo $_wp_admin_css_colors[$user_admin_color]->colors[2]; ?>;
						}

						/* Content Layouts */
						.sds-theme-options-content-layout:hover .sds-theme-options-content-layout-preview,
						.sds-theme-options-content-layout input[type=radio]:checked + .sds-theme-options-content-layout-preview {
							border: 1px solid <?php echo $_wp_admin_css_colors[$user_admin_color]->colors[2]; ?>;
						}

						.sds-theme-options-content-layout:hover .sds-theme-options-content-layout-preview  .col,
						.sds-theme-options-content-layout input[type=radio]:checked + .sds-theme-options-content-layout-preview .col {
							background: <?php echo $_wp_admin_css_colors[$user_admin_color]->colors[2]; ?>;
						}

						.sds-theme-options-content-layout:hover .sds-theme-options-content-layout-preview  .col-sidebar,
						.sds-theme-options-content-layout input[type=radio]:checked + .sds-theme-options-content-layout-preview .col-sidebar {
							background: <?php echo $_wp_admin_css_colors[$user_admin_color]->colors[3]; ?>;
						}
					</style>
				<?php endif; ?>

				<h1><?php echo $this->theme->get( 'Name' ); ?> <?php _e( 'Theme Options', 'capture' ); ?></h1>
				<div class="about-text sds-about-text"><?php printf( _x( '%1$s', 'Theme options panel description', 'capture' ), self::$options_page_description ); ?></div>

				<?php do_action( 'sds_theme_options_notifications' ); ?>

				<?php
					settings_errors( 'general' ); // General Settings Errors
					settings_errors( self::$option_name ); // Theme Options Panel Settings Errors
				?>

				<h3 class="nav-tab-wrapper sds-theme-options-nav-tab-wrapper sds-theme-options-tab-wrap">
					<a href="#general" id="general-tab" class="nav-tab sds-theme-options-tab nav-tab-active"><?php _e( 'General', 'capture' ); ?></a>
					<?php if ( function_exists( 'sds_content_layouts' ) ) : ?>
						<a href="#content-layout" id="content-layout-tab" class="nav-tab sds-theme-options-tab"><?php _e( 'Layout', 'capture' ); ?></a>
					<?php endif; ?>
					<a href="#social-media" id="social-media-tab" class="nav-tab sds-theme-options-tab"><?php _e( 'Social Media', 'capture' ); ?></a>
					<?php do_action( 'sds_theme_options_navigation_tabs' ); // Hook for extending tabs ?>
					<a href="#help-support" id="help-support-tab" class="nav-tab sds-theme-options-tab"><?php _e( 'Support', 'capture' ); ?></a>
				</h3>

				<form method="post" action="options.php" enctype="multipart/form-data" id="sds-theme-options-form">
					<?php settings_fields( self::$option_name ); ?>
					<input type="hidden" name="sds_theme_options_tab" id="sds_theme_options_tab" value="" />

					<?php
					/*
					 * General Settings
					 */
					?>
					<div id="general-tab-content" class="sds-theme-options-tab-content sds-theme-options-tab-content-active">
						<?php do_settings_sections( 'sds-theme-options[general]' ); ?>
					</div>

					<?php
					/*
					 * Content Layout Settings
					 */
					?>
					<?php if ( function_exists( 'sds_content_layouts' ) ) : ?>
						<div id="content-layout-tab-content" class="sds-theme-options-tab-content">
							<?php do_settings_sections( 'sds-theme-options[content-layout]' ); ?>
						</div>
					<?php endif; ?>

					<?php
					/*
					 * Social Media Settings
					 */
					?>
					<div id="social-media-tab-content" class="sds-theme-options-tab-content">
						<?php do_settings_sections( 'sds-theme-options[social-media]' ); ?>
					</div>

					<?php
					/*
					 * Help/Support
					 */
					?>
					<div id="help-support-tab-content" class="sds-theme-options-tab-content">
						<h3><?php _e( 'Help/Support', 'capture' ); ?></h3>

						<?php do_action( 'sds_theme_options_help_support_tab_content' ); ?>
						<?php do_action( 'sds_theme_options_upgrade_cta', 'help-support' ); ?>
					</div>

					<?php do_action( 'sds_theme_options_settings' ); // Hook for extending settings ?>

					<p class="submit">
						<?php submit_button( __( 'Save Options', 'capture' ), 'primary', 'submit', false ); ?>
						<?php submit_button( __( 'Restore Defaults', 'capture' ), 'secondary', 'sds_theme_options[reset]', false ); ?>
					</p>
				</form>

				<div id="sds-theme-options-ads" class="sidebar">
					<div class="sds-theme-options-ad">
						<div class="yt-subscribe">
							<div class="g-ytsubscribe" data-channel="slocumstudio" data-layout="default"></div>
							<script src="https://apis.google.com/js/plusone.js"></script>
						</div>

						<a href="https://twitter.com/slocumstudio/" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @slocumstudio</a>
						<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>

						<br />
						<br />

						<div class="slocum-themes">
							<?php printf( __( '<a href="%1$s" target="_blank"><strong>How to setup the perfect WordPress website</strong></a>', 'capture' ), 'http://slocumthemes.com/build-perfect-wordpress-website/' ); ?>
						</div>

						<br />

						<div class="slocum-themes">
							<?php printf( __( 'Brought to you by <a href="%1$s" target="_blank">Slocum Themes</a>', 'capture' ), 'http://slocumthemes.com/' ); ?>
						</div>
					</div>

					<?php do_action( 'sds_theme_options_ads' ); ?>
				</div>
			</div>
		<?php
		}

		/*
		 * This function appends the hash for the current tab based on POST data.
		 */
		function wp_redirect( $location ) {
			// Append tab "hash" to end of URL
			if ( strpos( $location, 'sds-theme-options' ) !== false && isset( $_POST['sds_theme_options_tab'] ) && $_POST['sds_theme_options_tab'] )
				$location .= esc_url( $_POST['sds_theme_options_tab'] );

			return $location;
		}



		/**
		 * External Functions (functions that can be used outside of this class to retrieve data)
		 */

		/**
		 * This function returns the current option values.
		 */
		public static function get_sds_theme_options() {
			global $sds_theme_options;

			$sds_theme_options = wp_parse_args( get_option( self::$option_name ), SDS_Theme_Options::get_sds_theme_option_defaults() );

			return $sds_theme_options;
		}

		/**
		 * This function returns the current option name.
		 */
		public static function get_option_name() {
			return self::$option_name;
		}



		/************************************************************************
		 * Internal Functions (functions used internally throughout this class) *
		 ************************************************************************/

		/**
		 * This function returns default values for SDS Theme Options
		 */
		public static function get_sds_theme_option_defaults() {
			$defaults = array(
				// General
				'logo_attachment_id' => false,
				'color_scheme' => false,
				'hide_tagline' => false,
				'web_font' => false,

				// Content Layouts
				'content_layouts' => array(
					'global' => false,
					'front_page'=> false,
					'home' => false,
					'single' => false,
					'page' => false,
					'archive' => false,
					'category' => false,
					'tag' => false,
					'404' => false
				),

				// Social Media
				'social_media' => array(
					'facebook_url' => '',
					'twitter_url' => '',
					'linkedin_url' => '',
					'google_plus_url' => '',
					'youtube_url' => '',
					'vimeo_url' => '',
					'instagram_url' => '',
					'pinterest_url' => '',
					'flickr_url' => '',
					//'yelp_url' => '',
					'foursquare_url' => '',
					'rss_url' => '',
					'rss_url_use_site_feed' => false
				)
			);

			return apply_filters( 'sds_theme_options_defaults', $defaults );
		}

		/**
		 * This function returns a formatted list of Google Web Font families for use when enqueuing styles.
		 */
		function get_google_font_families_list() {
			if ( function_exists( 'sds_web_fonts' ) ) {
				$web_fonts = sds_web_fonts();
				$web_fonts_count = count( $web_fonts );
				$google_families = '';

				if ( ! empty( $web_fonts ) && is_array( $web_fonts ) ) {
					foreach( $web_fonts as $name => $atts ) {
						// Google Font Name
						$google_families .= $name;

						if ( $web_fonts_count > 1 )
							$google_families .= '|';
					}

					// Trim last | when multiple fonts are set
					if ( $web_fonts_count > 1 )
						$google_families = substr( $google_families, 0, -1 );
				}

				return $google_families;
			}

			return false;
		}

		/**
		 * This function returns the details of the current parent theme.
		 */
		public function get_parent_theme() {
			if ( is_a( $this->theme, 'WP_Theme' ) )
				return $this->theme;

			return ( is_child_theme() ) ? wp_get_theme()->parent() : wp_get_theme();
		}


		/**
		 * This function returns the HTML output of a social media field.
		 */
		function social_media_field( $field_id ) {
			global $sds_theme_options;
		?>
			<input type="text" id="sds_theme_options_social_media_<?php echo $field_id; ?>" name="sds_theme_options[social_media][<?php echo $field_id; ?>]" class="large-text" value="<?php echo ( isset( $sds_theme_options['social_media'][$field_id] ) && ! empty( $sds_theme_options['social_media'][$field_id] ) ) ? esc_attr( esc_url( $sds_theme_options['social_media'][$field_id] ) ) : false; ?>" />
		<?php
		}

		/**
		 * This function returns the HTML output of a content layout field.
		 */
		function content_layouts_field( $field_id, $description = false ) {
			global $sds_theme_options;

			$content_layouts = ( function_exists( 'sds_content_layouts' ) ) ? sds_content_layouts() : false;

			if ( ! empty( $content_layouts ) )
			?>
				<div class="sds-theme-options-content-layout-wrap">
					<?php foreach( $content_layouts as $name => $atts ) : ?>
						<div class="sds-theme-options-content-layout sds-theme-options-content-layout-<?php echo $name; ?>">
							<label>
								<?php if ( ( ! isset( $sds_theme_options['content_layouts']['global'] ) || empty( $sds_theme_options['content_layouts'][$field_id] ) ) && isset( $atts['default'] ) && $atts['default'] ) : // No content layout selected, use default ?>
									<input type="radio" id="sds_theme_options_content_layouts_name_<?php echo $name; ?>" name="sds_theme_options[content_layouts][<?php echo $field_id; ?>]" <?php checked( true ); ?> value="<?php echo $name; ?>" />
								<?php else: ?>
									<input type="radio" id="sds_theme_options_content_layouts_name_<?php echo $name; ?>" name="sds_theme_options[content_layouts][<?php echo $field_id; ?>]" <?php ( isset( $sds_theme_options['content_layouts'][$field_id] ) ) ? checked( $sds_theme_options['content_layouts'][$field_id], $name ) : checked( false ); ?> value="<?php echo $name; ?>" />
								<?php endif; ?>

								<div class="sds-theme-options-content-layout-preview">
									<?php
									if ( isset( $atts['preview_values'] ) )
										vprintf( $atts['preview'], $atts['preview_values'] );
									else
										echo $atts['preview'];
									?>
								</div>
							</label>
						</div>
					<?php endforeach; ?>
				</div>
				<span class="description"><?php  printf( _x( '%1$s', 'Content layout description; describes where the content layout will be applied', 'capture' ), $description ); ?></span>
		<?php
		}
	}


	function SDS_Theme_Options_Instance() {
		return SDS_Theme_Options::instance();
	}

	// Instantiate SDS_Theme_Options
	SDS_Theme_Options_Instance();
}