<section id="capture-slideshow" class="capture-slideshow-container">
	<section class="loading-container loader-container">
		<section class="loading loader"><?php _e( 'Loading...', 'capture' ); ?></section>
	</section>
	<a href="#close" class="capture-slideshow-close close-slideshow capture-slideshow-ui"><?php _ex( 'X', 'Capture Slideshow close button', 'capture' ); ?></a>
	<section class="capture-slideshow-logo">
		<?php sds_logo(); ?>
	</section>

	<section class="capture-slideshow">
		<input type="hidden" class="capture-slideshow-current-post-id" value="<?php esc_attr_e( get_the_ID() ); ?>" />
	</section>
	<input type="hidden" class="capture-slideshow-initial-post-id" value="<?php esc_attr_e( get_the_ID() ); ?>" />

	<section class="capture-slideshow-nav">
		<a href="#prev" class="capture-slideshow-prev prev-image prev-photo capture-slideshow-ui">
			<!--[if gt IE 8]><!-->
				<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0" y="0" viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve"><path d="M33.249 49.889c0 0.711 0.242 1.423 0.728 2.004L60.64 83.876c1.103 1.326 3.077 1.505 4.401 0.398 c1.325-1.106 1.504-3.073 0.401-4.402L40.445 49.889l24.997-29.985c1.104-1.326 0.926-3.292-0.401-4.4 c-1.325-1.107-3.299-0.928-4.401 0.397L33.977 47.887C33.491 48.466 33.249 49.177 33.249 49.889z"/></svg>
			<!--<![endif]-->

			<!--[if lte IE 8]>
				<span class="svg">&lt;</span>
			<![endif]-->
		</a>
		<a href="#next" class="capture-slideshow-next next-image next-photo capture-slideshow-ui">
			<!--[if gt IE 8]><!-->
				<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0" y="0" viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve"><path d="M65.439 47.887L38.776 15.9c-1.103-1.325-3.076-1.505-4.401-0.397c-1.328 1.108-1.507 3.074-0.402 4.4l24.997 29.985 L33.974 79.872c-1.103 1.329-0.924 3.296 0.402 4.402c1.324 1.106 3.298 0.928 4.401-0.398l26.663-31.983 c0.485-0.581 0.728-1.293 0.728-2.004C66.167 49.177 65.925 48.466 65.439 47.887z"/></svg>
			<!--<![endif]-->

			<!--[if lte IE 8]>
				<span class="svg">&gt;</span>
			<![endif]-->
		</a>
	</section>
</section>