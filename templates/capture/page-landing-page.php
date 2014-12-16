<?php
/*
 * Template Name: Landing Page
 * This template is used for the display of landing pages.
 */

get_header( 'landing-page' ); ?>
	<section class="content-wrapper page-content full-content-wrapper landing-page-content-wrapper cf">
		<article class="content full-content cf">
			<?php get_template_part( 'loop', 'page-landing-page' ); // Loop - Landing Page ?>
		</article>
	</section>

<?php get_footer( 'landing-page' ); ?>