<?php
	global $multipage;

	// Loop through posts
	if ( have_posts() ) :
		while ( have_posts() ) : the_post();
?>
	<section id="post-<?php the_ID(); ?>" <?php post_class( 'post single-attachment single-attachment-image attachment-image single-image cf' ); ?>>
		<section class="post-container">
			<section class="post-title-wrap cf <?php echo ( has_post_thumbnail() ) ? 'post-title-wrap-featured-image' : 'post-title-wrap-no-image'; ?>">
				<h1 class="post-title"><?php the_title(); ?></h1>
			</section>

			<article class="post-content cf">
				<p>
					<?php
						$metadata = wp_get_attachment_metadata();
						printf( '<span class="meta-prep meta-prep-entry-date">Published </span> <span class="entry-date"><time class="entry-date" datetime="%1$s">%2$s</time></span> at <a href="%3$s" title="Link to full-size image">%4$s &times; %5$s</a> in <a href="%6$s" title="Return to %7$s" rel="gallery">%8$s</a>.',
							esc_attr( get_the_date( 'c' ) ),
							esc_html( get_the_date() ),
							esc_url( wp_get_attachment_url() ),
							$metadata['width'],
							$metadata['height'],
							esc_url( get_permalink( $post->post_parent ) ),
							esc_attr( strip_tags( get_the_title( $post->post_parent ) ) ),
							get_the_title( $post->post_parent )
						);
					?>
				</p>

				<a href="<?php echo get_permalink( $post->post_parent ); ?>" class="button back-to-post"><?php printf( __( '&#8592; Back to %1$s', 'capture' ), get_the_title( $post->post_parent ) ); ?></a>

				<section class="attachment">
					<?php
					/**
					 * Grab the IDs of all the image attachments in a gallery so we can get the URL of the next adjacent image in a gallery,
					 * or the first image (if we're looking at the last image in a gallery), or, in a gallery of one, just the link to that image file
					 */
					$attachments = array_values( get_children( array( 'post_parent' => $post->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID' ) ) );
					foreach ( $attachments as $k => $attachment )
						if ( $attachment->ID == $post->ID )
							break;

					$k++;
					// If there is more than 1 attachment in a gallery
					if ( count( $attachments ) > 1 ) :
						if ( isset( $attachments[ $k ] ) ) :
							// get the URL of the next image attachment
							$next_attachment_url = get_attachment_link( $attachments[ $k ]->ID );
						else :
							// or get the URL of the first image attachment
							$next_attachment_url = get_attachment_link( $attachments[ 0 ]->ID );
						endif;
					else :
						// or, if there's only 1 image, get the URL of the image
						$next_attachment_url = wp_get_attachment_url();
					endif;
					?>

					<a href="<?php echo esc_url( $next_attachment_url ); ?>" title="<?php the_title_attribute(); ?>" rel="attachment">
						<?php echo wp_get_attachment_image( $post->ID, 'large' ); ?>
					</a>

					<?php if ( ! empty( $post->post_excerpt ) ) : ?>
						<section class="entry-caption">
							<?php the_excerpt(); ?>
						</section>
					<?php endif; ?>
				</section>

				<section class="entry-description">
					<?php the_content(); ?>

					<section class="clear"></section>

					<?php if ( $multipage ) : ?>
						<section class="single-post-navigation single-post-pagination wp-link-pages">
							<?php wp_link_pages(); ?>
						</section>
					<?php endif; ?>

					<?php edit_post_link( __( 'Edit Attachment', 'capture' ) ); // Allow logged in users to edit ?>
				</section>

				<section class="clear"></section>

				<section class="single-post-navigation-container post-navigation-container cf">
					<?php sds_single_image_navigation(); ?>
				</section>
			</article>

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

			<?php comments_template(); // Comments ?>
		</section>
	</section>
<?php
		endwhile;
	endif;
?>