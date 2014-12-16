<?php
	global $multipage;

	// Loop through posts
	if ( have_posts() ) :
		while ( have_posts() ) : the_post();
?>
	<section id="post-<?php the_ID(); ?>" <?php post_class( 'post single-post capture-post cf' ); ?>>
		<section class="post-container">
			<?php
				// Featured Image
				if ( has_post_thumbnail() ) :
			?>
					<a href="<?php the_permalink(); ?><?php echo( get_option( 'permalink_structure' ) ) ? 'lightbox/' : '&lightbox=true'; ?>" class="post-image-full-link post-image-zoom-link featured-image-full-link featured-image-zoom-link">
						<?php sds_featured_image( false, 'capture-1200x9999' ); ?>
						<!--[if gt IE 8]><!-->
							<section class="slideshow-overlay">
								<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="102.222px" height="67.146px" viewBox="0.857 16.177 102.222 67.146" enable-background="new 0.857 16.177 102.222 67.146" xml:space="preserve"><g><path fill="#FFFFFF" d="M20.08 83.323h63.663V16.177H20.08V83.323z M24.576 22.357h54.785v54.786H24.576V22.357z"/><rect x="94.466" y="21.722" fill="#FFFFFF" width="4.676" height="52.753"/><rect x="84.993" y="18.369" fill="#FFFFFF" width="8.248" height="61.942"/><rect x="100.382" y="25.393" fill="#FFFFFF" width="2.697" height="45.412"/><rect x="4.795" y="21.722" fill="#FFFFFF" width="4.677" height="52.753"/><rect x="10.696" y="18.369" fill="#FFFFFF" width="8.248" height="61.942"/><rect x="0.857" y="25.393" fill="#FFFFFF" width="2.698" height="45.412"/></g><g><path fill="#FFFFFF" d="M31.696 29.477V46.1l7.124-7.125l7.124 7.125l2.376-2.375l-7.124-7.124l7.124-7.125H31.696z"/><path fill="#FFFFFF" d="M72.241 29.477v16.421l-7.036-7.037l-7.037 7.037l-2.347-2.346l7.037-7.037l-7.037-7.038H72.241z"/><path fill="#FFFFFF" d="M31.696 70.024V53.6l7.038 7.04l7.037-7.04l2.346 2.35l-7.038 7.037l7.038 7.037H31.696z"/><path fill="#FFFFFF" d="M72.241 70.024V53.6l-7.036 7.04l-7.037-7.04l-2.347 2.35l7.037 7.034l-7.037 7.04H72.241z"/></g></svg>
							</section>
						<!--<![endif]-->
					</a>
			<?php
				endif;
			?>

			<?php get_template_part( 'capture', 'slideshow' ); // Capture Slideshow Markup ?>
			<?php get_template_part( '_', 'capture-slideshow' ); // Capture Slideshow Underscore Template ?>

			<section class="post-title-wrap cf <?php echo ( has_post_thumbnail() ) ? 'post-title-wrap-featured-image' : 'post-title-wrap-no-image'; ?>">
				<h1 class="post-title"><?php the_title(); ?></h1>
				<p class="post-date">
					<?php the_time( 'F jS, Y' ); ?>
				</p>
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

				<?php edit_post_link( __( 'Edit Post', 'capture' ) ); // Allow logged in users to edit ?>
			</article>

			<footer class="post-footer single-post-footer cf">
				<?php if ( $post->post_type !== 'attachment' ) : // Post Meta Data (tags, categories, etc...) ?>
					<section class="post-meta">
						<?php sds_post_meta(); ?>
					</section>
				<?php endif ?>
			</footer>

			<section id="post-author" class="post-author cf">
				<section class="post-author-inner cf">
					<header class="author-header">
						<figure class="author-avatar">
							<?php echo get_avatar( get_the_author_meta( 'ID' ), 128 ); ?>
						</figure>
					</header>

					<aside class="author-details author-content">
						<h3><?php echo get_the_author_meta( 'display_name' ); ?></h3>
						<a href="<?php echo get_the_author_meta( 'user_url' ); ?>"><?php echo get_the_author_meta( 'user_url' ); ?></a>
						<p><?php echo get_the_author_meta( 'description' ); ?></p>
						<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><?php _e( 'View more posts from this author', 'capture' ); ?></a>
					</aside>
				</section>
			</section>

			<section class="single-post-navigation-container post-navigation-container cf">
				<?php sds_single_post_navigation(); ?>
			</section>

			<section class="clear"></section>

			<section class="after-posts-widgets cf <?php echo ( is_active_sidebar( 'after-posts-sidebar' ) ) ? 'after-posts-widgets-active widgets' : 'no-widgets'; ?>">
				<?php sds_after_posts_sidebar(); ?>
			</section>

			<section class="clear"></section>

			<?php comments_template(); // Comments ?>
		</section>
	</section>
<?php
		endwhile;
	endif;
?>