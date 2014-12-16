<header class="archive-title">
	<?php sds_archive_title(); ?>
</header>

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
				if ( has_post_thumbnail() )
					sds_featured_image( true );
			?>

			<section class="post-title-wrap cf <?php echo ( has_post_thumbnail() ) ? 'post-title-wrap-featured-image' : 'post-title-wrap-no-image'; ?>">
				<h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

				<?php if ( $post->post_type === 'post' ) : ?>
					<p class="post-date">
						<?php if ( strlen( get_the_title() ) > 0 ) : ?>
							<?php printf( __( 'Posted by %1$s On %2$s', 'capture' ) , '<a href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ) . '">' . get_the_author_meta( 'display_name' ) . '</a>', get_the_time( 'F jS, Y' ) ); ?>
						<?php else: // No title ?>
							<a href="<?php the_permalink(); ?>">
								<?php printf( __( 'Posted by %1$s On %2$s', 'capture' ) , get_the_author_meta( 'display_name' ), get_the_time( 'F jS, Y' ) ); ?>
							</a>
						<?php endif; ?>
					</p>
				<?php endif; ?>
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
	else:
?>
	<section class="no-results no-posts post">
		<section class="post-container">
			<article class="post-content cf">
				<h1 class="page-title"><?php _e( 'No Results', 'capture' ); ?></h1>

				<?php sds_no_posts(); ?>
			</article>
		</section>
	</section>
<?php
	endif;
?>

<?php get_template_part( 'capture', 'slideshow' ); // Capture Slideshow Markup ?>
<?php get_template_part( '_', 'capture-slideshow' ); // Capture Slideshow Underscore Template ?>