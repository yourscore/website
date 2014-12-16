<?php
	// Loop through posts
	if ( have_posts() ) :
		while ( have_posts() ) : the_post();
?>
	<?php get_template_part( 'yoast', 'breadcrumbs' ); // Yoast Breadcrumbs ?>

	<section id="post-<?php the_ID(); ?>" <?php post_class( 'post cf' ); ?>>
		<section class="post-container">
			<?php
				// Featured Image
				if ( has_post_thumbnail() ) :
			?>
				<a href="<?php the_permalink(); ?><?php echo( get_option( 'permalink_structure' ) ) ? 'lightbox/' : '&lightbox=true'; ?>" class="post-image-full-link post-image-zoom-link featured-image-full-link featured-image-zoom-link">
					<?php sds_featured_image( false ); ?>
					<!--[if gt IE 8]><!-->
						<section class="slideshow-overlay">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="102.222px" height="67.146px" viewBox="0.857 16.177 102.222 67.146" enable-background="new 0.857 16.177 102.222 67.146" xml:space="preserve"><g><path fill="#FFFFFF" d="M20.08 83.323h63.663V16.177H20.08V83.323z M24.576 22.357h54.785v54.786H24.576V22.357z"/><rect x="94.466" y="21.722" fill="#FFFFFF" width="4.676" height="52.753"/><rect x="84.993" y="18.369" fill="#FFFFFF" width="8.248" height="61.942"/><rect x="100.382" y="25.393" fill="#FFFFFF" width="2.697" height="45.412"/><rect x="4.795" y="21.722" fill="#FFFFFF" width="4.677" height="52.753"/><rect x="10.696" y="18.369" fill="#FFFFFF" width="8.248" height="61.942"/><rect x="0.857" y="25.393" fill="#FFFFFF" width="2.698" height="45.412"/></g><g><path fill="#FFFFFF" d="M31.696 29.477V46.1l7.124-7.125l7.124 7.125l2.376-2.375l-7.124-7.124l7.124-7.125H31.696z"/><path fill="#FFFFFF" d="M72.241 29.477v16.421l-7.036-7.037l-7.037 7.037l-2.347-2.346l7.037-7.037l-7.037-7.038H72.241z"/><path fill="#FFFFFF" d="M31.696 70.024V53.6l7.038 7.04l7.037-7.04l2.346 2.35l-7.038 7.037l7.038 7.037H31.696z"/><path fill="#FFFFFF" d="M72.241 70.024V53.6l-7.036 7.04l-7.037-7.04l-2.347 2.35l7.037 7.034l-7.037 7.04H72.241z"/></g></svg>
						</section>
					<!--<![endif]-->
				</a>
			<?php
				endif;
			?>

			<section class="post-title-wrap cf <?php echo ( has_post_thumbnail() ) ? 'post-title-wrap-featured-image' : 'post-title-wrap-no-image'; ?>">
				<h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<p class="post-date">
					<?php if ( strlen( get_the_title() ) > 0 ) : ?>
						<?php printf( __( 'Posted by %1$s On %2$s', 'capture' ) , '<a href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ) . '">' . get_the_author_meta( 'display_name' ) . '</a>', get_the_time( 'F jS, Y' ) ); ?>
					<?php else: // No title ?>
						<a href="<?php the_permalink(); ?>">
							<?php printf( __( 'Posted by %1$s On %2$s', 'capture' ) , get_the_author_meta( 'display_name' ), get_the_time( 'F jS, Y' ) ); ?>
						</a>
					<?php endif; ?>
				</p>
			</section>

			<article class="post-content cf">
				<?php
					// Display excerpt if one has been specifically set by post author
					if ( ! empty( $post->post_excerpt ) ) :
						the_excerpt();
				?>
						<p><a href="<?php the_permalink(); ?>" class="more-link"><?php _e( 'Read More', 'capture' ); ?></a></p>
				<?php
					else :
						the_content( __( 'Read More', 'capture' ) );
					endif;
				?>
			</article>

			<footer class="post-footer cf">
				<section class="comments-link-container">
					<?php if ( comments_open() && ! post_password_required() && ( int ) $post->comment_count ) : // Comments exist ?>
						<a href="<?php comments_link(); ?>" class="comments-link"><span class="fa fa-comment"></span> <?php printf( _n( '1 Observation', '%1$s Observations', get_comments_number(), 'capture' ), get_comments_number() ); ?></a>
					<?php elseif ( comments_open() ): // No Comments ?>
						<a href="<?php comments_link(); ?>" class="comments-link"><span class="fa fa-comment"></span> <?php _e( 'Leave Your Observation', 'capture' ); ?></a>
					<?php else: // Comments Disabled ?>
						<span class="comments-link"><span class="fa fa-minus-circle"></span> <?php _e( 'Observations Closed', 'capture' ); ?></span>
					<?php endif; ?>
				</section>

				<section class="post-footer-right">
					<?php do_action( 'capture_post_footer_right', $post ); ?>
				</section>
			</footer>
		</section>
	</section>
<?php
		endwhile;
	else : // No posts
?>
	<section class="no-results no-posts post">
		<section class="post-container">
			<article class="post-content cf">
				<h1 class="page-title"><?php _e( 'No Results', 'capture' ); ?></h1>

				<?php sds_no_posts(); ?>
			</article>
		</section>
	</section>
<?php endif; ?>

<?php get_template_part( 'capture', 'slideshow' ); // Capture Slideshow Markup ?>
<?php get_template_part( '_', 'capture-slideshow' ); // Capture Slideshow Underscore Template ?>