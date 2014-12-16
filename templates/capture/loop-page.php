<?php
	global $multipage;

	// Loop through posts
	if ( have_posts() ) :
		while ( have_posts() ) : the_post();
?>
	<section id="post-<?php the_ID(); ?>" <?php post_class( 'post single-page cf' ); ?>>
		<section class="post-container">
			<?php
				// Featured Image
				if ( has_post_thumbnail() )
					sds_featured_image( false, 'capture-1200x9999' );
			?>

			<?php get_template_part( 'capture', 'slideshow' ); // Capture Slideshow Markup ?>
			<?php get_template_part( '_', 'capture-slideshow' ); // Capture Slideshow Underscore Template ?>

			<section class="post-title-wrap cf <?php echo ( has_post_thumbnail() ) ? 'post-title-wrap-featured-image' : 'post-title-wrap-no-image'; ?>">
				<h1 class="post-title"><?php the_title(); ?></h1>
			</section>

			<article class="post-content cf">
				<?php the_content(); ?>

				<section class="clear"></section>

				<?php if ( $multipage ) : ?>
					<section class="single-post-navigation single-post-pagination wp-link-pages">
						<?php wp_link_pages(); ?>
					</section>
				<?php endif; ?>

				<section class="clear"></section>

				<?php edit_post_link( __( 'Edit Page', 'capture' ) ); // Allow logged in users to edit ?>
			</article>

			<section class="clear"></section>

			<?php comments_template(); // Comments ?>
		</section>
	</section>
<?php
		endwhile;
	endif;
?>