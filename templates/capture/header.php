<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8"> <![endif]-->
<!--[if IE 9 ]><html class="ie ie9"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html><!--<![endif]-->
	<head>
		<?php wp_head(); ?>
	</head>

	<body <?php language_attributes(); ?> <?php body_class(); ?>>
		<?php sds_social_media(); ?>

		<section class="top-bar cf">
			<button class="mobile-nav-button">
				<!--[if gt IE 8]><!-->
					<svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="100px" height="100px" viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve"><rect x="0.208" y="10.167" fill="#FFFFFF" width="99.792" height="19.958"/><rect x="0.208" y="40.104" fill="#FFFFFF" width="99.792" height="19.958"/><rect x="0.208" y="70.041" fill="#FFFFFF" width="99.792" height="19.959"/></svg>
					<svg class="close-icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="100px" height="100px" viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve"><path fill="#FFFFFF" d="M63.973 49.999l34.76-34.762c1.335-1.333 1.335-3.494 0-4.826l-9.144-9.146 c-1.333-1.332-3.493-1.332-4.827 0L50 36.028L15.237 1.265c-1.332-1.332-3.493-1.332-4.826 0l-9.146 9.146 c-1.333 1.332-1.333 3.493 0 4.826l34.763 34.762L1.267 84.762c-1.334 1.334-1.334 3.493 0 4.826l9.145 9.146 c1.333 1.334 3.495 1.334 4.826 0L50 63.973l34.761 34.761c1.334 1.334 3.493 1.334 4.826 0l9.146-9.146 c1.333-1.333 1.333-3.492 0-4.826L63.973 49.999z"/></svg>
				<!--<![endif]-->
				<span class="mobile-nav-label"><?php _e( 'Navigation', 'capture' ); ?></span>
			</button>
		</section>
		<?php capture_mobile_menu(); ?>

		<!-- Header	-->
		<header id="header" class="cf">
			<div class="in">
				<section class="logo-box <?php echo ( is_active_sidebar( 'header-call-to-action-sidebar' ) ) ? 'logo-box-header-cta': 'logo-box-no-header-cta'; ?> <?php echo ( ! is_active_sidebar( 'header-call-to-action-sidebar' ) && ! has_nav_menu( 'top_nav' ) ) ? 'logo-box-full-width': false; ?>">
					<?php sds_logo(); ?>
					<?php sds_tagline(); ?>
				</section>

				<!--  Header Call to Action -->
				<aside class="header-cta-container header-call-to-action <?php echo ( is_active_sidebar( 'header-call-to-action-sidebar' ) ) ? 'widgets' : 'no-widgets'; ?>">
					<?php sds_header_call_to_action_sidebar(); // Header CTA Sidebar ?>
				</aside>
			</div>

			<nav class="primary-nav-container">
				<div class="in">
					<?php
						wp_nav_menu( array(
							'theme_location' => 'primary_nav',
							'container' => false,
							'menu_class' => 'primary-nav menu',
							'menu_id' => 'primary-nav',
							'fallback_cb' => 'sds_primary_menu_fallback'
						) );
					?>
				</div>
			</nav>
		</header>

		
		<div class="in">